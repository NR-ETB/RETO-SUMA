<?php
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
                
                // Leer el primer dato del archivo 'process_3.csv'
                $archivo = './process_3.csv';

                ini_set('memory_limit', '512M');
                
                // Verificar si el archivo existe
                if (!file_exists($archivo)) {
                    die("El archivo 'process_3.csv' no existe.\n");
                }
                
                // Abrir el archivo en modo lectura
                $gestor = fopen($archivo, 'r');
                if (!$gestor) {
                    die("No se pudo abrir el archivo 'process_3.csv'.\n");
                }
                
                // Ruta del archivo CSV
                $filePath = './Retenciones_y_Usuarios_3.csv';
                
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

                // Ruta del archivo CSV
                $filePath_2 = './Retenciones_y_Usuarios_Falla_3.csv';
                
                // Comprobar si el archivo CSV existe para agregar encabezados si es necesario
                $fileExists_2 = file_exists($filePath_2);
                                
                // Abrir el archivo CSV en modo de escritura (lo creará si no existe)
                $file_2 = fopen($filePath_2, 'a');
                if (!$file_2) {
                    die("No se pudo abrir o crear el archivo CSV.\n");
                }
                                
                // Si el archivo CSV no existía, escribir los encabezados
                if (!$fileExists_2) {
                    fputcsv($file_2, ['NumOrden','Obser', 'Hor_Ini', 'Hor_Fin']);
                }

                $exceptionHandled = false;
                
                // Procesar cada línea del archivo
                while (($linea = fgets($gestor)) > 0) {
                    $primerDato = trim($linea);
                    if ($primerDato === '') {
                        continue;
                    }
                
                    try {

                        $timeout = 0.63;
                        // Intervalo de sondeo en milisegundos
                        $intervalo = 500; // Puedes ajustar este valor según tus necesidades

                        $wait = new WebDriverWait($driver, $timeout, $intervalo);

                        $timeout2 = 1.63;
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
                            usleep(750 * 1000); // Esperar 0.8 segundos
                            $botonNoModal->click();
                        }
                        
                        if (isElementVisible($driver, WebDriverBy::id('accept-button-modal'))) {
                            $botonAceptar = $driver->findElement(WebDriverBy::id('accept-button-modal'));
                            usleep(750 * 1000); // Esperar 0.8 segundos
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

                            $horaFin = microtime(true);
                            
                            $observacion = "No se encontro referencia para el ID $primerDato. Se requiere revisión manual.";
                            fputcsv($file, [$primerDato, '', '', $observacion, date('H:i:s', $horaInicio), date('H:i:s', $horaFin)]);
                            echo $observacion . "\n";

                            $exceptionHandled = true; // Marcar que la excepción fue manejada

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

                        $exceptionHandled = false;
                
                        $horaFin = microtime(true);
                        // Agregar la nueva fila con los datos obtenidos al archivo CSV
                        fputcsv($file, [$primerDato, $usuMod, $usuPass, 'Navegacion Exitosa', date('H:i:s', $horaInicio), date('H:i:s', $horaFin)]);

                        // Salir de cualquier iframe y volver al contexto principal
                        $driver->switchTo()->defaultContent();
                    
                        // Refrescar la página para preparar la siguiente iteración
                        $driver->navigate()->refresh();
                            
                
                    } catch (Exception $e) {
                        if (!$exceptionHandled) {
                            $horaFin = microtime(true);
                    
                            $observacion = "No fue posible realizar la navegación de $primerDato. Se requiere una segunda subida o revisión manual.";
                            fputcsv($file_2, [$primerDato, $observacion, date('H:i:s', $horaInicio), date('H:i:s', $horaFin)]);
                            echo $observacion . "\n";
                    
                            // Salir de cualquier iframe y volver al contexto principal
                            $driver->switchTo()->defaultContent();
                    
                            // Refrescar la página para preparar la siguiente iteración
                            $driver->navigate()->refresh();
                        }
                    }
                }             
                
                // Cerrar los archivos abiertos
                fclose($gestor);
                fclose($file);                    

            }catch (TimeoutException $e) {

                echo "<script type='text/javascript'>
                    alert('Se ha superado el tiempo de entrega');
                </script>";

            }catch (NoSuchElementException $e) {

                echo "<script type='text/javascript'>
                    alert('Elemento no encontrado o Inexistente');
                </script>";

            }finally {
                $driver->quit();
                header("Location: ../../View/rob3/end_3.php");
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../images/icons/window.png" />
    <title>Reto-Suma Bot3</title>
</head>
<body>

    <div class="container">
        
        <div class="inic" id="star3">

            <div class="help">
                <a href="../../Model/Documents/manReto-Suma.pdf" target="_blank"><span>?</span></a>
            </div>

            <div class="tittle">
                <img src="../images/icons/robot.png" alt="">
                <h1>RETO-SUMA3</h1>
            </div>

            <?php
            session_start();

                // Generar un token CSRF si no existe
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }

                // Obtener el token para usar en el formulario
                $csrfToken = $_SESSION['csrf_token'];
            ?>

            <form action="" method="POST">
                <div class="data">
                    <!-- Campo oculto para el token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                    <div>
                        <img src="../images/icons/email.png" alt="Icono de correo electrónico">
                        <input type="text" name="username" placeholder="Usuario_Suma" required>
                    </div>

                    <div>
                        <img src="../images/icons/pass.png" alt="Icono de contraseña">
                        <input type="password" name="password" placeholder="Contraseña_Suma" required>
                    </div>

                    <div class="buttons-act">
                        <button class="act" type="submit">Iniciar</button>
                    </div>
                </div>
            </form>

            <button class="act" onclick="next_End();" style="position: relative; left: 700px; top: 260px;">Siguiente</button>

        </div>

        <div class="backdrop"></div>

    </div>

<script src="../bootstrap/jquery.js"></script>
<script src="../bootstrap/bootstrap.bundle.min.js"></script>
<script src="../../Controller/buttons_Action_3.js"></script>
</body>
</html>