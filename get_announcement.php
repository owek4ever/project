<?php
session_start();
require_once 'db.php';

if (isset($_GET['id'])) {
    $announcement_id = $_GET['id'];
    
    $sql = "SELECT a.*, u.name as created_by_name 
            FROM announcements a 
            JOIN users u ON a.created_by = u.user_id 
            WHERE a.announcement_id = ?";
    $result = executeQuery($conn, $sql, [$announcement_id]);
    
    if ($result['success'] && $result['result']->num_rows > 0) {
        $announcement = $result['result']->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($announcement);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Announcement not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No announcement ID provided']);
}
?>