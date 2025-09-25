<?php
session_start();
header('Content-Type: application/json');

// Database connection
$host = '127.0.0.1';
$db   = 'user_management_system';
$user = 'root'; // Your MySQL username
$pass = '';     // Your MySQL password
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

// Check authentication (fixed to match auth.php session structure)
if (!isset($_SESSION['currentUser']['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'upload_document') {
    // Handle file upload
    if (!isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        exit;
    }
    
    $file = $_FILES['file'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'];
    $published = $_POST['published'] ?? 0;
    $userId = $_SESSION['currentUser']['user_id'];  // Fixed session reference
    
    // Generate unique ID for the content
    $contentId = uniqid();
    
    // Create uploads directory if it doesn't exist
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate a safe filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeFilename = $contentId . '.' . $fileExtension;
    $uploadPath = $uploadDir . $safeFilename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO content (content_id, title, body, type, created_by, published, file_name, file_size, file_path) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $contentId,
            $title,
            $description,
            $category,
            $userId,
            $published,
            $file['name'],
            $file['size'],
            $uploadPath
        ]);
        
        echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    }
    
} elseif ($action === 'get_documents') {
    // Get all documents
    $stmt = $pdo->prepare("SELECT * FROM content WHERE type IN ('policy', 'handbook', 'template', 'form', 'guide', 'other') ORDER BY created_date DESC");
    $stmt->execute();
    $documents = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'documents' => $documents]);
    
} elseif ($action === 'delete_document') {
    // Delete a document
    $contentId = $_POST['id'];
    $userId = $_SESSION['currentUser']['user_id'];  // Fixed session reference
    $userRole = $_SESSION['currentUser']['role'];
    
    // Fetch document and verify ownership (added for security)
    $stmt = $pdo->prepare("SELECT * FROM content WHERE content_id = ?");
    $stmt->execute([$contentId]);
    $document = $stmt->fetch();
    
    if ($document) {
        if ($document['created_by'] != $userId && $userRole != 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized to delete this document']);
            exit;
        }
        
        // Delete file from server
        if (file_exists($document['file_path'])) {
            unlink($document['file_path']);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM content WHERE content_id = ?");
        $stmt->execute([$contentId]);
        
        echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Document not found']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}