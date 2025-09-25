<?php
session_start();
header('Content-Type: application/json');

// Database connection
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
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Get user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Use password_verify for hashed passwords
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables to match profile upload requirements
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_department'] = $user['department'] ?? 'Administration';
        $_SESSION['user_profile_picture'] = $user['profile_picture'];
        
        // Also set currentUser array for compatibility with other scripts
        $_SESSION['currentUser'] = [
            'user_id' => $user['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'department' => $user['department'] ?? 'Administration',
            'profile_picture' => $user['profile_picture']
        ];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'user' => $_SESSION['currentUser'] // Send back user data for localStorage
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>