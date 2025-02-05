<?php
session_start(); // Iniciar sesión para almacenar datos

// Procesamiento del archivo CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    if ($_FILES['csvFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['csvFile']['tmp_name'];
        $fileName = $_FILES['csvFile']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        if ($fileExtension === 'csv') {
            if (($handle = fopen($fileTmpPath, 'r')) !== false) {
                $header = fgetcsv($handle, 1000, ",");

                if ($header === false) {
                    $_SESSION['message'] = "Error al leer el encabezado del archivo.";
                    fclose($handle);
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit;
                }

                // Eliminar BOM si existe
                if (strpos($header[0], "\xEF\xBB\xBF") === 0) {
                    $header[0] = substr($header[0], 3);
                }

                // Convertir encabezados a minúsculas
                $header = array_map('strtolower', $header);

                // Verificar si 'numorden' está presente en los encabezados
                if (in_array('numorden', $header)) {
                    $_SESSION['numOrdenes'] = []; // Resetear sesión antes de procesar

                    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                        if (count($data) !== count($header)) {
                            continue; // Saltar filas con número de columnas incorrecto
                        }

                        $row = array_combine($header, $data);
                        if ($row === false) {
                            continue; // Saltar filas con datos mal formados
                        }

                        $numOrden = $row['numorden'];
                        $_SESSION['numOrdenes'][] = $numOrden; // Guardar en sesión
                    }

                    fclose($handle);
                    $_SESSION['message'] = "Archivo procesado correctamente.";
                } else {
                    $_SESSION['message'] = "El archivo CSV no contiene la columna 'numorden'.";
                }
            } else {
                $_SESSION['message'] = "Error al abrir el archivo.";
            }
        } else {
            $_SESSION['message'] = "Por favor, suba un archivo con formato CSV.";
        }
    } else {
        $_SESSION['message'] = "No se ha subido ningún archivo o ha ocurrido un error.";
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
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