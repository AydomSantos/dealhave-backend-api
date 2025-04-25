<?php
session_start();
require_once '../includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificar se o ID do usuário para chat foi fornecido
if (!isset($_GET['user']) || !is_numeric($_GET['user'])) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido']);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$chat_user_id = $_GET['user'];
$since = isset($_GET['since']) && !empty($_GET['since']) ? $_GET['since'] : '1970-01-01 00:00:00';

// Obter novas mensagens
$query = "
    SELECT m.*, u.name as sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE ((m.sender_id = ? AND m.receiver_id = ?) 
        OR (m.sender_id = ? AND m.receiver_id = ?))
        AND m.sent_at > ?
    ORDER BY m.sent_at ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiiss", $current_user_id, $chat_user_id, $chat_user_id, $current_user_id, $since);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['success' => true, 'messages' => $messages]);
?>