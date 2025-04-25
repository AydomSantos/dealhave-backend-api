<?php
session_start();
require_once '../includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificar se os dados necessários foram enviados
if (!isset($_POST['receiver_id']) || !isset($_POST['content']) || empty($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$content = trim($_POST['content']);

// Inserir a mensagem no banco de dados
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, sent_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $sender_id, $receiver_id, $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message_id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem: ' . $conn->error]);
}

$stmt->close();
?>