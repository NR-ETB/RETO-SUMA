<?php
// Ruta del archivo process.txt
$archivoProcesados = 'process.txt';

// Verificar si el archivo process.txt existe
if (file_exists($archivoProcesados)) {
    // Leer todas las líneas del archivo
    $lineas = file($archivoProcesados, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Verificar que haya al menos una línea
    if (count($lineas) > 0) {
        // Obtener el primer y último usuario
        $primerUsuario = $lineas[0];
        $ultimoUsuario = $lineas[count($lineas) - 1];

        // Nombre del archivo CSV
        $nombreCSV = 'datos.csv';

        // Abrir el archivo CSV para escritura
        $archivoCSV = fopen($nombreCSV, 'w');

        // Escribir la línea de encabezado
        fputcsv($archivoCSV, ['NumOrden', 'Usu_Mod', 'Usu_Paso']);

        // Escribir los datos
        foreach ($lineas as $numOrden) {
            fputcsv($archivoCSV, [$numOrden, $primerUsuario, $ultimoUsuario]);
        }

        // Cerrar el archivo CSV
        fclose($archivoCSV);
    } else {
        echo "El archivo process.txt está vacío.";
    }
} else {
    echo "El archivo process.txt no existe.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="shortcut icon" href="images/icons/window.png" />
    <title>RoboSuma</title>
</head>
<body>

    <div class="container">

        <div class="inic" id="star2">

            <div class="help">
                <span>?</span>
            </div>

            <div class="tittle">
                <img src="images/icons/robot.png" alt="">
                <h1>ROBOSUMA</h1>
            </div>

            <div class="band-content">
                <div class="band-2">
                    <img src="images/icons/download.png" alt="">
                    <span class="add-2">Descarga la información filtrada en formato CSV</span>
                    <!-- Botón de descarga -->
                    <form action="download.php" method="post">
                        <button type="submit">Descargar CSV</button>
                    </form>
                </div>
            </div>

            <div class="buttons-act-2">
                <img class="act2" onclick="mid_Again();" src="images/icons/reply.png" alt="">
                <img class="act2" onclick="end();" src="images/icons/home.png" alt="">
            </div>

        </div>

        <div class="backdrop"></div>

    </div>

<script src="bootstrap/jquery.js"></script>
<script src="bootstrap/bootstrap.bundle.min.js"></script>
<script src="../Controller/buttons_Action.js"></script>
</body>
</html>