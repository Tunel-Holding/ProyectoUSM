<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/iniciostyle.css">
    <title>Inicio de Sesión - USM</title>
</head>
<body class="body1">
    
    <div id="passwordHint" class="password-hint" style="display:none;">
        La contraseña debe tener al menos una mayúscula, una minúscula, un número y un carácter especial.
    </div>

    <div class="main">
        <input type="checkbox" id="chk" aria-hidden="true">

        <div class="login">
            <form action="Ingreso.php" method="POST">
                <label for="chk" aria-hidden="true">Iniciar Sesión</label>
                <input type="text" name="email" placeholder="Nombre de Usuario" required>
                <input type="password" name="pswd" placeholder="Contraseña" required>
                <button>Entrar</button>
            </form>
        </div>
        <div class="signup">
            <form action="Registro.php" method="POST">
                <label for="chk" aria-hidden="true">Registrarse</label>
                <input type="text" name="name" placeholder="Nombre de Usuario" required>
                <input type="email" name="email" placeholder="Correo Electrónico" required>
                <input type="password" name="pswd" placeholder="Contraseña" required onfocus="showPasswordHint()" onblur="hidePasswordHint()" oninput="checkPasswordStrength(this.value)">
                <span id="passwordStrength" class="password-strength"></span>
                <button>Registrarse</button>
            </form>
        </div>

    </div>
    <div class="contenedorentrante1 fixed-bottom" id="contenedorEntrante">
        <img src="logo.png">
    </div>

    <script>
        function showPasswordHint() {
            document.getElementById('passwordHint').style.display = 'block';
        }

        function hidePasswordHint() {
            document.getElementById('passwordHint').style.display = 'none';
        }

        function checkPasswordStrength(password) {
            const strengthSpan = document.getElementById('passwordStrength');
            const regex = {
                upper: /[A-Z]/,
                lower: /[a-z]/,
                number: /[0-9]/,
                special: /[!@#$%^&*(),.?":{}|<>]/
            };
            let strength = 0;

            if (regex.upper.test(password)) strength++;
            if (regex.lower.test(password)) strength++;
            if (regex.number.test(password)) strength++;
            if (regex.special.test(password)) strength++;

            switch (strength) {
                case 0:
                case 1:
                    strengthSpan.textContent = 'Muy débil';
                    strengthSpan.style.color = 'red';
                    break;
                case 2:
                    strengthSpan.textContent = 'Débil';
                    strengthSpan.style.color = 'orange';
                    break;
                case 3:
                    strengthSpan.textContent = 'Fuerte';
                    strengthSpan.style.color = 'blue';
                    break;
                case 4:
                    strengthSpan.textContent = 'Muy fuerte';
                    strengthSpan.style.color = 'green';
                    break;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const contenedor = document.getElementById('contenedorEntrante');
            contenedor.style.transition = 'transform 1s';
            contenedor.style.transform = 'translateY(-100%)';
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const contenedor = document.getElementById('contenedorEntrante');
                contenedor.style.transform = 'translateY(0)';
                setTimeout(() => {
                    form.submit();
                }, 1000);
            });
        });
    </script>

</body>
</html>