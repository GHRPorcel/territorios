<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "inter123";
$dbname = "sistema_territorio";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $username = $_POST['login_username'];
        $password = $_POST['login_password'];

        $sql = "SELECT * FROM usuario WHERE username='$username'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['pass'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit();
            } else {
                echo "Senha incorreta.";
            }
        } else {
            echo "Usuário não encontrado.";
        }
    } elseif (isset($_POST['register'])) {
        $username = $_POST['register_username'];
        $password = password_hash($_POST['register_password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuario (username, pass) VALUES ('$username', '$password')";
        if ($conn->query($sql) === TRUE) {
            echo "Registro bem-sucedido. Faça login.";
        } else {
            echo "Erro: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login e Registro</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

    <div class="card">
        <div class="toggle">
            <button id="login-toggle" class="active">Login</button>
            <button id="register-toggle">Registro</button>
        </div>
        <div class="form-container">
            <form id="login-form" method="post" action="login.php">
                <label for="login_username">Nome de Usuário:</label>
                <input type="text" name="login_username" id="login_username" required><br>
                <label for="login_password">Senha:</label>
                <input type="password" name="login_password" id="login_password" required><br><br>
                <button type="submit" name="login">Login</button>
            </form>
            <form id="register-form" method="post" action="login.php" style="display:none;">
                <label for="register_username">Nome de Usuário:</label>
                <input type="text" name="register_username" id="register_username" required><br>
                <label for="register_password">Senha:</label>
                <input type="password" name="register_password" id="register_password" required><br><br>
                <button type="submit" name="register">Registrar</button>
            </form>
        </div>
    </div>
    <script>
        // Script para alternar entre as abas de login e registro
        const loginToggle = document.getElementById('login-toggle');
        const registerToggle = document.getElementById('register-toggle');
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');

        loginToggle.addEventListener('click', () => {
            loginToggle.classList.add('active');
            registerToggle.classList.remove('active');
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
        });

        registerToggle.addEventListener('click', () => {
            registerToggle.classList.add('active');
            loginToggle.classList.remove('active');
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        });
    </script>
</body>
</html>