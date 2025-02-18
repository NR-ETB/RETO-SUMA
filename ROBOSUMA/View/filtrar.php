<?php
// Incluir las clases necesarias de php-webdriver
require 'vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

// Iniciar la sesión
session_start();

if (isset($_SESSION['numOrdenes']) && !empty($_SESSION['numOrdenes'])) {
    $ordenes = $_SESSION['numOrdenes'];
    $existen = [];

    // Leer el archivo de registros procesados
    $archivoProcesados = 'process.txt';

    // Verificar si el archivo process.txt existe, si no, crear uno vacío
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

    // Si se han encontrado números de orden procesados, realizar la automatización
    if (count($existen) > 0) {
        echo "Los siguientes números de orden han sido procesados: " . implode(", ", $existen);

        // URL del servidor de Selenium
        $host = 'http://localhost:4444/wd/hub'; // Asegúrate de que Selenium Server se esté ejecutando en esta URL

        // Crear una instancia de WebDriver para Chrome
        $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());

        try {
            // Navegar a la URL objetivo
            $driver->get('URL_DE_LA_PAGINA'); // Reemplaza con la URL de tu página

            // Esperar y hacer clic en el primer botón
            $primerBoton = $driver->findElement(WebDriverBy::id('BotonRetenciones'));
            $primerBoton->click();

            // Esperar un momento para que la acción se complete
            sleep(2); // Ajusta el tiempo según sea necesario

            // Hacer clic en el segundo botón
            $segundoBoton = $driver->findElement(WebDriverBy::className('fa-eye'));
            $segundoBoton->click();

            // Esperar a que los elementos deseados estén presentes
            sleep(2); // Ajusta el tiempo según sea necesario

            // Obtener los elementos por sus IDs
            $tramiteElement = $driver->findElement(WebDriverBy::id('lblTramiteFijaUsuarioModificacion'));
            $usuarioElement = $driver->findElement(WebDriverBy::id('lblUsuarioPaso'));

            // Extraer el texto de los elementos
            $tramiteText = $tramiteElement->getText();
            $usuarioText = $usuarioElement->getText();

            // Mostrar los valores obtenidos
            echo "Trámite: $tramiteText\n";
            echo "Usuario: $usuarioText\n";

            // Aquí puedes agregar lógica adicional según tus necesidades

        } catch (Exception $e) {
            echo 'Ocurrió un error durante la automatización: ' . $e->getMessage();
        } finally {
            // Cerrar el navegador
            $driver->quit();
        }
    } else {
        echo "No se encontraron números de orden nuevos para procesar";
    }
} else {
    echo "No hay datos para filtrar";
}
?>