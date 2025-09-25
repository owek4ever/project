<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Parse the request for DELETE method
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $announcement_id = $_GET['id'] ?? null;
    
    if (!$announcement_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Announcement ID is required']);
        exit;
    }
    
    // First get the announcement to delete the image file if exists
    $sql = "SELECT image_path FROM announcements WHERE announcement_id = ?";
    $result = executeQuery($conn, $sql, [$announcement_id]);
    
    if ($result['success'] && $result['result']->num_rows > 0) {
        $announcement = $result['result']->fetch_assoc();
        
        // Delete the image file if exists
        if (!empty($announcement['image_path']) && file_exists($announcement['image_path'])) {
            unlink($announcement['image_path']);
        }
        
        // Delete the announcement from database
        $sql = "DELETE FROM announcements WHERE announcement_id = ?";
        $result = executeQuery($conn, $sql, [$announcement_id]);
        
        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Announcement not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>