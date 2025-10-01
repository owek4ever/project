<?php
// Start output buffering and session
ob_start();
session_start();

// Include database connection
require_once 'db.php';

// Check if user is authenticated
$current_user = $_SESSION['currentUser'] ?? [];
if (is_string($current_user)) {
    $current_user = json_decode($current_user, true);
}
$user_id = $current_user['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit();
}

// Verify user exists
$result = executeQuery($conn, "SELECT user_id FROM users WHERE user_id = ?", [$user_id]);
if (!$result['success'] || !$result['result']->fetch_assoc()) {
    echo json_encode(['success' => false, 'error' => 'User not found.']);
    exit();
}

// Handle actions based on GET/POST parameters
$action = $_GET['action'] ?? $_POST['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'get':
        // Fetch single coupon for viewing
        $coupon_id = $conn->real_escape_string($_GET['coupon_id'] ?? '');
        if (empty($coupon_id)) {
            echo json_encode(['success' => false, 'error' => 'Coupon ID required.']);
            exit();
        }
        $sql = "SELECT * FROM coupons WHERE coupon_id = ?";
        $result = executeQuery($conn, $sql, [$coupon_id]);
        if ($result['success'] && $row = $result['result']->fetch_assoc()) {
            echo json_encode(['success' => true, 'coupon' => $row]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Coupon not found or error fetching data.']);
        }
        break;

    case 'add':
        // Create new coupon (admin only)
        if ($current_user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Admin access required.']);
            exit();
        }
        $coupon_id = "coup" . sprintf('%03d', rand(1, 999));
        $description = $conn->real_escape_string($_POST['description'] ?? '');
        $coupon_code = $conn->real_escape_string($_POST['coupon_code'] ?? '');
        $partner_name = $conn->real_escape_string($_POST['partner_name'] ?? '');
        $discount_rate = floatval($_POST['discount_rate'] ?? 0);
        $expiry_date = $conn->real_escape_string($_POST['expiry_date'] ?? '');
        $issued_by = $user_id;

        if (empty($description) || empty($coupon_code) || empty($partner_name) || $discount_rate <= 0 || empty($expiry_date)) {
            echo json_encode(['success' => false, 'error' => 'Invalid input data. All fields, including coupon code, are required.']);
            exit();
        }

        // Check if coupon_code is unique
        $sql = "SELECT coupon_id FROM coupons WHERE coupon_code = ?";
        $result = executeQuery($conn, $sql, [$coupon_code]);
        if ($result['success'] && $result['result']->fetch_assoc()) {
            echo json_encode(['success' => false, 'error' => 'Coupon code already exists.']);
            exit();
        }

        $sql = "INSERT INTO coupons (coupon_id, description, coupon_code, partner_name, discount_rate, expiry_date, issued_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $result = executeQuery($conn, $sql, [$coupon_id, $description, $coupon_code, $partner_name, $discount_rate, $expiry_date, $issued_by]);
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Coupon created successfully!', 'coupon_id' => $coupon_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error creating coupon: ' . ($result['error'] ?? $conn->error)]);
        }
        break;

    case 'update':
        // Update existing coupon (admin only)
        if ($current_user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Admin access required.']);
            exit();
        }
        $coupon_id = $conn->real_escape_string($_POST['coupon_id'] ?? '');
        $description = $conn->real_escape_string($_POST['description'] ?? '');
        $coupon_code = $conn->real_escape_string($_POST['coupon_code'] ?? '');
        $partner_name = $conn->real_escape_string($_POST['partner_name'] ?? '');
        $discount_rate = floatval($_POST['discount_rate'] ?? 0);
        $expiry_date = $conn->real_escape_string($_POST['expiry_date'] ?? '');

        if (empty($coupon_id) || empty($description) || empty($coupon_code) || empty($partner_name) || $discount_rate <= 0 || empty($expiry_date)) {
            echo json_encode(['success' => false, 'error' => 'Invalid input data. All fields, including coupon code, are required.']);
            exit();
        }

        // Check if coupon_code is unique (excluding current coupon)
        $sql = "SELECT coupon_id FROM coupons WHERE coupon_code = ? AND coupon_id != ?";
        $result = executeQuery($conn, $sql, [$coupon_code, $coupon_id]);
        if ($result['success'] && $result['result']->fetch_assoc()) {
            echo json_encode(['success' => false, 'error' => 'Coupon code already exists.']);
            exit();
        }

        $sql = "UPDATE coupons SET description = ?, coupon_code = ?, partner_name = ?, discount_rate = ?, expiry_date = ? WHERE coupon_id = ?";
        $result = executeQuery($conn, $sql, [$description, $coupon_code, $partner_name, $discount_rate, $expiry_date, $coupon_id]);
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Coupon updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error updating coupon: ' . ($result['error'] ?? $conn->error)]);
        }
        break;

    case 'delete':
        // Delete coupon (admin only)
        if ($current_user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Admin access required.']);
            exit();
        }
        $coupon_id = $conn->real_escape_string($_POST['coupon_id'] ?? '');
        if (empty($coupon_id)) {
            echo json_encode(['success' => false, 'error' => 'Coupon ID required.']);
            exit();
        }

        // Delete redemptions first
        $sql = "DELETE FROM coupon_redemptions WHERE coupon_id = ?";
        executeQuery($conn, $sql, [$coupon_id]);

        // Delete coupon
        $sql = "DELETE FROM coupons WHERE coupon_id = ?";
        $result = executeQuery($conn, $sql, [$coupon_id]);
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Coupon deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error deleting coupon: ' . ($result['error'] ?? $conn->error)]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        break;
}

// Close connection and flush buffer
$conn->close();
ob_end_flush();
?>