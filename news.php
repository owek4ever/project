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
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';

// Clear session messages after displaying
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Generate a unique token for form submission to prevent duplicate submissions
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(16));
}
$form_token = $_SESSION['form_token'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_announcement'])) {
    // Verify the form token
    if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
        $_SESSION['error_message'] = 'Invalid form submission. Please try again.';
        header('Location: news.php');
        exit;
    }

    // Clear the form token after submission
    unset($_SESSION['form_token']);

    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';
    $published = isset($_POST['published']) ? 1 : 0;
    $content_id = $_POST['content_id'] ?? '';
    
    // Handle file upload
    $image_filename = '';
    $image_path = '';
    
    if (isset($_FILES['announcement_image']) && $_FILES['announcement_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/announcements/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['announcement_image']['name'], PATHINFO_EXTENSION);
        $image_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $image_path = $upload_dir . $image_filename;
        
        if (!move_uploaded_file($_FILES['announcement_image']['tmp_name'], $image_path)) {
            $_SESSION['error_message'] = 'Failed to upload image.';
            header('Location: news.php');
            exit;
        }
    }
    
    if (empty($error_message)) {
        if (empty($content_id)) {
            // Create new announcement
            $announcement_id = uniqid();
            $sql = "INSERT INTO announcements (announcement_id, title, content, created_by, is_published, published_at, image_filename, image_path) 
                    VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";
            $result = executeQuery($conn, $sql, [$announcement_id, $title, $body, $userData['user_id'], $published, $image_filename, $image_path]);
            
            if ($result['success']) {
                $_SESSION['success_message'] = 'Announcement created successfully!';
                header('Location: news.php');
                exit;
            } else {
                $_SESSION['error_message'] = 'Database error: ' . $result['error'];
                header('Location: news.php');
                exit;
            }
        } else {
            // Update existing announcement
            if (!empty($image_filename)) {
                // If new image uploaded, update image fields
                $sql = "UPDATE announcements SET title = ?, content = ?, is_published = ?, published_at = NOW(), image_filename = ?, image_path = ? 
                        WHERE announcement_id = ?";
                $result = executeQuery($conn, $sql, [$title, $body, $published, $image_filename, $image_path, $content_id]);
            } else {
                // Keep existing image
                $sql = "UPDATE announcements SET title = ?, content = ?, is_published = ?, published_at = NOW() 
                        WHERE announcement_id = ?";
                $result = executeQuery($conn, $sql, [$title, $body, $published, $content_id]);
            }
            
            if ($result['success']) {
                $_SESSION['success_message'] = 'Announcement updated successfully!';
                header('Location: news.php');
                exit;
            } else {
                $_SESSION['error_message'] = 'Database error: ' . $result['error'];
                header('Location: news.php');
                exit;
            }
        }
    }
}

