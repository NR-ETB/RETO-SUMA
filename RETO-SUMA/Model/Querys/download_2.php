<?php
// Ruta de la carpeta donde se encuentra el archivo
$rutaCarpeta = '../../View/rob2/';
$rutaCSV = '../../View/rob2/process_2.csv';

// Nombre del archivo CSV
$nombreArchivo = 'Retenciones_y_Usuarios_2.csv';
$nombreArchivo_2 = 'Retenciones_y_Usuarios_Falla_2.csv';

// Ruta completa al archivo
$rutaCompleta = $rutaCarpeta . $nombreArchivo;
$rutaCompleta_2 = $rutaCarpeta . $nombreArchivo_2;

if (file_exists($rutaCSV)) {
    // Abrir el archivo en modo lectura/escritura
    $gestor = fopen($rutaCSV, 'r+');
    if ($gestor) {
        // Truncar el archivo a 0 bytes
        ftruncate($gestor, 0);
        fclose($gestor);
        // echo "El contenido del archivo ha sido vaciado.\n";
    } else {
        // echo "No se pudo abrir el archivo para escritura.\n";
    }
} else {
    // echo "El archivo no existe.\n";
}

// Definir las rutas completas de los archivos
$archivos = [$rutaCompleta, $rutaCompleta_2];

// Nombre del archivo ZIP que se va a crear
$nombreArchivoZip = 'GestionesCSV.zip';

// Crear una nueva instancia de ZipArchive
$zip = new ZipArchive();

// Abrir el archivo ZIP para agregar archivos
if ($zip->open($nombreArchivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    foreach ($archivos as $archivo) {
        if (file_exists($archivo)) {
            $nombreArchivo = basename($archivo);
            $zip->addFile($archivo, $nombreArchivo);
        }
    }
    $zip->close();

    // Establecer las cabeceras para la descarga del archivo ZIP
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $nombreArchivoZip . '"');
    header('Content-Length: ' . filesize($nombreArchivoZip));

    // Leer el archivo ZIP y enviarlo al navegador
    readfile($nombreArchivoZip);

    // Eliminar el archivo ZIP del servidor después de la descarga
    unlink($nombreArchivoZip);

    // Eliminar los archivos originales del servidor
    foreach ($archivos as $archivo) {
        if (file_exists($archivo)) {
            unlink($archivo);
        }
    }
    exit;
} else {
    echo "No se pudo crear el archivo ZIP.";
}
?>