<?php
require "../config/session.php";
require "../config/database.php";

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if($_SESSION['role'] !== "worker"){
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

$worker_id = $_SESSION['user_id'];

if(!isset($_GET['application_id'])){
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing application_id']);
    exit();
}

$application_id = (int)$_GET['application_id'];
$after_id = isset($_GET['after_id']) ? (int)$_GET['after_id'] : 0;

// Same ownership check as chat.php: this worker must own the accepted
// application before they can read any of its messages.
$check = $conn->prepare("
SELECT applications.id
FROM applications
WHERE applications.id = ?
AND applications.worker_id = ?
AND applications.status = 'accepted'
");
$check->execute([$application_id, $worker_id]);

if(!$check->fetch(PDO::FETCH_ASSOC)){
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'You cannot access this chat']);
    exit();
}

$msg = $conn->prepare("
SELECT messages.*, users.fullname
FROM messages
INNER JOIN users ON messages.sender_id = users.id
WHERE messages.application_id = ?
AND messages.id > ?
ORDER BY messages.created_at ASC
");
$msg->execute([$application_id, $after_id]);
$rows = $msg->fetchAll(PDO::FETCH_ASSOC);

$messages = array_map(function($m) use ($worker_id) {
    return [
        'id'        => (int)$m['id'],
        'sender_id' => (int)$m['sender_id'],
        'fullname'  => $m['fullname'],
        'message'   => $m['message'],
        'time'      => date('H:i', strtotime($m['created_at'])),
        'date_key'  => date('Y-m-d', strtotime($m['created_at'])),
        'mine'      => ((int)$m['sender_id'] === (int)$worker_id),
    ];
}, $rows);

echo json_encode(['success' => true, 'messages' => $messages]);