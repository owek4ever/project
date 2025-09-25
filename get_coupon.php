<?php
// Start session explicitly
session_start();
require_once 'db.php';

// Debug: Log request and session details
error_log("get_coupon.php accessed with ID: " . ($_GET['id'] ?? 'not provided'));
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));

// Set JSON header
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    error_log("No coupon ID provided");
    echo json_encode(['error' => 'No coupon ID provided']);
    exit;
}

if (!isset($_SESSION['currentUser']['user_id'])) {
    error_log("User session not found in get_coupon.php");
    echo json_encode(['error' => 'User session not found']);
    exit;
}

$coupon_id = $_GET['id'];
$user_id = $_SESSION['currentUser']['user_id'];

// Debug: Log coupon and user ID
error_log("Fetching coupon ID: $coupon_id for user ID: $user_id");

$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM coupon_redemptions cr 
         WHERE cr.coupon_id = c.coupon_id 
         AND cr.employee_id = ?) as is_redeemed
        FROM coupons c
        WHERE c.coupon_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("ss", $user_id, $coupon_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    error_log("Coupon not found for ID: $coupon_id");
    echo json_encode(['error' => 'Coupon not found']);
}
$stmt->close();
$conn->close();
?>