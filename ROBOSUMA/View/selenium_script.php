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
                    // Esperar que el primer iframe (o <object>) esté presente
                    $iframePrincipal = $driver->wait(10)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('object')) // Cambia a 'iframe' si es necesario
                    );

                    // Cambiar al primer iframe
                    $driver->switchTo()->frame($iframePrincipal);

                    // Esperar el campo de entrada dentro del primer iframe
                    $campoNumOrden = $driver->wait(10)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('NumOrden'))
                    );
                    $campoNumOrden->sendKeys(trim($primerDato));

                    // IMPORTANTE: Salimos del primer iframe antes de entrar al segundo
                    $driver->switchTo()->defaultContent();

                    // Esperar el segundo iframe
                    $iframeSecundario = $driver->wait(10)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('object'))
                    );

                    // Cambiar al segundo iframe
                    $driver->switchTo()->frame($iframeSecundario);

                    // Esperar a que el botón esté clickeable
                    $botonBuscar = $driver->wait(15)->until(
                        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('btnbuscar'))
                    );

                    // Hacer scroll al botón si está oculto
                    $driver->executeScript("arguments[0].scrollIntoView();", [$botonBuscar]);

                    // Hacer clic en el botón
                    $botonBuscar->click();

                    // Esperar y hacer clic en el botón (no-button-modal2)
                    $botonNoModal = $driver->wait(10)->until(
                        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('no-button-modal2'))
                    );
                    $botonNoModal->click();

                    // Esperar 5 segundos
                    sleep(5);

                    // Volver a hacer clic en el mismo botón
                    $botonNoModal->click();

                    // Esperar y hacer clic en el segundo botón (accept-button-modal)
                    $botonAceptar = $driver->wait(10)->until(
                        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('accept-button-modal'))
                    );
                    $botonAceptar->click();

                    // Esperar y hacer clic en el botón 'BotonRetenciones'
                    $botonRetenciones = $driver->wait(10)->until(
                        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('BotonRetenciones'))
                    );
                    $botonRetenciones->click();

                    // Esperar 3 segundos antes de continuar (opcional, si la página tarda en cargar)
                    sleep(3);

                    // Esperar y hacer clic en el botón con la clase 'fa fa-eye'
                    $botonOjo = $driver->wait(10)->until(
                        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('button.fa.fa-eye'))
                    );
                    $botonOjo->click();

                    // Esperar y obtener el valor del span lblTramiteFijaUsuarioModificacion
                    $usuMod = $driver->wait(10)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('lblTramiteFijaUsuarioModificacion'))
                    )->getText();

                    // Esperar y obtener el valor del span lblUsuarioPaso
                    $usuPass = $driver->wait(10)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('lblUsuarioPaso'))
                    )->getText();

                    // Ruta del archivo CSV
                    $filePath = 'Retenciones y Usuarios.csv';

                    // Comprobar si el archivo existe
                    $fileExists = file_exists($filePath);

                    // Abrir el archivo en modo escritura (lo creará si no existe)
                    $file = fopen($filePath, 'a');

                    // Si el archivo no existía, escribir los encabezados
                    if (!$fileExists) {
                        fputcsv($file, ['NumOrden', 'Usu_Mod', 'Usu_Pass']);
                    }

                    // Agregar la nueva fila con los datos obtenidos
                    fputcsv($file, [$primerDato, $usuMod, $usuPass]);

                    // Cerrar el archivo
                    fclose($file);

                    echo "Archivo CSV 'Retenciones y Usuarios.csv' actualizado correctamente.";

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