// Fetch announcements from database
$sql = "SELECT a.*, u.name as created_by_name 
        FROM announcements a 
        JOIN users u ON a.created_by = u.user_id 
        ORDER BY a.created_at DESC";
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
    <title>TT Dashboard - Announcement Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Your existing CSS styles (updated user-avatar styles) */
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

        /* Updated user-avatar styles to match content.php */
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
            animation: fadeIn 0.8s ease-out;
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
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-logout {
            background: rgba(231, 76, 60, 0.2);
            color: var(--light-text);
        }

        .btn-logout:hover {
            background: rgba(231, 76, 60, 0.3);
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
            cursor: pointer;
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
        
        .expired {
            background: rgba(231, 76, 60, 0.2);
            color: var(--danger-color);
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

        /* Modal Styles */
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
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        /* Simple file upload button */
        .file-upload-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
        }

        .file-upload-btn:hover {
            background-color: #2980b9;
        }

        .file-upload-btn i {
            margin-right: 8px;
        }

        /* Current image display */
        .current-image {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        
        .current-image img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 8px;
            display: block;
            margin: 10px auto;
        }

        /* View Announcement Modal */
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

        .announcement-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            margin-top: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            object-fit: contain;
        }

        /* Notification */
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

        /* Role-specific styles */
        .employee-view {
            display: none;
        }

        .admin-view {
            display: block;
        }

        /* Responsive Design */
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
            
            /* Mobile modal adjustments */
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
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
            
            .modal-body {
                padding: 15px;
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
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-header admin-view">Admin Tools</li>
                <li class="nav-item admin-view">
                    <a href="news.php" class="nav-link active">
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
                <h1 class="page-title">Announcement Management</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" id="createAnnouncementBtn">
                        <i class="fas fa-plus"></i>
                        <span>Create Announcement</span>
                    </button>
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
            
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Active Announcements</h2>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Created By</th>
                                <th>Created Date</th>
                                <th>Published Date</th>
                                <th>Status</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($announcements)): ?>
                                <?php foreach ($announcements as $announcement): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                                    <td><?php echo htmlspecialchars($announcement['created_by_name']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($announcement['created_at'])); ?></td>
                                    <td><?php echo $announcement['published_at'] ? date('M j, Y', strtotime($announcement['published_at'])) : 'Not published'; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $announcement['is_published'] ? 'published' : 'draft'; ?>">
                                            <?php echo $announcement['is_published'] ? 'Published' : 'Draft'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($announcement['image_filename'])): ?>
                                            <i class="fas fa-image" style="color: var(--primary-color);"></i>
                                        <?php else: ?>
                                            <span class="status-badge draft">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="action-btn btn-view" data-id="<?php echo $announcement['announcement_id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn btn-edit" data-id="<?php echo $announcement['announcement_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn btn-delete" data-id="<?php echo $announcement['announcement_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No announcements found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Create/Edit Announcement Modal -->
    <div class="modal-overlay" id="announcementModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create New Announcement</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form id="announcementForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="contentId" name="content_id" value="">
                    <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($form_token); ?>">
                    <div class="form-group">
                        <label class="form-label" for="announcementTitle">Title</label>
                        <input type="text" class="form-control" id="announcementTitle" name="title" placeholder="Enter announcement title" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="announcementContent">Content</label>
                        <textarea class="form-control" id="announcementContent" name="body" placeholder="Enter announcement content" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Announcement Image</label>
                        <div>
                            <label class="file-upload-btn">
                                <i class="fas fa-upload"></i>
                                Choose Image
                                <input type="file" id="announcementImage" name="announcement_image" accept="image/*" style="display: none;">
                            </label>
                            <span id="fileName" style="margin-left: 10px; color: #666;">No file chosen</span>
                        </div>
                        <div id="currentImageContainer" class="current-image" style="display: none;">
                            <p class="form-label">Current Image:</p>
                            <img id="currentImage" src="" alt="Current image">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" id="announcementPublish" name="published">
                            Publish immediately
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="create_announcement">
                        Save Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Announcement Modal -->
    <div class="modal-overlay" id="viewAnnouncementModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Announcement Details</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="view-modal-content">
                    <div class="view-modal-label">Title</div>
                    <div class="view-modal-value" id="viewTitle"></div>
                    
                    <div class="view-modal-label">Content</div>
                    <div class="view-modal-value" id="viewContent"></div>
                    
                    <div class="view-modal-label" id="viewImageLabel" style="display: none;">Image</div>
                    <div class="view-modal-value" id="viewImageContainer" style="display: none;">
                        <img id="viewImage" src="" alt="Announcement image" class="announcement-image">
                    </div>
                    
                    <div class="view-modal-label">Created By</div>
                    <div class="view-modal-value" id="viewAuthor"></div>
                    
                    <div class="view-modal-label">Created Date</div>
                    <div class="view-modal-value" id="viewCreatedDate"></div>
                    
                    <div class="view-modal-label">Published Date</div>
                    <div class="view-modal-value" id="viewPublishedDate"></div>
                    
                    <div class="view-modal-label">Status</div>
                    <div class="view-modal-value">
                        <span class="status-badge" id="viewStatus"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" id="closeViewBtn">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Profile picture loading functionality (updated to match content.php)
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
        });

        function loadProfilePicture(imagePath, avatarElement, nameInitial) {
            // Load image with error handling
            const img = new Image();
            img.onload = function() {
                avatarElement.style.backgroundImage = `url(${imagePath})`;
                avatarElement.style.backgroundSize = 'cover';
                avatarElement.style.backgroundPosition = 'center';
                avatarElement.textContent = '';
            };
            img.onerror = function() {
                // Fallback to initial with gradient background
                avatarElement.style.backgroundImage = '';
                avatarElement.style.background = 'linear-gradient(135deg, var(--primary-color), var(--secondary-color))';
                avatarElement.textContent = nameInitial;
                avatarElement.style.color = 'white';
            };
            img.src = imagePath;
        }

        // Existing announcement management functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Modal elements
            const announcementModal = document.getElementById('announcementModal');
            const viewModal = document.getElementById('viewAnnouncementModal');
            const modalTitle = document.getElementById('modalTitle');
            const createBtn = document.getElementById('createAnnouncementBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const closeViewBtn = document.getElementById('closeViewBtn');
            const modalCloseButtons = document.querySelectorAll('.modal-close');
            
            // Form elements
            const announcementForm = document.getElementById('announcementForm');
            const contentId = document.getElementById('contentId');
            const announcementTitle = document.getElementById('announcementTitle');
            const announcementContent = document.getElementById('announcementContent');
            const announcementImage = document.getElementById('announcementImage');
            const fileName = document.getElementById('fileName');
            const currentImageContainer = document.getElementById('currentImageContainer');
            const currentImage = document.getElementById('currentImage');
            const announcementPublish = document.getElementById('announcementPublish');
            
            // View modal elements
            const viewTitle = document.getElementById('viewTitle');
            const viewContent = document.getElementById('viewContent');
            const viewImageLabel = document.getElementById('viewImageLabel');
            const viewImageContainer = document.getElementById('viewImageContainer');
            const viewImage = document.getElementById('viewImage');
            const viewAuthor = document.getElementById('viewAuthor');
            const viewCreatedDate = document.getElementById('viewCreatedDate');
            const viewPublishedDate = document.getElementById('viewPublishedDate');
            const viewStatus = document.getElementById('viewStatus');
            
            // Open create modal
            createBtn.addEventListener('click', function() {
                modalTitle.textContent = 'Create New Announcement';
                announcementForm.reset();
                contentId.value = '';
                currentImageContainer.style.display = 'none';
                fileName.textContent = 'No file chosen';
                announcementModal.style.display = 'flex';
            });
            
            // Close modals
            cancelBtn.addEventListener('click', closeModals);
            closeViewBtn.addEventListener('click', closeModals);
            modalCloseButtons.forEach(btn => {
                btn.addEventListener('click', closeModals);
            });
            
            // Close modal when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal-overlay')) {
                    closeModals();
                }
            });
            
            // File input change handler
            announcementImage.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileName.textContent = this.files[0].name;
                } else {
                    fileName.textContent = 'No file chosen';
                }
            });
            
            // View buttons
            document.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const announcementId = this.getAttribute('data-id');
                    
                    fetch(`get_announcement.php?id=${announcementId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert('Error: ' + data.error);
                                return;
                            }
                            viewTitle.textContent = data.title;
                            viewContent.textContent = data.content;
                            viewAuthor.textContent = data.created_by_name;
                            viewCreatedDate.textContent = new Date(data.created_at).toLocaleString();
                            viewPublishedDate.textContent = data.published_at ? new Date(data.published_at).toLocaleString() : 'Not published';
                            
                            viewStatus.textContent = data.is_published ? 'Published' : 'Draft';
                            viewStatus.className = 'status-badge ' + (data.is_published ? 'published' : 'draft');
                            
                            if (data.image_filename) {
                                viewImageLabel.style.display = 'block';
                                viewImageContainer.style.display = 'block';
                                viewImage.src = data.image_path;
                            } else {
                                viewImageLabel.style.display = 'none';
                                viewImageContainer.style.display = 'none';
                            }
                            
                            viewModal.style.display = 'flex';
                        })
                        .catch(error => {
                            console.error('Error fetching announcement:', error);
                            alert('Error loading announcement details');
                        });
                });
            });
            
            // Edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const announcementId = this.getAttribute('data-id');
                    
                    fetch(`get_announcement.php?id=${announcementId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert('Error: ' + data.error);
                                return;
                            }
                            modalTitle.textContent = 'Edit Announcement';
                            announcementTitle.value = data.title;
                            announcementContent.value = data.content;
                            contentId.value = data.announcement_id;
                            announcementPublish.checked = data.is_published;
                            
                            if (data.image_filename) {
                                currentImageContainer.style.display = 'block';
                                currentImage.src = data.image_path;
                            } else {
                                currentImageContainer.style.display = 'none';
                            }
                            
                            fileName.textContent = 'No file chosen';
                            announcementModal.style.display = 'flex';
                        })
                        .catch(error => {
                            console.error('Error fetching announcement:', error);
                            alert('Error loading announcement details');
                        });
                });
            });
            
            // Delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const announcementId = this.getAttribute('data-id');
                    
                    if (confirm('Are you sure you want to delete this announcement?')) {
                        fetch(`delete_announcement.php?id=${announcementId}`, {
                            method: 'DELETE'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Error deleting announcement: ' + data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting announcement:', error);
                            alert('Error deleting announcement');
                        });
                    }
                });
            });
            
            function closeModals() {
                announcementModal.style.display = 'none';
                viewModal.style.display = 'none';
            }
            
            // Auto-hide notifications after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.notification').forEach(notification => {
                    notification.style.display = 'none';
                });
            }, 5000);
        });
    </script>
</body>
</html>