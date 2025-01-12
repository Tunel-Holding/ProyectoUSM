document.addEventListener('DOMContentLoaded', function() {
    // Animación del contenedor entrante
    const contenedor = document.getElementById('contenedorEntrante');
    contenedor.style.transition = 'transform 1s';
    contenedor.style.transform = 'translateY(-100%)';

    // Manejo de selección de opciones
    const opciones = document.querySelectorAll('.opcion');
    opciones.forEach(opcion => {
        opcion.addEventListener('click', function() {
            opciones.forEach(o => o.classList.remove('opcion-seleccionada'));
            this.classList.add('opcion-seleccionada');
            localStorage.setItem('selectedOption', this.innerText);
        });
    });

    // Restaurar la última opción seleccionada
    const selectedOption = localStorage.getItem('selectedOption');
    if (selectedOption) {
        opciones.forEach(opcion => {
            if (opcion.innerText === selectedOption) {
                opcion.classList.add('opcion-seleccionada');
            }
        });
    }

    // Manejo del menú
    const button = document.querySelector('.button');
    const menu = document.getElementById('menu');
    button.addEventListener('click', function(event) {
        event.stopPropagation();
        menu.classList.toggle('visible');
    });
    document.addEventListener('click', function(event) {
        if (!menu.contains(event.target) && !button.contains(event.target)) {
            menu.classList.remove('visible');
        }
    });

    // Manejo del modo oscuro
    const darkModeSwitch = document.getElementById('switch');
    const body = document.body;
    const darkMode = localStorage.getItem('darkMode');
    if (darkMode === 'enabled') {
        body.classList.remove('darkmode');
        darkModeSwitch.checked = true;
    }
    darkModeSwitch.addEventListener('change', function() {
        if (this.checked) {
            body.classList.remove('darkmode');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            body.classList.add('darkmode');
            localStorage.setItem('darkMode', 'disabled');
        }
    });

    // Manejo del botón de logout
    const logoutButton = document.querySelector('.logout');
    logoutButton.addEventListener('click', function() {
        window.location.href = 'logout.php';
    });
    
});