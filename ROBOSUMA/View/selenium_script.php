<?php
session_start();

require '../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: mid.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $host = 'http://localhost:4444/wd/hub';

        $options = new ChromeOptions();
        $options->addArguments([
            // '--headless', // Comentado para mostrar la ventana del navegador
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--window-size=1920,1080'
        ]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        $driver = RemoteWebDriver::create($host, $capabilities);

        try {
            $driver->get('https://suma.etb.co:6443/');

            $driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::name('username'))
            );
            $driver->findElement(WebDriverBy::name('username'))->sendKeys($username);
            $driver->findElement(WebDriverBy::name('password'))->sendKeys($password);
            $driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

            $driver->wait()->until(
                WebDriverExpectedCondition::urlContains('mid.php')
            );

            $_SESSION['loggedin'] = true;

            $driver->quit();

            header("Location: mid.php");
            exit;

        } catch (Exception $e) {
            echo "Error en la automatización: " . $e->getMessage();
        } finally {
            if ($driver) {
                $driver->quit();
            }
        }
    } else {
        echo "⚠️ Debes ingresar usuario y contraseña.";
    }
}
?>