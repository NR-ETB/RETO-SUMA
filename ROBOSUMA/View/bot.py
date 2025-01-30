from flask import Flask, request, render_template
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

app = Flask(__name__)

# Ruta principal con el formulario
@app.route('/')
def index():
    return render_template('index.html')

# Ruta para ejecutar el bot cuando se presione el botón
@app.route('/start_bot', methods=['POST'])
def start_bot():
    usuario = request.form['username']
    contraseña = request.form['password']

    # Configura Selenium y espera que se cargue el navegador
    driver = webdriver.Chrome(executable_path="path_to_your_chromedriver")  # Asegúrate de poner el path correcto
    driver.get("https://suma.etb.co:6443/")

    try:
        # Espera hasta que el campo de usuario esté disponible
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "username")))

        # Loguearse
        username = driver.find_element(By.ID, "username")
        password = driver.find_element(By.ID, "password")
        username.send_keys(usuario)
        password.send_keys(contraseña)
        
        # Haz clic en el botón "Iniciar"
        driver.find_element(By.CLASS_NAME, "act").click()

        # Espera que cargue la página de inicio
        WebDriverWait(driver, 10).until(EC.url_contains("dashboard"))  # Espera que la URL contenga "dashboard"
        
        # Verifica el éxito del login
        nueva_url = driver.current_url
        if "dashboard" in nueva_url or "bienvenido" in nueva_url:
            mensaje = f"Inicio de sesión exitoso. URL actual: {nueva_url}"
        else:
            mensaje = "Error al iniciar sesión. Verifique los datos ingresados."

    except Exception as e:
        mensaje = f"Hubo un error: {e}"

    finally:
        driver.quit()

    return mensaje  # Devuelve el mensaje como respuesta al usuario

if __name__ == '__main__':
    app.run(debug=True)