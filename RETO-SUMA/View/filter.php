<?php
// Incluir las dependencias de Composer
require '../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Chrome\ChromeOptions;

// URL del servidor de Selenium
$host = 'http://localhost:4444/wd/hub';

// Configurar las opciones de Chrome
$options = new ChromeOptions();
$options->addArguments([
    '--headless', // Ejecutar en modo headless para no abrir una ventana del navegador
    '--disable-gpu',
    '--no-sandbox',
    '--disable-dev-shm-usage',
    '--window-size=1920,1080'
]);

// Establecer las capacidades deseadas para Chrome
$capabilities = DesiredCapabilities::chrome();
$capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

// Crear una instancia del WebDriver
$driver = RemoteWebDriver::create($host, $capabilities);

try {
    // Navegar a la página objetivo
    $driver->get('https://suma.etb.co:6443/ConectorMDM/Base?sOpcion=Consultas&sAdicional=Vista360');

    // Esperar hasta que el campo con id 'NumOrden' esté presente
    $driver->wait(10)->until(
        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('NumOrden'))
    );

    // Ingresar el primer dato en el campo 'NumOrden'
    $campoNumOrden = $driver->findElement(WebDriverBy::id('NumOrden'));
    $campoNumOrden->sendKeys(trim($primerDato));

    // Hacer clic en el botón con id 'btnbuscar'
    $botonBuscar = $driver->findElement(WebDriverBy::id('btnbuscar'));
    $botonBuscar->click();

    // Puedes agregar aquí más lógica según lo que necesites hacer después

} catch (Exception $e) {
    echo "Ocurrió un error: " . $e->getMessage();
} finally {

}
?>
