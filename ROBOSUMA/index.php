<?php

class SUMALogin {
    private $baseUrl;
    private $logPath;

    public function __construct($baseUrl, $logPath) {
        $this->baseUrl = $baseUrl;
        $this->logPath = $logPath;
    }

    public function login($username, $password) {
        // Archivo para almacenar cookies
        $cookieFile = 'suma_cookies.txt';

        // Paso 1: Obtener la página de login para extraer el token de verificación
        $ch = curl_init($this->baseUrl);  // URL base para el formulario de login

        // Configuración de cURL para obtener la página de login
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Seguir redirecciones
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);  // Guardar cookies
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);  // Usar cookies guardadas
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Desactivar verificación SSL (solo para pruebas)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');  // Agregar agente de usuario

        // Obtener el HTML de la página de login
        $response = curl_exec($ch);

        // Obtener el código HTTP
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Verificar si hubo algún error en la solicitud
        $errorInfo = curl_error($ch);

        // Log detallado
        file_put_contents($this->logPath,
            "Intento de Login: " . date('Y-m-d H:i:s') . "\n" . 
            "URL: " . $this->baseUrl . "\n" . 
            "HTTP Code: $httpCode\n" . 
            "Error: $errorInfo\n" . 
            "Response Length: " . strlen($response) . "\n" . 
            "Response Preview: " . substr($response, 0, 500) . "\n\n", 
            FILE_APPEND
        );

        curl_close($ch);

        // Paso 2: Extraer el token de verificación del HTML de la página de login
        preg_match('/name="__RequestVerificationToken" type="hidden" value="(.*?)"/', $response, $matches);
        if (!isset($matches[1])) {
            return false; // Token de verificación no encontrado
        }
        $csrfToken = $matches[1];

        // Paso 3: Enviar las credenciales con el token de verificación
        $ch = curl_init($this->baseUrl);  // URL base del formulario de login

        $postData = [
            'username' => $username,  // El campo "name" del input
            'password' => $password,  // El campo "name" del input
            '__RequestVerificationToken' => $csrfToken,  // El token de verificación obtenido
        ];

        // Configuración de cURL para hacer un POST con los datos del formulario
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Seguir redirecciones
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);  // Guardar cookies
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);  // Usar cookies guardadas
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  // Datos a enviar en el POST
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Desactivar verificación SSL (solo para pruebas)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');  // Agregar agente de usuario

        // Realizar la solicitud y obtener la respuesta
        $response = curl_exec($ch);

        // Obtener código HTTP
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorInfo = curl_error($ch);

        // Log detallado
        file_put_contents($this->logPath,
            "Intento de Login POST: " . date('Y-m-d H:i:s') . "\n" . 
            "URL: " . $this->baseUrl . "\n" . 
            "HTTP Code: $httpCode\n" . 
            "Error: $errorInfo\n" . 
            "Response Length: " . strlen($response) . "\n" . 
            "Response Preview: " . substr($response, 0, 500) . "\n\n", 
            FILE_APPEND
        );

        curl_close($ch);

        return $this->validateLogin($response, $httpCode);
    }

    private function validateLogin($response, $httpCode) {
        // Validación para determinar si el login fue exitoso
        if ($httpCode == 200 || $httpCode == 302) {
            // Verificar contenido de la respuesta para determinar login exitoso
            if (strpos($response, 'Bienvenido') !== false) return true; // Cambiar la lógica según lo que devuelva la plataforma
        }
        return false;
    }
}

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Asegúrate de que las variables no estén vacías
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        $username = $_POST['username'];  // Usuario ingresado por el usuario
        $password = $_POST['password'];  // Contraseña ingresada por el usuario

        // Configuración
        $logPath = 'C:/xampp/htdocs/login_debug.txt';  // Ruta de log
        $suma = new SUMALogin('https://suma.etb.co:6443/', $logPath);  // URL base para login

        // Intentar hacer login
        $loginResult = $suma->login($username, $password);

        if ($loginResult) {
            echo "Login exitoso";
            header("Location: View/mid.php");
        } else {
            echo "Login fallido o Credenciales Incorrectas. Revisa el log en " . $logPath;
        }
    } else {
        echo "Por favor, ingresa usuario y contraseña.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="View/css/style.css">
    <link rel="shortcut icon" href="View/images/icons/window.png" />
    <title>RoboSuma</title>
</head>
<body>

    <div class="container">

        <div class="inic" id="star1" style="display: none;">

            <div class="tittle">
                <img src="View/images/icons/robot.png" alt="">
                <h1>ROBOSUMA</h1>
            </div>

            <div class="band-content">
                <div class="band">
                    <img src="View/images/icons/download.png" alt="">
                    <span class="add">Añade la base de datos en formato CVS</span>
                </div>
            </div>

            <div class="buttons-act">
                <button class="act" onclick="mid();">Filtrar</button>
            </div>

        </div>

        <div class="inic" id="star2" style="display: none;">

            <div class="tittle">
                <img src="View/images/icons/robot.png" alt="">
                <h1>ROBOSUMA</h1>
            </div>

            <div class="band-content">
                <div class="band-2">
                    <img src="View/images/icons/download.png" alt="">
                    <span class="add-2">Descarga el la informacion filtrada en formato CVS</span>
                </div>
            </div>

            <div class="buttons-act-2">
                <button class="act2" onclick="mid_Again();">Filtrar Nuevo</button>
                <button class="act2" onclick="end();">Volver Inicio</button>
            </div>

        </div>
        
        <div class="inic" id="star3">

            <div class="tittle">
                <img src="View/images/icons/robot.png" alt="">
                <h1>ROBOSUMA</h1>
            </div>

            <?php
                // Iniciar la sesión
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
                        <img src="View/images/icons/email.png" alt="Icono de correo electrónico">
                        <input type="text" name="username" placeholder="Usuario_Suma" required>
                    </div>

                    <div>
                        <img src="View/images/icons/pass.png" alt="Icono de contraseña">
                        <input type="password" name="password" placeholder="Contraseña_Suma" required>
                    </div>

                    <div class="buttons-act">
                        <button class="act" type="submit">Iniciar</button>
                    </div>
                </div>
            </form>

        </div>

        <div class="backdrop"></div>

    </div>

<script src="View/bootstrap/jquery.js"></script>
<script src="View/bootstrap/bootstrap.bundle.min.js"></script>
<script src="Controller/buttons_Action.js"></script>
</body>
</html>