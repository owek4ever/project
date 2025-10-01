<?php
// Suppress PHP errors and warnings to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unwanted output
ob_start();

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
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Check if user is logged in - handle both session formats
$user_id = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['currentUser']['user_id'])) {
    $user_id = $_SESSION['currentUser']['user_id'];
}

if (!$user_id) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized - No user session found']);
    exit;
}

if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error: ' . ($_FILES['profile_picture']['error'] ?? 'Unknown')]);
    exit;
}

$file = $_FILES['profile_picture'];
$uploadedUserId = $_POST['user_id'] ?? '';

// Verify user_id matches session
if ($uploadedUserId !== $user_id) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized user']);
    exit;
}

// Validate file
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowedTypes)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
    exit;
}

if ($file['size'] > $maxSize) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.']);
    exit;
}

// Create upload directories if they don't exist
$uploadDir = 'uploads/profile_pictures/';
$originalDir = $uploadDir . 'original/';
$thumbDir = $uploadDir . 'thumbnails/';

if (!file_exists($originalDir)) {
    if (!mkdir($originalDir, 0755, true)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}
if (!file_exists($thumbDir)) {
    if (!mkdir($thumbDir, 0755, true)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to create thumbnail directory']);
        exit;
    }
}

// Check if directories are writable
if (!is_writable($originalDir)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Upload directory is not writable. Check permissions.']);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = $user_id . '_' . time() . '_' . uniqid() . '.' . $extension;
$originalPath = $originalDir . $filename;
$thumbPath = $thumbDir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $originalPath)) {
    // Create thumbnail (if GD is available)
    if (extension_loaded('gd')) {
        createThumbnail($originalPath, $thumbPath, 150, 150);
    }
    
    // Delete old profile picture if exists
    deleteOldProfilePicture($pdo, $user_id);
    
    // Update database
    $stmt = $pdo->prepare("UPDATE users SET profile_picture = ?, updated_at = CURRENT_TIMESTAMP() WHERE user_id = ?");
    $success = $stmt->execute([$originalPath, $user_id]);
    
    if ($success) {
        // Update session data
        if (isset($_SESSION['user_profile_picture'])) {
            $_SESSION['user_profile_picture'] = $originalPath;
        }
        if (isset($_SESSION['currentUser']) && is_array($_SESSION['currentUser'])) {
            $_SESSION['currentUser']['profile_picture'] = $originalPath;
        }
        
        ob_end_clean();
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture uploaded successfully',
            'profile_picture_path' => $originalPath
        ]);
    } else {
        // Delete uploaded files if database update fails
        if (file_exists($originalPath)) unlink($originalPath);
        if (file_exists($thumbPath)) unlink($thumbPath);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }
} else {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file. Check directory permissions.']);
}

function createThumbnail($source, $destination, $width, $height) {
    $imageInfo = getimagesize($source);
    if (!$imageInfo) return;
    
    $sourceImage = null;
    
    switch ($imageInfo['mime']) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($source);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $sourceImage = imagecreatefromwebp($source);
            }
            break;
    }
    
    if ($sourceImage) {
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);
        
        $ratio = min($width / $originalWidth, $height / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);
        
        $thumbImage = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($imageInfo['mime'] === 'image/png') {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefill($thumbImage, 0, 0, $transparent);
        }
        
        imagecopyresampled(
            $thumbImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight, $originalWidth, $originalHeight
        );
        
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
                imagejpeg($thumbImage, $destination, 85);
                break;
            case 'image/png':
                imagepng($thumbImage, $destination, 6);
                break;
            case 'image/gif':
                imagegif($thumbImage, $destination);
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    imagewebp($thumbImage, $destination, 80);
                }
                break;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);
    }
}

function deleteOldProfilePicture($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $oldPath = $stmt->fetchColumn();
        
        if ($oldPath && file_exists($oldPath)) {
            unlink($oldPath);
            $thumbPath = str_replace('original/', 'thumbnails/', $oldPath);
            if (file_exists($thumbPath)) {
                unlink($thumbPath);
            }
        }
    } catch (Exception $e) {
        error_log("Failed to delete old profile picture: " . $e->getMessage());
    }
}
?>