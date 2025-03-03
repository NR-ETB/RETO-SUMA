<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style2.css">
    <link rel="shortcut icon" href="../images/icons/window.png" />
    <title>Reto-Suma Bot1</title>
</head>
<body>

    <div class="container">

        <div class="inic" id="star2">

            <div class="help">
                <span>?</span>
            </div>

            <div class="tittle">
                <img src="../images/icons/robot.png" alt="">
                <h1>RETO-SUMA1</h1>
            </div>

            <div class="band-content">
                <div class="band-2">
                <form action="../../Model/Querys/download_1.php" method="post">
                    <button type="submit" class="custom-button">
                        <img src="../images/icons/download.png" alt="">
                        <span class="add-2">Descarga la información filtrada en formato CSV</span>
                    </button>
                </form>
                </div>
            </div>

            <div class="buttons-act-2">
                <img class="act2" onclick="mid();" src="../images/icons/reply.png" alt="">
                <img class="act2" onclick="end();" src="../images/icons/home.png" alt="">
            </div>

        </div>

        <div class="backdrop"></div>

    </div>

<script src="../bootstrap/jquery.js"></script>
<script src="../bootstrap/bootstrap.bundle.min.js"></script>
<script src="../../Controller/buttons_Action.js"></script>
</body>
</html>