<?php
// user_save.php – Create or update admin user in billing_login.user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$id       = isset($_POST['id'])       ? (int)$_POST['id']                                  : 0;
$username = isset($_POST['username']) ? trim($conn_login->real_escape_string($_POST['username'])) : '';
$password = isset($_POST['password']) ? trim($_POST['password'])                            : '';
$role     = isset($_POST['role'])     ? strtoupper(trim($conn_login->real_escape_string($_POST['role']))) : 'ADMIN';

// Validate username
if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
    echo json_encode(['success' => false, 'message' => 'Invalid username. Use 3–30 alphanumeric characters or underscores.']);
    exit();
}

// Validate password required for new users
if ($id === 0 && $password === '') {
    echo json_encode(['success' => false, 'message' => 'Password is required for new users.']);
    exit();
}

$now = date('Y-m-d H:i:s');

if ($id > 0) {
    // UPDATE existing user
    if ($password !== '') {
        $escaped_pass = $conn_login->real_escape_string($password);
        $sql = "UPDATE user SET role = '$role', password = '$escaped_pass' WHERE id = $id";
    } else {
        $sql = "UPDATE user SET role = '$role' WHERE id = $id";
    }
    if ($conn_login->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . $conn_login->error]);
    }
} else {
    // INSERT new user – check for duplicate username
    $check = $conn_login->query("SELECT id FROM user WHERE username = '$username'");
    if ($check && $check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => "Username '$username' already exists."]);
        exit();
    }

    $escaped_pass = $conn_login->real_escape_string($password);
    $sql = "INSERT INTO user (username, password, role, createdOn)
            VALUES ('$username', '$escaped_pass', '$role', '$now')";
    if ($conn_login->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $conn_login->error]);
    }
}
