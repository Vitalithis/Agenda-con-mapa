<?php
session_start();
require('../conexion.php');

$isTokenValid = false; // Variable para determinar si el token es válido

// Verificar si el token está presente en la URL
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conexion, $_GET['token']);

    // Buscar el token en la base de datos
    $query = "SELECT * FROM users WHERE recovery_token = '$token' LIMIT 1";
    $result = mysqli_query($conexion, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verificar si el token ha expirado
        if (strtotime($user['token_expiry']) > time()) {
            $isTokenValid = true; // El token es válido
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Enlace expirado',
                    text: 'El enlace de recuperación ha expirado.',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = '../login/login.php';
                });
            </script>";
            exit;
        }
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Token inválido',
                text: 'El token no es válido.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = '../login/login.php';
            });
        </script>";
        exit;
    }
} else {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Token no proporcionado',
            text: 'No se ha proporcionado un token válido.',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = '../login/login.php';
        });
    </script>";
    exit;
}

// Manejar la solicitud POST para actualizar la contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isTokenValid) {
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    // Actualizar la contraseña en la base de datos
    $updateQuery = "UPDATE users SET password = '$new_password', recovery_token = NULL, token_expiry = NULL WHERE recovery_token = '$token'";
    if (mysqli_query($conexion, $updateQuery)) {
        echo json_encode(['status' => 'success', 'message' => 'Tu contraseña ha sido cambiada exitosamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hubo un error al actualizar la contraseña.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: rgba(0, 128, 255, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
        h2, label {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-6 col-lg-4">
                <div class="card text-center">
                    <h2 class="text-center mb-4">Cambiar Contraseña</h2>
                    <form id="changePasswordForm">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Cambiar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById("changePasswordForm").addEventListener("submit", function (e) {
            e.preventDefault(); // Detener envío del formulario

            const formData = new URLSearchParams();
            formData.append("new_password", document.getElementById("new_password").value);

            const token = new URLSearchParams(window.location.search).get('token'); // Obtener el token de la URL

            fetch(`recuperar_contrasena_confirmar.php?token=${token}`, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: data.status === "success" ? "success" : (data.status === "exists" ? "info" : "error"),
                    title: data.message,
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });

                if (data.status === "success") {
                    setTimeout(() => {
                        window.location.href = "../login/login.php"; // Redirigir a login.php
                    }, 3000); // Esperar hasta que termine la notificación
                }
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Hubo un problema al intentar cambiar tu contraseña. Por favor, intenta de nuevo.",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            });
        });
    </script>
</body>
</html>