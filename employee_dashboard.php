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

// Fetch stats for cards (Employee-specific)
// My Coupons (Removed from display, keeping query for potential future use)
$stmt = $conn->prepare("SELECT COUNT(*) as my_coupons FROM coupon_redemptions WHERE employee_id = ?");
$stmt->execute([$userData['user_id']]);
$my_coupons = $stmt->fetch(PDO::FETCH_ASSOC)['my_coupons'];

// System Health (Removed from display, no need to keep placeholder)
$system_health = 98;

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
    <title>TT Employee Dashboard - User Management System</title>
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
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            height: 100vh;
            color: var(--dark-text);
            position: relative;
            overflow: hidden;
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

        .main-content {
            flex: 1;
            padding: 30px;
            height: 100vh;
            overflow: hidden;
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title-container {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: white;
        }

        .welcome-message {
            font-size: 20px;
            font-weight: 400;
            color: var(--light-text);
            opacity: 0.9;
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

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            height: 0; /* Empty, so collapse */
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

        .bg-warning {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }

        .bg-success {
            background: rgba(46, 204, 113, 0.2);
            color: var(--accent-color);
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

        .content-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }

        .summary-section {
            padding: 20px;
            font-size: 16px;
            line-height: 1.6;
            color: var(--dark-text);
            height: 120px;
            overflow: hidden;
        }

        .summary-section h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--secondary-color);
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

        .view-all-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .view-all-link:hover {
            color: var(--primary-dark);
        }

        .feedback-list {
            list-style: none;
            height: 360px;
            overflow: hidden;
        }

        .feedback-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .feedback-item:last-child {
            border-bottom: none;
        }

        .feedback-date {
            width: 60px;
            text-align: center;
            margin-right: 20px;
        }

        .feedback-day {
            display: block;
            font-size: 24px;
            font-weight: bold;
        }

        .feedback-month {
            display: block;
            font-size: 14px;
            text-transform: uppercase;
        }

        .feedback-details {
            flex: 1;
        }

        .feedback-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .feedback-category {
            font-size: 14px;
            color: #7f8c8d;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .feedback-status {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .mini-chart {
            display: flex;
            justify-content: space-between;
            height: 120px;
            align-items: flex-end;
        }

        .chart-bar {
            flex: 1;
            background: var(--primary-color);
            border-radius: 5px 5px 0 0;
            position: relative;
            margin: 0 5px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
        }

        .chart-value {
            position: absolute;
            top: -25px;
            font-size: 14px;
            font-weight: bold;
        }

        .chart-label {
            margin-top: 10px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Two columns: feedback and announcement */
            gap: 20px;
            height: calc(100% - 210px); /* Fit within main-content minus header (90px) and summary (120px) */
            overflow: hidden;
        }

        .feedback-section {
            height: 420px; /* Fixed height to fit */
        }

        .announcement-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            height: 420px; /* Match feedback-section */
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 15px; /* Space between image, title, and description */
        }

        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .card-image {
            width: 100%;
            height: 65%; /* Slightly bigger than previous 60% */
            border-radius: 8px;
            display: block;
            object-fit: cover; /* Fill space, may crop */
            margin: 0 0 15px; /* Spacing below image */
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .card-description {
            font-size: 14px;
            color: #7f8c8d;
            line-height: 1.5;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Limit to 2 lines */
            -webkit-box-orient: vertical;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
        }

        .modal {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            box-shadow: var(--card-shadow);
            animation: modalFadeIn 0.3s ease-out;
            display: flex;
            flex-direction: column;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-shrink: 0;
        }

        .view-modal-content {
            margin-bottom: 15px;
        }

        .view-modal-label {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--secondary-color);
        }

        .view-modal-value {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            z-index: 1100;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: var(--card-shadow);
            animation: slideIn 0.3s ease-out;
        }

        .notification.success {
            background-color: var(--accent-color);
        }

        .notification.error {
            background-color: var(--danger-color);
        }

        @keyframes slideIn {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .employee-view {
            display: block;
        }

        .admin-view {
            display: none;
        }

        @media (max-width: 992px) {
            .dashboard-container {
                flex-direction: column;
                height: 100vh;
                overflow: hidden;
            }

            .sidebar {
                width: 100%;
                height: auto;
                padding: 15px 0;
            }

            .logo-area {
                display: none;
            }

            .user-info {
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
                height: 100vh;
                padding: 20px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr; /* Stack on smaller screens */
                height: calc(100% - 180px); /* Adjust for header (80px) and summary (100px) */
            }

            .feedback-list {
                height: 300px;
            }

            .feedback-section {
                height: 360px;
            }

            .announcement-card {
                height: 360px;
            }

            .card-image {
                height: 55%; /* Slightly bigger than previous 50% */
            }

            .card-description {
                -webkit-line-clamp: 2; /* Maintain 2 lines */
            }

            .modal {
                width: 95%;
                max-height: 85vh;
            }

            .modal-overlay {
                padding: 10px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                margin-bottom: 20px;
            }

            .page-title-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .header-actions {
                width: 100%;
                justify-content: space-between;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
                height: calc(100% - 170px);
            }

            .summary-section {
                height: 100px;
            }

            .feedback-list {
                height: 280px;
            }

            .feedback-section {
                height: 340px;
            }

            .announcement-card {
                height: 340px;
            }

            .card-image {
                height: 55%; /* Slightly bigger than previous 50% */
            }

            .card-description {
                -webkit-line-clamp: 2; /* Maintain 2 lines */
            }

            .modal-body {
                padding: 15px;
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
                    <a href="employee_dashboard.php" class="nav-link active" aria-label="Dashboard">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-header employee-view">Employee Tools</li>
                <li class="nav-item employee-view">
                    <a href="employee_news.php" class="nav-link" aria-label="Announcements">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li class="nav-item employee-view">
                    <a href="my-coupons.php" class="nav-link" aria-label="My Coupons">
                        <i class="fas fa-ticket-alt"></i>
                        <span>My Coupons</span>
                    </a>
                </li>
                <li class="nav-header">Support</li>
                <li class="nav-item">
                    <a href="feedback.php" class="nav-link" aria-label="Feedback">
                        <i class="fas fa-comment-dots"></i>
                        <span>Feedback</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link" aria-label="Profile">
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
                    <h1 class="page-title">Employee Dashboard</h1>
                    <span class="welcome-message">Welcome, <?php echo htmlspecialchars($userData['name']); ?></span>
                </div>
                <div class="header-actions">
                    <a href="logout.php" class="btn btn-logout" aria-label="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
            
            <!-- Summary Section -->
            <div class="content-section summary-section">
                <p>🌐 Our mini portal is the central hub for employees of our Tunisian telecom company. It brings everything together in one place — from managing 🎟️ coupons, to sharing 💬 feedback, and staying up to date with 📢 announcements. Built for simplicity and connection, the portal makes daily tasks easier and keeps everyone engaged.</p>
            </div>
            
            <!-- Stats Cards (Removed My Coupons and System Health widgets) -->
            <div class="stats-cards">
            </div>
            
            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- My Recent Feedback Section -->
                <div class="content-section feedback-section">
                    <div class="section-header">
                        <h2 class="section-title">My Recent Feedback</h2>
                        <a href="feedback.php" class="view-all-link">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <ul class="feedback-list">
                        <?php if (empty($my_feedback_list)): ?>
                            <li class="feedback-item">
                                <div class="feedback-date">
                                    <span class="feedback-day">No</span>
                                    <span class="feedback-month">Data</span>
                                </div>
                                <div class="feedback-details">
                                    <div class="feedback-title">No feedback submitted yet</div>
                                    <div class="feedback-category">
                                        <i class="far fa-comment"></i> None
                                    </div>
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
                                            <i class="far fa-comment"></i> <?php echo htmlspecialchars($feedback['category']); ?>
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
                <div class="announcement-card">
                    <?php if (!empty($announcement) && !empty($announcement['image_path']) && file_exists($announcement['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($announcement['image_path']) . '?v=' . time(); ?>" alt="Announcement Image" class="card-image">
                    <?php else: ?>
                        <img src="path/to/placeholder-image.jpg" alt="No Announcement Image" class="card-image">
                    <?php endif; ?>
                    <div class="card-title">
                        <?php echo empty($announcement) ? 'No announcements available' : htmlspecialchars($announcement['title']); ?>
                    </div>
                    <div class="card-description">
                        <?php 
                        $description = empty($announcement) || empty($announcement['description']) 
                            ? 'No description available.' 
                            : htmlspecialchars(substr($announcement['description'], 0, 100)) . (strlen($announcement['description']) > 100 ? '...' : '');
                        echo $description;
                        ?>
                    </div>
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

            document.querySelector('.btn-logout').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.removeItem('currentUser');
                    window.location.href = 'logout.php';
                }
            });

            // Make announcement card clickable
            const announcementCard = document.querySelector('.announcement-card');
            if (announcementCard) {
                announcementCard.style.cursor = 'pointer';
                announcementCard.addEventListener('click', function() {
                    window.location.href = 'employee_news.php';
                });
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
        });
    </script>
</body>
</html>