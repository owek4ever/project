<?php
session_start();
require_once 'db.php';

// Generate UUID for new users
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

if ($action === 'get') {
    $user_id = $_GET['user_id'] ?? '';
    $result = executeQuery($conn, "SELECT user_id, name, email, role FROM users WHERE user_id = ?", [$user_id]);
    if ($result['success'] && $row = $result['result']->fetch_assoc()) {
        $row['plain_password'] = $_SESSION['temp_passwords'][$user_id] ?? null;
        echo json_encode(['success' => true, 'user' => $row]);
    } else {
        $_SESSION['error_message'] = $result['error'] ?? 'User not found';
        echo json_encode(['success' => false, 'error' => $result['error'] ?? 'User not found']);
    }
} elseif ($action === 'add') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'employee';
    $user_id = generateUUID();

    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = 'All fields are required';
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    if (!isset($_SESSION['temp_passwords'])) {
        $_SESSION['temp_passwords'] = [];
    }
    $_SESSION['temp_passwords'][$user_id] = $password;

    $result = executeQuery($conn, 
        "INSERT INTO users (user_id, name, email, role, password) VALUES (?, ?, ?, ?, ?)",
        [$user_id, $name, $email, $role, $hashed_password]
    );

    if ($result['success']) {
        $_SESSION['success_message'] = "User created successfully!";
        echo json_encode(['success' => true]);
    } else {
        $_SESSION['error_message'] = $result['error'] ?? 'Error creating user';
        echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Error creating user']);
    }
} elseif ($action === 'update') {
    $user_id = $_POST['user_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'employee';
    $password = $_POST['password'] ?? '';

    if (empty($user_id) || empty($name) || empty($email)) {
        $_SESSION['error_message'] = 'User ID, name, and email are required';
        echo json_encode(['success' => false, 'error' => 'User ID, name, and email are required']);
        exit;
    }

    $params = [$name, $email, $role];
    $query = "UPDATE users SET name = ?, email = ?, role = ?";

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query .= ", password = ?";
        $params[] = $hashed_password;
        if (!isset($_SESSION['temp_passwords'])) {
            $_SESSION['temp_passwords'] = [];
        }
        $_SESSION['temp_passwords'][$user_id] = $password;
    }
    $query .= " WHERE user_id = ?";
    $params[] = $user_id;

    $result = executeQuery($conn, $query, $params);
    if ($result['success']) {
        $_SESSION['success_message'] = "User updated successfully!";
        echo json_encode(['success' => true]);
    } else {
        $_SESSION['error_message'] = $result['error'] ?? 'Error updating user';
        echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Error updating user']);
    }
} elseif ($action === 'delete') {
    $user_id = $_POST['user_id'] ?? '';
    if (empty($user_id)) {
        $_SESSION['error_message'] = 'User ID is required';
        echo json_encode(['success' => false, 'error' => 'User ID is required']);
        exit;
    }

    $result = executeQuery($conn, "DELETE FROM users WHERE user_id = ?", [$user_id]);
    if (isset($_SESSION['temp_passwords'][$user_id])) {
        unset($_SESSION['temp_passwords'][$user_id]);
    }
    if ($result['success']) {
        $_SESSION['success_message'] = "User deleted successfully!";
        echo json_encode(['success' => true]);
    } else {
        $_SESSION['error_message'] = $result['error'] ?? 'Error deleting user';
        echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Error deleting user']);
    }
} else {
    $_SESSION['error_message'] = 'Invalid action';
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>