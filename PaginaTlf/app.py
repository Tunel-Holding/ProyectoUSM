import flet as ft
from time import sleep
import requests


def main(page: ft.Page):
    page.title = "USM app"
    
    page.window.full_screen = True
    page.window.maximized = True
    page.padding = 0
    page.update()

    # Recuperar usuario y contraseña de localStorage
    saved_user = page.client_storage.get("saved_user")
    saved_password = page.client_storage.get("saved_password")

    def on_login_click(e):
        usuario = e.control.parent.parent.controls[1].value
        contraseña = e.control.parent.parent.controls[2].value
        try:
            response = requests.post("http://127.0.0.1:5000/login", 
                json={
                "usuario": usuario,
                "contraseña": contraseña
            })
            print(response.status_code)
            if response.status_code == 200:
                data = response.json()
                nivel_usuario = data.get("nivel_usuario")

                # Animar la opacidad del Stack antes de limpiar la página
                login_container.opacity=0
                background_container.opacity=0
                login_container.update()
                background_container.update()
                sleep(2)
                login_container.visible = False
                background_container.visible = False
                login_container.update()
                background_container.update()
                sleep(2)
                principal_container.visible=True
                principal_container.update()

                # Mostrar el diálogo para guardar la contraseña solo si no hay credenciales guardadas
                if not saved_user or not saved_password:
                    page.dialog = ft.AlertDialog(
                        title=ft.Text("Guardar Contraseña"),
                        content=ft.Text("¿Deseas guardar la contraseña para futuras sesiones?"),
                        actions=[
                            ft.TextButton("Sí", on_click=lambda e: guardar_contraseña(usuario, contraseña)),
                            ft.TextButton("No", on_click=cerrar_dialogo)
                        ],
                        actions_alignment=ft.MainAxisAlignment.END,
                    )
                    page.dialog.open = True
                    page.update()
            elif response.status_code == 401:
                print("Usuario o Contraseña incorrectos")
            else:
                print(response.status_code)
        except Exception as err:
            print("Error de conexión: ", err)

    def guardar_contraseña(usuario, contraseña):
        # Guardar el usuario y la contraseña en localStorage
        page.client_storage.set("saved_user", usuario)
        page.client_storage.set("saved_password", contraseña)
        print("Contraseña guardada.")
        page.dialog.open = False
        page.update()

    def aplicartransicion(cosa1, cosa2, fondo):
        cosa1.opacity = 0
        cosa1.update()
        sleep(0.7)
        cosa1.visible = False
        cosa1.update()
        sleep(0.2)
        fondo.visible = True
        fondo.update()
        sleep(0.2)
        fondo.opacity = 1
        fondo.update()
        sleep(0.2)
        cosa2.visible = True
        cosa2.update()
        sleep(0.2)
        cosa2.opacity = 0.9
        cosa2.update()

    def cerrar_dialogo(e):
        page.dialog.open = False
        page.update()
    
    def cerrar_sesión():            
            # Mostrar el contenedor de inicio de sesión
            print("Sesión cerrada.")
        
            # Mostrar el contenedor de inicio de sesión y el fondo
            login_container.visible = True
            background_container.visible = True
            login_container.opacity = 1
            background_container.opacity = 1
            login_container.update()
            background_container.update()
            
            # Ocultar las demás páginas
            
            pagina_item2.visible = False
            pagina_item3.visible = False
            page.update()
    
    def mostrar_pagina(index):
            # Ocultar todas las páginas
            
            principal_container.update()
            pagina_item2.visible = False
            pagina_item2.update()
            pagina_item3.visible = False
            pagina_item3.update()
            
            # Mostrar la página seleccionada
            if index == 0:  # Inicio
                principal_container.visible = True
                principal_container.update()
                
            elif index == 1:  # Item 2
                pagina_item2.visible = True
                pagina_item2.update()
                
            elif index == 2:  # Item 3
                pagina_item3.visible = True
                pagina_item3.update()
            elif index == 3:
                cerrar_sesión()

    loading_container = ft.Container(
        content=ft.Column(
            controls=[
                ft.Image(src="https://i.ibb.co/txP36Y4/logo.png", width=200, height=200),
                ft.ProgressRing()
            ],
            alignment=ft.MainAxisAlignment.CENTER,
            horizontal_alignment=ft.CrossAxisAlignment.CENTER
        ),
        alignment=ft.alignment.center,
        opacity=0,
        animate_opacity=300,
        visible=True,
        expand=True,
        bgcolor=ft.Colors.BLUE_700
    )

    login_container = ft.Container(
        content=ft.Column(
            controls=[
                ft.Text("Iniciar Sesión", size=30, weight=ft.FontWeight.BOLD, color=ft.Colors.BLUE),
                ft.TextField(label="Usuario", width=300, bgcolor=ft.Colors.GREY_50, color=ft.Colors.BLACK, value=saved_user if saved_user else ""),
                ft.TextField(label="Contraseña", password=True, width=300, bgcolor=ft.Colors.GREY_50, can_reveal_password=True, color=ft.Colors.BLACK, value=saved_password if saved_password else ""),
                ft.TextButton("¿No tienes una cuenta? Regístrate", on_click=lambda e: aplicartransicion(login_container, register_container, background_container)),
                ft.Container(ft.ElevatedButton("Iniciar Sesión", bgcolor="blue", color="white", width=150, on_click=on_login_click), padding=0, margin=0)
            ],
            alignment=ft.MainAxisAlignment.CENTER,
            horizontal_alignment=ft.CrossAxisAlignment.CENTER,
            spacing=20
        ),
        alignment=ft.alignment.center,
        border=ft.border.all(1, "blue"),
        border_radius=20,
        padding=40,
        opacity=0,
        animate_opacity=300,
        margin=20,
        visible=False,
        bgcolor="#FFFFFF",
        shadow=ft.BoxShadow(color=ft.Colors.GREY, blur_radius=10, offset=ft.Offset(2, 2))
    )

    background_container = ft.Container(
        content=ft.Image(src="https://i.ibb.co/fv0Yst8/IMG-7235copia.webp", width=page.width, height=page.height, fit=ft.ImageFit.COVER),
        alignment=ft.alignment.center,
        width=page.width,
        height=page.height,
        opacity=0,
        animate_opacity=300,
        visible=False
    )

    register_container = ft.Container(
        content=ft.Column(
            controls=[
                ft.Text("Registro", size=30, weight=ft.FontWeight.BOLD, color=ft.Colors.BLUE),
                ft.TextField(label="Usuario", width=300, bgcolor=ft.Colors.GREY_50, color=ft.Colors.BLACK),
                ft.TextField(label="Correo Electrónico", width=300, bgcolor=ft.Colors.GREY_50, color=ft.Colors.BLACK),
                ft.TextField(label="Contraseña", password=True, width=300, bgcolor=ft.Colors.GREY_50, can_reveal_password=True, color=ft.Colors.BLACK),
                ft.TextButton("¿Ya tienes una cuenta? Inicia sesión", on_click=lambda e: aplicartransicion(register_container, login_container, background_container)),
                ft.Container(ft.ElevatedButton("Registrar", bgcolor="blue", color="white"), width=150, padding=0, margin=0)
            ],
            alignment=ft.MainAxisAlignment.CENTER,
            horizontal_alignment=ft.CrossAxisAlignment.CENTER,
            spacing=20
        ),
        alignment=ft.alignment.center,
        border=ft.border.all(1, "blue"),
        border_radius=20,
        padding=40,
        opacity=0,
        animate_opacity=300,
        margin=20,
        visible=False,
        bgcolor="#FFFFFF",
        shadow=ft.BoxShadow(color=ft.Colors.GREY, blur_radius=10, offset=ft.Offset(2, 2))
    )
    pagina_item3 = ft.Container(
            content=ft.Column(
                controls=[ 
                    ft.IconButton(icon=ft.Icons.MENU,on_click=lambda e: page.open(drawer)),
                    ft.Container(  # Contenedor adicional para centrar el texto
                        content=ft.Text("Bienvenido a la pagina 3", size=35, text_align=ft.TextAlign.CENTER),
                        alignment=ft.Alignment(0.0,-1.0),
                        expand=True  # Permitir que el contenedor ocupe el espacio disponible
                    )

                ],
                
                expand=True
            ),
            opacity=1,  # Comenzar con opacidad 0
            animate_opacity=300,  # Animar la opacidad
            margin=ft.Margin(0,40,0,0),
            expand=True,
            visible=False  # Esta página está oculta por defecto
        )
    
    drawer = ft.NavigationDrawer(
            controls=[
                ft.NavigationDrawerDestination(
                    label="Inicio",
                    icon=ft.Icon(ft.Icons.HOME_FILLED, ft.Colors.WHITE),
                    icon_content=ft.Icon(ft.Icons.HOME_FILLED, ft.Colors.WHITE),
                    
                ),
                ft.Divider(thickness=1),
                ft.NavigationDrawerDestination(
                    icon=ft.Icon(ft.Icons.PERSON_ROUNDED, ft.Colors.WHITE),
                    icon_content=ft.Icon(ft.Icons.PERSON_ROUNDED, ft.Colors.WHITE),
                    label="Horario",
                    
                ),
                ft.NavigationDrawerDestination(
                    icon=ft.Icon(ft.Icons.PHONE_OUTLINED, ft.Colors.WHITE),
                    icon_content=ft.Icon(ft.Icons.PHONE_OUTLINED, ft.Colors.WHITE),
                    label="Item 3",
                    
                ),
                ft.NavigationDrawerDestination(
                    icon=ft.Icon(ft.Icons.EXIT_TO_APP_ROUNDED, ft.Colors.WHITE),
                    icon_content=ft.Icon(ft.Icons.EXIT_TO_APP_ROUNDED, ft.Colors.WHITE),
                    label="Salir",
                    
                ),
            ],
            on_change=lambda e: mostrar_pagina(e.control.selected_index),
            bgcolor=ft.Colors.BLUE_700,
            elevation=40
        )
    
    pagina_item2 = ft.Container(
            content=ft.Column(
                controls=[ 
                    ft.IconButton(icon=ft.Icons.MENU,on_click=lambda e: page.open(drawer)),
                    ft.Container(  # Contenedor adicional para centrar el texto
                        content=ft.Text("Horario", size=35, text_align=ft.TextAlign.CENTER),
                        alignment=ft.Alignment(0.0,-1.0),
                        expand=True  # Permitir que el contenedor ocupe el espacio disponible
                    )

                ],
                
                expand=True
            ),
            opacity=1,  # Comenzar con opacidad 0
            animate_opacity=300,  # Animar la opacidad
            margin=ft.Margin(0,40,0,0),
            expand=True,
            visible=False  # Esta página está oculta por defecto
        )

    principal_container = ft.Container(
            content=ft.Column(
                controls=[ 
                    ft.IconButton(icon=ft.Icons.MENU,on_click=lambda e: page.open(drawer)),
                    ft.Container(  # Contenedor adicional para centrar el texto
                        content=ft.Text("Bienvenido a la USM", size=35, text_align=ft.TextAlign.CENTER),
                        alignment=ft.Alignment(0.0,-1.0),
                        expand=True  # Permitir que el contenedor ocupe el espacio disponible
                    )

                ],
                
                expand=True
            ),
            opacity=1,  # Comenzar con opacidad 0
            animate_opacity=300,  # Animar la opacidad
            margin=ft.Margin(0,40,0,0),
            visible=True,
            expand=True
        )   

    # Crear el Stack y agregarlo a la página
    stack = ft.Stack([background_container, login_container, register_container], alignment=ft.alignment.center, animate_opacity=300)
    page.add(loading_container)
    page.add(stack)
    page.add(register_container)
    page.add(principal_container)
    page.add(pagina_item2)
    page.add(pagina_item3)

    loading_container.opacity = 1
    loading_container.update()
    sleep(4)
    aplicartransicion(loading_container, login_container, background_container)

ft.app(target=main)
