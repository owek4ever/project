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
// Total Users (Admin only)
if ($role === 'admin') {
    $stmt = $conn->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Calculate new users in the last 7 days
    $stmt = $conn->query("SELECT COUNT(*) as new_users FROM users WHERE created_at >= CURDATE() - INTERVAL 7 DAY");
    $new_users = $stmt->fetch(PDO::FETCH_ASSOC)['new_users'];
    $total_users_change = $total_users > 0 ? ($new_users / $total_users * 100) : 0;
    $total_users_change_text = number_format($total_users_change, 1) . '% from last week';
    $total_users_change_class = $new_users >= 0 ? 'positive' : 'negative';
    $total_users_change_icon = $new_users >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

    // Published Content (Admin only)
    $stmt = $conn->query("SELECT COUNT(*) as published_content FROM content WHERE published = 1");
    $published_content = $stmt->fetch(PDO::FETCH_ASSOC)['published_content'];

    // Calculate new published content in the last 7 days
    $stmt = $conn->query("SELECT COUNT(*) as new_content FROM content WHERE published = 1 AND published_at >= CURDATE() - INTERVAL 7 DAY");
    $new_content = $stmt->fetch(PDO::FETCH_ASSOC)['new_content'];
    $published_content_change = $published_content > 0 ? ($new_content / $published_content * 100) : 0;
    $published_content_change_text = number_format($published_content_change, 1) . '% from last week';
    $published_content_change_class = $new_content >= 0 ? 'positive' : 'negative';
    $published_content_change_icon = $new_content >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

    // Active Coupons (Admin only)
    $stmt = $conn->query("SELECT COUNT(*) as active_coupons FROM coupons WHERE expiry_date >= CURDATE()");
    $active_coupons = $stmt->fetch(PDO::FETCH_ASSOC)['active_coupons'];

    // Calculate new active coupons in the last 7 days
    $stmt = $conn->query("SELECT COUNT(*) as new_active_coupons FROM coupons WHERE expiry_date >= CURDATE() AND created_at >= CURDATE() - INTERVAL 7 DAY");
    $new_active_coupons = $stmt->fetch(PDO::FETCH_ASSOC)['new_active_coupons'];
    $active_coupons_change = $active_coupons > 0 ? ($new_active_coupons / $active_coupons * 100) : 0;
    $active_coupons_change_text = number_format($active_coupons_change, 1) . '% from last week';
    $active_coupons_change_class = $new_active_coupons >= 0 ? 'positive' : 'negative';
    $active_coupons_change_icon = $new_active_coupons >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

    // Fetch recent feedback (Admin only)
    $stmt = $conn->query("SELECT feedback_id, subject, category, created_at FROM feedback ORDER BY created_at DESC LIMIT 5");
    $feedback_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// My Coupons (Employee only)
$stmt = $conn->prepare("SELECT COUNT(*) as my_coupons FROM coupon_redemptions WHERE employee_id = ?");
$stmt->execute([$user_id]);
$my_coupons = $stmt->fetch(PDO::FETCH_ASSOC)['my_coupons'];

// System Health (Placeholder: Assume 98% if database is accessible)
$system_health = 98;

// Fetch recent announcements
$stmt = $conn->query("SELECT a.announcement_id, a.title, a.created_at, u.name as created_by_name 
                     FROM announcements a 
                     JOIN users u ON a.created_by = u.user_id 
                     WHERE a.is_published = 1 
                     ORDER BY a.created_at DESC LIMIT 3");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch coupon usage data for the last 5 days
$usage_data = [];
$days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
$max_usage = 100; // For scaling chart bars
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
        'height' => $count > 0 ? ($count / $max_usage * 100) : 10 // Minimum height for visibility
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TT Dashboard - User Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Original CSS styles unchanged */
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
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            min-height: 100vh;
            color: var(--dark-text);
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
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

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            color: var(--light-text);
            padding: 20px 0;
            box-shadow: var(--card-shadow);
            z-index: 100;
            transition: var(--transition);
            overflow: hidden;
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

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow: hidden;
            height: calc(100vh - 60px);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: white;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-logout {
            background: rgba(231, 76, 60, 0.2);
            color: var(--light-text);
        }

        .btn-logout:hover {
            background: rgba(231, 76, 60, 0.3);
        }

        /* Dashboard Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            font-size: 16px;
            font-weight: 600;
            color: #7f8c8d;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .bg-primary {
            background: rgba(52, 152, 219, 0.2);
            color: var(--primary-color);
        }

        .bg-success {
            background: rgba(46, 204, 113, 0.2);
            color: var(--accent-color);
        }

        .bg-warning {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }

        .bg-danger {
            background: rgba(231, 76, 60, 0.2);
            color: var(--danger-color);
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .positive {
            color: var(--accent-color);
        }

        .negative {
            color: var(--danger-color);
        }

        /* Content Sections */
        .content-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
        }

        .data-table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }

        .data-table tbody tr {
            border-bottom: 1px solid #eee;
            transition: var(--transition);
        }

        .data-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .published {
            background: rgba(46, 204, 113, 0.2);
            color: var(--accent-color);
        }

        .draft {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            margin-right: 5px;
        }

        .btn-view {
            background: rgba(52, 152, 219, 0.2);
            color: var(--primary-color);
        }

        .btn-edit {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }

        .btn-delete {
            background: rgba(231, 76, 60, 0.2);
            color: var(--danger-color);
        }

        .btn-redeem {
            background: rgba(46, 204, 113, 0.2);
            color: var(--accent-color);
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 16px;
            background: #f9f9f9;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        /* NEW STYLES FOR ADDED COMPONENTS */
        
        /* Dashboard grid for new components */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            height: calc(100% - 120px);
            overflow: hidden;
        }
        
        /* Feedback and Announcement Lists */
        .feedback-list, .announcement-list {
            list-style: none;
        }
        
        .feedback-item, .announcement-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: flex-start;
        }
        
        .feedback-item:last-child, .announcement-item:last-child {
            border-bottom: none;
        }
        
        .feedback-date {
            min-width: 50px;
            text-align: center;
            margin-right: 15px;
        }
        
        .feedback-day {
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-color);
            display: block;
        }
        
        .feedback-month {
            font-size: 12px;
            text-transform: uppercase;
            color: #7f8c8d;
        }
        
        .feedback-details, .announcement-details {
            flex: 1;
        }
        
        .feedback-title, .announcement-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-text);
        }
        
        .feedback-category, .announcement-date {
            font-size: 12px;
            color: #7f8c8d;
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
            color: #7f8c8d;
        }
        
        /* Mini Chart Styles */
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
            pos...(truncated 1050 characters)...        
            .sidebar {
                width: 100%;
                height: auto;
                position: fixed;
                bottom: 0;
                z-index: 1000;
                padding: 10px 0;
                overflow: hidden;
            }
            
            .logo-area {
                display: none;
            }
            
            .user-info {
                display: none;
            }
            
            .nav-menu {
                display: flex;
            }
            
            .nav-item {
                margin-bottom: 0;
                flex-shrink: 0;
            }
            
            .nav-link {
                border-left: none;
                border-top: 4px solid transparent;
                flex-direction: column;
                padding: 10px 15px;
                font-size: 12px;
            }
            
            .nav-link i {
                margin-right: 0;
                margin-bottom: 5px;
                font-size: 16px;
            }
            
            .nav-link:hover, .nav-link.active {
                border-left-color: transparent;
                border-top-color: var(--primary-color);
            }
            
            .main-content {
                margin-bottom: 80px;
                height: calc(100vh - 80px);
                padding: 20px;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
                height: calc(100% - 100px);
            }
        }

        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: flex-end;
            }
            
            .main-content {
                padding: 15px;
                height: calc(100vh - 70px);
            }
            
            .dashboard-grid {
                height: calc(100% - 80px);
                gap: 15px;
            }
        }

        /* Animation for content appearance */
        .main-content {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Role-specific styles */
        .employee-view {
            display: none;
        }

        .admin-view {
            display: none;
        }

        /* Utility classes */
        .text-center {
            text-align: center;
        }
        
        .mt-20 {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar - Updated with Profile Structure -->
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
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1 class="page-title">Dashboard</h1>
                <div class="header-actions">
                    <button class="btn btn-logout" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card admin-view">
                    <div class="stat-header">
                        <div class="stat-title">Total Users</div>
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo htmlspecialchars($total_users ?? 0); ?></div>
                    <div class="stat-change <?php echo $total_users_change_class ?? ''; ?>">
                        <i class="fas <?php echo $total_users_change_icon ?? ''; ?>"></i>
                        <span><?php echo htmlspecialchars($total_users_change_text ?? ''); ?></span>
                    </div>
                </div>
                
                <div class="stat-card admin-view">
                    <div class="stat-header">
                        <div class="stat-title">Published Content</div>
                        <div class="stat-icon bg-success">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo htmlspecialchars($published_content ?? 0); ?></div>
                    <div class="stat-change <?php echo $published_content_change_class ?? ''; ?>">
                        <i class="fas <?php echo $published_content_change_icon ?? ''; ?>"></i>
                        <span><?php echo htmlspecialchars($published_content_change_text ?? ''); ?></span>
                    </div>
                </div>
                
                <div class="stat-card admin-view">
                    <div class="stat-header">
                        <div class="stat-title">Active Coupons</div>
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo htmlspecialchars($active_coupons ?? 0); ?></div>
                    <div class="stat-change <?php echo $active_coupons_change_class ?? ''; ?>">
                        <i class="fas <?php echo $active_coupons_change_icon ?? ''; ?>"></i>
                        <span><?php echo htmlspecialchars($active_coupons_change_text ?? ''); ?></span>
                    </div>
                </div>
                
                <div class="stat-card employee-view">
                    <div class="stat-header">
                        <div class="stat-title">My Coupons</div>
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo htmlspecialchars($my_coupons); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Dynamic data not available</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">System Health</div>
                        <div class="stat-icon bg-success">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo htmlspecialchars($system_health); ?>%</div>
                    <div class="stat-change positive">
                        <i class="fas fa-check-circle"></i>
                        <span>All systems operational</span>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Grid with Recent Feedback -->
            <div class="dashboard-grid">
                <!-- Recent Feedback Section -->
                <div class="content-section admin-view">
                    <div class="section-header">
                        <h2 class="section-title">Recent Feedback</h2>
                        <a href="feedback.php" class="view-all-link">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <ul class="feedback-list">
                        <?php if (empty($feedback_list ?? [])): ?>
                            <li class="feedback-item">
                                <div class="feedback-date">
                                    <span class="feedback-day">No</span>
                                    <span class="feedback-month">Data</span>
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
                
                <!-- Recent Announcements Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Announcements</h2>
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
                
                <!-- Coupon Usage Chart Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title"><?php echo ($role === 'employee' ? 'My ' : ''); ?>Coupon Usage</h2>
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
        // Original JavaScript unchanged
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