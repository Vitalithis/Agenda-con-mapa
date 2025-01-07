<?php
// Incluimos el archivo autoload de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

session_start();
require('../conexion.php');

// Configurar cabeceras para devolver JSON si es una petición fetch
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conexion, $_POST['email']);

    // Verificar si el correo existe en la base de datos
    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conexion, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Generar un token único para la recuperación de contraseña
        $token = bin2hex(random_bytes(50)); // Token único
        $token_expiry = date("Y-m-d H:i:s", strtotime('+1 hour')); // Válido 1 hora

        // Guardar el token en la base de datos
        $updateQuery = "UPDATE users SET recovery_token = '$token', token_expiry = '$token_expiry' WHERE email = '$email'";
        if (mysqli_query($conexion, $updateQuery)) {
            // Generar la URL de recuperación
            $url = "http://" . $_SERVER['HTTP_HOST'] . "/xampp/TIS1/login/recuperar_contrasena_confirmar.php?token=$token";

            // Configuración de PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'tisnology1@gmail.com';
                $mail->Password = 'ytfksqrqrginpvge';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';

                $mail->setFrom('tisnology1@gmail.com', 'Tisnology');
                $mail->addAddress($email);

                $mail->Subject = 'Recuperación de contraseña';
                $mail->Body = "Hola, para recuperar tu contraseña, haz clic en el siguiente enlace:\n\n$url";
                $mail->send();

                echo json_encode(['status' => 'success', 'message' => 'Correo enviado correctamente.']);
                exit;
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo enviar el correo.']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al generar el token.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Correo no registrado.']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        h2, label, p {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-6 col-lg-4">
                <div class="card text-center">
                    <h2 class="text-center mb-4">Recuperar Contraseña</h2>
                    <form id="passwordRecoveryForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@correo.com" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Recuperar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    document.getElementById("passwordRecoveryForm").addEventListener("submit", function (e) {
        e.preventDefault(); // Detener envío del formulario

        const formData = new URLSearchParams();
        formData.append("email", document.getElementById("email").value);

        fetch("recuperar_contrasena.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                icon: data.status === "success" ? "success" : "error",
                title: data.message,
                toast: true,
                position: "top-end",
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });

            if (data.status === "success") {
                document.getElementById("email").value = ""; // Limpiar formulario
            }
        })
        .catch(error => {
            console.error("Error:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo enviar el correo. Intenta nuevamente más tarde.",
                toast: true,
                position: "top-end",
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        });
    });
</script>
</html>