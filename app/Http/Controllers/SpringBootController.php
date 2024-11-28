<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SpringBootController extends Controller
{
    public function generarProyecto(Request $request)
    {
        //return $request;
        $titulo = $request->input('titulo');
        $diagramCode = $request->input('diagram');
        $descripcion = $request->input('descripcion');
        $ruta = storage_path("app/public/{$titulo}");
        if (File::exists($ruta)) {
            File::deleteDirectory($ruta); // Elimina la carpeta del proyecto
        }
         $response = Http::get('https://start.spring.io/starter.zip', [
        'type' => 'maven-project',
        'language' => 'java',
        'bootVersion' => '3.3.4',
        'baseDir' => $titulo,
        'groupId' => 'com.example',
        'artifactId' => $titulo,
        'name' => $titulo,
        'description' => $descripcion,
        'packageName' => "com.example.$titulo",
        'dependencies' => 'web,data-jpa,postgresql'
    ]);

    // Guardar el archivo ZIP en una ruta temporal
    $zipFilePath = storage_path("app/public/{$titulo}.zip");
    file_put_contents($zipFilePath, $response->body());

    // Extraer el archivo ZIP en una carpeta temporal
    $extractPath = storage_path("app/public/{$titulo}");
    $zip = new \ZipArchive;
    if ($zip->open($zipFilePath) === TRUE) {
        $zip->extractTo($extractPath);
        $zip->close();
    }

    // Llamar a la función para generar las carpetas y archivos extra
    $this->generarCRUD($extractPath, $titulo,$diagramCode);
    $zipFinalPath = $this->comprimirProyecto($extractPath, $titulo);
    // Devolver el archivo ZIP extraído como descarga
    return response()->json(['zipUrl' => url('/download-zip?file=' . urlencode($zipFinalPath))]);
        //return $zipFinalPath;
    }

    private function comprimirProyecto($rutaProyecto, $nombreProyecto)
    {
        $zip = new \ZipArchive;
        $zipFilePath = "{$rutaProyecto}.zip";

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rutaProyecto),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                // Ignorar carpetas
                if (!$file->isDir()) {
                    // Obtener la ruta relativa del archivo en el ZIP
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rutaProyecto) + 1);

                    // Añadir el archivo al ZIP
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            return $zipFilePath;
        } else {
            throw new \Exception("No se pudo crear el archivo ZIP.");
        }
    }

    // Función para generar el CRUD a partir del diagrama Mermaid
    private function generarCRUD($extractPath, $nombreProyecto, $diagramCode)
    {
        // Crear directorios del proyecto en la ruta del servidor
        $rutaProyecto = storage_path("app/public/{$nombreProyecto}/{$nombreProyecto}/src/main/java/com/example/{$nombreProyecto}");
        //$rutaProyecto = "{$extractPath}/src/main/java/com/example/{$nombreProyecto}";

        if (!file_exists($rutaProyecto)) {
            mkdir($rutaProyecto, 0777, true);
        }

        // Carpetas del proyecto
        $carpetas = ['model', 'repository', 'service', 'controller'];
        foreach ($carpetas as $carpeta) {
            mkdir("{$rutaProyecto}/{$carpeta}", 0777, true);
        }

        // Analizar el diagrama Mermaid para extraer tablas/clases
        $tablas = $this->analizarMermaid($diagramCode);
        
        // Generar clases de modelo
        foreach ($tablas as $tabla) {
            $modelo = $this->generarModelo($tabla, $nombreProyecto);
            file_put_contents("{$rutaProyecto}/model/{$tabla['nombre']}.java", $modelo);
        }

        // Generar repositorios, servicios y controladores
        foreach ($tablas as $tabla) {
            $repositorio = $this->generarRepository($tabla, $nombreProyecto);
            file_put_contents("{$rutaProyecto}/repository/{$tabla['nombre']}Repository.java", $repositorio);

            $servicio = $this->generarService($tabla, $nombreProyecto);
            file_put_contents("{$rutaProyecto}/service/{$tabla['nombre']}Service.java", $servicio);

            $controlador = $this->generarController($tabla, $nombreProyecto);
            file_put_contents("{$rutaProyecto}/controller/{$tabla['nombre']}Controller.java", $controlador);
        }

        // Devolver el path del proyecto generado
        return $rutaProyecto;
    }

    // Función auxiliar para analizar el código Mermaid y extraer las tablas/clases
    private function analizarMermaid($diagramCode)
        {
            $tablas = [];
        
            // Expresión regular para detectar tablas (clases)
            $tablaRegex = '/(\w+)\s*{\s*([^}]*)}/m';
            // Expresión regular para detectar campos con PK o FK
            $campoRegex = '/(\w+)\s+(\w+)\s*(PK|FK)?/';
        
            // Buscar todas las tablas (clases) y sus campos
            if (preg_match_all($tablaRegex, $diagramCode, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $nombreTabla = $match[1];
                    $contenidoTabla = $match[2];
                    $tablas[$nombreTabla] = ['nombre' => $nombreTabla, 'campos' => []];
        
                    // Buscar campos dentro de la tabla
                    if (preg_match_all($campoRegex, $contenidoTabla, $campoMatches, PREG_SET_ORDER)) {
                        foreach ($campoMatches as $campoMatch) {
                            $tipoCampo = $campoMatch[1];
                            $nombreCampo = $campoMatch[2];
                            $constraint = isset($campoMatch[3]) ? $campoMatch[3] : null;
        
                            // Convertir tipo de campo a su equivalente Java
                            switch (strtolower($tipoCampo)) {
                                case 'int':
                                    $tipoCampo = 'int';
                                    break;
                                case 'string':
                                    $tipoCampo = 'String';
                                    break;
                                // Agrega más conversiones de tipos según lo que necesitas
                                default:
                                    $tipoCampo = ucfirst($tipoCampo);  // Default a PascalCase
                                    break;
                            }
        
                            // Definir el tipo y constraint (si es PK o FK)
                            $tablas[$nombreTabla]['campos'][$nombreCampo] = [
                                'tipo' => $tipoCampo,
                                'constraint' => $constraint
                            ];
                        }
                    }
                }
            }
        
            // Retornar la estructura de tablas y campos
            return $tablas;
        }

        // Genera el código Java de la clase modelo basado en los datos de la tabla
        private function generarModelo($tabla, $nombreProyecto)
        {
            $campos = '';
            $gettersSetters = '';

            // Convertir tipos Mermaid a tipos de Java
            foreach ($tabla['campos'] as $nombreCampo => $atributo) {
                $tipoDato = $this->convertirTipoJava($atributo['tipo']);  // Convertimos los tipos a tipos de Java
                $campos .= "    private {$tipoDato} {$nombreCampo};\n";
                
                // Generar los métodos getters y setters
                $gettersSetters .= $this->generarGetterSetter($nombreCampo, $tipoDato);
            }

            // Retornamos el contenido del archivo de modelo
            return <<<EOT
        package com.example.{$nombreProyecto}.model;

        import javax.persistence.Entity;
        import javax.persistence.Id;

        @Entity
        @Table(name = "{$tabla['nombre']}")
        public class {$tabla['nombre']} {

            @Id
            
        $campos
            // Getters y Setters
        $gettersSetters
        }
        EOT;
        }

        private function convertirTipoJava($tipoMermaid)
        {
            // Convertir los tipos Mermaid a tipos Java
            switch (strtolower($tipoMermaid)) {
                case 'int':
                    return 'int';
                case 'varchar':
                    return 'String';
                case 'float':
                    return 'float';
                default:
                    return 'String';  // Tipo por defecto si no se reconoce
            }
        }

        private function generarGetterSetter($campo, $tipo)
        {
            // Formatear el nombre del campo para los métodos (primera letra en mayúscula)
            $campoCapitalizado = ucfirst($campo);

            // Crear los métodos getter y setter
            return <<<EOT
            public {$tipo} get{$campoCapitalizado}() {
                return this.{$campo};
            }

            public void set{$campoCapitalizado}({$tipo} {$campo}) {
                this.{$campo} = {$campo};
            }

        EOT;
        }


    // Generar código de repositorio
    private function generarRepository($tabla, $nombreProyecto) 
    {
        return <<<EOT
        package com.example.{$nombreProyecto}.repository;

        import org.springframework.data.jpa.repository.JpaRepository;
        import com.example.{$nombreProyecto}.model.{$tabla['nombre']};

        public interface {$tabla['nombre']}Repository extends JpaRepository<{$tabla['nombre']}, Long> {
        }
        EOT;
    }


    // Generar código de servicio
    private function generarService($tabla, $nombreProyecto) 
{
    return <<<EOT
    package com.example.{$nombreProyecto}.service;

    import com.example.{$nombreProyecto}.model.{$tabla['nombre']};
    import com.example.{$nombreProyecto}.repository.{$tabla['nombre']}Repository;
    import org.springframework.beans.factory.annotation.Autowired;
    import org.springframework.stereotype.Service;

    import java.util.List;
    import java.util.Optional;

    @Service
    public class {$tabla['nombre']}Service {

        @Autowired
        private {$tabla['nombre']}Repository {$tabla['nombre']}Repository;

        public List<{$tabla['nombre']}> findAll() {
            return {$tabla['nombre']}Repository.findAll();
        }

        public Optional<{$tabla['nombre']}> findById(Long id) {
            return {$tabla['nombre']}Repository.findById(id);
        }

        public {$tabla['nombre']} save({$tabla['nombre']} entity) {
            return {$tabla['nombre']}Repository.save(entity);
        }

        public void deleteById(Long id) {
            {$tabla['nombre']}Repository.deleteById(id);
        }
    }
    EOT;
}


    // Generar código de controlador
    private function generarController($tabla, $nombreProyecto) 
    {
        return <<<EOT
        package com.example.{$nombreProyecto}.controller;

        import com.example.{$nombreProyecto}.model.{$tabla['nombre']};
        import com.example.{$nombreProyecto}.service.{$tabla['nombre']}Service;
        import org.springframework.beans.factory.annotation.Autowired;
        import org.springframework.web.bind.annotation.*;

        import java.util.List;
        import java.util.Optional;

        @RestController
        @RequestMapping("/api/{$tabla['nombre']}")
        public class {$tabla['nombre']}Controller {

            @Autowired
            private {$tabla['nombre']}Service {$tabla['nombre']}Service;

            @GetMapping
            public List<{$tabla['nombre']}> getAll() {
                return {$tabla['nombre']}Service.findAll();
            }

            @GetMapping("/{id}")
            public Optional<{$tabla['nombre']}> getById(@PathVariable Long id) {
                return {$tabla['nombre']}Service.findById(id);
            }

            @PostMapping
            public {$tabla['nombre']} create(@RequestBody {$tabla['nombre']} entity) {
                return {$tabla['nombre']}Service.save(entity);
            }

            @PutMapping("/{id}")
            public {$tabla['nombre']} update(@PathVariable Long id, @RequestBody {$tabla['nombre']} entity) {
                return {$tabla['nombre']}Service.save(entity);
            }

            @DeleteMapping("/{id}")
            public void delete(@PathVariable Long id) {
                {$tabla['nombre']}Service.deleteById(id);
            }
        }
        EOT;
    }


}
