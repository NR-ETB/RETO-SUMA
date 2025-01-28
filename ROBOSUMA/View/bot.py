from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import time

# Configura el navegador
driver = webdriver.Chrome()  # Usa el controlador correcto
driver.get("https://ejemplo.com/login")

# Loguearse
username = driver.find_element(By.ID, "username")
password = driver.find_element(By.ID, "password")
username.send_keys("tu_usuario")
password.send_keys("tu_contraseña")
driver.find_element(By.ID, "login_button").click()

time.sleep(3)  # Espera que cargue la página

# Navegar y obtener el campo
campo = driver.find_element(By.XPATH, "//div[@class='clase_del_campo']")
print("Campo necesario:", campo.text)

# Cerrar navegador
driver.quit()