<?php
// Iniciar la sesión al comienzo del script
session_start();

// Procesar el formulario de carga de CSV cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csvFile'])) {
    // Verificar si hubo algún error al subir el archivo
    if ($_FILES['csvFile']['error'] === UPLOAD_ERR_OK) {
        $nombreTmp = $_FILES['csvFile']['tmp_name'];
        $nombreArchivo = $_FILES['csvFile']['name'];
        $ext = pathinfo($nombreArchivo, PATHINFO_EXTENSION);

        // Verificar que el archivo sea un CSV
        if (strtolower($ext) === 'csv') {
            // Abrir el archivo CSV para lectura
            if (($handle = fopen($nombreTmp, 'r')) !== FALSE) {
                // Omitir la primera línea (cabecera)
                fgetcsv($handle);

                // Abrir o crear el archivo process_2.txt para agregar datos
                $outputFile = './process_2.txt';
                $modo = file_exists($outputFile) ? 'a' : 'w';

                if (($outputHandle = fopen($outputFile, $modo)) !== FALSE) {
                    // Leer cada línea del CSV y escribirla en process_2.txt
                    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                        // Convertir el array en una línea de texto separada por comas
                        $linea = implode(',', $data) . PHP_EOL;
                        fwrite($outputHandle, $linea);
                    }
                    fclose($outputHandle);
                    $mensaje = "Los datos se han almacenado correctamente en 'process_2.txt'.";
                } else {
                    $mensaje = "No se pudo abrir 'process_2.txt' para escritura.";
                }
                fclose($handle);
            } else {
                $mensaje = "No se pudo abrir el archivo CSV.";
            }
        } else {
            $mensaje = "Por favor, sube un archivo con formato CSV.";
        }
    } else {
        $mensaje = "Error al subir el archivo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style2.css">
    <link rel="shortcut icon" href="../images/icons/window.png" />
    <title>Reto-Suma Bot2</title>
</head>
<body>
    <div class="container">
        
        <div class="inic" id="star1">

            <div class="help">
                <span>?</span>
            </div>

            <div class="tittle">
                <img src="../images/icons/robot.png" alt="">
                <h1>RETO-SUMA2</h1>
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
                        <img src="../images/icons/download.png" alt="">
                        <span class="add">Añade la base de datos en formato CSV</span>
                    </div>
                </form>
            </div>

            <div class="buttons-act">
                <button class="act" style="position: relative; left: 550px; top: 200px;" onclick="mid();">Filtrar</button>
            </div>

        </div>

        <div class="backdrop"></div>

    </div>

<script src="../bootstrap/jquery.js"></script>
<script src="../bootstrap/bootstrap.bundle.min.js"></script>
<script src="../../Controller/buttons_Action_2.js"></script>

</body>
</html>