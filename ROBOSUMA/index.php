<?php

// La URL del formulario de inicio de sesión donde se obtiene el token CSRF
$login_url = 'https://suma.etb.co:6443/';

// Paso 1: Obtener el formulario de inicio de sesión y extraer el token CSRF

// Iniciar una sesión cURL para obtener el formulario
$ch = curl_init();

// Configurar cURL
curl_setopt($ch, CURLOPT_URL, $login_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Ejecutar cURL para obtener la página de inicio de sesión
$response = curl_exec($ch);

// Verificar si hubo un error en la solicitud
if ($response === false) {
    die('Error al obtener el formulario de inicio de sesión: ' . curl_error($ch));
}

// Cerrar la sesión de cURL
curl_close($ch);

// Usar una librería de manejo de HTML como DOMDocument para analizar la respuesta
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($response);
$xpath = new DOMXPath($dom);

// Buscar el valor del token CSRF en la página (normalmente está en un campo oculto)
$csrf_token = '';
$csrf_elements = $xpath->query('//input[@name="__RequestVerificationToken"]');

if ($csrf_elements->length > 0) {
    $csrf_token = $csrf_elements->item(0)->getAttribute('value');
} else {
    die('No se encontró el token CSRF en el formulario.');
}

// Paso 2: Enviar las credenciales y el token CSRF al servidor

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibe el nombre de usuario y la contraseña del formulario
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Los datos para la solicitud POST incluyen el nombre de usuario, la contraseña y el token CSRF
    $data = [
        'username' => $username,
        'password' => $password,
        '__RequestVerificationToken' => $csrf_token
    ];

    // Paso 3: Realizar el inicio de sesión enviando los datos al servidor

    // Iniciar cURL para enviar los datos del formulario
    $ch = curl_init();

    // Configurar cURL para la solicitud POST
    curl_setopt($ch, CURLOPT_URL, $login_url); // La URL para la solicitud POST
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Ejecutar la solicitud POST
    $response = curl_exec($ch);

    // Verificar si hubo un error en la solicitud
    if ($response === false) {
        die('Error al enviar los datos de inicio de sesión: ' . curl_error($ch));
    }

    // Cerrar la sesión de cURL
    curl_close($ch);

    // Procesar la respuesta del servidor
    echo 'Respuesta del servidor: ' . $response;
} else {
    // Si el formulario no ha sido enviado aún, muestra el formulario de inicio de sesión
    echo 'Formulario no enviado aún.';
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

            <form action="" method="post">
                <div class="data">
                    <div>
                        <img src="View/images/icons/email.png" alt="">
                        <input type="text" name="username" id="username" placeholder="Usuario_Suma" required>
                    </div>

                    <div>
                        <img src="View/images/icons/pass.png" alt="">
                        <input type="password" name="password" id="password" placeholder="Contraseña_Suma" required>
                    </div>

                    <!-- Token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="buttons-act">
                        <button class="act" type="submit" onclick="start();">Iniciar</button>
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