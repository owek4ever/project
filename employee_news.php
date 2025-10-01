<?php
// Start session and include database connection
session_start();
require_once 'db.php';

// Define base URL for AJAX requests (adjust this based on your server setup)
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

// Debug: Check session status
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

// Check if user is an employee
if ($userData['role'] !== 'employee') {
    error_log("User is not an employee. Redirecting to dashboard.php");
    header('Location: dashboard.php');
    exit;
}

// Fetch published announcements from database
$sql = "SELECT a.*, u.name as created_by_name 
        FROM announcements a 
        JOIN users u ON a.created_by = u.user_id 
        WHERE a.is_published = 1 
        ORDER BY a.published_at DESC";
$result = executeQuery($conn, $sql);

if ($result['success']) {
    $announcements = $result['result']->fetch_all(MYSQLI_ASSOC);
} else {
    $error_message = 'Failed to fetch announcements: ' . $result['error'];
    $announcements = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Tunisie Telecom</title>
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
            --gradient-announcement: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gold-gradient: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
            --info-gradient: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
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
            background: var(--info-gradient);
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
            background: var(--info-gradient);
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
            color: var(--primary-color);
            font-size: 20px;
        }

        /* Enhanced Announcement Cards */
        .announcement-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .announcement-card {
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

        .announcement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--gradient-announcement);
        }

        .announcement-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-hover-shadow);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .announcement-card:nth-child(1) { animation-delay: 0.1s; }
        .announcement-card:nth-child(2) { animation-delay: 0.2s; }
        .announcement-card:nth-child(3) { animation-delay: 0.3s; }
        .announcement-card:nth-child(4) { animation-delay: 0.4s; }
        .announcement-card:nth-child(5) { animation-delay: 0.5s; }
        .announcement-card:nth-child(6) { animation-delay: 0.6s; }

        .announcement-header {
            padding: 25px 25px 0;
            position: relative;
        }

        .announcement-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .brand-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--gradient-announcement);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }

        .brand-details {
            flex: 1;
        }

        .brand-details h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .announcement-badge {
            background: var(--info-gradient);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .announcement-badge::before {
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

        .announcement-content {
            padding: 0 25px 20px;
        }

        .card-image {
            width: 100%;
            max-height: 200px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: block;
            object-fit: cover;
            transition: var(--transition);
        }

        .card-image:hover {
            transform: scale(1.02);
        }

        .announcement-description {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .announcement-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
            width: 16px;
        }

        .meta-item strong {
            color: var(--secondary-color);
            font-weight: 600;
        }

        .announcement-footer {
            padding: 0 25px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .published-date {
            font-size: 13px;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .read-more-btn {
            padding: 8px 16px;
            font-size: 13px;
            border-radius: 6px;
            background: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .read-more-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
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
            background: var(--info-gradient);
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
            max-width: 800px;
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
            flex: 1;
            margin-right: 20px;
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
            flex-shrink: 0;
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
            line-height: 1.6;
        }

        .modal-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: block;
            object-fit: cover;
            width: 100%;
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
            .announcement-grid {
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

            .announcement-grid {
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

            .announcement-meta {
                grid-template-columns: 1fr;
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
                    <a href="employee_news.php" class="nav-link active">
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
                <h1 class="page-title">
                    <i class="fas fa-bullhorn"></i>
                    Announcements
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
                        <i class="fas fa-newspaper"></i>
                        Published Announcements
                    </h2>
                </div>

                <div class="announcement-grid">
                    <?php if (!empty($announcements)): ?>
                        <?php foreach ($announcements as $index => $announcement): ?>
                            <div class="announcement-card" 
                                 data-id="<?php echo htmlspecialchars($announcement['announcement_id']); ?>"
                                 style="animation-delay: <?php echo $index * 0.1; ?>s">
                                
                                <div class="announcement-header">
                                    <div class="announcement-brand">
                                        <div class="brand-info">
                                            <div class="brand-logo">
                                                <i class="fas fa-bullhorn"></i>
                                            </div>
                                            <div class="brand-details">
                                                <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                            </div>
                                        </div>
                                        <div class="announcement-badge">
                                            News
                                        </div>
                                    </div>
                                </div>

                                <div class="announcement-content">
                                    <?php if (!empty($announcement['image_filename'])): ?>
                                        <img src="<?php echo htmlspecialchars($announcement['image_path']); ?>" alt="Announcement image" class="card-image">
                                    <?php endif; ?>
                                    
                                    <div class="announcement-description">
                                        <?php echo htmlspecialchars($announcement['content']); ?>
                                    </div>

                                    <div class="announcement-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-user"></i>
                                            <span><strong>By:</strong> <?php echo htmlspecialchars($announcement['created_by_name']); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span><strong>Published:</strong> <?php echo date('M j, Y', strtotime($announcement['published_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="announcement-footer">
                                    <div class="published-date">
                                        <i class="fas fa-clock"></i>
                                        <?php 
                                            $published_time = strtotime($announcement['published_at']);
                                            $time_diff = time() - $published_time;
                                            if ($time_diff < 86400) {
                                                echo 'Today';
                                            } elseif ($time_diff < 172800) {
                                                echo 'Yesterday';
                                            } else {
                                                echo ceil($time_diff / 86400) . ' days ago';
                                            }
                                        ?>
                                    </div>
                                    <button class="read-more-btn" data-id="<?php echo htmlspecialchars($announcement['announcement_id']); ?>">
                                        <i class="fas fa-eye"></i>
                                        Read More
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bullhorn"></i>
                            <h3>No Announcements Available</h3>
                            <p>Check back later for company news and important updates from management!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- View Announcement Modal -->
    <div class="modal-overlay" id="viewAnnouncementModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="viewTitle">
                    <i class="fas fa-bullhorn"></i>
                    <span></span>
                </h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="view-modal-content">
                    <img id="viewImage" src="" alt="Announcement image" class="modal-image" style="display: none;">
                    
                    <div class="view-modal-label">
                        <i class="fas fa-align-left"></i>
                        Content
                    </div>
                    <div class="view-modal-value" id="viewContent"></div>
                    
                    <div class="view-modal-label">
                        <i class="fas fa-user"></i>
                        Created By
                    </div>
                    <div class="view-modal-value" id="viewCreatedBy"></div>
                    
                    <div class="view-modal-label">
                        <i class="fas fa-calendar-check"></i>
                        Published Date
                    </div>
                    <div class="view-modal-value" id="viewPublishedDate"></div>
                </div>
            </div>
            <div class="modal-footer">
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
            const announcementsData = <?php echo json_encode($announcements); ?>;

            // Log initial announcements data for debugging
            console.log('Initial announcements data:', announcementsData);

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

            // Announcement view functionality
            const viewModal = document.getElementById('viewAnnouncementModal');
            const closeViewBtn = document.getElementById('closeViewBtn');
            const modalCloseButtons = document.querySelectorAll('.modal-close');
            const viewTitle = document.getElementById('viewTitle').querySelector('span');
            const viewContent = document.getElementById('viewContent');
            const viewCreatedBy = document.getElementById('viewCreatedBy');
            const viewPublishedDate = document.getElementById('viewPublishedDate');
            const viewImage = document.getElementById('viewImage');
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');

            function showAnnouncementDetails(announcementId) {
                const announcementUrl = `${baseUrl}/get_announcement.php?id=${announcementId}`;
                console.log('Fetching announcement from:', announcementUrl);
                fetch(announcementUrl)
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Announcement data received:', data);
                        if (data.error) {
                            console.error('Server error:', data.error);
                            showNotification('error', 'Error: ' + data.error);
                            const localAnnouncement = announcementsData.find(a => a.announcement_id === announcementId);
                            if (localAnnouncement) {
                                displayAnnouncementDetails(localAnnouncement);
                            } else {
                                showNotification('error', 'Announcement data not available locally.');
                            }
                            return;
                        }
                        displayAnnouncementDetails(data);
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        showNotification('error', `Error loading announcement details: ${error.message}. Using local data.`);
                        const localAnnouncement = announcementsData.find(a => a.announcement_id === announcementId);
                        if (localAnnouncement) {
                            displayAnnouncementDetails(localAnnouncement);
                        } else {
                            showNotification('error', 'Announcement data not available locally.');
                        }
                    });
            }

            function displayAnnouncementDetails(data) {
                console.log('Displaying announcement details:', data);
                viewTitle.textContent = data.title || 'Announcement';
                viewContent.textContent = data.content || 'No content available';
                viewCreatedBy.textContent = data.created_by_name || 'Unknown';
                viewPublishedDate.textContent = new Date(data.published_at).toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                if (data.image_path && data.image_filename) {
                    viewImage.src = data.image_path;
                    viewImage.style.display = 'block';
                } else {
                    viewImage.style.display = 'none';
                }
                
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

            // Handle announcement card click
            document.querySelectorAll('.announcement-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (e.target.closest('.read-more-btn')) {
                        return;
                    }
                    const announcementId = this.getAttribute('data-id');
                    console.log('Announcement card clicked, announcementId:', announcementId);
                    if (!announcementId || announcementId === 'undefined' || announcementId.trim() === '') {
                        showNotification('error', 'Invalid announcement ID. Please try again.');
                        return;
                    }
                    showAnnouncementDetails(announcementId);
                });
            });

            // Handle read more button clicks
            document.querySelectorAll('.read-more-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent card click event
                    const announcementId = this.getAttribute('data-id');
                    console.log('Read more button clicked, announcementId:', announcementId);
                    if (!announcementId || announcementId === 'undefined' || announcementId.trim() === '') {
                        showNotification('error', 'Invalid announcement ID. Please try again.');
                        return;
                    }
                    showAnnouncementDetails(announcementId);
                });
            });

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