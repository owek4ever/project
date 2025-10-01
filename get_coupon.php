<?php
ob_start();
session_start();
require_once 'db.php';
header('Content-Type: application/json');

error_log("get_coupon.php accessed with ID: " . ($_GET['id'] ?? 'not provided'));
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));

if (!isset($_GET['id'])) {
    error_log("No coupon ID provided");
    echo json_encode(['error' => 'No coupon ID provided']);
    ob_end_flush();
    exit;
}

if (!isset($_SESSION['currentUser']['user_id'])) {
    error_log("User session not found in get_coupon.php");
    echo json_encode(['error' => 'User session not found']);
    ob_end_flush();
    exit;
}

$coupon_id = $_GET['id'];
$user_id = $_SESSION['currentUser']['user_id'];
error_log("Fetching coupon ID: $coupon_id for user ID: $user_id");

// Modified query to use redeem_code since coupon_code is empty in your database
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM coupon_redemptions cr WHERE cr.coupon_id = c.coupon_id AND cr.employee_id = ?) as is_redeemed,
        (SELECT cr.coupon_code FROM coupon_redemptions cr WHERE cr.coupon_id = c.coupon_id AND cr.employee_id = ?) as redeemed_coupon_code,
        c.redeem_code as coupon_code
        FROM coupons c
        WHERE c.coupon_id = ?";
        
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
    ob_end_flush();
    exit;
}
$stmt->bind_param("sss", $user_id, $user_id, $coupon_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("Coupon not found: $coupon_id");
    echo json_encode(['error' => 'Coupon not found']);
    ob_end_flush();
    exit;
}

$coupon = $result->fetch_assoc();

// If already redeemed, use the stored coupon_code from redemptions table, otherwise use redeem_code
if ($coupon['is_redeemed'] && !empty($coupon['redeemed_coupon_code'])) {
    $coupon['coupon_code'] = $coupon['redeemed_coupon_code'];
} else {
    $coupon['coupon_code'] = $coupon['redeem_code'];
}

error_log("Coupon fetched: " . print_r($coupon, true));
echo json_encode($coupon);

$stmt->close();
$conn->close();
ob_end_flush();
?>