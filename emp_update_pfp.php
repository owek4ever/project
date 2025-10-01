<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

session_start();
header('Content-Type: application/json');

// Database connection (same as above)
$host = '127.0.0.1';
$db   = 'user_management_system';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check session
$user_id = $_SESSION['user_id'] ?? ($_SESSION['currentUser']['user_id'] ?? null);
if (!$user_id) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$session_user_id = $user_id;
$request_user_id = $_POST['user_id'] ?? '';

if ($request_user_id !== $session_user_id) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized user']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$department = trim($_POST['department'] ?? '');

// Validation
if (empty($name) || strlen($name) < 2) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Name must be at least 2 characters']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Valid email is required']);
    exit;
}

// Check email uniqueness
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
$stmt->execute([$email, $user_id]);
if ($stmt->fetch()) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Email address is already taken']);
    exit;
}

// Update user
try {
    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, email = ?, department = ?, updated_at = CURRENT_TIMESTAMP() 
        WHERE user_id = ?
    ");
    $success = $stmt->execute([$name, $email, $department, $user_id]);
    
    if ($success) {
        // Update session data
        if (isset($_SESSION['currentUser']) && is_array($_SESSION['currentUser'])) {
            $_SESSION['currentUser']['name'] = $name;
            $_SESSION['currentUser']['email'] = $email;
            $_SESSION['currentUser']['department'] = $department;
        }
    }
    
    ob_end_clean();
    echo json_encode([
        'success' => $success, 
        'message' => $success ? 'Profile updated successfully' : 'Failed to update profile'
    ]);
} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>