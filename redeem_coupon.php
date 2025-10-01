<?php
// Start output buffering to prevent stray output
ob_start();

session_start();
require_once 'db.php';

// Set JSON header
header('Content-Type: application/json');

// Debug: Log request and session details
error_log("redeem_coupon.php accessed with ID: " . ($_GET['id'] ?? 'not provided'));
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));

// Check for coupon ID
if (!isset($_GET['id'])) {
    error_log("No coupon ID provided");
    echo json_encode(['error' => 'No coupon ID provided']);
    ob_end_flush();
    exit;
}

// Check for user session
if (!isset($_SESSION['currentUser']['user_id'])) {
    error_log("User session not found in redeem_coupon.php");
    echo json_encode(['error' => 'User session not found']);
    ob_end_flush();
    exit;
}

$coupon_id = $_GET['id'];
$user_id = $_SESSION['currentUser']['user_id'];

error_log("Redeeming coupon ID: $coupon_id for user ID: $user_id");

// Check if coupon exists and is not expired
$sql = "SELECT redeem_code FROM coupons WHERE coupon_id = ? AND expiry_date >= CURDATE()";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
    ob_end_flush();
    exit;
}
$stmt->bind_param("s", $coupon_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("Coupon not found or expired: $coupon_id");
    echo json_encode(['error' => 'Coupon not found or expired']);
    ob_end_flush();
    exit;
}

$coupon = $result->fetch_assoc();
if (empty($coupon['redeem_code'])) {
    error_log("Redeem code is empty for coupon_id: $coupon_id");
    echo json_encode(['error' => 'Invalid coupon: Redeem code is missing']);
    ob_end_flush();
    exit;
}
$coupon_code = $coupon['redeem_code'];
error_log("Fetched redeem code: $coupon_code for coupon_id: $coupon_id");

// Check if already redeemed
$sql = "SELECT coupon_code FROM coupon_redemptions WHERE coupon_id = ? AND employee_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
    ob_end_flush();
    exit;
}
$stmt->bind_param("ss", $coupon_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    error_log("Coupon already redeemed by user: $coupon_id, $user_id");
    // Return the redeem_code as the coupon code
    echo json_encode(['success' => true, 'is_redeemed' => true, 'coupon_code' => $coupon_code]);
    ob_end_flush();
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Insert redemption record
    $sql = "INSERT INTO coupon_redemptions (redemption_id, coupon_id, employee_id, redeemed_at, coupon_code) 
            VALUES (UUID(), ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare redemption insert: " . $conn->error);
    }
    $stmt->bind_param("sss", $coupon_id, $user_id, $coupon_code);
    $stmt->execute();

    // Update coupon usage count
    $sql = "UPDATE coupons SET usage_count = usage_count + 1 WHERE coupon_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare usage update: " . $conn->error);
    }
    $stmt->bind_param("s", $coupon_id);
    $stmt->execute();

    $conn->commit();
    error_log("Coupon redeemed successfully: $coupon_id, $user_id, Code: $coupon_code");
    echo json_encode(['success' => true, 'is_redeemed' => false, 'coupon_code' => $coupon_code]);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Redemption failed: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to redeem coupon: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();

// Flush output buffer
ob_end_flush();
?>