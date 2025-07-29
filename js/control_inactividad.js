
// Control de inactividad y actualización de sesión PHP

// Tiempo máximo de inactividad en milisegundos (5 minutos)
const DURACION_MAXIMA = 20 * 60 * 1000; // 300000 ms
const DURACION_ADVERTENCIA = 15 * 60 * 1000; // 240000 ms
let timeoutInactividad;
let timeoutAdvertencia;
let advertenciaMostrada = false;

function resetearTemporizador() {
    clearTimeout(timeoutInactividad);
    clearTimeout(timeoutAdvertencia);
    advertenciaMostrada = false;
    // Oculta la advertencia si existe
    const advertencia = document.getElementById('advertencia-inactividad');
    if (advertencia) advertencia.style.display = 'none';

    timeoutInactividad = setTimeout(() => {
        window.location.href = 'inicio.php?mensaje=expirado';
    }, DURACION_MAXIMA);

    timeoutAdvertencia = setTimeout(() => {
        if (!advertenciaMostrada) {
            mostrarAdvertencia();
            advertenciaMostrada = true;
        }
    }, DURACION_ADVERTENCIA);
}

function mostrarAdvertencia() {
    let advertencia = document.getElementById('advertencia-inactividad');
    if (!advertencia) {
        advertencia = document.createElement('div');
        advertencia.id = 'advertencia-inactividad';
        // Sugerencia: mover este estilo a un archivo CSS para mejor mantenimiento
        advertencia.style = 'color: orange; font-weight: bold; text-align: center; position: fixed; top: 0; width: 100%; background: #fff8e1; z-index: 9999; padding: 10px;';
        document.body.appendChild(advertencia);
    }
    advertencia.innerHTML = 'Su sesión expirará en un minuto por inactividad';
    // Botón para cerrar advertencia manualmente (solo si no existe ya)
    if (!document.getElementById('btn-cerrar-advertencia')) {
        const btnCerrar = document.createElement('button');
        btnCerrar.id = 'btn-cerrar-advertencia';
        btnCerrar.innerText = 'X';
        btnCerrar.style = 'margin-left: 20px; background: transparent; border: none; color: #ff9800; font-size: 18px; cursor: pointer;';
        btnCerrar.onclick = function() {
            advertencia.style.display = 'none';
        };
        advertencia.appendChild(btnCerrar);
    }
    advertencia.style.display = 'block';
}

// Detecta actividad del usuario
['touchstart', 'mousemove', 'keydown', 'mousedown'].forEach(evento => {
    document.addEventListener(evento, resetearTemporizador, false);
});

// Inicia el temporizador al cargar la página
resetearTemporizador();
console.log('Temporizador iniciado');
// Mantiene la sesión activa en el servidor cada minuto si hay actividad
setInterval(() => {
    fetch('comprobar_sesion.php?actualizar=1', { method: 'GET', credentials: 'same-origin' });
}, 60 * 1000); // Cada minuto 