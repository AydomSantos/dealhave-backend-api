<?php
session_start();
require_once 'includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Usuário não autenticado";
    exit;
}

// Verificar se os dados de latitude e longitude foram enviados
if (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
    http_response_code(400);
    echo "Dados de localização não fornecidos";
    exit;
}

$user_id = $_SESSION['user_id'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];

// Validar os dados de latitude e longitude
if (!is_numeric($latitude) || !is_numeric($longitude)) {
    http_response_code(400);
    echo "Dados de localização inválidos";
    exit;
}

// Atualizar a localização do usuário no banco de dados
$stmt = $conn->prepare("UPDATE users SET latitude = ?, longitude = ? WHERE id = ?");
$stmt->bind_param("ddi", $latitude, $longitude, $user_id);

if ($stmt->execute()) {
    echo "Localização atualizada com sucesso";
} else {
    http_response_code(500);
    echo "Erro ao atualizar localização: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>