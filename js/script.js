const btnSignIn = document.getElementById("btn-sign-in");
const btnSignUp = document.getElementById("btn-sign-up");
const container = document.querySelector(".container");
const pass = document.getElementById("pass");
const icon = document.querySelector(".bx");
const passs = document.getElementById("passs");
const icons = document.getElementById("icons");
const boton = document.getElementById('boton')

icon.addEventListener("click", () => {
  if (pass.type === "password") {
    pass.type = "text";
    icon.classList.remove("bx-show");
    icon.classList.add("bx-hide");
  } else {
    pass.type = "password";
    icon.classList.add("bx-show");
    icon.classList.remove("bx-hide");
  }
})

icons.addEventListener("click", () => {
  if (passs.type === "password") {
    passs.type = "text";
    icons.classList.remove("bx-show");
    icons.classList.add("bx-hide");
  } else {
    passs.type = "password";
    icons.classList.add("bx-show");
    icons.classList.remove("bx-hide");
  }
})

btnSignIn.addEventListener("click", () => {
  container.classList.remove("toggle");
})

btnSignUp.addEventListener("click", () => {
  container.classList.add("toggle");
})

document.getElementById('passs').addEventListener('mouseenter', function () {
  const tooltip = document.getElementById('tooltip');
  tooltip.style.display = 'block';
  setTimeout(() => {
    tooltip.style.opacity = '1';
  }, 10); // Timeout pequeño para asegurar que el display se establezca primero
});

document.getElementById('passs').addEventListener('mouseleave', function () {
  const tooltip = document.getElementById('tooltip');
  tooltip.style.opacity = '0';
  setTimeout(() => {
    tooltip.style.display = 'none';
  }, 500);
});

document.getElementById('passs').addEventListener('input', function () {
  const passwordfield = document.getElementById('passs');
  const spancontraseña = document.getElementById('spancontraseña');
  const contraseña = passwordfield.value;
  let fuerza = 0;

  if (contraseña.match(/[a-z]+/)) {
    fuerza++;
  }
  if (contraseña.match(/[A-Z]+/)) {
    fuerza++;
  }
  if (contraseña.match(/[0-9]+/)) {
    fuerza++;
  }
  if (contraseña.match(/[$@#%&/*]+/)) {
    fuerza++;
  }
  if (contraseña.length >= 8) {
    fuerza++;
  }

  switch (fuerza) {
    case 0:
      spancontraseña.textContent = ' Ingrese una contraseña';
      spancontraseña.style.color = 'gray';
      boton.type = 'button';
      break;
    case 1:
      spancontraseña.textContent = ' Muy débil (recomendado: usar mayúsculas, números y caracteres especiales)';
      spancontraseña.style.color = 'darkred';
      boton.type = 'button';
      break;
    case 2:
      spancontraseña.textContent = ' Débil (recomendado: usar números y caracteres especiales)';
      spancontraseña.style.color = 'darkorange';
      boton.type = 'button';
      break;
    case 3:
      spancontraseña.textContent = ' Medio (recomendado: usar caracteres especiales)';
      spancontraseña.style.color = 'goldenrod';
      boton.type = 'button';
      break;
    case 4:
      spancontraseña.textContent = ' Fuerte (recomendado: usar al menos 8 caracteres)';
      spancontraseña.style.color = 'forestgreen';
      boton.type = 'button';
      break;
    case 5:
      spancontraseña.textContent = ' Muy fuerte ✓';
      spancontraseña.style.color = 'darkgreen';
      boton.type = 'submit';
      break;
  }
});

// Agregar validación del formulario antes del envío
document.getElementById('sign-up').addEventListener('submit', function(e) {
  const nombre = document.querySelector('input[name="nombre"]').value.trim();
  const email = document.querySelector('input[name="mail"]').value.trim();
  const password = document.getElementById('passs').value;
  
  let errores = [];
  
  if (nombre.length < 3) {
    errores.push('El nombre de usuario debe tener al menos 3 caracteres');
  }
  
  if (!email.includes('@') || !email.includes('.')) {
    errores.push('El formato del correo electrónico no es válido');
  }
  
  if (password.length < 6) {
    errores.push('La contraseña debe tener al menos 6 caracteres');
  }
  
  if (errores.length > 0) {
    e.preventDefault();
    alert('Por favor corrija los siguientes errores:\n' + errores.join('\n'));
  }
});