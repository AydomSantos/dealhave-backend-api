
<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
    http_response_code(401);
    exit;
}

$user_id = $_SESSION['user_id'];
$other_user_id = $_GET['user_id'];

// Get user name
$name_query = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($name_query);
$stmt->bind_param("i", $other_user_id);
$stmt->execute();
$user_name = $stmt->get_result()->fetch_assoc()['name'];

// Get messages
$messages_query = "SELECT id, sender_id, content, sent_at 
                  FROM messages 
                  WHERE (sender_id = ? AND receiver_id = ?)
                  OR (sender_id = ? AND receiver_id = ?)
                  ORDER BY sent_at ASC";

$stmt = $conn->prepare($messages_query);
$stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode(['user_name' => $user_name, 'messages' => $messages]);
?>
