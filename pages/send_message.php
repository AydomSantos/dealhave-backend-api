
<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['content'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $content = trim($_POST['content']);
    
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $content);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message_id'] = $conn->insert_id;
        }
    }
}

echo json_encode($response);
?>
