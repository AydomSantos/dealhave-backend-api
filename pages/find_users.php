
<?php
session_start();
require_once '../includes/db.php';

// Verificar autenticação e parâmetro de busca
if (!isset($_SESSION['user_id']) || !isset($_GET['search'])) {
    http_response_code(401);
    exit;
}

// Preparar a busca
$search = '%' . $_GET['search'] . '%';
$current_user = $_SESSION['user_id'];

// Consultar usuários que correspondem à busca
$query = "SELECT id, name FROM users WHERE name LIKE ? AND id != ? LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $search, $current_user);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

// Retornar resultados em formato JSON
header('Content-Type: application/json');
echo json_encode($users);
?>
