<?php
require "../config/session.php";
require "../config/database.php";

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if($_SESSION['role'] !== "employer"){
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

$employer_id = $_SESSION['user_id'];

if(!isset($_GET['application_id'])){
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing application_id']);
    exit();
}

$application_id = (int)$_GET['application_id'];
$after_id = isset($_GET['after_id']) ? (int)$_GET['after_id'] : 0;

// Same ownership check as chat.php: this employer must own the job that
// this accepted application belongs to.
$check = $conn->prepare("
SELECT applications.id
FROM applications
INNER JOIN jobs ON applications.job_id = jobs.id
WHERE applications.id = ?
AND jobs.employer_id = ?
AND applications.status = 'accepted'
");
$check->execute([$application_id, $employer_id]);

if(!$check->fetch(PDO::FETCH_ASSOC)){
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'You cannot access this chat']);
    exit();
}

$msg = $conn->prepare("
SELECT messages.*, users.fullname
FROM messages
INNER JOIN users ON messages.sender_id = users.id
WHERE application_id = ?
AND messages.id > ?
ORDER BY messages.created_at ASC
");
$msg->execute([$application_id, $after_id]);
$rows = $msg->fetchAll(PDO::FETCH_ASSOC);

$messages = array_map(function($m) {
    return [
        'id'         => (int)$m['id'],
        'sender_id'  => (int)$m['sender_id'],
        'fullname'   => $m['fullname'],
        'message'    => $m['message'],
        'time'       => date('H:i', strtotime($m['created_at'])),
    ];
}, $rows);

echo json_encode(['success' => true, 'messages' => $messages]);