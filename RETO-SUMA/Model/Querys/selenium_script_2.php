<?php
session_start();

require '../../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverWait;


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($password)) {
            $host = 'http://localhost:4444/wd/hub';

            // Configurar las opciones de Chrome
            $options = new ChromeOptions();
            $options->addArguments([
                //'--headless',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--window-size=1200,720'
            ]);

            // Establecer las capacidades deseadas para Chrome
            $capabilities = DesiredCapabilities::chrome();
            $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

            // Crear una instancia del WebDriver
            $driver = RemoteWebDriver::create($host, $capabilities);

            try {
                // Navegar a la página de inicio de sesión
                $driver->get('https://suma.etb.co:6443/');

                $driver->executeScript('window.localStorage.clear();');
                $driver->executeScript('window.sessionStorage.clear();');

                // Ingresar el nombre de usuario y la contraseña
                $driver->findElement(WebDriverBy::name('username'))->sendKeys($username);
                $driver->findElement(WebDriverBy::name('password'))->sendKeys($password);

                // Hacer clic en el botón de envío
                $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

                // Esperar a que la URL cambie correctamente
                $driver->wait(2)->until(
                    WebDriverExpectedCondition::urlContains('ConectorMDM/Base?sOpcion=Consultas&sAdicional=Vista360')
                );
                
                echo "Página actual: " . $driver->getCurrentURL() . "\n";
                
                // Establecer sesión de usuario logueado
                $_SESSION['loggedin'] = true;
                
                // Leer el primer dato del archivo 'process_2.txt'
                $archivo = realpath(__DIR__ . '/../../View/rob2/process_2.txt');

                ini_set('memory_limit', '512M');
                
                // Verificar si el archivo existe
                if (!file_exists($archivo)) {
                    die("El archivo 'process_2.txt' no existe.\n");
                }
                
                // Abrir el archivo en modo lectura
                $gestor = fopen($archivo, 'r');
                if (!$gestor) {
                    die("No se pudo abrir el archivo 'process_2.txt'.\n");
                }
                
                // Ruta del archivo CSV
                $filePath = '../../View/rob2/Retenciones_y_Usuarios_2.csv';
                
                // Comprobar si el archivo CSV existe para agregar encabezados si es necesario
                $fileExists = file_exists($filePath);
                
                // Abrir el archivo CSV en modo de escritura (lo creará si no existe)
                $file = fopen($filePath, 'a');
                if (!$file) {
                    die("No se pudo abrir o crear el archivo CSV.\n");
                }
                
                // Si el archivo CSV no existía, escribir los encabezados
                if (!$fileExists) {
                    fputcsv($file, ['NumOrden', 'Usu_Mod', 'Usu_Pass', 'Obser', 'Hor_Ini', 'Hor_Fin']);
                }
                
                // Procesar cada línea del archivo
                while (($linea = fgets($gestor)) !== false) {
                    $primerDato = trim($linea);
                    if ($primerDato === '') {
                        continue;
                    }
                
                    try {

                        $timeout = 0.7;
                        // Intervalo de sondeo en milisegundos
                        $intervalo = 500; // Puedes ajustar este valor según tus necesidades

                        $wait = new WebDriverWait($driver, $timeout, $intervalo);

                        $timeout2 = 1.6;
                        // Intervalo de sondeo en milisegundos
                        $intervalo2 = 500; // Puedes ajustar este valor según tus necesidades

                        $wait2 = new WebDriverWait($driver, $timeout2, $intervalo2);

                        // Capturar el tiempo de inicio
                        $horaInicio = microtime(true);

                        // Esperar que el primer iframe (o <object>) esté presente
                        $iframePrincipal = $wait ->until(
                            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('object')) // Cambia a 'iframe' si es necesario
                        );
                
                        // Cambiar al primer iframe
                        $driver->switchTo()->frame($iframePrincipal);
                
                        // Esperar el campo de entrada dentro del primer iframe
                        $campoNumOrden = $wait ->until(
                            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('NumOrden'))
                        );
                        $campoNumOrden->sendKeys($primerDato);
                
                        // Salir del primer iframe antes de entrar al segundo
                        $driver->switchTo()->defaultContent();
                
                        // Esperar el segundo iframe
                        $iframeSecundario = $wait ->until(
                            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('object'))
                        );
                
                        // Cambiar al segundo iframe
                        $driver->switchTo()->frame($iframeSecundario);
                
                        // Esperar a que el botón esté clickeable
                        $botonBuscar = $wait ->until(
                            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('btnbuscar'))
                        );
                
                        // Hacer scroll al botón si está oculto
                        $driver->executeScript("arguments[0].scrollIntoView();", [$botonBuscar]);
                
                        // Hacer clic en el botón
                        $botonBuscar->click();

                        if (!function_exists('isElementVisible')) {
                            function isElementVisible(RemoteWebDriver $driver, WebDriverBy $by, int $timeout = 2): bool {
                                try {
                                    $driver->wait($timeout)->until(
                                        WebDriverExpectedCondition::visibilityOfElementLocated($by)
                                    );
                                    return true;
                                } catch (NoSuchElementException | TimeoutException $e) {
                                    return false;
                                }
                            }
                        }                    
                        
                        if (isElementVisible($driver, WebDriverBy::id('continue-button-modal'))) {
                            $botonAceptar = $driver->findElement(WebDriverBy::id('continue-button-modal'));
                            $botonAceptar->click();
                        } 
                
                        // Esperar y hacer clic en el botón (no-button-modal2)
                        if (isElementVisible($driver, WebDriverBy::id('no-button-modal2'))) {
                            $botonNoModal = $driver->findElement(WebDriverBy::id('no-button-modal2'));
                            $botonNoModal->click();
                            usleep(700 * 1000); // Esperar 0.8 segundos
                            $botonNoModal->click();
                        }
                        
                        if (isElementVisible($driver, WebDriverBy::id('accept-button-modal'))) {
                            $botonAceptar = $driver->findElement(WebDriverBy::id('accept-button-modal'));
                            usleep(700 * 1000); // Esperar 0.8 segundos
                            $botonAceptar->click();
                        }               
                        
                        if (isElementVisible($driver, WebDriverBy::id('continue-button-modal'))) {
                            $botonAceptar = $driver->findElement(WebDriverBy::id('continue-button-modal'));
                            $botonAceptar->click();
                        } 
                
                        // Esperar y hacer clic en el botón 'BotonRetenciones'
                        $botonRetenciones = $wait2->until(
                            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('BotonRetenciones'))
                        );
                        $botonRetenciones->click();
                
                        // Suponiendo que $primerDato contiene el identificador único del cliente
                        $primerDato = trim($linea);

                        // Construir el XPath dinámico
                        $xpath = sprintf("//tr[td[contains(text(), '%s')]]//button[contains(@class, 'fa-eye')]", $primerDato);
                        $byXpath = WebDriverBy::xpath($xpath);
                        
                        if (isElementVisible($driver, $byXpath)) {
                            $botonOjo = $driver->findElement($byXpath);
                            $botonOjo->click();
                            // Continuar con la lógica después de hacer clic en el botón
                        } else {
                            $observacion = "No se encontro referencia para el ID $primerDato. Se requiere revisión manual.";
                            fputcsv($file, [$primerDato, '', '', $observacion, date('H:i:s', $horaInicio), date('H:i:s', $horaFin)]);
                            echo $observacion . "\n";

                            // Salir de cualquier iframe y volver al contexto principal
                            $driver->switchTo()->defaultContent();
                    
                            // Refrescar la página para preparar la siguiente iteración
                            $driver->navigate()->refresh();
                        }
                        // Esperar y obtener el valor del span lblTramiteFijaUsuarioModificacion
                        $usuMod = $wait->until(
                            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('lblTramiteFijaUsuarioModificacion'))
                        )->getText();
                
                        // Esperar y obtener el valor del span lblUsuarioPaso
                        $usuPass = $wait->until(
                            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('lblUsuarioPaso'))
                        )->getText();
                
                        $horaFin = microtime(true);
                        // Agregar la nueva fila con los datos obtenidos al archivo CSV
                        fputcsv($file, [$primerDato, $usuMod, $usuPass, 'Navegacion Exitosa', date('H:i:s', $horaInicio), date('H:i:s', $horaFin)]);

                        $archivoHistory = __DIR__ . '/history_2.txt';

                        // Leer todas las líneas del archivo process_2.txt
                        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        
                        if ($lineas && count($lineas) > 0) {
                            // Extraer el primer dato procesado
                            $datoProcesado = array_shift($lineas);
                        
                            // Guardar el dato en history.txt
                            file_put_contents($archivoHistory, $datoProcesado . "\n", FILE_APPEND);
                        
                            // Sobrescribir process_2.txt sin la primera línea
                            file_put_contents($archivo, implode("\n", $lineas) . "\n");
                        }
                
                            // exec('pkill -f chrome');
                
                            // Salir de cualquier iframe y volver al contexto principal
                            $driver->switchTo()->defaultContent();
                    
                            // Refrescar la página para preparar la siguiente iteración
                            $driver->navigate()->refresh();
                
                    } catch (Exception $e) {
                        // echo "Error en la ejecución para el dato '$primerDato': " . $e->getMessage() . "\n";
                        // echo "Archivo: " . $e->getFile() . " en línea " . $e->getLine() . "\n";
                        // echo "Trace: " . $e->getTraceAsString() . "\n";

                        // query_Main();
                        $horaFin = microtime(true);
                        $driver->switchTo()->defaultContent();
                        $driver->navigate()->refresh();
                        
                    }
                }            
                
                // Cerrar los archivos abiertos
                fclose($gestor);
                fclose($file);                    

            } catch (TimeoutException $e) {
                // Manejar excepción de tiempo de espera
                echo "<script type='text/javascript'>
                    alert('Error: Se superó el tiempo de entrega. Detalles: " . addslashes($e->getMessage()) . "');
                </script>";

                // Salir de cualquier iframe y volver al contexto principal
                $driver->switchTo()->defaultContent();
                    
                // Refrescar la página para preparar la siguiente iteración
                $driver->navigate()->refresh();

                //header("Location: ../../View/rob2/mid_2.php");
            }catch (NoSuchElementException $e) {
                // Manejar excepción de tiempo de espera
                echo "<script type='text/javascript'>
                    alert('Error: Elemento no Encontrado. Detalles: " . addslashes($e->getMessage()) . "');
                </script>";

                // Salir de cualquier iframe y volver al contexto principal
                $driver->switchTo()->defaultContent();
                    
                // Refrescar la página para preparar la siguiente iteración
                $driver->navigate()->refresh();

                //header("Location: ../../View/rob2/mid_2.php");
            }
            finally {
                $driver->quit();
                header("Location: ../../View/rob2/end_2.php");
            }
        }
    }


?>