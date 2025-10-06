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

// Function to format file size
function formatFileSize($bytes) {
    if (!$bytes || $bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round(($bytes / pow($k, $i)), 2) . ' ' . $sizes[$i];
}

// Fetch published content from database
$sql = "SELECT c.*, u.name as created_by_name 
        FROM content c
        LEFT JOIN users u ON c.created_by = u.user_id
        WHERE c.published = 1
        ORDER BY c.created_date DESC";
$result = $conn->query($sql);
if ($result) {
    $content = $result->fetch_all(MYSQLI_ASSOC);
    error_log("Content fetched: " . print_r($content, true));
} else {
    $error_message = 'Failed to fetch content: ' . $conn->error;
    error_log($error_message);
    $content = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TT Dashboard - Company Content</title>
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
            overflow-y: auto;
            flex-shrink: 0;
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
            padding: 20px;
            overflow-y: auto;
            height: 100vh;
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
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
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

        .btn-download {
            background: var(--accent-color);
            color: white;
            padding: 8px 15px;
            font-size: 14px;
        }

        .btn-download:hover {
            background: #27ae60;
            transform: translateY(-2px);
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

        .filter-options {
            display: flex;
            gap: 10px;
        }

        .form-control {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            max-height: none;
            overflow-y: visible;
        }

        .content-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            cursor: pointer;
            border-left: 4px solid var(--primary-color);
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .file-icon {
            font-size: 24px;
            color: var(--primary-color);
            margin-left: 10px;
        }

        .card-meta {
            margin-bottom: 15px;
            font-size: 12px;
            color: #666;
        }

        .card-meta span {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
        }

        .card-content {
            font-size: 14px;
            color: var(--dark-text);
            margin-bottom: 15px;
            line-height: 1.6;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .category-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .category-policy {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .category-handbook {
            background-color: rgba(46, 204, 113, 0.1);
            color: #27ae60;
        }

        .category-template {
            background-color: rgba(155, 89, 182, 0.1);
            color: #8e44ad;
        }

        .category-form {
            background-color: rgba(241, 196, 15, 0.1);
            color: #f39c12;
        }

        .category-guide {
            background-color: rgba(230, 126, 34, 0.1);
            color: #d35400;
        }

        .category-other {
            background-color: rgba(149, 165, 166, 0.1);
            color: #7f8c8d;
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
            display: none;
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

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 15px;
            color: #bdc3c7;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .empty-state .instruction {
            font-size: 14px;
            color: #95a5a6;
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
            }

            .content-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .content-grid {
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
                    <a href="employee_news.php" class="nav-link">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="employee_content.php" class="nav-link active">
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

        <main class="main-content">
            <div class="header">
                <h1 class="page-title">Company Content</h1>
                <div class="header-actions">
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Available Documents</h2>
                    <div class="filter-options">
                        <select id="contentFilter" class="form-control">
                            <option value="all">All Categories</option>
                            <option value="policy">Policies</option>
                            <option value="handbook">Handbooks</option>
                            <option value="template">Templates</option>
                            <option value="form">Forms</option>
                            <option value="guide">Guides</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="content-grid">
                    <?php if (!empty($content)): ?>
                        <?php foreach ($content as $item): ?>
                            <?php
                                $fileExtension = $item['file_name'] ? pathinfo($item['file_name'], PATHINFO_EXTENSION) : '';
                                $fileIcon = 'fa-file';
                                
                                if (in_array(strtolower($fileExtension), ['pdf'])) {
                                    $fileIcon = 'fa-file-pdf';
                                } elseif (in_array(strtolower($fileExtension), ['doc', 'docx'])) {
                                    $fileIcon = 'fa-file-word';
                                } elseif (in_array(strtolower($fileExtension), ['xls', 'xlsx'])) {
                                    $fileIcon = 'fa-file-excel';
                                } elseif (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])) {
                                    $fileIcon = 'fa-file-image';
                                } elseif (in_array(strtolower($fileExtension), ['zip', 'rar'])) {
                                    $fileIcon = 'fa-file-archive';
                                } elseif (in_array(strtolower($fileExtension), ['txt'])) {
                                    $fileIcon = 'fa-file-alt';
                                } elseif (in_array(strtolower($fileExtension), ['php', 'html', 'css', 'js'])) {
                                    $fileIcon = 'fa-file-code';
                                }
                                
                                $categoryClass = 'category-' . ($item['type'] ?: 'other');
                                $fileSize = $item['file_size'] ? formatFileSize($item['file_size']) : 'N/A';
                            ?>
                            <div class="content-card" data-id="<?php echo htmlspecialchars($item['content_id']); ?>" data-category="<?php echo htmlspecialchars($item['type'] ?: 'other'); ?>">
                                <div class="card-header">
                                    <div>
                                        <h3 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <span class="category-badge <?php echo $categoryClass; ?>"><?php echo htmlspecialchars($item['type'] ?: 'other'); ?></span>
                                    </div>
                                    <i class="fas <?php echo $fileIcon; ?> file-icon"></i>
                                </div>
                                <div class="card-meta">
                                    <?php if ($item['file_size']): ?>
                                        <span><i class="fas fa-file"></i> <?php echo $fileSize; ?></span>
                                    <?php endif; ?>
                                    <span><i class="fas fa-calendar-alt"></i> <?php echo date('M j, Y', strtotime($item['created_date'])); ?></span>
                                    <?php if ($item['created_by_name']): ?>
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($item['created_by_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-content">
                                    <?php echo htmlspecialchars($item['body'] ?: 'No description available'); ?>
                                </div>
                                <div class="card-footer">
                                    <span class="card-meta">
                                        <?php if ($item['file_name']): ?>
                                            <span><i class="fas fa-paperclip"></i> <?php echo htmlspecialchars($item['file_name']); ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <?php if ($item['file_path']): ?>
                                        <button class="btn btn-download" data-id="<?php echo htmlspecialchars($item['content_id']); ?>">
                                            <i class="fas fa-download"></i>
                                            <span>Download</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <p>No content available</p>
                            <p class="instruction">Check back later for new company documents and resources</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- View Content Modal -->
    <div class="modal-overlay" id="viewContentModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="viewTitle"></h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="view-modal-content">
                    <div class="view-modal-label">Description</div>
                    <div class="view-modal-value" id="viewDescription"></div>
                    <div class="view-modal-label">Category</div>
                    <div class="view-modal-value" id="viewCategory"></div>
                    <div class="view-modal-label">File Name</div>
                    <div class="view-modal-value" id="viewFileName"></div>
                    <div class="view-modal-label">File Size</div>
                    <div class="view-modal-value" id="viewFileSize"></div>
                    <div class="view-modal-label">Created By</div>
                    <div class="view-modal-value" id="viewCreatedBy"></div>
                    <div class="view-modal-label">Date Created</div>
                    <div class="view-modal-value" id="viewCreatedDate"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-download" id="downloadContentBtn" style="display: none;">
                    <i class="fas fa-download"></i>
                    Download
                </button>
                <button class="btn" id="closeViewBtn">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userData = <?php echo json_encode($userData); ?>;
            const nameInitial = userData.name.charAt(0).toUpperCase();
            const userAvatar = document.getElementById('userAvatar');
            const baseUrl = '<?php echo BASE_URL; ?>';
            const contentData = <?php echo json_encode($content); ?>;

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

            // Content view and download functionality
            const viewModal = document.getElementById('viewContentModal');
            const closeViewBtn = document.getElementById('closeViewBtn');
            const modalCloseButtons = document.querySelectorAll('.modal-close');
            const downloadContentBtn = document.getElementById('downloadContentBtn');
            const contentFilter = document.getElementById('contentFilter');
            
            // Modal elements
            const viewTitle = document.getElementById('viewTitle');
            const viewDescription = document.getElementById('viewDescription');
            const viewCategory = document.getElementById('viewCategory');
            const viewFileName = document.getElementById('viewFileName');
            const viewFileSize = document.getElementById('viewFileSize');
            const viewCreatedBy = document.getElementById('viewCreatedBy');
            const viewCreatedDate = document.getElementById('viewCreatedDate');

            function showContentDetails(contentId) {
                const content = contentData.find(c => c.content_id === contentId);
                if (content) {
                    displayContentDetails(content);
                } else {
                    showNotification('error', 'Content not found');
                }
            }

            function displayContentDetails(data) {
                viewTitle.textContent = data.title;
                viewDescription.textContent = data.body || 'No description available';
                viewCategory.textContent = (data.type || 'other').charAt(0).toUpperCase() + (data.type || 'other').slice(1);
                viewFileName.textContent = data.file_name || 'N/A';
                viewFileSize.textContent = data.file_size ? formatFileSize(data.file_size) : 'N/A';
                viewCreatedBy.textContent = data.created_by_name || 'Unknown';
                viewCreatedDate.textContent = new Date(data.created_date).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                if (data.file_path) {
                    downloadContentBtn.style.display = 'inline-flex';
                    downloadContentBtn.setAttribute('data-id', data.content_id);
                } else {
                    downloadContentBtn.style.display = 'none';
                }
                
                viewModal.style.display = 'flex';
            }

            function formatFileSize(bytes) {
                if (!bytes || bytes == 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
            }

            function downloadContent(contentId) {
                const content = contentData.find(c => c.content_id === contentId);
                if (!content || !content.file_path) {
                    showNotification('error', 'File not available for download');
                    return;
                }
                
                // Create download link and trigger download
                const link = document.createElement('a');
                link.href = content.file_path;
                link.download = content.file_name || 'download';
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showNotification('success', 'Download started');
            }

            function showNotification(type, message) {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span>${message}</span>
                `;
                document.body.appendChild(notification);
                notification.style.display = 'flex';
                setTimeout(() => {
                    notification.style.display = 'none';
                    notification.remove();
                }, 3000);
            }

            // Event listeners
            
            // Handle content card click (view details)
            document.querySelectorAll('.content-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't trigger if download button was clicked
                    if (e.target.closest('.btn-download')) {
                        return;
                    }
                    const contentId = this.getAttribute('data-id');
                    showContentDetails(contentId);
                });
            });

            // Handle download button clicks
            document.querySelectorAll('.btn-download').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent card click
                    const contentId = this.getAttribute('data-id');
                    downloadContent(contentId);
                });
            });

            // Handle modal download button
            downloadContentBtn.addEventListener('click', function() {
                const contentId = this.getAttribute('data-id');
                downloadContent(contentId);
            });

            // Close modals
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

            function closeModals() {
                viewModal.style.display = 'none';
            }

            // Filter functionality
            contentFilter.addEventListener('change', function() {
                const filterValue = this.value;
                const cards = document.querySelectorAll('.content-card');
                
                cards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    if (filterValue === 'all' || category === filterValue) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Check if any cards are visible
                const visibleCards = document.querySelectorAll('.content-card[style="display: block"], .content-card:not([style*="display: none"])');
                const emptyState = document.querySelector('.empty-state');
                
                if (visibleCards.length === 0 && !emptyState) {
                    // Create temporary empty state
                    const tempEmpty = document.createElement('div');
                    tempEmpty.className = 'empty-state temp-empty';
                    tempEmpty.innerHTML = `
                        <i class="fas fa-search"></i>
                        <p>No content found for selected category</p>
                        <p class="instruction">Try selecting a different category</p>
                    `;
                    document.querySelector('.content-grid').appendChild(tempEmpty);
                } else if (visibleCards.length > 0) {
                    // Remove temporary empty state
                    const tempEmpty = document.querySelector('.temp-empty');
                    if (tempEmpty) {
                        tempEmpty.remove();
                    }
                }
            });

            // Logout functionality
            document.querySelector('.btn-logout').addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'logout.php';
                }
            });
        });
    </script>
</body>
</html>