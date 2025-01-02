import requests
import flet as ft
from datetime import datetime

# Función para obtener los datos del horario
def obtener_horario():
    try:
        response = requests.get("http://127.0.0.1:5000/horario")
        response.raise_for_status()  # Levanta una excepción para códigos de error HTTP
        datos = response.json()
        print("Datos obtenidos del horario:", datos)  # Depuración
        return datos
    except requests.exceptions.RequestException as e:
        print(f"Error al obtener los datos del horario: {e}")
        return []

# Función para crear una celda del horario
def crear_celda(horarios, dia, hora):
    hora_inicio_dt = datetime.strptime(hora, '%H:%M')
    clases = [horario for horario in horarios if horario['dia'] == dia and datetime.strptime(horario['hora_inicio'], '%H:%M:%S') == hora_inicio_dt]
    print(f"Clases encontradas para {dia} a las {hora}: {clases}")  # Depuración
    if clases:
        clase = clases[0]
        return ft.Container(
            content=ft.Text(f"{clase['id_materia']}\n{clase['hora_inicio']} - {clase['hora_fin']}", color=ft.colors.BLACK),
            width=80, height=60, bgcolor=ft.colors.BLUE_100, border_radius=5
        )
    return ft.Container(width=80, height=60, bgcolor=ft.colors.WHITE, border_radius=5)

# Función principal para construir la página
def main(page: ft.Page):
    horarios = obtener_horario()

    # Crear una lista de días y horas para el horario
    dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']
    horas = ['07:00', '08:30', '09:15', '10:00','10:45', '12:00', '13:15', '14:30', '15:45', '17:00']

    # Crear la cabecera de la tabla del horario
    cabecera = ft.Row(controls=[
        ft.Container(content=ft.Text('Horario', color=ft.colors.BLACK), width=80, bgcolor=ft.colors.GREY_300, border_radius=2),
        *[ft.Container(content=ft.Text(dia, color=ft.colors.BLACK), width=80, bgcolor=ft.colors.GREY_300, border_radius=2) for dia in dias_semana]
    ])
    
    filas = []
    # Crear las filas del horario
    for hora in horas:
        fila = ft.Row(controls=[
            ft.Container(content=ft.Text(hora, color=ft.colors.BLACK), width=80, bgcolor=ft.colors.GREY_300, border_radius=5),
            *[crear_celda(horarios, dia, hora) for dia in dias_semana]
        ])
        filas.append(fila)
        

    # Crear contenedor scrolleable para la tabla 
    columna_scrolleable = ft.Column(
        controls=[cabecera] + filas,
        scroll="always",
        expand=True
    )
    tabla_scrolleable = ft.Row(
        controls=[columna_scrolleable],
        scroll="always",
        expand=True
    )

    page.add(tabla_scrolleable)

# Ejecutar la aplicación
ft.app(target=main)
