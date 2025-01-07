<?php
session_start();
include('../conexion.php'); // Asegúrate de incluir tu archivo de conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verificar que el correo electrónico no esté ya registrado
    $query_email = "SELECT * FROM users WHERE email = '$email'";
    $result_email = mysqli_query($conexion, $query_email);

    // Comprobar si hay algún error
    if (!$result_email) {
        $error_message = 'Error en la consulta a la base de datos: ' . mysqli_error($conexion);
    } else {
        // Verificar si el correo electrónico ya está en uso
        if (mysqli_num_rows($result_email) > 0) {
            $error_message = 'El correo electrónico ya está en uso.';
        } else {
            // Hashear la contraseña antes de almacenarla
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar nuevo usuario
            $query_insert = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', 'user')";
            if (mysqli_query($conexion, $query_insert)) {
                $_SESSION['user_id'] = mysqli_insert_id($conexion);
                $_SESSION['role'] = 'user'; // Asignar rol por defecto

                header('Location: ../login/user_panel.php');
                exit();
            } else {
                $error_message = 'Error al registrar el usuario: ' . mysqli_error($conexion);
            }
        }
    }
}
?>