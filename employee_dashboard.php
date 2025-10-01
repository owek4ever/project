<?php
// Database connection
$servername = "127.0.0.1";
$username = "root"; // Adjust as needed
$password = ""; // Adjust as needed
$dbname = "user_management_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session to access user data
session_start();
if (!isset($_SESSION['currentUser'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Parse user data from session
$userData = $_SESSION['currentUser'];

// Ensure role is employee
if ($userData['role'] !== 'employee') {
    header("Location: dashboard.php"); // Redirect admins to admin dashboard
    exit();
}

// Fetch stats for dashboard cards
// My Coupons Count
$stmt = $conn->prepare("SELECT COUNT(*) as my_coupons FROM coupon_redemptions WHERE employee_id = ?");
$stmt->execute([$userData['user_id']]);
$my_coupons = $stmt->fetch(PDO::FETCH_ASSOC)['my_coupons'];

// Available Coupons Count
$stmt = $conn->query("SELECT COUNT(*) as available_coupons FROM coupons WHERE expiry_date >= CURDATE()");
$available_coupons = $stmt->fetch(PDO::FETCH_ASSOC)['available_coupons'];

// Recent Announcements Count
$stmt = $conn->query("SELECT COUNT(*) as recent_announcements FROM announcements WHERE is_published = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$recent_announcements = $stmt->fetch(PDO::FETCH_ASSOC)['recent_announcements'];

// My Feedback Count
$stmt = $conn->prepare("SELECT COUNT(*) as my_feedback FROM feedback WHERE employee_id = ?");
$stmt->execute([$userData['user_id']]);
$my_feedback_count = $stmt->fetch(PDO::FETCH_ASSOC)['my_feedback'];

// Fetch the most recent announcement (title, image, and description)
$stmt = $conn->query("SELECT a.title, a.image_path, a.content AS description 
                     FROM announcements a 
                     WHERE a.is_published = 1 
                     ORDER BY a.created_at DESC LIMIT 1");
$announcement = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch employee's recent feedback
$stmt = $conn->prepare("SELECT feedback_id, subject, category, created_at, status FROM feedback WHERE employee_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$userData['user_id']]);
$my_feedback_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Tunisie Telecom</title>
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
            --gradient-stats: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gold-gradient: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
            --info-gradient: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --success-gradient: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            --warning-gradient: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-primary);
            color: var(--dark-text);
            position: relative;
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
                transform: scale(1.05);
                opacity: 0.8;
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
            overflow: hidden;
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
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            animation: fadeInUp 0.8s ease-out;
            flex-shrink: 0;
        }

        .page-title-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
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

        .welcome-message {
            font-size: 16px;
            font-weight: 400;
            color: var(--light-text);
            opacity: 0.9;
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
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            text-align: center;
            animation: fadeInUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--info-gradient);
        }

        .welcome-banner h2 {
            color: var(--secondary-color);
            margin-bottom: 8px;
            font-size: 20px;
        }

        .welcome-banner p {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
        }

        /* Enhanced Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
            animation: fadeInUp 0.6s ease-out 0.2s both;
            flex-shrink: 0;
        }

        .stat-card {
            background: var(--gradient-card);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-hover-shadow);
        }

        .stat-card.coupons::before {
            background: var(--gold-gradient);
        }

        .stat-card.available::before {
            background: var(--success-gradient);
        }

        .stat-card.announcements::before {
            background: var(--info-gradient);
        }

        .stat-card.feedback::before {
            background: var(--warning-gradient);
        }

        .stat-info {
            flex: 1;
        }

        .stat-title {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 3px;
        }

        .stat-change {
            font-size: 11px;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            gap: 3px;
            font-weight: 600;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .stat-icon::before {
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
            animation: shimmer 3s infinite;
        }

        .stat-icon.coupons {
            background: var(--gold-gradient);
        }

        .stat-icon.available {
            background: var(--success-gradient);
        }

        .stat-icon.announcements {
            background: var(--info-gradient);
        }

        .stat-icon.feedback {
            background: var(--warning-gradient);
        }

        .content-section {
            background: var(--gradient-card);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            position: relative;
            flex-shrink: 0;
        }

        .section-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--info-gradient);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: var(--primary-color);
            font-size: 16px;
        }

        .view-all-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 3px;
            transition: var(--transition);
            font-weight: 600;
        }

        .view-all-link:hover {
            color: var(--secondary-color);
            transform: translateX(3px);
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            animation: fadeInUp 0.6s ease-out 0.4s both;
            flex: 1;
            min-height: 0;
        }

        /* Enhanced Feedback Section */
        .feedback-list {
            list-style: none;
            flex: 1;
            overflow-y: auto;
            padding-right: 5px;
        }

        .feedback-list::-webkit-scrollbar {
            width: 4px;
        }

        .feedback-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .feedback-list::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .feedback-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            transition: var(--transition);
        }

        .feedback-item:last-child {
            border-bottom: none;
        }

        .feedback-item:hover {
            background: rgba(52, 152, 219, 0.05);
            margin: 0 -15px;
            padding: 15px;
            border-radius: 8px;
        }

        .feedback-date {
            width: 60px;
            text-align: center;
            margin-right: 15px;
            background: var(--gradient-card);
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
        }

        .feedback-day {
            display: block;
            font-size: 20px;
            font-weight: 700;
            color: var(--secondary-color);
            line-height: 1;
        }

        .feedback-month {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }

        .feedback-details {
            flex: 1;
            min-width: 0;
        }

        .feedback-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--secondary-color);
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .feedback-category {
            font-size: 12px;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 3px;
        }

        .feedback-status {
            font-size: 11px;
            color: var(--primary-color);
            font-weight: 600;
            text-transform: capitalize;
        }

        .feedback-empty {
            text-align: center;
            padding: 30px 20px;
            color: #6c757d;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .feedback-empty i {
            font-size: 36px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        /* Enhanced Announcement Card */
        .announcement-card {
            background: var(--gradient-card);
            border-radius: var(--border-radius);
            padding: 0;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .announcement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--info-gradient);
        }

        .announcement-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-hover-shadow);
        }

        .card-image-container {
            position: relative;
            height: 140px;
            overflow: hidden;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            flex-shrink: 0;
        }

        .card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .card-image:hover {
            transform: scale(1.05);
        }

        .image-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 8px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .card-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-shrink: 0;
        }

        .card-description {
            font-size: 13px;
            color: #6c757d;
            line-height: 1.5;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .read-more-btn {
            background: var(--info-gradient);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: var(--transition);
        }

        .read-more-btn:hover {
            transform: translateX(3px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .announcement-empty {
            text-align: center;
            padding: 30px 20px;
            color: #6c757d;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .announcement-empty i {
            font-size: 36px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        /* Responsive Design */
        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .stat-card {
                padding: 15px;
                gap: 10px;
            }

            .stat-value {
                font-size: 20px;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-end;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-grid {
                gap: 15px;
            }

            .page-title {
                font-size: 24px;
            }

            .welcome-message {
                font-size: 14px;
            }

            .welcome-banner {
                padding: 15px;
            }

            .welcome-banner h2 {
                font-size: 18px;
            }

            .welcome-banner p {
                font-size: 13px;
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
                    <a href="employee_dashboard.php" class="nav-link active">
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
                    <a href="my-coupons.php" class="nav-link">
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
                <div class="page-title-container">
                    <h1 class="page-title">
                        <i class="fas fa-tachometer-alt"></i>
                        Employee Dashboard
                    </h1>
                    <span class="welcome-message">Welcome back, <?php echo htmlspecialchars($userData['name']); ?>!</span>
                </div>
                <div class="header-actions">
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <div class="welcome-banner">
                <h2>Your Employee Portal</h2>
                <p>Your central hub for managing coupons, staying updated with announcements, sharing feedback, and accessing company resources.</p>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card coupons" onclick="window.location.href='my-coupons.php'">
                    <div class="stat-info">
                        <div class="stat-title">My Coupons</div>
                        <div class="stat-value"><?php echo $my_coupons; ?></div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            Redeemed
                        </div>
                    </div>
                    <div class="stat-icon coupons">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>

                <div class="stat-card available" onclick="window.location.href='my-coupons.php'">
                    <div class="stat-info">
                        <div class="stat-title">Available Coupons</div>
                        <div class="stat-value"><?php echo $available_coupons; ?></div>
                        <div class="stat-change">
                            <i class="fas fa-gift"></i>
                            Active
                        </div>
                    </div>
                    <div class="stat-icon available">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>

                <div class="stat-card announcements" onclick="window.location.href='employee_news.php'">
                    <div class="stat-info">
                        <div class="stat-title">New Announcements</div>
                        <div class="stat-value"><?php echo $recent_announcements; ?></div>
                        <div class="stat-change">
                            <i class="fas fa-clock"></i>
                            This Week
                        </div>
                    </div>
                    <div class="stat-icon announcements">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                </div>

                <div class="stat-card feedback" onclick="window.location.href='emp_feedback.php'">
                    <div class="stat-info">
                        <div class="stat-title">My Feedback</div>
                        <div class="stat-value"><?php echo $my_feedback_count; ?></div>
                        <div class="stat-change">
                            <i class="fas fa-comments"></i>
                            Submitted
                        </div>
                    </div>
                    <div class="stat-icon feedback">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- My Recent Feedback Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-comment-alt"></i>
                            My Recent Feedback
                        </h2>
                        <a href="emp_feedback.php" class="view-all-link">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <ul class="feedback-list">
                        <?php if (empty($my_feedback_list)): ?>
                            <li class="feedback-empty">
                                <i class="fas fa-comment-slash"></i>
                                <div>
                                    <h4>No feedback submitted yet</h4>
                                    <p>Share your thoughts and help us improve!</p>
                                </div>
                            </li>
                        <?php else: ?>
                            <?php foreach ($my_feedback_list as $feedback): ?>
                                <li class="feedback-item">
                                    <div class="feedback-date">
                                        <span class="feedback-day"><?php echo date('d', strtotime($feedback['created_at'])); ?></span>
                                        <span class="feedback-month"><?php echo date('M', strtotime($feedback['created_at'])); ?></span>
                                    </div>
                                    <div class="feedback-details">
                                        <div class="feedback-title"><?php echo htmlspecialchars($feedback['subject']); ?></div>
                                        <div class="feedback-category">
                                            <i class="fas fa-tag"></i>
                                            <?php echo htmlspecialchars($feedback['category']); ?>
                                        </div>
                                        <div class="feedback-status">
                                            Status: <?php echo ucfirst(htmlspecialchars($feedback['status'])); ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Recent Announcement Card -->
                <div class="announcement-card" onclick="window.location.href='employee_news.php'">
                    <?php if (!empty($announcement)): ?>
                        <div class="card-image-container">
                            <?php if (!empty($announcement['image_path']) && file_exists($announcement['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($announcement['image_path']) . '?v=' . time(); ?>" alt="Announcement Image" class="card-image">
                            <?php else: ?>
                                <div class="card-image" style="background: var(--gradient-stats); display: flex; align-items: center; justify-content: center; color: white; font-size: 36px;">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                            <?php endif; ?>
                            <div class="image-overlay">
                                <i class="fas fa-newspaper"></i>
                                Latest
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="card-title">
                                <?php echo htmlspecialchars($announcement['title']); ?>
                            </div>
                            <div class="card-description">
                                <?php echo htmlspecialchars($announcement['description']); ?>
                            </div>
                            <div class="card-footer">
                                <span style="font-size: 12px; color: #6c757d;">
                                    <i class="fas fa-clock"></i>
                                    Latest News
                                </span>
                                <button class="read-more-btn">
                                    <i class="fas fa-arrow-right"></i>
                                    Read More
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="announcement-empty">
                            <i class="fas fa-bullhorn"></i>
                            <div>
                                <h4>No announcements available</h4>
                                <p>Check back later for company updates!</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userData = <?php echo json_encode($userData); ?>;
            const nameInitial = userData.name.charAt(0).toUpperCase();
            const userAvatar = document.getElementById('userAvatar');

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

            // Add hover effects to stat cards
            document.querySelectorAll('.stat-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('.stat-icon');
                    if (icon) {
                        icon.style.animation = 'pulse 1s infinite';
                    }
                });

                card.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('.stat-icon');
                    if (icon) {
                        icon.style.animation = '';
                    }
                });
            });

            // Logout handler
            document.querySelector('.btn-logout').addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.removeItem('currentUser');
                    window.location.href = 'logout.php';
                }
            });

            // Add staggered animation to feedback items
            document.querySelectorAll('.feedback-item').forEach((item, index) => {
                item.style.animation = `slideInLeft 0.6s ease-out ${0.1 * index}s both`;
            });
        });
    </script>
</body>
</html>