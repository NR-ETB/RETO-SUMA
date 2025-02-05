<?php
session_start();

if (isset($_SESSION['numOrdenes']) && !empty($_SESSION['numOrdenes'])) {
    $ordenes = $_SESSION['numOrdenes'];
    $existen = [];

    // Leer el archivo de registros procesados
    $archivoProcesados = 'procesados.txt';
    
    // Verificar si el archivo procesados.txt existe, si no, crear uno vacío
    if (!file_exists($archivoProcesados)) {
        file_put_contents($archivoProcesados, '');
    }

    $procesados = file($archivoProcesados, FILE_IGNORE_NEW_LINES);

    foreach ($ordenes as $numOrden) {
        // Verificar si el número de orden ya ha sido procesado
        if (!in_array($numOrden, $procesados)) {
            // Procesar el número de orden
            // Registrar el número de orden como procesado
            file_put_contents($archivoProcesados, $numOrden . PHP_EOL, FILE_APPEND);
            $existen[] = $numOrden;
        }
    }

    // Si se han encontrado números de orden procesados, mostrar un mensaje
    if (count($existen) > 0) {
        echo "Los siguientes números de orden han sido procesados: " . implode(", ", $existen);
        echo "<script>
                // Buscar el primer botón y simular un clic
                var primerBoton = document.getElementById('primerBoton');
                if (primerBoton) {
                    primerBoton.click();
                } else {
                    console.error('Primer botón no encontrado.');
                }

                // Buscar el segundo botón y simular un clic
                var segundoBoton = document.getElementById('segundoBoton');
                if (segundoBoton) {
                    segundoBoton.click();
                } else {
                    console.error('Segundo botón no encontrado.');
                }
              </script>";
    } else {
        echo "No se encontraron números de orden nuevos para procesar";
    }
} else {
    echo "No hay datos para filtrar";
}
?>
 
