<?php
// Start session and include database connection
session_start();
require_once 'db.php';

// Define base URL for AJAX requests (adjust this based on your server setup)
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

// Debug: Log session details
error_log("Session status: " . session_status());
error_log("Session data: " . print_r($_SESSION, true));
error_log("Base URL: " . BASE_URL);

// Check if user is logged in
if (!isset($_SESSION['currentUser'])) {
    error_log("User not logged in. Redirecting to login.php");
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// Parse user data from session
$userData = $_SESSION['currentUser'];

// Debug: Log user ID
error_log("User ID: " . ($userData['user_id'] ?? 'not set'));

// Check if user is an employee
if ($userData['role'] !== 'employee') {
    error_log("User is not an employee. Redirecting to dashboard.php");
    header('Location: dashboard.php');
    exit;
}

// Fetch available coupons from database
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM coupon_redemptions cr 
         WHERE cr.coupon_id = c.coupon_id 
         AND cr.employee_id = ?) as is_redeemed,
        (SELECT cr.coupon_code FROM coupon_redemptions cr 
         WHERE cr.coupon_id = c.coupon_id 
         AND cr.employee_id = ?) as redeemed_coupon_code,
        c.redeem_code as coupon_code
        FROM coupons c
        WHERE c.expiry_date >= CURDATE()
        ORDER BY c.expiry_date ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error_message = 'Failed to prepare query: ' . $conn->error;
    error_log($error_message);
    $coupons = [];
} else {
    $stmt->bind_param("ss", $userData['user_id'], $userData['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $coupons = $result->fetch_all(MYSQLI_ASSOC);
        
        // Process coupons to use correct coupon code
        foreach ($coupons as &$coupon) {
            if ($coupon['is_redeemed'] && !empty($coupon['redeemed_coupon_code'])) {
                $coupon['coupon_code'] = $coupon['redeemed_coupon_code'];
            } else {
                $coupon['coupon_code'] = $coupon['redeem_code'];
            }
        }
        
        error_log("Coupons fetched: " . print_r($coupons, true));
    } else {
        $error_message = 'Failed to fetch coupons: ' . $conn->error;
        error_log($error_message);
        $coupons = [];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Coupons - Tunisie Telecom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #ecf0f1;
            --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --card-hover-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --border-radius: 15px;
            --gradient-primary: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            --gradient-card: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            --coupon-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gold-gradient: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            color: var(--dark-text);
            position: relative;
            overflow-x: hidden;
            overflow-y: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 60%);
            animation: rotate 30s linear infinite;
            z-index: -1;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200px 0;
            }
            100% {
                background-position: 200px 0;
            }
        }

        .dashboard-container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            color: var(--light-text);
            padding: 20px 0;
            box-shadow: var(--card-shadow);
            z-index: 100;
            transition: var(--transition);
            flex-shrink: 0;
            overflow-y: auto;
        }

        .logo-area {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .logo-area img {
            width: 180px;
            height: auto;
            display: block;
        }

        .user-info {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 30px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            margin-right: 15px;
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-role {
            font-size: 12px;
            opacity: 0.8;
            text-transform: capitalize;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--light-text);
            text-decoration: none;
            transition: var(--transition);
            border-left: 4px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(52, 152, 219, 0.2);
            border-left-color: var(--primary-color);
        }

        .nav-link i {
            margin-right: 15px;
            font-size: 18px;
        }

        .nav-header {
            padding: 12px 20px;
            color: var(--light-text);
            opacity: 0.7;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            height: 100%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease-out;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: rgba(52, 152, 219, 0.9);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-primary:hover {
            background: rgba(52, 152, 219, 1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-redeem {
            background: var(--gold-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(247, 151, 30, 0.3);
        }

        .btn-redeem:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(247, 151, 30, 0.4);
        }

        .btn-redeem:disabled {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-logout {
            background: rgba(231, 76, 60, 0.9);
            color: white;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-logout:hover {
            background: rgba(231, 76, 60, 1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }

        .welcome-banner {
            background: var(--gradient-card);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            text-align: center;
            animation: fadeInUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gold-gradient);
        }

        .welcome-banner h2 {
            color: var(--secondary-color);
            margin-bottom: 10px;
            font-size: 24px;
        }

        .welcome-banner p {
            color: #6c757d;
            font-size: 16px;
            margin: 0;
        }

        .content-section {
            background: var(--gradient-card);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.6s ease-out;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
            position: relative;
        }

        .section-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: var(--gold-gradient);
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #f39c12;
            font-size: 20px;
        }

        /* Enhanced Coupon Cards */
        .coupon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .coupon-card {
            background: var(--gradient-card);
            border-radius: var(--border-radius);
            padding: 0;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            animation: slideInLeft 0.6s ease-out;
            animation-fill-mode: both;
            cursor: pointer;
        }

        .coupon-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--coupon-gradient);
        }

        .coupon-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-hover-shadow);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .coupon-card.redeemed::before {
            background: var(--accent-color);
        }

        .coupon-card:nth-child(1) { animation-delay: 0.1s; }
        .coupon-card:nth-child(2) { animation-delay: 0.2s; }
        .coupon-card:nth-child(3) { animation-delay: 0.3s; }
        .coupon-card:nth-child(4) { animation-delay: 0.4s; }
        .coupon-card:nth-child(5) { animation-delay: 0.5s; }
        .coupon-card:nth-child(6) { animation-delay: 0.6s; }

        .coupon-header {
            padding: 25px 25px 0;
            position: relative;
        }

        .coupon-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .brand-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--coupon-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }

        .brand-details h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 4px;
        }

        .discount-badge {
            background: var(--gold-gradient);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            position: relative;
            overflow: hidden;
        }

        .discount-badge::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255,255,255,0.1),
                transparent
            );
            animation: shimmer 2s infinite;
        }

        .coupon-content {
            padding: 0 25px 20px;
        }

        .coupon-description {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .coupon-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(248, 249, 250, 0.8);
            border-radius: 10px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #6c757d;
        }

        .meta-item i {
            color: var(--primary-color);
        }

        .coupon-footer {
            padding: 0 25px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-available {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.15), rgba(52, 152, 219, 0.25));
            color: var(--primary-color);
        }

        .status-redeemed {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.15), rgba(46, 204, 113, 0.25));
            color: var(--accent-color);
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 13px;
            border-radius: 6px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--secondary-color);
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 0;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
            backdrop-filter: blur(5px);
        }

        .modal {
            background: white;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            animation: fadeInUp 0.3s ease-out;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .modal-header {
            padding: 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            background: var(--gradient-card);
        }

        .modal-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
            transition: var(--transition);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .modal-body {
            padding: 25px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            flex-shrink: 0;
            background: var(--gradient-card);
        }

        .view-modal-content {
            margin-bottom: 20px;
        }

        .view-modal-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-modal-value {
            margin-bottom: 20px;
            padding: 15px;
            background: var(--gradient-card);
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }

        .coupon-code {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            text-align: center;
            background: var(--gold-gradient);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 10px;
            font-weight: bold;
            letter-spacing: 2px;
            position: relative;
            overflow: hidden;
        }

        .coupon-code::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255,255,255,0.1),
                transparent
            );
            animation: shimmer 2s infinite;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
            z-index: 1100;
            display: none;
            align-items: center;
            gap: 10px;
            box-shadow: var(--card-shadow);
            animation: slideInRight 0.3s ease-out;
            backdrop-filter: blur(10px);
        }

        .notification.success {
            background: linear-gradient(135deg, var(--accent-color), #27ae60);
        }

        .notification.error {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
        }

        @keyframes slideInRight {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .coupon-grid {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .dashboard-container {
                flex-direction: column;
                height: 100vh;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: fixed;
                bottom: 0;
                z-index: 1000;
                padding: 10px 0;
                flex-shrink: 0;
            }

            .logo-area, .user-info {
                display: none;
            }

            .nav-menu {
                display: flex;
                overflow-x: auto;
            }

            .nav-item {
                margin-bottom: 0;
                flex-shrink: 0;
            }

            .nav-link {
                border-left: none;
                border-top: 4px solid transparent;
                flex-direction: column;
                padding: 8px 12px;
                font-size: 11px;
            }

            .nav-link i {
                margin-right: 0;
                margin-bottom: 3px;
                font-size: 14px;
            }

            .nav-link:hover, .nav-link.active {
                border-left-color: transparent;
                border-top-color: var(--primary-color);
            }

            .main-content {
                margin-bottom: 70px;
                padding: 15px;
                height: calc(100vh - 70px);
            }

            .coupon-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header-actions {
                width: 100%;
                justify-content: space-between;
            }

            .modal {
                width: 95%;
                max-height: 85vh;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="logo.png" alt="Tunisie Telecom Logo">
            </div>
            
            <div class="user-info">
                <div class="user-avatar" id="userAvatar"><?php echo strtoupper(substr($userData['name'], 0, 1)); ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($userData['name']); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($userData['role']); ?></div>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="employee_dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-header">Employee Tools</li>
                <li class="nav-item">
                    <a href="employee_news.php" class="nav-link">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="employee_content.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Company Content</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="my-coupons.php" class="nav-link active">
                        <i class="fas fa-ticket-alt"></i>
                        <span>My Coupons</span>
                    </a>
                </li>
                <li class="nav-header">Support</li>
                <li class="nav-item">
                    <a href="emp_feedback.php" class="nav-link">
                        <i class="fas fa-comment-dots"></i>
                        <span>Feedback</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="emp_profile.php" class="nav-link">
                        <i class="fas fa-user-circle"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1 class="page-title">
                    <i class="fas fa-gift"></i>
                    My Coupons
                </h1>
                <div class="header-actions">
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-tags"></i>
                        Available Coupons
                    </h2>
                </div>

                <div class="coupon-grid">
                    <?php if (!empty($coupons)): ?>
                        <?php foreach ($coupons as $index => $coupon): ?>
                            <div class="coupon-card <?php echo $coupon['is_redeemed'] ? 'redeemed' : ''; ?>" 
                                 data-id="<?php echo htmlspecialchars($coupon['coupon_id']); ?>"
                                 style="animation-delay: <?php echo $index * 0.1; ?>s">
                                
                                <div class="coupon-header">
                                    <div class="coupon-brand">
                                        <div class="brand-info">
                                            <div class="brand-logo">
                                                <?php echo strtoupper(substr($coupon['partner_name'], 0, 2)); ?>
                                            </div>
                                            <div class="brand-details">
                                                <h3><?php echo htmlspecialchars($coupon['partner_name']); ?></h3>
                                            </div>
                                        </div>
                                        <div class="discount-badge">
                                            <?php echo htmlspecialchars($coupon['discount_rate']); ?>% OFF
                                        </div>
                                    </div>
                                </div>

                                <div class="coupon-content">
                                    <div class="coupon-description">
                                        <?php echo htmlspecialchars($coupon['description']); ?>
                                    </div>

                                    <div class="coupon-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>Expires: <?php echo date('M j, Y', strtotime($coupon['expiry_date'])); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php 
                                                $days_left = ceil((strtotime($coupon['expiry_date']) - time()) / (60 * 60 * 24));
                                                echo $days_left > 0 ? $days_left . ' days left' : 'Expired';
                                            ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="coupon-footer">
                                    <div class="status-badge <?php echo $coupon['is_redeemed'] ? 'status-redeemed' : 'status-available'; ?>">
                                        <i class="fas <?php echo $coupon['is_redeemed'] ? 'fa-check-circle' : 'fa-circle'; ?>"></i>
                                        <?php echo $coupon['is_redeemed'] ? 'Redeemed' : 'Available'; ?>
                                    </div>
                                    <button class="btn btn-redeem btn-small" 
                                            data-id="<?php echo htmlspecialchars($coupon['coupon_id']); ?>" 
                                            <?php echo $coupon['is_redeemed'] ? 'disabled' : ''; ?>>
                                        <i class="fas <?php echo $coupon['is_redeemed'] ? 'fa-check' : 'fa-ticket-alt'; ?>"></i>
                                        <span><?php echo $coupon['is_redeemed'] ? 'Redeemed' : 'Redeem Now'; ?></span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-ticket-alt"></i>
                            <h3>No Coupons Available</h3>
                            <p>Check back later for exclusive employee offers and discounts!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- View Coupon Modal -->
    <div class="modal-overlay" id="viewCouponModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="viewTitle">
                    <i class="fas fa-ticket-alt"></i>
                    <span></span>
                </h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="view-modal-content">
                    <div class="view-modal-label">
                        <i class="fas fa-info-circle"></i>
                        Description
                    </div>
                    <div class="view-modal-value" id="viewDescription"></div>
                    
                    <div class="view-modal-label">
                        <i class="fas fa-store"></i>
                        Partner Name
                    </div>
                    <div class="view-modal-value" id="viewPartnerName"></div>
                    
                    <div class="view-modal-label">
                        <i class="fas fa-percentage"></i>
                        Discount Rate
                    </div>
                    <div class="view-modal-value" id="viewDiscountRate"></div>
                    
                    <div class="view-modal-label">
                        <i class="fas fa-calendar-times"></i>
                        Expiry Date
                    </div>
                    <div class="view-modal-value" id="viewExpiryDate"></div>
                    
                    <div class="view-modal-label">
                        <i class="fas fa-flag"></i>
                        Status
                    </div>
                    <div class="view-modal-value" id="viewStatus"></div>
                    
                    <div class="view-modal-label">
                        <i class="fas fa-barcode"></i>
                        Coupon Code
                    </div>
                    <div class="view-modal-value coupon-code" id="viewCouponCode">
                        Not redeemed yet
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-redeem" id="redeemCouponBtn" style="display: none;">
                    <i class="fas fa-gift"></i>
                    Redeem Coupon
                </button>
                <button class="btn btn-primary" id="closeViewBtn">
                    <i class="fas fa-times"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification">
        <i class="fas fa-check-circle"></i>
        <span id="notificationText"></span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userData = <?php echo json_encode($userData); ?>;
            const nameInitial = userData.name.charAt(0).toUpperCase();
            const userAvatar = document.getElementById('userAvatar');
            const baseUrl = '<?php echo BASE_URL; ?>';
            const couponsData = <?php echo json_encode($coupons); ?>;

            // Log initial coupons data for debugging
            console.log('Initial coupons data:', couponsData);

            // Set initial avatar with gradient background
            userAvatar.style.background = 'linear-gradient(135deg, var(--primary-color), var(--secondary-color))';
            userAvatar.textContent = nameInitial;
            userAvatar.style.color = 'white';

            // Load profile picture if exists
            if (userData.profile_picture) {
                loadProfilePicture(userData.profile_picture, userAvatar, nameInitial);
            }

            function loadProfilePicture(imagePath, avatarElement, nameInitial) {
                const img = new Image();
                img.onload = function() {
                    avatarElement.style.backgroundImage = `url(${imagePath})`;
                    avatarElement.style.backgroundSize = 'cover';
                    avatarElement.style.backgroundPosition = 'center';
                    avatarElement.textContent = '';
                };
                img.onerror = function() {
                    avatarElement.style.backgroundImage = '';
                    avatarElement.style.background = 'linear-gradient(135deg, var(--primary-color), var(--secondary-color))';
                    avatarElement.textContent = nameInitial;
                    avatarElement.style.color = 'white';
                };
                img.src = imagePath;
            }

            // Coupon view and redemption functionality
            const viewModal = document.getElementById('viewCouponModal');
            const closeViewBtn = document.getElementById('closeViewBtn');
            const modalCloseButtons = document.querySelectorAll('.modal-close');
            const redeemCouponBtn = document.getElementById('redeemCouponBtn');
            const viewTitle = document.getElementById('viewTitle').querySelector('span');
            const viewDescription = document.getElementById('viewDescription');
            const viewPartnerName = document.getElementById('viewPartnerName');
            const viewDiscountRate = document.getElementById('viewDiscountRate');
            const viewExpiryDate = document.getElementById('viewExpiryDate');
            const viewStatus = document.getElementById('viewStatus');
            const viewCouponCode = document.getElementById('viewCouponCode');
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');

            function showCouponDetails(couponId) {
                const couponUrl = `${baseUrl}/get_coupon.php?id=${couponId}`;
                console.log('Fetching coupon from:', couponUrl);
                fetch(couponUrl)
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Coupon data received:', data);
                        if (data.error) {
                            console.error('Server error:', data.error);
                            showNotification('error', 'Error: ' + data.error);
                            const localCoupon = couponsData.find(c => c.coupon_id === couponId);
                            if (localCoupon) {
                                displayCouponDetails(localCoupon);
                            } else {
                                showNotification('error', 'Coupon data not available locally.');
                            }
                            return;
                        }
                        displayCouponDetails(data);
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        showNotification('error', `Error loading coupon details: ${error.message}. Using local data.`);
                        const localCoupon = couponsData.find(c => c.coupon_id === couponId);
                        if (localCoupon) {
                            displayCouponDetails(localCoupon);
                        } else {
                            showNotification('error', 'Coupon data not available locally.');
                        }
                    });
            }

            function displayCouponDetails(data) {
                console.log('Displaying coupon details:', data);
                viewTitle.textContent = `${data.partner_name} - ${data.discount_rate}% Off`;
                viewDescription.textContent = data.description || 'No description available';
                viewPartnerName.textContent = data.partner_name;
                viewDiscountRate.textContent = `${data.discount_rate}%`;
                viewExpiryDate.textContent = new Date(data.expiry_date).toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                });
                
                const statusBadge = data.is_redeemed ? 
                    '<span class="status-badge status-redeemed"><i class="fas fa-check-circle"></i> Redeemed</span>' :
                    '<span class="status-badge status-available"><i class="fas fa-circle"></i> Available</span>';
                viewStatus.innerHTML = statusBadge;
                
                if (data.is_redeemed && data.coupon_code) {
                    viewCouponCode.textContent = data.coupon_code;
                    viewCouponCode.style.background = 'var(--accent-color)';
                } else {
                    viewCouponCode.textContent = 'Not redeemed yet';
                    viewCouponCode.style.background = 'var(--gold-gradient)';
                }
                
                redeemCouponBtn.style.display = data.is_redeemed ? 'none' : 'inline-flex';
                redeemCouponBtn.setAttribute('data-id', data.coupon_id);
                viewModal.style.display = 'flex';
            }

            function showNotification(type, message) {
                const icon = notification.querySelector('i');
                if (type === 'success') {
                    notification.className = 'notification success';
                    icon.className = 'fas fa-check-circle';
                } else {
                    notification.className = 'notification error';
                    icon.className = 'fas fa-exclamation-circle';
                }
                notificationText.textContent = message;
                notification.style.display = 'flex';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 5000);
            }

            // Handle coupon card click
            document.querySelectorAll('.coupon-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.closest('.btn-redeem')) {
                        return;
                    }
                    const couponId = this.getAttribute('data-id');
                    console.log('Coupon card clicked, couponId:', couponId);
                    if (!couponId || couponId === 'undefined' || couponId.trim() === '') {
                        showNotification('error', 'Invalid coupon ID. Please try again.');
                        return;
                    }
                    showCouponDetails(couponId);
                });
            });

            // Handle redeem button clicks
            document.querySelectorAll('.btn-redeem').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent card click event
                    const couponId = this.getAttribute('data-id');
                    console.log('Redeem button clicked, couponId:', couponId);
                    redeemCoupon(couponId, this);
                });
            });

            redeemCouponBtn.addEventListener('click', function() {
                const couponId = this.getAttribute('data-id');
                console.log('Modal redeem button clicked, couponId:', couponId);
                redeemCoupon(couponId, this);
            });

            function redeemCoupon(couponId, button) {
                if (button.disabled) return;
                if (!couponId || couponId === 'undefined' || couponId.trim() === '') {
                    console.error('Invalid or missing coupon ID:', couponId);
                    showNotification('error', 'Invalid coupon ID. Please try again.');
                    return;
                }

                // Disable button during request
                button.disabled = true;
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Redeeming...</span>';

                const redeemUrl = `${baseUrl}/redeem_coupon.php?id=${couponId}`;
                console.log('Redeeming coupon at:', redeemUrl);

                fetch(redeemUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Redemption response:', data);
                        if (data.error) {
                            showNotification('error', data.error);
                            if (data.coupon_code && data.is_redeemed) {
                                updateCouponUI(couponId, data.coupon_code, true);
                            } else {
                                // Re-enable button on error
                                button.disabled = false;
                                button.innerHTML = originalHTML;
                            }
                            return;
                        }
                        if (!data.coupon_code) {
                            showNotification('error', 'Coupon code not provided by server.');
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                            return;
                        }
                        
                        showNotification('success', `Coupon redeemed successfully! Your code: ${data.coupon_code}`);
                        updateCouponUI(couponId, data.coupon_code, true);
                    })
                    .catch(error => {
                        console.error('Redemption error:', error);
                        showNotification('error', `Error redeeming coupon: ${error.message}`);
                        button.disabled = false;
                        button.innerHTML = originalHTML;
                    });
            }

            function updateCouponUI(couponId, couponCode, isRedeemed) {
                // Update modal
                if (viewCouponCode) {
                    viewCouponCode.textContent = couponCode;
                    viewCouponCode.style.background = 'var(--accent-color)';
                }
                if (viewStatus) {
                    viewStatus.innerHTML = '<span class="status-badge status-redeemed"><i class="fas fa-check-circle"></i> Redeemed</span>';
                }
                if (redeemCouponBtn) {
                    redeemCouponBtn.style.display = 'none';
                }

                // Update card
                const couponCard = document.querySelector(`.coupon-card[data-id="${couponId}"]`);
                if (couponCard) {
                    couponCard.classList.add('redeemed');
                    
                    const statusBadge = couponCard.querySelector('.status-badge');
                    if (statusBadge) {
                        statusBadge.className = 'status-badge status-redeemed';
                        statusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Redeemed';
                    }

                    const redeemButton = couponCard.querySelector('.btn-redeem');
                    if (redeemButton) {
                        redeemButton.disabled = true;
                        redeemButton.innerHTML = '<i class="fas fa-check"></i><span>Redeemed</span>';
                    }
                }

                // Update local data
                const localCoupon = couponsData.find(c => c.coupon_id === couponId);
                if (localCoupon) {
                    localCoupon.is_redeemed = 1;
                    localCoupon.coupon_code = couponCode;
                }
            }

            // Modal close handlers
            closeViewBtn.addEventListener('click', closeModals);
            modalCloseButtons.forEach(btn => {
                btn.addEventListener('click', closeModals);
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal-overlay')) {
                    closeModals();
                }
            });

            function closeModals() {
                viewModal.style.display = 'none';
            }

            // Logout handler
            document.querySelector('.btn-logout').addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.removeItem('currentUser');
                    window.location.href = 'logout.php';
                }
            });
        });
    </script>
</body>
</html>