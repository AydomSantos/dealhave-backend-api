
<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['user_id']) || !isset($_GET['last_id'])) {
    http_response_code(401);
    exit;
}

$user_id = $_SESSION['user_id'];
$other_user_id = $_GET['user_id'];
$last_id = $_GET['last_id'];

$query = "SELECT id, sender_id, content, sent_at 
          FROM messages 
          WHERE id > ? AND
          ((sender_id = ? AND receiver_id = ?) OR 
           (sender_id = ? AND receiver_id = ?))
          ORDER BY sent_at ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiiii", $last_id, $user_id, $other_user_id, $other_user_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode(['messages' => $messages]);
?>
