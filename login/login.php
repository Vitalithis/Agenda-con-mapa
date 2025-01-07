<?php
session_start();
include('../conexion.php');

// Inicializar mensajes
$message = '';
$error_message = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Manejar la acción de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conexion, $query);

    if ($result) {
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            header('Location: ../index.php');
            exit();
        } else {
            $_SESSION['error_message'] = 'Correo o contraseña incorrectos.';
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'Error en la consulta a la base de datos.';
        header('Location: login.php');
        exit();
    }
}

// Manejar la acción de registro
// Manejar la acción de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conexion, $_POST['reg_username']);
    $email = mysqli_real_escape_string($conexion, $_POST['reg_email']);
    $password = mysqli_real_escape_string($conexion, $_POST['reg_password']);
    $default_img = 'https://static.vecteezy.com/system/resources/previews/007/167/661/non_2x/user-blue-icon-isolated-on-white-background-free-vector.jpg';

    // Verificar si el correo ya existe
    $sql_check = "SELECT * FROM users WHERE email = ?";
    $stmt_check = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_check, 's', $email);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $_SESSION['error_message'] = 'El correo electrónico ya está registrado.';
        mysqli_stmt_close($stmt_check);
        header('Location: login.php');
        exit();
    } else {
        // Insertar el usuario con la imagen predeterminada
        $sql_insert = "INSERT INTO users (username, email, password, role, img) VALUES (?, ?, ?, 'user', ?)";
        $stmt_insert = mysqli_prepare($conexion, $sql_insert);

        if ($stmt_insert) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt_insert, 'ssss', $username, $email, $hashed_password, $default_img);

            if (mysqli_stmt_execute($stmt_insert)) {
                $_SESSION['message'] = 'Usuario registrado exitosamente.';
                mysqli_stmt_close($stmt_insert);
                header('Location: login.php');
                exit();
            } else {
                $_SESSION['error_message'] = 'Error al registrar el usuario. Inténtalo de nuevo.';
                mysqli_stmt_close($stmt_insert);
                header('Location: login.php');
                exit();
            }
        } else {
            $_SESSION['error_message'] = 'Error al preparar la consulta de inserción.';
            mysqli_stmt_close($stmt_check);
            header('Location: login.php');
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: rgba(0, 128, 255, 0.1);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 5vh;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background-color: rgba(0, 128, 255, 0.5);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            padding: 2rem;
        }
        .btn-primary {
            background-color: rgba(0, 128, 255, 0.9);
            border: none;
        }
        .btn-primary:hover {
            background-color: rgba(0, 128, 255, 1);
        }
        h1 {
            color: #fff;
        }
        label, p {
            color: #ffffff;
        }
        .alert {
            border-radius: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <a href="../index.php">
                <img class="logo img-fluid w-25 rounded-pill" src="../logopng.png" alt="Logo">
            </a>
        </div>
        <!-- Contenedor de Login -->
        <div class="row justify-content-center" id="login-container">
            <div class="col-md-6">
                <div class="card text-center">
                    <h1>Iniciar sesión</h1>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger mt-3"><?= $error_message ?></div>
                    <?php elseif (!empty($message)): ?>
                        <div class="alert alert-success mt-3"><?= $message ?></div>
                    <?php endif; ?>

                    <form method="POST" class="mt-4">
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" placeholder="ejemplo@ejemplo.cl" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" placeholder="contraseña" name="password" required>
                        </div>
                        <p class="mt-2">
                            <a href="recuperar_contrasena.php" class="text-decoration-none text-white">¿Olvidaste tu contraseña?</a>
                        </p>
                        <button type="submit" name="login" class="btn btn-primary w-100">Iniciar Sesión</button>
                    </form>
                    <p class="mt-3">¿No tienes una cuenta? <a href="#" onclick="toggleForms()" class="text-white">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
        
        <!-- Contenedor de Registro -->
        <div class="row justify-content-center" id="register-container" style="display: none;">
            <div class="col-md-6">
                <div class="card text-center">
                    <h1>Registrarse</h1>
                    <form method="POST" class="mt-4">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="usuario" placeholder="Usuario" name="reg_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" placeholder="ejemplo@ejemplo.cl" name="reg_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" placeholder="Contraseña" name="reg_password" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100">Registrarse</button>
                    </form>
                    <p class="mt-3">¿Ya tienes una cuenta? <a href="#" onclick="toggleForms()" class="text-white">Inicia sesión aquí</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleForms() {
        const loginContainer = document.getElementById('login-container');
        const registerContainer = document.getElementById('register-container');
        loginContainer.style.display = loginContainer.style.display === 'none' ? 'flex' : 'none';
        registerContainer.style.display = registerContainer.style.display === 'none' ? 'flex' : 'none';
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
