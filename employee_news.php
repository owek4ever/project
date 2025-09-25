<?php
// Start session and include database connection
session_start();
require_once 'db.php';

// Debug: Check session status
error_log("Session status: " . session_status());
error_log("Session data: " . print_r($_SESSION, true));

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
    <title>TT Dashboard - Announcements</title>
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
            min-height: 100vh;
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
            min-height: 100vh;
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
            overflow-y: auto;
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

        .announcement-list {
            list-style: none;
        }

        .announcement-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .announcement-item:last-child {
            border-bottom: none;
        }

        .announcement-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .announcement-date {
            font-size: 14px;
            color: #7f8c8d;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 5px;
        }

        .announcement-meta {
            font-size: 14px;
            color: #7f8c8d;
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .announcement-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            max-height: none;
            overflow-y: auto;
            padding-right: 10px;
            padding-bottom: 20px;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #f1f1f1;
        }

        .announcement-grid::-webkit-scrollbar {
            width: 8px;
        }

        .announcement-grid::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .announcement-grid::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .announcement-grid::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        .announcement-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            cursor: pointer;
        }

        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-text);
        }

        .card-meta {
            margin-bottom: 10px;
            font-size: 14px;
            color: #666;
        }

        .card-meta span {
            display: block;
            margin-bottom: 5px;
        }

        .card-content {
            font-size: 14px;
            color: var(--dark-text);
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .card-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: block;
            object-fit: cover;
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

        .modal-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: block;
            object-fit: cover;
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
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: fixed;
                bottom: 0;
                z-index: 1000;
                padding: 10px 0;
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
                margin-bottom: 80px;
            }

            .announcement-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                max-height: none;
                padding-bottom: 20px;
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
            }

            .header-actions {
                width: 100%;
                justify-content: space-between;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .announcement-grid {
                grid-template-columns: 1fr;
                max-height: none;
                padding-bottom: 20px;
            }

            .modal-body {
                padding: 15px;
            }
        }

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
                <li class="nav-header employee-view">Employee Tools</li>
                <li class="nav-item employee-view">
                    <a href="employee_news.php" class="nav-link active">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
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
                <h1 class="page-title">Announcements</h1>
                <div class="header-actions">
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Published Announcements</h2>
                </div>

                <div class="announcement-grid">
                    <?php if (!empty($announcements)): ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-card" data-id="<?php echo $announcement['announcement_id']; ?>">
                                <div class="card-header">
                                    <h3 class="card-title"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                </div>
                                <?php if (!empty($announcement['image_filename'])): ?>
                                    <img src="<?php echo htmlspecialchars($announcement['image_path']); ?>" alt="Announcement image" class="card-image">
                                <?php endif; ?>
                                <div class="card-content"><?php echo htmlspecialchars($announcement['content']); ?></div>
                                <div class="card-meta">
                                    <span><strong>Created By:</strong> <?php echo htmlspecialchars($announcement['created_by_name']); ?></span>
                                    <span><strong>Published:</strong> <?php echo date('M j, Y', strtotime($announcement['published_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 20px;">
                            No published announcements found.
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
                <h3 class="modal-title" id="viewTitle"></h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="view-modal-content">
                    <img id="viewImage" src="" alt="Announcement image" class="modal-image" style="display: none;">
                    <div class="view-modal-label">Description</div>
                    <div class="view-modal-value" id="viewContent"></div>
                    <div class="view-modal-label">Created By</div>
                    <div class="view-modal-value" id="viewCreatedBy"></div>
                    <div class="view-modal-label">Published Date</div>
                    <div class="view-modal-value" id="viewPublishedDate"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" id="closeViewBtn">Close</button>
            </div>
        </div>
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

            // Announcement view functionality
            const viewModal = document.getElementById('viewAnnouncementModal');
            const closeViewBtn = document.getElementById('closeViewBtn');
            const modalCloseButtons = document.querySelectorAll('.modal-close');
            const viewTitle = document.getElementById('viewTitle');
            const viewContent = document.getElementById('viewContent');
            const viewCreatedBy = document.getElementById('viewCreatedBy');
            const viewPublishedDate = document.getElementById('viewPublishedDate');
            const viewImage = document.getElementById('viewImage');

            function showAnnouncementDetails(announcementId) {
                fetch(`get_announcement.php?id=${announcementId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert('Error: ' + data.error);
                            return;
                        }
                        viewTitle.textContent = data.title;
                        viewContent.textContent = data.content;
                        viewCreatedBy.textContent = data.created_by_name;
                        viewPublishedDate.textContent = new Date(data.published_at).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });
                        if (data.image_path) {
                            viewImage.src = data.image_path;
                            viewImage.style.display = 'block';
                        } else {
                            viewImage.style.display = 'none';
                        }
                        viewModal.style.display = 'flex';
                    })
                    .catch(error => {
                        console.error('Error fetching announcement:', error);
                        alert('Error loading announcement details');
                    });
            }

            document.querySelectorAll('.announcement-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    const announcementId = this.getAttribute('data-id');
                    showAnnouncementDetails(announcementId);
                });
            });

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

            document.querySelector('.btn-logout').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.removeItem('currentUser');
                    window.location.href = 'logout.php';
                }
            });
        });
    </script>
</body>
</html>