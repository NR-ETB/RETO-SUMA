<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="shortcut icon" href="./images/icons/window.png" />
    <title>Reto-Suma</title>
</head>
<body>

    <div class="container">
        
        <div class="inic" id="star3">

            <div class="help">
                <span>?</span>
            </div>

            <div class="tittle">
                <img src="./images/icons/robot.png" alt="">
                <h1>RETO-SUMA</h1>
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

            <form action="../../Model/Querys/selenium_script.php" method="POST">
                <div class="data">
                    <!-- Campo oculto para el token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                    <div>
                        <img src="./images/icons/email.png" alt="Icono de correo electrónico">
                        <input type="text" name="username" placeholder="Usuario_Suma" required>
                    </div>

                    <div>
                        <img src="./images/icons/pass.png" alt="Icono de contraseña">
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

<script src="bootstrap/jquery.js"></script>
<script src="bootstrap/bootstrap.bundle.min.js"></script>
<script src="../Controller/buttons_Action.js"></script>
</body>
</html>