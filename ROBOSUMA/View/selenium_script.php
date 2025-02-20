<?php
session_start();

require '../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\WebDriverException;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $host = 'http://localhost:4444/wd/hub';

        // Configurar las opciones de Chrome
        $options = new ChromeOptions();
        $options->addArguments([
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
            // Navegar a la página de inicio de sesión
            $driver->get('https://suma.etb.co:6443/');

            // Ingresar el nombre de usuario y la contraseña
            $driver->findElement(WebDriverBy::name('username'))->sendKeys($username);
            $driver->findElement(WebDriverBy::name('password'))->sendKeys($password);

            // Hacer clic en el botón de envío
            $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

            // Esperar a que la URL cambie correctamente
            $driver->wait(10)->until(
                WebDriverExpectedCondition::urlContains('ConectorMDM/Base?sOpcion=Consultas&sAdicional=Vista360')
            );
            
            echo "Página actual: " . $driver->getCurrentURL() . "\n";
            
            // Establecer sesión de usuario logueado
            $_SESSION['loggedin'] = true;
            
            // Leer el primer dato del archivo 'process.txt'
            $archivo = __DIR__ . '/process.txt';

            if (file_exists($archivo)) {
                $primerDato = file_get_contents($archivo); // Leer todo el contenido del archivo
            
                if ($primerDato === false || trim($primerDato) === '') {
                    die("El archivo 'process.txt' está vacío o no se pudo leer.\n");
                }
            
                echo "Primer Dato: " . $primerDato . "\n"; // Para depuración
            
                try {
                    // Asegurar que la página ha cargado completamente
                    $driver->wait(10)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('body'))
                    );
                
                    // Intentar encontrar el campo NumOrden con diferentes métodos
                    $campoNumOrden = $driver->wait(120)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('NumOrden'))
                    );
                
                    $campoNumOrden->sendKeys(trim($primerDato));
                
                    // Buscar y hacer clic en el botón de búsqueda
                    $botonBuscar = $driver->wait(10)->until(
                        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('btnbuscar'))
                    );
                    $botonBuscar->click();
                }
                 catch (Exception $e) {
                    echo "Error en la ejecución: " . $e->getMessage() . "\n";
                    echo "Archivo: " . $e->getFile() . " en línea " . $e->getLine() . "\n";
                    echo "Trace: " . $e->getTraceAsString() . "\n";
                }
            } else {
                echo "El archivo 'process.txt' no existe.\n";
            }
                                            

        } catch (TimeoutException $e) {
            // Manejar excepción de tiempo de espera
            echo "Error: Se supero el tiempo de Entrega. Detalles: " . $e->getMessage();
        } catch (NoSuchElementException $e) {
            // Manejar excepción de elemento no encontrado
            echo "Error: No se encontró el elemento especificado. Detalles: " . $e->getMessage();
        } catch (WebDriverException $e) {
            // Manejar otras excepciones de WebDriver
            echo "Error de WebDriver: " . $e->getMessage();
        } catch (Exception $e) {
            // Manejar cualquier otra excepción
            echo "Error general: " . $e->getMessage();
        } finally {
            // Cerrar el WebDriver
            $driver->quit();
        }
    } else {
        // Mensaje de advertencia si los campos están vacíos
        echo "⚠️ Debes ingresar usuario y contraseña.";
    }
}
?>