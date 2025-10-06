<?php
// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "user_management_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session to access user data
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch current user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, role, profile_picture FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);
$role = $current_user['role'];

// Fetch stats for cards
if ($role === 'admin') {
    $stmt = $conn->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    $stmt = $conn->query("SELECT COUNT(*) as new_users FROM users WHERE created_at >= CURDATE() - INTERVAL 7 DAY");
    $new_users = $stmt->fetch(PDO::FETCH_ASSOC)['new_users'];
    $total_users_change = $total_users > 0 ? ($new_users / $total_users * 100) : 0;
    $total_users_change_text = number_format($total_users_change, 1) . '% from last week';
    $total_users_change_class = $new_users >= 0 ? 'positive' : 'negative';
    $total_users_change_icon = $new_users >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

    $stmt = $conn->query("SELECT COUNT(*) as published_content FROM content WHERE published = 1");
    $published_content = $stmt->fetch(PDO::FETCH_ASSOC)['published_content'];

    $stmt = $conn->query("SELECT COUNT(*) as new_content FROM content WHERE published = 1 AND published_at >= CURDATE() - INTERVAL 7 DAY");
    $new_content = $stmt->fetch(PDO::FETCH_ASSOC)['new_content'];
    $published_content_change = $published_content > 0 ? ($new_content / $published_content * 100) : 0;
    $published_content_change_text = number_format($published_content_change, 1) . '% from last week';
    $published_content_change_class = $new_content >= 0 ? 'positive' : 'negative';
    $published_content_change_icon = $new_content >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

    $stmt = $conn->query("SELECT COUNT(*) as active_coupons FROM coupons WHERE expiry_date >= CURDATE()");
    $active_coupons = $stmt->fetch(PDO::FETCH_ASSOC)['active_coupons'];

    $stmt = $conn->query("SELECT COUNT(*) as new_active_coupons FROM coupons WHERE expiry_date >= CURDATE() AND created_at >= CURDATE() - INTERVAL 7 DAY");
    $new_active_coupons = $stmt->fetch(PDO::FETCH_ASSOC)['new_active_coupons'];
    $active_coupons_change = $active_coupons > 0 ? ($new_active_coupons / $active_coupons * 100) : 0;
    $active_coupons_change_text = number_format($active_coupons_change, 1) . '% from last week';
    $active_coupons_change_class = $new_active_coupons >= 0 ? 'positive' : 'negative';
    $active_coupons_change_icon = $new_active_coupons >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

    $stmt = $conn->query("SELECT feedback_id, subject, category, created_at FROM feedback ORDER BY created_at DESC LIMIT 5");
    $feedback_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $conn->prepare("SELECT COUNT(*) as my_coupons FROM coupon_redemptions WHERE employee_id = ?");
$stmt->execute([$user_id]);
$my_coupons = $stmt->fetch(PDO::FETCH_ASSOC)['my_coupons'];

$system_health = 98;

$stmt = $conn->query("SELECT a.announcement_id, a.title, a.created_at, u.name as created_by_name 
                     FROM announcements a 
                     JOIN users u ON a.created_by = u.user_id 
                     WHERE a.is_published = 1 
                     ORDER BY a.created_at DESC LIMIT 3");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usage_data = [];
$days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
$max_usage = 100;
for ($i = 4; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    if ($role === 'employee') {
        $stmt = $conn->prepare("SELECT COUNT(*) as usage_count 
                               FROM coupon_redemptions 
                               WHERE employee_id = ? AND DATE(redeemed_at) = ?");
        $stmt->execute([$user_id, $date]);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as usage_count 
                               FROM coupon_redemptions 
                               WHERE DATE(redeemed_at) = ?");
        $stmt->execute([$date]);
    }
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['usage_count'];
    $usage_data[] = [
        'day' => $days[4 - $i],
        'count' => $count,
        'height' => $count > 0 ? ($count / $max_usage * 100) : 10
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
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

        @keyframes shimmer {
            0% { background-position: -200px 0; }
            100% { background-position: 200px 0; }
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

        /* Hide sidebar scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 0;
            display: none;
        }

        .sidebar {
            -ms-overflow-style: none;
            scrollbar-width: none;
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

        .stat-card.users::before {
            background: var(--info-gradient);
        }

        .stat-card.content::before {
            background: var(--success-gradient);
        }

        .stat-card.coupons::before {
            background: var(--warning-gradient);
        }

        .stat-card.health::before {
            background: var(--success-gradient);
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
            display: flex;
            align-items: center;
            gap: 3px;
            font-weight: 600;
        }

        .positive {
            color: var(--accent-color);
        }

        .negative {
            color: var(--danger-color);
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
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 3s infinite;
        }

        .stat-icon.users {
            background: var(--info-gradient);
        }

        .stat-icon.content {
            background: var(--success-gradient);
        }

        .stat-icon.coupons {
            background: var(--warning-gradient);
        }

        .stat-icon.health {
            background: var(--success-gradient);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            animation: fadeInUp 0.6s ease-out 0.4s both;
            flex: 1;
            min-height: 0;
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

        .feedback-list, .announcement-list {
            list-style: none;
            flex: 1;
            overflow-y: auto;
            padding-right: 5px;
        }

        .feedback-list::-webkit-scrollbar, .announcement-list::-webkit-scrollbar {
            width: 4px;
        }

        .feedback-list::-webkit-scrollbar-track, .announcement-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .feedback-list::-webkit-scrollbar-thumb, .announcement-list::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .feedback-item, .announcement-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            transition: var(--transition);
        }

        .feedback-item:last-child, .announcement-item:last-child {
            border-bottom: none;
        }

        .feedback-item:hover, .announcement-item:hover {
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

        .feedback-details, .announcement-details {
            flex: 1;
            min-width: 0;
        }

        .feedback-title, .announcement-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--secondary-color);
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .feedback-category, .announcement-date {
            font-size: 12px;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .announcement-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 5px;
        }

        .announcement-author {
            font-size: 12px;
            color: #6c757d;
        }

        .mini-chart {
            height: 200px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding: 15px 0;
        }

        .chart-bar {
            flex: 1;
            margin: 0 5px;
            background: var(--primary-color);
            border-radius: 5px 5px 0 0;
            position: relative;
            transition: var(--transition);
            cursor: pointer;
        }

        .chart-bar:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }

        .chart-label {
            position: absolute;
            bottom: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }

        .chart-value {
            position: absolute;
            top: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            color: var(--secondary-color);
        }

        .admin-view {
            display: block;
        }

        .employee-view {
            display: none;
        }

        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 992px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: fixed;
                bottom: 0;
                z-index: 1000;
                padding: 10px 0;
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

            .page-title {
                font-size: 24px;
            }

            .welcome-message {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo-area">
                <img src="logo.png" alt="Tunisie Telecom Logo">
            </div>
            
            <div class="user-info">
                <div class="user-avatar" id="userAvatar"><?php echo htmlspecialchars($current_user['name'][0]); ?></div>
                <div class="user-details">
                    <div class="user-name" id="userName"><?php echo htmlspecialchars($current_user['name']); ?></div>
                    <div class="user-role" id="userRole"><?php echo htmlspecialchars($current_user['role']); ?></div>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-header admin-view">Admin Tools</li>
                <li class="nav-item admin-view">
                    <a href="news.php" class="nav-link">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li class="nav-item admin-view">
                    <a href="content.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Content</span>
                    </a>
                </li>
                <li class="nav-item admin-view">
                    <a href="coupons.php" class="nav-link">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Coupons</span>
                    </a>
                </li>
                <li class="nav-item admin-view">
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-header employee-view">Employee Tools</li>
                <li class="nav-item employee-view">
                    <a href="available-coupons.php" class="nav-link">
                        <i class="fas fa-tags"></i>
                        <span>Available Coupons</span>
                    </a>
                </li>
                <li class="nav-item employee-view">
                    <a href="my-coupons.php" class="nav-link">
                        <i class="fas fa-ticket-alt"></i>
                        <span>My Coupons</span>
                    </a>
                </li>
                <li class="nav-header">Support</li>
                <li class="nav-item">
                    <a href="feedback.php" class="nav-link">
                        <i class="fas fa-comment-dots"></i>
                        <span>Feedback</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user-circle"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <div class="page-title-container">
                    <h1 class="page-title">
                        <i class="fas fa-tachometer-alt"></i>
                        Admin Dashboard
                    </h1>
                    <span class="welcome-message">Welcome back, <?php echo htmlspecialchars($current_user['name']); ?>!</span>
                </div>
                <div class="header-actions">
                    <button class="btn btn-logout" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>

            <div class="welcome-banner">
                <h2>Your Admin Control Center</h2>
                <p>Manage users, oversee content, track system performance, and monitor all platform activities from your centralized dashboard.</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card users admin-view" onclick="window.location.href='users.php'" style="cursor: pointer;">
                    <div class="stat-info">
                        <div class="stat-title">Total Users</div>
                        <div class="stat-value"><?php echo htmlspecialchars($total_users ?? 0); ?></div>
                        <div class="stat-change <?php echo $total_users_change_class ?? ''; ?>">
                            <i class="fas <?php echo $total_users_change_icon ?? ''; ?>"></i>
                            <span><?php echo htmlspecialchars($total_users_change_text ?? ''); ?></span>
                        </div>
                    </div>
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                
                <div class="stat-card content admin-view" onclick="window.location.href='content.php'" style="cursor: pointer;">
                    <div class="stat-info">
                        <div class="stat-title">Published Content</div>
                        <div class="stat-value"><?php echo htmlspecialchars($published_content ?? 0); ?></div>
                        <div class="stat-change <?php echo $published_content_change_class ?? ''; ?>">
                            <i class="fas <?php echo $published_content_change_icon ?? ''; ?>"></i>
                            <span><?php echo htmlspecialchars($published_content_change_text ?? ''); ?></span>
                        </div>
                    </div>
                    <div class="stat-icon content">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                
                <div class="stat-card coupons admin-view" onclick="window.location.href='coupons.php'" style="cursor: pointer;">
                    <div class="stat-info">
                        <div class="stat-title">Active Coupons</div>
                        <div class="stat-value"><?php echo htmlspecialchars($active_coupons ?? 0); ?></div>
                        <div class="stat-change <?php echo $active_coupons_change_class ?? ''; ?>">
                            <i class="fas <?php echo $active_coupons_change_icon ?? ''; ?>"></i>
                            <span><?php echo htmlspecialchars($active_coupons_change_text ?? ''); ?></span>
                        </div>
                    </div>
                    <div class="stat-icon coupons">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>
                
                <div class="stat-card health" onclick="window.location.href='dashboard.php'" style="cursor: pointer;">
                    <div class="stat-info">
                        <div class="stat-title">System Health</div>
                        <div class="stat-value"><?php echo htmlspecialchars($system_health); ?>%</div>
                        <div class="stat-change positive">
                            <i class="fas fa-check-circle"></i>
                            <span>All systems operational</span>
                        </div>
                    </div>
                    <div class="stat-icon health">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="content-section admin-view">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-comment-alt"></i>
                            Recent Feedback
                        </h2>
                        <a href="feedback.php" class="view-all-link">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <ul class="feedback-list">
                        <?php if (empty($feedback_list ?? [])): ?>
                            <li class="feedback-item">
                                <div class="feedback-date">
                                    <span class="feedback-day">--</span>
                                    <span class="feedback-month">N/A</span>
                                </div>
                                <div class="feedback-details">
                                    <div class="feedback-title">No feedback available</div>
                                    <div class="feedback-category">
                                        <i class="far fa-comment"></i> None
                                    </div>
                                </div>
                            </li>
                        <?php else: ?>
                            <?php foreach ($feedback_list as $feedback): ?>
                                <li class="feedback-item">
                                    <div class="feedback-date">
                                        <span class="feedback-day"><?php echo date('d', strtotime($feedback['created_at'])); ?></span>
                                        <span class="feedback-month"><?php echo date('M', strtotime($feedback['created_at'])); ?></span>
                                    </div>
                                    <div class="feedback-details">
                                        <div class="feedback-title"><?php echo htmlspecialchars($feedback['subject']); ?></div>
                                        <div class="feedback-category">
                                            <i class="far fa-comment"></i> <?php echo htmlspecialchars($feedback['category']); ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-bullhorn"></i>
                            Recent Announcements
                        </h2>
                        <a href="news.php" class="view-all-link">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <ul class="announcement-list">
                        <?php if (empty($announcements)): ?>
                            <li class="announcement-item">
                                <div class="announcement-details">
                                    <div class="announcement-title">No announcements available</div>
                                    <div class="announcement-date">
                                        <i class="far fa-calendar"></i> N/A
                                    </div>
                                    <div class="announcement-meta">
                                        <span class="announcement-author">By: None</span>
                                    </div>
                                </div>
                            </li>
                        <?php else: ?>
                            <?php foreach ($announcements as $announcement): ?>
                                <li class="announcement-item">
                                    <div class="announcement-details">
                                        <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                        <div class="announcement-date">
                                            <i class="far fa-calendar"></i> <?php echo date('F d, Y', strtotime($announcement['created_at'])); ?>
                                        </div>
                                        <div class="announcement-meta">
                                            <span class="announcement-author">By: <?php echo htmlspecialchars($announcement['created_by_name']); ?></span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-chart-line"></i>
                            <?php echo ($role === 'employee' ? 'My ' : ''); ?>Coupon Usage
                        </h2>
                        <a href="<?php echo ($role === 'employee' ? 'my-coupons.php' : 'coupons.php'); ?>" class="view-all-link">
                            View Report <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="mini-chart">
                        <?php foreach ($usage_data as $data): ?>
                            <div class="chart-bar" style="height: <?php echo htmlspecialchars($data['height']); ?>%;">
                                <span class="chart-value"><?php echo htmlspecialchars($data['count']); ?></span>
                                <span class="chart-label"><?php echo htmlspecialchars($data['day']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentUser = localStorage.getItem('currentUser');
            
            if (!currentUser) {
                window.location.href = 'login.php';
                return;
            }
            
            const userData = JSON.parse(currentUser);
            
            document.getElementById('userName').textContent = userData.name;
            document.getElementById('userRole').textContent = userData.role;
            
            const nameInitial = userData.name.charAt(0).toUpperCase();
            const userAvatar = document.getElementById('userAvatar');
            
            if (userData.profile_picture) {
                loadProfilePicture(userData.profile_picture, userAvatar, nameInitial);
            } else {
                userAvatar.textContent = nameInitial;
            }
            
            const adminElements = document.querySelectorAll('.admin-view');
            const employeeElements = document.querySelectorAll('.employee-view');
            
            if (userData.role === 'admin') {
                adminElements.forEach(el => el.style.display = 'block');
                employeeElements.forEach(el => el.style.display = 'none');
            } else {
                adminElements.forEach(el => el.style.display = 'none');
                employeeElements.forEach(el => el.style.display = 'block');
            }
            
            document.getElementById('logoutBtn').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.removeItem('currentUser');
                    window.location.href = 'login.php';
                }
            });

            // Add staggered animation to feedback items
            document.querySelectorAll('.feedback-item, .announcement-item').forEach((item, index) => {
                item.style.animation = `slideInLeft 0.6s ease-out ${0.1 * index}s both`;
            });
        });
        
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
                avatarElement.textContent = nameInitial;
            };
            img.src = imagePath;
        }
    </script>
</body>
</html>