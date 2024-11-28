<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagrama de Secuencia</title>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    <!-- Agrega el enlace a los archivos CSS de Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Agrega el enlace a los archivos JavaScript de Bootstrap (jQuery y Popper.js) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="{{ asset('css/gojs/style.css') }}" />
    <style>
        /* Estilos personalizados para la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #444;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #333;
            color: white;
        }

        td {
            background-color: #2b2b2b;
            color: white;
        }

        code {
            background-color: #444;
            padding: 2px 5px;
            border-radius: 4px;
            color: #ffcc00;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar navbar-dark bg-info justify-content-center">
        <div class="container">
            <a class="navbar-brand" href="#">Diagramador</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ url('/home') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ url('/diagramas') }}">Volver</a>
                    </li>
                    <form id="guardarDiagramaForm" method="post" action="{{ url('/diagramas/pizarra') }}">
                        <input type="hidden" name="diagram_id" value="{{ $diagram->id }}">
                        @csrf
                        <input type="hidden" name="contenidoJson" id="mySavedModel" value="">
                        <button class="btn btn-sm btn-success" type="button" id="guardarDiagramaButton">Guardar
                            Diagrama</button>
                    </form>

                    <li class="nav-item">
                        <a class="nav-link active" href="#" id="manual" data-bs-toggle="modal" data-bs-target="#tablaModal">Manual</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#" id="manual" data-bs-toggle="modal" data-bs-target="#tabla">Relacion muchos a muchos</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="md:flex flex-col md:flex-row md:min-h-screen w-full max-w-screen-xl mx-auto">
        <script src="{{ asset('js/gojs/go.js') }}"></script>
        <div id="allSampleContent" class="p-4 w-full">
            <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro' rel='stylesheet' type='text/css'>
            <div class="language-mermaid" id="sample" style="display: flex">
                <textarea id="mermaid-code" style="width: 25%; height: 450px;"></textarea>
                <div id="mermaid-container" style="border: solid 1px black; width: 75%; height: 450px">
                    <pre class="mermaid" id="prueba"></pre>
                </div>
            </div>
            <div>
                <div>
                    
                    <button id="generar"">Descargar Script SQL</button>
                    <button id="descargarCSV">Descargar CSV</button>
                    <button id="generateSpringBootProject">Generar Proyecto Spring Boot</button>
                    <button id="generateLaravelProject">Generar Proyecto Laravel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar la tabla -->
    <div class="modal fade" id="tablaModal" tabindex="-1" aria-labelledby="tablaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tablaModalLabel">Relaciones de Base de Datos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Valor (izquierda)</th>
                                <th>Valor (derecha)</th>
                                <th>Significado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>|o</code></td>
                                <td><code>o|</code></td>
                                <td>Cero o uno</td>
                            </tr>
                            <tr>
                                <td><code>||</code></td>
                                <td><code>||</code></td>
                                <td>Exactamente uno</td>
                            </tr>
                            <tr>
                                <td><code>}o</code></td>
                                <td><code>o{</code></td>
                                <td>Cero o más (sin límite superior)</td>
                            </tr>
                            <tr>
                                <td><code>}|</code></td>
                                <td><code>|{</code></td>
                                <td>Uno o más (sin límite superior)</td>
                            </tr>
                            <tr>
                                <td><code>||</code></td>
                                <td><code>o|</code></td>
                                <td>Exactamente uno relacionado con cero o uno</td>
                            </tr>
                            <tr>
                                <td><code>|}</code></td>
                                <td><code>{o</code></td>
                                <td>Cero o más relacionados con uno o más</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="tabla" tabindex="-1" aria-labelledby="tablaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tablaModalLabel">Ejemplo de Relaciones en Base de Datos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6><strong>Relación Muchos a Muchos</strong></h6>
                    <p>Una relación muchos a muchos ocurre cuando varios registros de una tabla están asociados con varios registros de otra tabla. Esta relación se maneja mediante una tabla intermedia.</p>
                    
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tabla</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Estudiantes</strong></td>
                                <td>Contiene los datos de los estudiantes, con un campo clave primaria (<code>id</code>).</td>
                            </tr>
                            <tr>
                                <td><strong>Cursos</strong></td>
                                <td>Contiene los datos de los cursos, con un campo clave primaria (<code>id</code>).</td>
                            </tr>
                            <tr>
                                <td><strong>Inscripciones</strong> (Tabla intermedia)</td>
                                <td>
                                    Almacena las relaciones entre estudiantes y cursos, con dos claves foráneas:
                                    <ul>
                                        <li><code>id_estudiante</code> (FK hacia Estudiantes)</li>
                                        <li><code>id_curso</code> (FK hacia Cursos)</li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
    
                    <h6><strong>Representación en Diagrama</strong></h6>
                    <pre><code class="language-mermaid">
                
                    Estudiante {
                        int id PK
                        string nombre
                    }
                    Curso {
                        int id PK
                        string titulo
                    }
                    Inscripcion {
                        int estudiante_id FK
                        int curso_id FK
                    }
                    
                    Estudiante ||--o{ Inscripcion : "inscribe"
                    Curso ||--o{ Inscripcion : "tiene"
                    </code></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script type="module">
        import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
        mermaid.initialize({ securityLevel: 'loose', theme: 'neutral', suppressErrorRendering: 'true' });

        // Función para actualizar el diagrama cuando el usuario haga cambios en el textarea
        document.getElementById('mermaid-code').addEventListener('input', function () {
            const newDiagramCode = document.getElementById('mermaid-code').value;
            const diagramContainer = document.getElementById('mermaid-container');

            // Actualizar el contenido del contenedor del diagrama con el nuevo código
            const inicio = 'erDiagram ';
            diagramContainer.innerHTML = '<pre class="mermaid">' + inicio + newDiagramCode + '</pre>';

            // Vuelve a inicializar Mermaid para renderizar el nuevo diagrama
            mermaid.run(undefined, diagramContainer);
        });

        function saveDiagramAutomatically() {
            var diagramCode = document.getElementById('mermaid-code').value;
            console.log(diagramCode);

            var contenidoJson = { diagramCode };
            console.log(contenidoJson);

            $.ajax({
                type: 'POST',
                url: $('#guardarDiagramaForm').attr('action'),
                data: {
                    _token: $('input[name="_token"]').val(),
                    diagram_id: $('input[name="diagram_id"]').val(),
                    contenidoJson: contenidoJson
                },
                success: function (response) {
                    console.log('Diagrama guardado con éxito.');
                    alert('El diagrama ha sido guardado correctamente.');
                },
                error: function (error) {
                    console.error('Error al guardar el diagrama:', error);
                    alert('Ocurrió un error al guardar el diagrama.');
                }
            });
        }

        document.getElementById('guardarDiagramaButton').addEventListener('click', function () {
            saveDiagramAutomatically();
        });

        document.addEventListener('DOMContentLoaded', function () {
            var diagramId = $('input[name="diagram_id"]').val();
            if (!diagramId) {
                console.error('No se encontró el ID del diagrama.');
                return;
            }

            $.ajax({
                type: 'GET',
                url: '/diagramas/' + diagramId,
                success: function (response) {
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (error) {
                            console.error('Error al parsear el JSON:', error);
                        }
                    }
                    if (response) {
                        var contenidoJson = response.diagramCode;
                        document.getElementById('mermaid-code').value = contenidoJson;

                        const diagramContainer = document.getElementById('mermaid-container');
                        const inicio = 'erDiagram ';
                        diagramContainer.innerHTML = '<pre class="mermaid">' + inicio + contenidoJson + '</pre>';

                        mermaid.initialize({ startOnLoad: true, theme: 'neutral' });
                        mermaid.run(undefined, diagramContainer);

                        console.log('Diagrama cargado y renderizado.');
                    } else {
                        console.error('No se encontró contenido JSON en la respuesta.');
                    }
                },
                error: function (error) {
                    console.error('Error al cargar el diagrama:', error);
                    alert('Ocurrió un error al cargar el diagrama.');
                }
            });
        });

        document.getElementById('generar').addEventListener('click',function(){
            descargarSQL();
        });

        document.getElementById('descargarCSV').addEventListener('click',function(){
            descargarCSV();
        });


        // Obtener el contenido JSON del textarea de Mermaid
            //const contenidoJson = document.getElementById('mermaid-code').value;
            var titulo = {!! json_encode($diagram->titulo) !!};
            // Función para generar script SQL 
            function generarSQL(js) 
            {
                // Almacenar el script SQL
                let sqlScript = '';
                let foreignKeys = [];

                // Crear la base de datos con el nombre que viene en 'titulo'
                sqlScript += `CREATE DATABASE ${titulo};\n\n`;

                // Dividimos el contenido por líneas
                const lines = js.split('\n');
                let tableName = '';
                let columns = [];
                
                lines.forEach(line => {
                    line = line.trim();

                    // Si encontramos una línea que define una tabla
                    if (line.endsWith('{')) {
                        tableName = line.replace('{', '').trim();  // Extraemos el nombre de la tabla
                        sqlScript += `CREATE TABLE ${tableName} (\n`;
                    } 
                    // Si encontramos el cierre de una tabla
                    else if (line.endsWith('}')) {
                        sqlScript += columns.join(',\n') + '\n);\n\n';  // Agregamos las columnas y cerramos la tabla
                        columns = [];  // Reiniciamos para la siguiente tabla
                    } 
                    // Si encontramos una columna en la tabla
                    else if (line.includes(' ')) {
                        const [type, name, constraint] = line.split(' ').filter(Boolean);  // Separamos por espacios
                        let sqlType = '';
                        
                        // Convertir los tipos de Mermaid a tipos de PostgreSQL
                        switch (type.toLowerCase()) {
                            case 'int':
                                sqlType = 'INTEGER';
                                break;
                            case 'string':
                                sqlType = 'TEXT';
                                break;
                            case 'float':
                                sqlType = 'FLOAT';
                                break;
                            case 'date':
                                sqlType = 'DATE';
                                break;
                            case 'varchar':
                                sqlType = 'VARCHAR';
                                break;
                            default:
                                sqlType = 'TEXT';  // Por defecto TEXT
                                break;
                        }

                        // Si hay una constraint, como 'PK' (primary key), la agregamos
                        let columnDef = `${name} ${sqlType}`;
                        if (constraint === 'PK') {
                            columnDef += ' PRIMARY KEY';
                        } else if (constraint === 'FK') {
                            // Guardamos la relación de foreign key para agregarla al final
                            foreignKeys.push({ table: tableName, column: name });
                        }

                        columns.push(columnDef);
                    }
                });

                // Agregar las llaves foráneas al script
                foreignKeys.forEach(fk => {
                    let referencedTable = fk.column.replace('_id', '');  // Asumimos que la foreign key se refiere a la tabla base del id
                    sqlScript += `ALTER TABLE ${fk.table} ADD CONSTRAINT fk_${fk.table}_${fk.column} FOREIGN KEY (${fk.column}) REFERENCES ${referencedTable}(id);\n\n`;
                });

                console.log(sqlScript);
                return sqlScript;
            }


            function descargarSQL() {
                const contenidoJson = document.getElementById('mermaid-code').value; // Obtener el contenido del textarea
                const scriptSQL = generarSQL(contenidoJson);  // Generar el script SQL

                // Crear un Blob con el contenido del script
                const blob = new Blob([scriptSQL], { type: 'text/sql' });
                const link = document.createElement('a');
                
                // Nombre del archivo SQL (puedes modificarlo según lo que necesites)
                link.download = `${titulo}.sql`;  
                link.href = window.URL.createObjectURL(blob);

                // Simula el clic en el enlace para iniciar la descarga
                link.click();
            }

            
            function generarCSV(js) {
                let csvContent = '';
                let tableName = '';
                let rows = [];

                // Dividimos el contenido por líneas
                const lines = js.split('\n');

                lines.forEach(line => {
                    line = line.trim();

                    // Si encontramos una línea que define una tabla
                    if (line.endsWith('{')) {
                        tableName = line.replace('{', '').trim();  // Extraemos el nombre de la tabla
                        rows.push(`Table: ${tableName}`);  // Agregar el nombre de la tabla al CSV
                    } 
                    // Si encontramos el cierre de una tabla
                    else if (line.endsWith('}')) {
                        rows.push('');  // Línea en blanco para separar tablas en el CSV
                    } 
                    // Si encontramos una columna en la tabla
                    else if (line.includes(' ')) {
                        const [type, name, constraint] = line.split(' ').filter(Boolean);  // Separamos por espacios

                        // Convertir los tipos Mermaid a tipos de PostgreSQL (si aplica)
                        let sqlType = '';
                        switch (type.toLowerCase()) {
                            case 'int':
                                sqlType = 'INTEGER';
                                break;
                            case 'string':
                                sqlType = 'TEXT';
                                break;
                            case 'float':
                                sqlType = 'FLOAT';
                                break;
                            case 'date':
                                sqlType = 'DATE';
                                break;
                            default:
                                sqlType = 'TEXT';  // Por defecto TEXT
                                break;
                        }

                        // Si hay una constraint, la añadimos
                        let columnDef = `${name}, ${sqlType}`;
                        if (constraint === 'PK') {
                            columnDef += ', PRIMARY KEY';
                        } else if (constraint === 'FK') {
                            columnDef += ', FOREIGN KEY';
                        }

                        rows.push(columnDef);  // Agregamos la definición de la columna al CSV
                    }
                });

                // Unimos todo el contenido como una cadena CSV separada por saltos de línea
                csvContent = rows.join('\n');

                return csvContent;
            }

            function descargarCSV() {
                const contenidoJson = document.getElementById('mermaid-code').value; // Obtener el contenido del textarea
                const csvContent = generarCSV(contenidoJson);  // Generar el contenido del CSV

                // Crear un Blob con el contenido del CSV
                const blob = new Blob([csvContent], { type: 'text/csv' });
                const link = document.createElement('a');
                
                // Nombre del archivo CSV (puedes modificarlo según lo que necesites)
                link.download = `${titulo}.csv`;  
                link.href = window.URL.createObjectURL(blob);

                // Simula el clic en el enlace para iniciar la descarga
                link.click();
            }

            document.getElementById('generateSpringBootProject').addEventListener('click', function () {
                // Obtener el código del diagrama generado y el título del diagrama
                const diagram = document.getElementById('mermaid-code').value; // Suponiendo que tienes un textarea con id 'mermaid-code'
                var titulo = {!! json_encode($diagram->titulo) !!}; // Suponiendo que tienes un campo para el título
                var descripcion = {!! json_encode($diagram->descripcion) !!};
                console.log({ titulo: titulo, diagram: diagram });

                // Realizar una solicitud AJAX o fetch al backend para generar el proyecto
                $.ajax({
                    type: 'POST',
                    url: '/generarSpringBootProject',                    
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Token CSRF de Laravel
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({ titulo: titulo, diagram: diagram, descripcion: descripcion }), // Enviar el título y el código del diagrama
                    success: function(data) {
                        if (data.zipUrl) {
                            // Redirigir a la URL para descargar el ZIP
                            window.location.href = data.zipUrl;
                        } else {
                            alert('Ocurrió un error al generar el proyecto.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al generar el proyecto:', error);
                        alert('Ocurrió un error al generar el proyecto.');
                    }
                });

            });      

            document.getElementById('generateLaravelProject').addEventListener('click', function () {
                // Obtener el código del diagrama generado y el título del diagrama
                const diagram = document.getElementById('mermaid-code').value; // Suponiendo que tienes un textarea con id 'mermaid-code'
                var titulo = {!! json_encode($diagram->titulo) !!}; // Suponiendo que tienes un campo para el título
                var descripcion = {!! json_encode($diagram->descripcion) !!};
                console.log({ titulo: titulo, diagram: diagram });

                // Realizar una solicitud AJAX o fetch al backend para generar el proyecto Laravel
                $.ajax({
                    type: 'POST',
                    url: '/generarLaravelProject',  // Endpoint para generar el proyecto Laravel                
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Token CSRF de Laravel
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({ titulo: titulo, diagram: diagram, descripcion: descripcion }), // Enviar el título y el código del diagrama
                    success: function(data) {
                        if (data.zipUrl) {
                            // Redirigir a la URL para descargar el ZIP del proyecto Laravel
                            window.location.href = data.zipUrl;
                        } else {
                            alert('Ocurrió un error al generar el proyecto Laravel.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al generar el proyecto:', error);
                        alert('Ocurrió un error al generar el proyecto.');
                    }
                });

            });
     

    </script>
</body>




@vite(['resources/js/socket-client.js'])
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.4.0/socket.io.js"></script>

<script type="module">
import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';   
mermaid.initialize({ securityLevel: 'loose', theme: 'neutral', suppressErrorRendering: 'true' }); 
const socket = io('http://127.0.0.1:3000',{ transports : ['websocket'] }); 
    const textarea = document.getElementById('mermaid-code');
    const diagramID = {!! json_encode($diagram->id) !!}; // Este es el identificador único del diagrama actual
    console.log(diagramID);
        // Unirse a una sala específica con el ID del diagrama
        socket.emit('joinRoom', diagramID);

        // Escuchar cambios en el textarea y enviarlos al servidor solo para la sala del diagrama
        textarea.addEventListener('input', () => {
            socket.emit('editTextarea', { diagramID, content: textarea.value });
        });

        // Actualizar el contenido del textarea cuando otro usuario lo edite en la misma sala
        socket.on('updateTextarea', (content) => {
            const inicio = 'erDiagram ';
            const diagramContainer = document.getElementById('mermaid-container');
            textarea.value = content;
            diagramContainer.innerHTML = '<pre class="mermaid">' + inicio + content + '</pre>';

            // Vuelve a inicializar Mermaid para renderizar el nuevo diagrama
            mermaid.run(undefined, diagramContainer);
            console.log(content);
        });
    
</script>
</html>
