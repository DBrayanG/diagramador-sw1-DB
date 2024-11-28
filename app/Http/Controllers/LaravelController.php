<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


class LaravelController extends Controller
{
    public function generarProyectoLaravel(Request $request)
    {
        set_time_limit(1200);
        $titulo = $request->input('titulo');
        $diagramCode = $request->input('diagram');
        $descripcion = $request->input('descripcion');
        
        // Definir una ruta fuera del proyecto Laravel actual
        $rutaBase = dirname(base_path()); // Esto toma el directorio padre del proyecto actual
        $rutaProyecto = "{$rutaBase}/proyectos_generados/{$titulo}L";

        // Elimina la carpeta si ya existe
        if (File::exists($rutaProyecto)) {
            File::deleteDirectory($rutaProyecto);
        }

        // Ejecutar el comando de Composer para crear el proyecto Laravel
        $output = shell_exec("composer create-project --prefer-dist laravel/laravel \"{$rutaProyecto}\"");

        // Esperar a que el proyecto se haya creado
        if (File::exists("{$rutaProyecto}/artisan")) {
            // Analizar el código Mermaid
            $tablas = $this->analizarMermaid($diagramCode);

            // Generar los modelos, migraciones y controladores
            $this->generarModelosConRecursos($tablas, $rutaProyecto);

            // Comprimir el proyecto
            $zipFinalPath = $this->comprimirProyecto($rutaProyecto, $titulo);

            // Retornar la URL para descargar el archivo ZIP
            return response()->json(['zipUrl' => url('/download-zip?file=' . urlencode($zipFinalPath))]);
        } else {
            return response()->json(['mensaje' => 'Error al crear el proyecto Laravel'], 500);
        }
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
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rutaProyecto) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            return $zipFilePath;
        } else {
            throw new \Exception("No se pudo crear el archivo ZIP.");
        }
    }


    private function analizarMermaid($diagramCode)
    {
        $tablas = [];
    
        // Expresión regular para detectar tablas (clases)
        $tablaRegex = '/(\w+)\s*{\s*([^}]*)}/m';
    
        // Buscar todas las tablas (clases)
        if (preg_match_all($tablaRegex, $diagramCode, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $nombreTabla = $match[1];
                // Agregar solo el nombre de la tabla al array
                $tablas[] = $nombreTabla; // Asegúrate de que solo agregue el nombre como string
            }
        }
    
        // Retornar la lista de nombres de tablas
        return $tablas; // Esto ahora es un array de strings
    }   


    public function generarModelosConRecursos($tablas, $rutaProyecto)
    {       
        
        foreach ($tablas as $nombreModelo) {
            $nombreModelo = ucfirst($nombreModelo);

            Log::info("Ejecutando comando: make:model {$nombreModelo}");

            // Cambia el directorio de trabajo al proyecto de Laravel
            chdir($rutaProyecto);

            // 1. Crear el modelo
            $resultadoModelo = shell_exec("php artisan make:model {$nombreModelo}");
            Log::info($resultadoModelo);

            // 2. Crear la migración
            $resultadoMigracion = shell_exec("php artisan make:migration create_{$nombreModelo}_table");
            Log::info($resultadoMigracion);

            // 3. Crear el controlador
            $resultadoControlador = shell_exec("php artisan make:controller {$nombreModelo}Controller --resource");
            Log::info($resultadoControlador);            
            
        }
        
        shell_exec("composer require jeroennoten/laravel-adminlte");
        shell_exec("php artisan adminlte:install");
        shell_exec("composer require laravel/ui");
        shell_exec("php artisan ui bootstrap --auth");
        shell_exec("composer require livewire/livewire");

        foreach($tablas as $nombre){
            //4. crear los componentes livewire
            $resultadoControlador = shell_exec("php artisan make:livewire {$nombre}");
            Log::info($resultadoControlador);
        }

    }

}
