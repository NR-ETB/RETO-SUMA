<?php
session_start();

// Verificar si hay números de orden para filtrar
if (isset($_SESSION['numOrdenes']) && !empty($_SESSION['numOrdenes'])) {
    $ordenes = $_SESSION['numOrdenes'];
    $existen = [];

    // Debugging: Mostrar el contenido de la variable 'ordenes'
    echo "<pre>";
    echo "Contenido de 'ordenes': ";
    var_dump($ordenes);
    echo "</pre>";

    // Leer el archivo de registros procesados
    $archivoProcesados = 'procesados.txt';
    $procesados = file_exists($archivoProcesados) ? file($archivoProcesados, FILE_IGNORE_NEW_LINES) : [];

    // Debugging: Mostrar el contenido de la variable 'procesados'
    echo "<pre>";
    echo "Contenido de 'procesados': ";
    var_dump($procesados);
    echo "</pre>";

    foreach ($ordenes as $numOrden) {
        // Verificar si el número de orden ya ha sido procesado
        if (!in_array($numOrden, $procesados)) {
            // Procesar el número de orden
            // Debugging: Mostrar qué número de orden está siendo procesado
            echo "<pre>";
            echo "Procesando número de orden: ";
            var_dump($numOrden);
            echo "</pre>";

            // Registrar el número de orden como procesado
            file_put_contents($archivoProcesados, $numOrden . PHP_EOL, FILE_APPEND);
            $existen[] = $numOrden;
        }
    }

    // Verificar si se encontraron órdenes nuevas
    if (count($existen) > 0) {
        echo "Los siguientes números de orden han sido procesados: " . implode(", ", $existen);
    } else {
        echo "No se encontraron números de orden nuevos para procesar.";
    }
} else {
    echo "No hay datos para filtrar.";
}
?>