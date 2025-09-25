<?php
// download.php
session_start();
require_once 'db.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Not authenticated');
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "SELECT file_name, file_path FROM content WHERE content_id = ?";
    $result = executeQuery($conn, $sql, [$id]);
    
    if ($result['success'] && $result['result']->num_rows > 0) {
        $document = $result['result']->fetch_assoc();
        
        if (file_exists($document['file_path'])) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($document['file_name']) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($document['file_path']));
            readfile($document['file_path']);
            exit;
        } else {
            http_response_code(404);
            die('File not found on server');
        }
    } else {
        http_response_code(404);
        die('Document not found in database');
    }
}