<?php
// Iniciar la sesión
session_start();

// Incluir las clases necesarias de php-webdriver
require '../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

// Generar un nuevo token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Variable para almacenar el mensaje de salida
$output = "";

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (isset($_POST['csrf_token'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        
        // Capturar y limpiar los datos del formulario
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Verificar que los campos no estén vacíos
        if (!empty($username) && !empty($password)) {

            // URL del servidor Selenium
            $host = 'http://localhost:4444/wd/hub'; // Asegúrate de que Selenium esté corriendo

            // Crear una instancia de WebDriver para Chrome
            $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());

            try {
                // Navegar a la página de inicio de sesión
                $driver->get('https://suma.etb.co:6443/');

                // Esperar hasta que el campo de usuario esté presente
                $driver->wait()->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::name('username'))
                );

                // Ingresar el nombre de usuario
                $driver->findElement(WebDriverBy::name('username'))->sendKeys($username);

                // Ingresar la contraseña
                $driver->findElement(WebDriverBy::name('password'))->sendKeys($password);

                // Hacer clic en el botón de inicio de sesión
                $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

                // Esperar hasta que la página de destino después del inicio de sesión esté cargada
                $driver->wait()->until(
                    WebDriverExpectedCondition::urlContains('View/mid.php') // Ajusta según la URL de destino
                );

                // Guardar datos en sesión para mantener autenticado al usuario
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $username;

                // Redirigir a mid.php
                header("Location: View/mid.php");
                exit;

            } catch (Exception $e) {
                $output = "⚠️ Error durante la automatización: " . $e->getMessage();
            } finally {
                // Cerrar el navegador
                $driver->quit();
            }

        } else {
            $output = "⚠️ Por favor, complete todos los campos.";
        }

    } else {
        die("❌ Token CSRF inválido.");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="shortcut icon" href="images/icons/window.png" />
    <title>RoboSuma</title>
</head>
<body>
    <div class="container">
        
        <div class="inic" id="star1">

            <div class="help">
                <span>?</span>
            </div>

            <div class="tittle">
                <img src="images/icons/robot.png" alt="">
                <h1>ROBOSUMA</h1>
            </div>

            <?php
                if (isset($_SESSION['message'])) {
                    echo "<p>" . $_SESSION['message'] . "</p>";
                    unset($_SESSION['message']);
                }
            ?>

            <div class="band-content" onclick="document.getElementById('csvFile').click();">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="file" id="csvFile" name="csvFile" accept=".csv" onchange="this.form.submit()" style="display: none;">
                    <div class="band">
                        <img src="images/icons/download.png" alt="">
                        <span class="add">Añade la base de datos en formato CSV</span>
                    </div>
                </form>
            </div>

            <div class="buttons-act">
                <button class="act" onclick="filtrarOrden();">Filtrar</button>
            </div>
        </div>
        <div class="backdrop"></div>
    </div>

<script src="bootstrap/jquery.js"></script>
<script src="bootstrap/bootstrap.bundle.min.js"></script>
<script src="../Controller/buttons_Action.js"></script>

<script>
function filtrarOrden() {
    $.ajax({
        url: 'filtrar.php', // Archivo PHP que hará la validación
        type: 'POST',
        success: function(response) {
            alert(response); // Mostrar resultado
        },
        error: function(jqXHR, textStatus, errorMessage) {
            alert('Error en la búsqueda: ' + errorMessage);
        }
    });
}
</script>

</body>
</html>