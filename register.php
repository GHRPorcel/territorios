<?php
$servername = "localhost";
$username = "root";
$password = "inter123";
$dbname = "sistema_territorio";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['pass'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuario (username, pass) VALUES ('$username', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "Registro bem-sucedido!";
    } else {
        echo "Erro: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuário</title>
</head>
<body>
    <h1>Registro de Usuário</h1>
    <form method="post" action="register.php">
        <label for="username">Nome de Usuário:</label>
        <input type="text" name="username" id="username" required><br>
        <label for="pass">Senha:</label>
        <input type="pass" name="pass" id="pass" required><br><br>
        <button type="submit">Registrar</button>
    </form>
</body>
</html>
