<?php
session_start();
require_once 'includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Não autorizado";
    exit;
}

// Verificar se os dados foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['latitude']) && isset($_POST['longitude'])) {
    $user_id = $_SESSION['user_id'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    
    // Validar os dados
    if (!is_numeric($latitude) || !is_numeric($longitude)) {
        http_response_code(400);
        echo "Dados inválidos";
        exit;
    }
    
    // Atualizar a localização do usuário
    $stmt = $conn->prepare("UPDATE users SET latitude = ?, longitude = ? WHERE id = ?");
    $stmt->bind_param("ddi", $latitude, $longitude, $user_id);
    
    if ($stmt->execute()) {
        echo "Localização atualizada com sucesso";
    } else {
        http_response_code(500);
        echo "Erro ao atualizar localização: " . $conn->error;
    }
    
    $stmt->close();
} else {
    http_response_code(400);
    echo "Requisição inválida";
}
?>