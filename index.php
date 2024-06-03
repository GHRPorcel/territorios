<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "inter123";
$dbname = "sistema_territorio";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Inserir dados no banco
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    $numero_territorio = $_POST['numero_territorio'];
    $dirigente = $_POST['dirigente'];
    $data_entrega = $_POST['data_entrega'];

    $sql = "INSERT INTO territorios (user_id, numero_territorio, dirigente, data_entrega) VALUES ('$user_id', '$numero_territorio', '$dirigente', '$data_entrega')";
    $conn->query($sql);
}

// Excluir dados do banco
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM territorios WHERE id=$id AND user_id='$user_id'");
}

// Finalizar território
if (isset($_GET['finalize'])) {
    $id = $_GET['finalize'];
    $data_finalizacao = date('Y-m-d');
    $conn->query("UPDATE territorios SET data_finalizacao='$data_finalizacao' WHERE id=$id AND user_id='$user_id'");
}

// Editar dados do banco
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $numero_territorio = $_POST['numero_territorio'];
    $dirigente = $_POST['dirigente'];
    $data_entrega = $_POST['data_entrega'];

    $conn->query("UPDATE territorios SET numero_territorio='$numero_territorio', dirigente='$dirigente', data_entrega='$data_entrega' WHERE id=$id AND user_id='$user_id'");
}

// Exportar para Excel
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=territorios.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Número de Território', 'Dirigente', 'Data de Entrega', 'Data de Finalização'));
    $rows = $conn->query("SELECT * FROM territorios WHERE user_id='$user_id'");
    while ($row = $rows->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}
$territorios = $conn->query("SELECT * FROM territorios WHERE user_id='$user_id' ORDER BY numero_territorio ASC");

// Salvar dados do gráfico
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateChart'])) {
    $totalTerritories = $_POST['totalTerritories'];
    $completedTerritories = $_POST['completedTerritories'];
    $porcentage = ($completedTerritories / $totalTerritories) * 100;
    $data = date('Y-m-d');

    $sql = "INSERT INTO grafico_territorios (user_id, data, porcentage) VALUES ('$user_id', '$data', '$porcentage')";
    $conn->query($sql);
}

// Excluir dados do gráfico
if (isset($_GET['delete_chart'])) {
    $id = $_GET['delete_chart'];
    $conn->query("DELETE FROM grafico_territorios WHERE id=$id AND user_id='$user_id'");
}

$grafico_territorios = $conn->query("SELECT * FROM grafico_territorios WHERE user_id='$user_id' ORDER BY data DESC");

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Territórios</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            display: flex;
            gap: 20px;
        }
        .card {
            flex: 1;
        }
        .chart-table-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #36a2eb;
            color: white;
            text-align: left;
        }
        .welcome {
            font-size: 1.5em;
            margin-bottom: 20px;
        }
        .logout-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            float: right;
            margin: 10px;
            font-size: 1em;
            border-radius: 5px;
        }
        .logout-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <h1>Sistema de Territórios</h1>
    <div class="welcome">
        Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        <a href="logout.php" class="logout-btn">Sair</a>
    </div>

    <div class="container">
        <div class="card">
            <form method="post" action="index.php">
                <input type="hidden" name="id" id="id">
                <label for="numero_territorio">Número de Território:</label>
                <input type="number" name="numero_territorio" id="numero_territorio" required><br>
                <label for="dirigente">Dirigente:</label>
                <input type="text" name="dirigente" id="dirigente" required><br>
                <label for="data_entrega">Data de Entrega:</label>
                <input type="date" name="data_entrega" id="data_entrega" required><br><br>
                <button type="submit" name="save">Salvar</button>
            </form>
            <br>
            <form action="index.php" method="get">
                <button type="submit" name="export">Exportar para Excel</button>
            </form>
        </div>

        <div class="card">
            <form method="post" action="index.php">
                <label for="totalTerritories">Total de Territórios:</label>
                <input type="number" name="totalTerritories" id="totalTerritories" required><br><br>
                <label for="completedTerritories">Territórios Finalizados:</label>
                <input type="number" name="completedTerritories" id="completedTerritories" required><br><br>
                <button type="submit" name="updateChart">Atualizar</button>
            </form>
            <canvas id="territoriesChart"></canvas>
        </div>
    </div>

    <div class="chart-table-container">
        <table>
            <tr>
                <th>Número de Território</th>
                <th>Dirigente</th>
                <th>Data de Entrega</th>
                <th>Data de Finalização</th>
                <th>Ações</th>
            </tr>
            <?php while ($row = $territorios->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['numero_territorio']; ?></td>
                    <td><?php echo $row['dirigente']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['data_entrega'])); ?></td>
                    <td><?php echo $row['data_finalizacao'] ? date('d/m/Y', strtotime($row['data_finalizacao'])) : ''; ?></td>
                    <td>
                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                        <a href="?finalize=<?php echo $row['id']; ?>">Finalizar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <br>

        <table>
            <tr>
                <th>Data da Consulta</th>
                <th>Porcentagem</th>
                <th>Ações</th>
            </tr>
            <?php while ($row = $grafico_territorios->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($row['data'])); ?></td>
                    <td><?php echo number_format($row['porcentage'], 2) . '%'; ?></td>
                    <td>
                        <a href="?delete_chart=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>

        window.addEventListener('unload', function() {
            // Requisição assíncrona para limpar a sessão
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'logout.php', true);
            xhr.send();
        });
        // Configurações do gráfico
        var ctx = document.getElementById('territoriesChart').getContext('2d');
        var territoriesChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Territórios Completos', 'Territórios Pendentes'],
                datasets: [{
                    label: 'Porcentagem de Territórios Completos',
                    data: [
                        <?php echo isset($completedTerritories) ? $completedTerritories : 0; ?>,
                        <?php echo isset($totalTerritories) && isset($completedTerritories) ? $totalTerritories - $completedTerritories : 0; ?>
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Porcentagem de Territórios Completos'
                    }
                }
            }
        });
    </script>
</body>
</html>