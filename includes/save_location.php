<?php
session_start();
require_once 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não está logado']);
    exit;
}

// Verificar se os dados de latitude e longitude foram enviados
if (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
    echo json_encode(['success' => false, 'message' => 'Dados de localização não fornecidos']);
    exit;
}

$user_id = $_SESSION['user_id'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];

// Validar os dados de latitude e longitude
if (!is_numeric($latitude) || !is_numeric($longitude)) {
    echo json_encode(['success' => false, 'message' => 'Dados de localização inválidos']);
    exit;
}

// Atualizar a localização do usuário no banco de dados
$stmt = $conn->prepare("UPDATE users SET latitude = ?, longitude = ? WHERE id = ?");
$stmt->bind_param("ddi", $latitude, $longitude, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Localização atualizada com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar localização: ' . $stmt->error]);
}

$stmt->close();