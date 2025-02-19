<?php
// Incluir las clases necesarias de php-webdriver
require 'vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverExpectedCondition;

// Iniciar la sesión
session_start();

if (isset($_SESSION['numOrdenes']) && !empty($_SESSION['numOrdenes'])) {
    $ordenes = $_SESSION['numOrdenes'];
    $nuevasOrdenes = [];

    // Leer el archivo de registros procesados
    $archivoProcesados = 'process.txt';

    // Verificar si el archivo process.txt existe, si no, crearlo vacío
    if (!file_exists($archivoProcesados)) {
        file_put_contents($archivoProcesados, '');
    }

    $procesados = file($archivoProcesados, FILE_IGNORE_NEW_LINES);

    // Filtrar las órdenes no procesadas
    foreach ($ordenes as $numOrden) {
        if (!in_array($numOrden, $procesados)) {
            $nuevasOrdenes[] = $numOrden;
            // Registrar el número de orden como procesado
            file_put_contents($archivoProcesados, $numOrden . PHP_EOL, FILE_APPEND);
        }
    }

    // Si se han encontrado números de orden no procesados, proceder
    if (count($nuevasOrdenes) > 0) {
        echo "Los siguientes números de orden han sido procesados: " . implode(", ", $nuevasOrdenes) . "\n";

        // Leer el archivo CSV línea por línea
        $archivoCSV = 'datos.csv'; // Reemplaza con la ruta de tu archivo CSV
        if (!file_exists($archivoCSV)) {
            die("El archivo CSV no existe.");
        }

        if (($gestor = fopen($archivoCSV, 'r')) !== FALSE) {
            // URL del servidor de Selenium
            $host = 'http://localhost:4444/wd/hub'; // Asegúrate de que Selenium Server se esté ejecutando en esta URL

            // Crear una instancia de WebDriver para Chrome
            $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());

            try {
                // Navegar a la URL objetivo
                $driver->get('URL_DE_LA_PAGINA'); // Reemplaza con la URL de tu página

                // Esperar hasta que el campo de entrada esté presente
                $wait = new WebDriverWait($driver, 10); // Espera máxima de 10 segundos
                $inputField = $wait->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ID_DEL_INPUT'))
                );

                // Procesar cada línea del CSV
                while (($datosCSV = fgetcsv($gestor, 1000, ',')) !== FALSE) {
                    // Suponiendo que el valor que necesitas está en la primera columna
                    $valorInput = $datosCSV[0]; // Ajusta el índice según la columna que necesites

                    // Ingresar el valor en el campo de entrada
                    $inputField->clear();
                    $inputField->sendKeys($valorInput);

                    // Esperar hasta que el botón de búsqueda esté presente y hacer clic en él
                    $searchButton = $wait->until(
                        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('btn_azul'))
                    );
                    $searchButton->click();

                    // Esperar un momento para que la acción se complete
                    sleep(2); // Ajusta el tiempo según sea necesario

                    // Continuar con el resto de las operaciones
                    // Esperar y hacer clic en el primer botón
                    $primerBoton = $wait->until(
                        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('BotonRetenciones'))
                    );
                    $primerBoton->click();

                    // Esperar un momento para que la acción se complete
                    sleep(2); // Ajusta el tiempo según sea necesario

                    // Hacer clic en el segundo botón
                    $segundoBoton = $wait->until(
                        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('fa-eye'))
                    );
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

                    // Esperar antes de procesar la siguiente línea
                    sleep(1); // Ajusta el tiempo según sea necesario
                }
            } catch (Exception $e) {
                echo 'Ocurrió un error durante la automatización: ' . $e->getMessage();
            } finally {
                // Cerrar el navegador
                $driver->quit();
                // Cerrar el archivo CSV
                fclose($gestor);
            }
        } else {
            echo "No se pudo abrir el archivo CSV.";
        }
    } else {
        echo "No se encontraron números de orden nuevos para procesar";
    }
} else {
    echo "No hay datos para filtrar";
}
?>