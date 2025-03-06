<?php
// Ruta de la carpeta donde se encuentra el archivo
$rutaCarpeta = '../../View/rob/';
$rutaCSV = '../../View/rob/process_1.cvs';

// Nombre del archivo CSV
$nombreArchivo = 'Retenciones_y_Usuarios_1.csv';
$nombreArchivo_2 = 'Retenciones_y_Usuarios_Falla_1.csv';

// Ruta completa al archivo
$rutaCompleta = $rutaCarpeta . $nombreArchivo;
$rutaCompleta_2 = $rutaCarpeta . $nombreArchivo_2;

if (file_exists($rutaCSV)) {
    // Abrir el archivo en modo escritura para vaciar su contenido
    $gestor = fopen($rutaCSV, 'w');
    if ($gestor) {
        fclose($gestor);
        // echo "El contenido del archivo 'process_2.csv' ha sido vaciado.\n";
    } else {
        // echo "No se pudo abrir el archivo 'process_2.csv' para escritura.\n";
    }
} else {
    // echo "El archivo 'process.csv' no existe.\n";
}

// Verificar si el archivo existe
if (file_exists($rutaCompleta)) {
    // Establecer las cabeceras para la descarga
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Content-Length: ' . filesize($rutaCompleta));

    // Leer el archivo y enviarlo al navegador
    readfile($rutaCompleta);

    // Eliminar el archivo del servidor después de la descarga
    unlink($rutaCompleta);
    unlink($rutaCVS);
    exit;
} else {
    echo "El archivo no está disponible para descargar.";
}

// Verificar si el archivo existe
if (file_exists($rutaCompleta_2)) {
    // Establecer las cabeceras para la descarga
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo_2 . '"');
    header('Content-Length: ' . filesize($rutaCompleta_2));

    // Leer el archivo y enviarlo al navegador
    readfile($rutaCompleta_2);

    // Eliminar el archivo del servidor después de la descarga
    unlink($rutaCompleta_2);
    unlink($rutaCVS);
    exit;
} else {
    echo "El archivo no está disponible para descargar.";
}
?>
