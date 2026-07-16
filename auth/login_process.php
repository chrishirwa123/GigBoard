<?php

require "../config/session.php";

require "../config/database.php";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit();
}

// Get form data
$login = trim($_POST['login']);
$password = $_POST['password'];

// Validate inputs
if (empty($login) || empty($password)) {
    die("Please fill in all fields.");
}

// Find user by email or username
$stmt = $conn->prepare("
    SELECT * FROM users
    WHERE email = ? OR username = ?
");

$stmt->execute([$login, $login]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if account exists
if (!$user) {
    die("Account not found.");
}

// Verify password
if (!password_verify($password, $user['password'])) {
    die("Incorrect password.");
}

// Save session
$_SESSION['user_id'] = $user['id'];
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

// Redirect according to role
switch ($user['role']) {

    case 'worker':
        header("Location: ../worker/dashboard.php");
        exit();

    case 'employer':
        header("Location: ../employer/dashboard.php");
        exit();

    case 'admin':
        header("Location: ../admin/dashboard.php");
        exit();

    default:
        session_destroy();
        die("Invalid account type.");
}
?>