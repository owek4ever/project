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

$input = json_decode(file_get_contents('php://input'), true);
$request_user_id = $input['user_id'] ?? '';

if ($request_user_id !== $user_id) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized user']);
    exit;
}

// Get current profile picture
$stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile_picture = $stmt->fetchColumn();

$success = false;
$message = '';

if ($profile_picture && file_exists($profile_picture)) {
    unlink($profile_picture);
    $thumbPath = str_replace('original/', 'thumbnails/', $profile_picture);
    if (file_exists($thumbPath)) {
        unlink($thumbPath);
    }
    
    $stmt = $pdo->prepare("UPDATE users SET profile_picture = NULL, updated_at = CURRENT_TIMESTAMP() WHERE user_id = ?");
    $success = $stmt->execute([$user_id]);
    
    if (isset($_SESSION['currentUser']) && is_array($_SESSION['currentUser'])) {
        $_SESSION['currentUser']['profile_picture'] = null;
    }
    
    $message = 'Profile picture removed successfully';
} else {
    $stmt = $pdo->prepare("UPDATE users SET profile_picture = NULL, updated_at = CURRENT_TIMESTAMP() WHERE user_id = ?");
    $success = $stmt->execute([$user_id]);
    $message = 'Profile picture cleared successfully';
}

ob_end_clean();
echo json_encode([
    'success' => $success,
    'message' => $message
]);
?>