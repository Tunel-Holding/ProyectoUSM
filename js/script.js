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
    case 1:
      spancontraseña.textContent = ' Muy débil';
      spancontraseña.style.color = 'darkred';
      boton.type = 'button';
      break;
    case 2:
      spancontraseña.textContent = ' Débil';
      spancontraseña.style.color = 'darkorange';
      boton.type = 'button';
      break;
    case 3:
      spancontraseña.textContent = ' Medio';
      spancontraseña.style.color = 'goldenrod';
      boton.type = 'button';
      break;
    case 4:
      spancontraseña.textContent = ' Fuerte';
      spancontraseña.style.color = 'forestgreen';
      boton.type = 'button';
      break;
    case 5:
      spancontraseña.textContent = ' Muy fuerte';
      spancontraseña.style.color = 'darkgreen';
      boton.type = 'submit';
      break;
  }
});