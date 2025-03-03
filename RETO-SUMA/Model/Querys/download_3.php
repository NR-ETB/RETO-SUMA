<?php
// Nombre del archivo CSV
$nombreCSV = '../../View/rob3/Retenciones_y_Usuarios_3.csv';

// Verificar si el archivo existe
if (file_exists($nombreCSV)) {
    // Establecer las cabeceras para la descarga
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $nombreCSV . '"');
    header('Content-Length: ' . filesize($nombreCSV));

    // Leer el archivo y enviarlo al navegador
    readfile($nombreCSV);

    // Eliminar el archivo del servidor después de la descarga
    unlink($nombreCSV);
    exit;
} else {
    echo "El archivo no está disponible para descargar.";
}
?>