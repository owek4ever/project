<?php
// feedback_handler.php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("Starting feedback_handler.php");

// Check if user is authenticated
if (!isset($_SESSION['currentUser'])) {
    error_log("Unauthorized access: No currentUser in session");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$currentUser = $_SESSION['currentUser'];
$role = $currentUser['role'];

// Check database connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Handle different request types
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        error_log("Invalid JSON input");
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    if (isset($data['action']) && $data['action'] === 'response') {
        // Handle admin response
        if ($role !== 'admin') {
            error_log("Non-admin attempted to respond");
            echo json_encode(['success' => false, 'message' => 'Only admins can respond']);
            exit;
        }
        $feedbackId = $data['feedback_id'] ?? '';
        $message = $data['message'] ?? '';
        
        if (empty($feedbackId) || empty($message)) {
            error_log("Missing feedback_id or message for response");
            echo json_encode(['success' => false, 'message' => 'Feedback ID and message are required']);
            exit;
        }
        
        $responseId = uniqid('resp_', true);
        $sql = "INSERT INTO feedback_responses (response_id, feedback_id, admin_id, message, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $params = [$responseId, $feedbackId, $currentUser['user_id'], $message];
        $result = executeQuery($conn, $sql, $params);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Response added successfully']);
        } else {
            error_log("Failed to add response: " . $result['error']);
            echo json_encode(['success' => false, 'message' => 'Failed to add response: ' . $result['error']]);
        }
    } elseif (isset($data['action']) && $data['action'] === 'update_status') {
        // Handle status update
        if ($role !== 'admin') {
            error_log("Non-admin attempted to update status");
            echo json_encode(['success' => false, 'message' => 'Only admins can update status']);
            exit;
        }
        $feedbackId = $data['feedback_id'] ?? '';
        $status = $data['status'] ?? '';
        
        if (empty($feedbackId) || empty($status)) {
            error_log("Missing feedback_id or status for update");
            echo json_encode(['success' => false, 'message' => 'Feedback ID and status are required']);
            exit;
        }
        
        $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses)) {
            error_log("Invalid status: $status");
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        
        $sql = "UPDATE feedback SET status = ?, updated_at = NOW() WHERE feedback_id = ?";
        $params = [$status, $feedbackId];
        $result = executeQuery($conn, $sql, $params);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            error_log("Failed to update status: " . $result['error']);
            echo json_encode(['success' => false, 'message' => 'Failed to update status: ' . $result['error']]);
        }
    } else {
        // Handle feedback submission (only for employees)
        if ($role !== 'employee') {
            error_log("Non-employee attempted to submit feedback");
            echo json_encode(['success' => false, 'message' => 'Only employees can submit feedback']);
            exit;
        }
        $category = $data['category'] ?? '';
        $subject = $data['subject'] ?? '';
        $message = $data['message'] ?? '';
        
        // Validate input
        if (empty($category) || empty($subject) || empty($message)) {
            error_log("Missing required fields for feedback submission");
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        // Validate category
        $validCategories = ['suggestion', 'complaint', 'question', 'other'];
        if (!in_array($category, $validCategories)) {
            error_log("Invalid category: $category");
            echo json_encode(['success' => false, 'message' => 'Invalid category']);
            exit;
        }
        
        // Generate feedback ID
        $feedbackId = uniqid('fb_', true);
        
        // Insert feedback into database
        $sql = "INSERT INTO feedback (feedback_id, employee_id, subject, message, category, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'open', NOW(), NOW())";
        $params = [$feedbackId, $currentUser['user_id'], $subject, $message, $category];
        $result = executeQuery($conn, $sql, $params);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully']);
        } else {
            error_log("Failed to submit feedback: " . $result['error']);
            echo json_encode(['success' => false, 'message' => 'Failed to submit feedback: ' . $result['error']]);
        }
    }
} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'details') {
        // Fetch feedback details and responses
        $feedbackId = $_GET['id'] ?? '';
        if (empty($feedbackId)) {
            error_log("Missing feedback_id for details request");
            echo json_encode(['success' => false, 'message' => 'Feedback ID is required']);
            exit;
        }
        
        // Validate feedback access
        $sql = "SELECT f.*, u.name as employee_name 
                FROM feedback f 
                LEFT JOIN users u ON f.employee_id = u.user_id 
                WHERE f.feedback_id = ?";
        if ($role === 'employee') {
            $sql .= " AND f.employee_id = ?";
            $params = [$feedbackId, $currentUser['user_id']];
        } else {
            $params = [$feedbackId];
        }
        
        $result = executeQuery($conn, $sql, $params);
        
        if (!$result['success']) {
            error_log("Failed to fetch feedback: " . $result['error']);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch feedback: ' . $result['error']]);
            exit;
        }
        
        $feedbackRow = $result['result']->fetch_assoc();
        if (!$feedbackRow) {
            error_log("Feedback not found for ID: $feedbackId");
            echo json_encode(['success' => false, 'message' => 'Feedback not found']);
            exit;
        }
        $feedback = [
            'feedback_id' => $feedbackRow['feedback_id'],
            'subject' => $feedbackRow['subject'],
            'message' => $feedbackRow['message'],
            'category' => $feedbackRow['category'],
            'status' => $feedbackRow['status'],
            'created_at' => date('c', strtotime($feedbackRow['created_at'])),
            'employee_name' => $feedbackRow['employee_name'] ?? null
        ];
        
        // Fetch responses
        $sql = "SELECT fr.*, ua.name as admin_name 
                FROM feedback_responses fr 
                LEFT JOIN users ua ON fr.admin_id = ua.user_id 
                WHERE fr.feedback_id = ? 
                ORDER BY fr.created_at ASC";
        $params = [$feedbackId];
        $result = executeQuery($conn, $sql, $params);
        
        $responses = [];
        if ($result['success']) {
            while ($row = $result['result']->fetch_assoc()) {
                $responses[] = [
                    'admin_name' => $row['admin_name'] ?? 'Unknown Admin',
                    'message' => $row['message'],
                    'created_at' => date('c', strtotime($row['created_at']))
                ];
            }
        } else {
            error_log("Failed to fetch responses: " . $result['error']);
        }
        
        echo json_encode(['success' => true, 'feedback' => $feedback, 'responses' => $responses]);
    } else {
        // Fetch list of feedbacks with filters
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $sql = "SELECT f.feedback_id, f.subject, f.message, f.category, f.status, f.created_at, u.name as employee_name 
                FROM feedback f 
                LEFT JOIN users u ON f.employee_id = u.user_id";
        $params = [];
        $where = "WHERE 1=1";
        
        if ($role === 'employee') {
            $where .= " AND f.employee_id = ?";
            $params[] = $currentUser['user_id'];
        }
        
        if (!empty($category)) {
            $where .= " AND f.category = ?";
            $params[] = $category;
        }
        if (!empty($status)) {
            $where .= " AND f.status = ?";
            $params[] = $status;
        }
        
        $sql .= " " . $where . " ORDER BY f.created_at DESC";
        $result = executeQuery($conn, $sql, $params);
        
        if ($result['success']) {
            $feedbacks = [];
            while ($row = $result['result']->fetch_assoc()) {
                $feedbacks[] = [
                    'feedback_id' => $row['feedback_id'],
                    'subject' => $row['subject'],
                    'message' => $row['message'],
                    'category' => $row['category'],
                    'status' => $row['status'],
                    'created_at' => date('c', strtotime($row['created_at'])),
                    'employee_name' => $row['employee_name'] ?? null
                ];
            }
            echo json_encode(['success' => true, 'feedbacks' => $feedbacks]);
        } else {
            error_log("Failed to fetch feedback list: " . $result['error']);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch feedback: ' . $result['error']]);
        }
    }
} else {
    error_log("Invalid request method: $method");
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>