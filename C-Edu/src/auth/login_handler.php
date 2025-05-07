<?php
session_start();
// Incluir el archivo de conexión a la base de datos
// La ruta es relativa desde src/auth/login_handler.php hacia src/config/database.php
require_once __DIR__ . '/../config/database.php';

// Check if the form was submitted using POST method and the required fields are set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['password'], $_POST['role'])) {

    // Get the submitted email, password, and the role identifier from the form
    $email = $_POST['email'];
    $password = $_POST['password'];
    $submitted_role = $_POST['role']; // This will be 'docente' or 'administrativo'

    // Prepare the SQL query to find the user by email
    // FIXED: Removed special characters to avoid encoding issues
    $stmt = $conn->prepare("SELECT id_usuario, email_usuario, contrasena_usuario, id_rol FROM usuario WHERE email_usuario = ?");

    // Check if the statement preparation was successful
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the email parameter to the statement
    $stmt->bind_param("s", $email);

    // Execute the statement
    $stmt->execute();

    // Get the result of the query
    $resultado = $stmt->get_result();

    // Check if a user with that email was found
    if ($resultado->num_rows > 0) {
        // Fetch the user data
        $fila = $resultado->fetch_assoc();

        // Verify the submitted password against the hashed password in the database
        if (password_verify($password, $fila['contrasena_usuario'])) {

            // Password is correct. Now check if the user's role matches the submitted form's role.
            $database_role_id = $fila['id_rol']; // Get the role ID from the database (1 for Docente, etc.)

            // Determine the expected role ID based on the submitted form role name
            $expected_role_id = null;
            if ($submitted_role == 'docente') {
                $expected_role_id = 1; // Assuming id_rol = 1 corresponds to Docente
            } elseif ($submitted_role == 'administrativo') {
                // Assuming id_rol = 2 (or anything other than 1) corresponds to Administrativo
                // We'll check if the database role is NOT 1 for admin login
                $expected_role_id = 2; // We'll use 2 as a placeholder for admin role ID
            }

            // Check if the database role matches the expected role from the form
            $role_match = false;
            if ($submitted_role == 'docente' && $database_role_id == 1) {
                 $role_match = true; // User is Docente and used Docente form
            } elseif ($submitted_role == 'administrativo' && $database_role_id != 1) { // User is NOT Docente (Admin) and used Admin form
                 $role_match = true;
            }
            // If you have a specific ID for Admin role (e.g., 2), you could use:
            // } elseif ($submitted_role == 'administrativo' && $database_role_id == 2) {
            //      $role_match = true; // User is Admin and used Admin form
            // }


            if ($role_match) {
                // Roles match! User is authenticated and used the correct form for their role.
                // Store user data in session variables
                $_SESSION['id_usuario'] = $fila['id_usuario'];
                // FIXED: Check if nombre_usuario exists in the result before assigning
                if(isset($fila['nombre_usuario'])) {
                    $_SESSION['nombre_usuario'] = $fila['nombre_usuario'];
                }
                $_SESSION['rol'] = $database_role_id; // Store the actual role ID from the database

                // Redirect based on the user's actual role ID from the database
                if ($database_role_id == 1) {
                    header("Location: docente_dashboard.php");
                } else { // Redirect admin based on their actual role (anything not 1)
                    header("Location: admin_dashboard.php");
                }
                exit(); // Stop script execution after redirection

            } else {
                // Roles do NOT match (e.g., Admin trying to log in as Docente or vice versa)
                $error_message = ($submitted_role == 'docente')
                               ? 'Tu cuenta no tiene permisos para iniciar sesión como Docente.'
                               : 'Tu cuenta no tiene permisos para iniciar sesión como Administrativo.';
                echo "<script>alert('" . $error_message . "'); window.location.href='index.php';</script>";
            }

        } else {
            // Password was incorrect
            echo "<script>alert('Contraseña incorrecta'); window.location.href='index.php';</script>";
        }
    } else {
        // User email not found in the database
        echo "<script>alert('Usuario no registrado'); window.location.href='index.php';</script>";
    }

    // Close the statement and database connection
    $stmt->close();
    $conn->close();

} else {
    // If the script was accessed directly or required POST data is missing
    // Redirect back to the login page
    header("Location: index.php");
    exit();
}
?>