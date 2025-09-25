<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TT Dashboard - Document Management</title>
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
            overflow: hidden;
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
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeIn 0.8s ease-out;
            flex-shrink: 0;
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

        /* Content Sections */
        .content-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            animation: fadeIn 0.8s ease-out;
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            flex-shrink: 0;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
        }

        /* Document Upload Styles */
        .upload-area {
            border: 2px dashed #3498db;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
            background: rgba(52, 152, 219, 0.05);
            transition: var(--transition);
            flex-shrink: 0;
        }

        .upload-area:hover {
            background: rgba(52, 152, 219, 0.1);
        }

        .upload-icon {
            font-size: 48px;
            color: #3498db;
            margin-bottom: 15px;
        }

        .upload-text {
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .file-input {
            display: none;
        }

        .upload-btn {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .upload-btn:hover {
            background: var(--primary-dark);
        }

        .file-list {
            margin-top: 20px;
            flex: 1;
            overflow: hidden;
            max-height: 100%;
        }

        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .file-item:hover {
            background-color: #f8f9fa;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        .file-icon {
            color: #7f8c8d;
            font-size: 20px;
            flex-shrink: 0;
        }

        .file-details {
            min-width: 0;
        }

        .file-name {
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-meta {
            color: #7f8c8d;
            font-size: 12px;
            display: flex;
            gap: 12px;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .file-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .file-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        .file-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #7f8c8d;
            transition: var(--transition);
            padding: 5px;
        }

        .file-action-btn:hover {
            color: var(--primary-color);
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
            max-width: 600px;
            box-shadow: var(--card-shadow);
            animation: modalFadeIn 0.3s ease-out;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
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
            flex: 1;
            overflow: hidden;
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
            position: relative;
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
            resize: none;
            overflow: hidden;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M8 12L2 6h12L8 12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
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

        /* Authentication Error Styles */
        .auth-error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #c0392b;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .auth-error i {
            font-size: 24px;
        }
        
        .auth-error-content {
            flex: 1;
        }
        
        .auth-error-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        /* Responsive Design */
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
                overflow: hidden;
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
                padding: 20px;
                overflow: hidden;
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
            
            .file-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .file-actions {
                align-self: flex-end;
            }
            
            .modal {
                width: 95%;
                margin: 20px;
                max-height: 85vh;
            }
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

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
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
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .employee-view {
            display: none;
        }

        .admin-view {
            display: none;
        }

        .filter-options {
            display: flex;
            gap: 10px;
        }

        /* Hide scrollbars completely */
        *::-webkit-scrollbar {
            display: none;
        }

        * {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
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
                <div class="user-avatar" id="userAvatar">A</div>
                <div class="user-details">
                    <div class="user-name" id="userName">Administrator</div>
                    <div class="user-role" id="userRole">admin</div>
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
                    <a href="news.php" class="nav-link">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li class="nav-item admin-view">
                    <a href="content.php" class="nav-link active">
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
                <h1 class="page-title">Document Management</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" id="uploadDocumentBtn">
                        <i class="fas fa-upload"></i>
                        <span>Upload Document</span>
                    </button>
                    <button class="btn btn-logout" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
            
            <!-- Authentication Error Message (initially hidden) -->
            <div class="auth-error" id="authError" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="auth-error-content">
                    <h3>Authentication Required</h3>
                    <p>Your session has expired or you're not properly authenticated. Please log in again to continue.</p>
                </div>
                <button class="auth-error-btn" id="loginRedirectBtn">Go to Login</button>
            </div>
            
            <!-- Document Upload Section -->
            <div class="content-section admin-view">
                <div class="section-header">
                    <h2 class="section-title">Company Documents</h2>
                    <div class="filter-options">
                        <select id="documentFilter" class="form-control" style="width: auto; display: inline-block;">
                            <option value="all">All Documents</option>
                            <option value="policy">Policies</option>
                            <option value="handbook">Handbooks</option>
                            <option value="template">Templates</option>
                            <option value="form">Forms</option>
                            <option value="guide">Guides</option>
                        </select>
                    </div>
                </div>
                
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <p class="upload-text">Drag and drop files here or click to upload</p>
                    <input type="file" id="fileInput" class="file-input" multiple>
                    <button class="upload-btn" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-file-upload"></i> Select Files
                    </button>
                </div>
                
                <div class="file-list" id="fileList">
                    <!-- Documents will be listed here -->
                    <div class="empty-state" id="emptyState">
                        <i class="fas fa-folder-open"></i>
                        <p>No documents uploaded yet</p>
                        <p class="instruction">Use the upload button above to add your first document</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Upload Document Modal -->
    <div class="modal-overlay" id="uploadModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Upload Document</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="documentTitle">Document Title</label>
                        <input type="text" class="form-control" id="documentTitle" name="title" placeholder="Enter document title" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="documentDescription">Description</label>
                        <textarea class="form-control" id="documentDescription" name="description" placeholder="Enter document description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="documentCategory">Category</label>
                        <select class="form-control" id="documentCategory" name="category" required>
                            <option value="">Select Category</option>
                            <option value="policy">Policy</option>
                            <option value="handbook">Handbook</option>
                            <option value="template">Template</option>
                            <option value="form">Form</option>
                            <option value="guide">Guide</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="documentFile">File</label>
                        <input type="file" class="form-control" id="documentFile" name="file" required>
                    </div>
                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" id="documentNotify" name="notify">
                            Publish immediately
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" id="closeUploadModal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadDocument">
                        <i class="fas fa-upload"></i>
                        <span>Upload</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // DOM elements
        const uploadModal = document.getElementById('uploadModal');
        const uploadDocumentBtn = document.getElementById('uploadDocumentBtn');
        const uploadForm = document.getElementById('uploadForm');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const uploadArea = document.getElementById('uploadArea');
        const emptyState = document.getElementById('emptyState');
        const documentFilter = document.getElementById('documentFilter');
        const uploadButton = document.getElementById('uploadDocument');
        const authError = document.getElementById('authError');
        const loginRedirectBtn = document.getElementById('loginRedirectBtn');

        // Check if user is logged in
        document.addEventListener('DOMContentLoaded', function() {
            const currentUser = localStorage.getItem('currentUser');
            
            if (!currentUser) {
                showAuthError();
                return;
            }
            
            // Parse user data
            const userData = JSON.parse(currentUser);
            
            // Set user info
            document.getElementById('userName').textContent = userData.name;
            document.getElementById('userRole').textContent = userData.role;
            
            // Set profile picture or initial
            const nameInitial = userData.name.charAt(0).toUpperCase();
            const userAvatar = document.getElementById('userAvatar');
            
            if (userData.profile_picture) {
                loadProfilePicture(userData.profile_picture, userAvatar, nameInitial);
            } else {
                userAvatar.textContent = nameInitial;
            }
            
            // Show/hide elements based on user role
            const adminElements = document.querySelectorAll('.admin-view');
            const employeeElements = document.querySelectorAll('.employee-view');
            
            if (userData.role === 'admin') {
                adminElements.forEach(el => el.style.display = 'block');
                employeeElements.forEach(el => el.style.display = 'none');
                
                // Load documents from server
                loadDocuments();
            } else {
                adminElements.forEach(el => el.style.display = 'none');
                employeeElements.forEach(el => el.style.display = 'block');
            }
            
            // Logout functionality
            document.getElementById('logoutBtn').addEventListener('click', function() {
                localStorage.removeItem('currentUser');
                window.location.href = 'login.php';
            });
            
            // Login redirect functionality
            loginRedirectBtn.addEventListener('click', function() {
                localStorage.removeItem('currentUser');
                window.location.href = 'login.php';
            });
        });

        function showAuthError() {
            authError.style.display = 'flex';
            document.querySelectorAll('.admin-view, .employee-view').forEach(el => {
                el.style.display = 'none';
            });
        }

        // Profile Picture Functions
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
                // Fallback to initial
                avatarElement.style.backgroundImage = '';
                avatarElement.textContent = nameInitial;
            };
            img.src = imagePath;
        }

        // Open upload document modal
        uploadDocumentBtn.addEventListener('click', function() {
            const currentUser = localStorage.getItem('currentUser');
            if (!currentUser) {
                showAuthError();
                return;
            }
            uploadModal.style.display = 'flex';
        });

        // Close modals
        document.querySelectorAll('.modal-close, #closeUploadModal').forEach(btn => {
            btn.addEventListener('click', function() {
                uploadModal.style.display = 'none';
            });
        });

        // Handle upload form submission
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentUser = localStorage.getItem('currentUser');
            if (!currentUser) {
                showAuthError();
                return;
            }
            
            const title = document.getElementById('documentTitle').value;
            const description = document.getElementById('documentDescription').value;
            const category = document.getElementById('documentCategory').value;
            const file = document.getElementById('documentFile').files[0];
            const published = document.getElementById('documentNotify').checked ? 1 : 0;
            
            if (file) {
                // Show loading state
                uploadButton.innerHTML = '<div class="loading"></div> Uploading...';
                uploadButton.disabled = true;
                
                // Create form data
                const formData = new FormData();
                formData.append('title', title);
                formData.append('description', description);
                formData.append('category', category);
                formData.append('file', file);
                formData.append('published', published);
                formData.append('action', 'upload_document');
                
                // Send to server with authentication error handling
                fetch('content_ajax.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include' // Include cookies for session authentication
                })
                .then(response => {
                    if (response.status === 401 || response.status === 403) {
                        throw new Error('Not authenticated');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showNotification('Document uploaded successfully!', 'success');
                        
                        // Close the modal and reset form
                        uploadModal.style.display = 'none';
                        uploadForm.reset();
                        
                        // Reload documents
                        loadDocuments();
                    } else {
                        showNotification('Error: ' + data.message, 'error');
                    }
                    
                    // Reset button
                    uploadButton.innerHTML = '<i class="fas fa-upload"></i> Upload';
                    uploadButton.disabled = false;
                })
                .catch(error => {
                    if (error.message.includes('Not authenticated')) {
                        showNotification('Your session has expired. Please log in again.', 'error');
                        setTimeout(() => {
                            localStorage.removeItem('currentUser');
                            window.location.href = 'login.php';
                        }, 2000);
                    } else {
                        showNotification('Error uploading document: ' + error.message, 'error');
                    }
                    // Reset button
                    uploadButton.innerHTML = '<i class="fas fa-upload"></i> Upload';
                    uploadButton.disabled = false;
                });
            }
        });

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#2ecc71';
            this.style.backgroundColor = 'rgba(46, 204, 113, 0.1)';
        });

        uploadArea.addEventListener('dragleave', function() {
            this.style.borderColor = '#3498db';
            this.style.backgroundColor = 'rgba(52, 152, 219, 0.05)';
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#3498db';
            this.style.backgroundColor = 'rgba(52, 152, 219, 0.05)';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                // Auto-open the modal with the first file selected
                uploadModal.style.display = 'flex';
                document.getElementById('documentFile').files = files;
                document.getElementById('documentTitle').value = files[0].name.split('.')[0];
            }
        });

        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                // Auto-open the modal with the file selected
                uploadModal.style.display = 'flex';
                document.getElementById('documentTitle').value = this.files[0].name.split('.')[0];
            }
        });

        // Filter documents by category
        documentFilter.addEventListener('change', function() {
            const filterValue = this.value;
            const items = fileList.querySelectorAll('.file-item');
            
            items.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        function loadDocuments() {
            // Show loading state
            fileList.innerHTML = '<div class="empty-state"><div class="loading"></div><p>Loading documents...</p></div>';
            
            // Fetch documents from server with authentication error handling
            fetch('content_ajax.php?action=get_documents', {
                credentials: 'include' // Include cookies for session authentication
            })
            .then(response => {
                if (response.status === 401 || response.status === 403) {
                    throw new Error('Not authenticated');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayDocuments(data.documents);
                } else {
                    showNotification('Error loading documents: ' + data.message, 'error');
                    fileList.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Error loading documents</p></div>';
                }
            })
            .catch(error => {
                if (error.message.includes('Not authenticated')) {
                    showNotification('Your session has expired. Please log in again.', 'error');
                    setTimeout(() => {
                        localStorage.removeItem('currentUser');
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showNotification('Error loading documents: ' + error.message, 'error');
                    fileList.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Error loading documents</p></div>';
                }
            });
        }

        function displayDocuments(documents) {
            if (documents.length === 0) {
                fileList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p>No documents uploaded yet</p>
                        <p class="instruction">Use the upload button above to add your first document</p>
                    </div>
                `;
                return;
            }
            
            // Clear empty state
            emptyState.style.display = 'none';
            
            // Generate documents list (limit to fit viewport)
            fileList.innerHTML = '';
            
            // Only show first 5 documents to prevent overflow
            const documentsToShow = documents.slice(0, 5);
            
            documentsToShow.forEach(doc => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.setAttribute('data-category', doc.category);
                fileItem.setAttribute('data-id', doc.content_id);
                
                const fileExtension = doc.file_name ? doc.file_name.split('.').pop().toLowerCase() : 'file';
                let fileIcon = 'fa-file';
                
                if (['pdf'].includes(fileExtension)) {
                    fileIcon = 'fa-file-pdf';
                } else if (['doc', 'docx'].includes(fileExtension)) {
                    fileIcon = 'fa-file-word';
                } else if (['xls', 'xlsx'].includes(fileExtension)) {
                    fileIcon = 'fa-file-excel';
                } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                    fileIcon = 'fa-file-image';
                } else if (['zip', 'rar'].includes(fileExtension)) {
                    fileIcon = 'fa-file-archive';
                }
                
                // Format file size
                const fileSize = formatFileSize(doc.file_size);
                
                // Get category badge class
                const categoryClass = `category-${doc.category}`;
                
                // Format date
                const uploadDate = new Date(doc.created_date).toLocaleDateString();
                
                fileItem.innerHTML = `
                    <div class="file-info">
                        <i class="fas ${fileIcon} file-icon"></i>
                        <div class="file-details">
                            <div class="file-name">${doc.title}</div>
                            <div class="file-meta">
                                <span>${fileSize}</span>
                                <span><i class="fas fa-calendar-alt"></i> ${uploadDate}</span>
                                <span class="category-badge ${categoryClass}">${doc.category}</span>
                                ${doc.published ? '<span><i class="fas fa-check-circle"></i> Published</span>' : '<span><i class="fas fa-clock"></i> Draft</span>'}
                            </div>
                        </div>
                    </div>
                    <div class="file-actions">
                        <button class="file-action-btn" title="Download" onclick="downloadFile('${doc.content_id}')">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="file-action-btn" title="Delete" onclick="deleteFile('${doc.content_id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                
                fileList.appendChild(fileItem);
            });
            
            // If there are more documents, show a message
            if (documents.length > 5) {
                const moreItems = document.createElement('div');
                moreItems.className = 'file-item';
                moreItems.innerHTML = `
                    <div class="file-info">
                        <div class="file-details">
                            <div class="file-name">+${documents.length - 5} more documents</div>
                            <div class="file-meta">
                                <span><i class="fas fa-info-circle"></i> Showing first 5 documents</span>
                            </div>
                        </div>
                    </div>
                `;
                fileList.appendChild(moreItems);
            }
        }

        function formatFileSize(bytes) {
            if (!bytes) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function downloadFile(id) {
            // In a real application, this would download the actual file
            window.open(`download.php?id=${id}`, '_blank');
        }

        function deleteFile(id) {
            if (confirm('Are you sure you want to delete this document?')) {
                fetch('content_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_document&id=${id}`,
                    credentials: 'include' // Include cookies for session authentication
                })
                .then(response => {
                    if (response.status === 401 || response.status === 403) {
                        throw new Error('Not authenticated');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showNotification('Document deleted successfully', 'success');
                        // Remove from UI
                        const fileItem = document.querySelector(`.file-item[data-id="${id}"]`);
                        if (fileItem) {
                            fileItem.remove();
                        }
                        // Reload documents if empty
                        if (fileList.querySelectorAll('.file-item').length === 0) {
                            loadDocuments();
                        }
                    } else {
                        showNotification('Error deleting document: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    if (error.message.includes('Not authenticated')) {
                        showNotification('Your session has expired. Please log in again.', 'error');
                        setTimeout(() => {
                            localStorage.removeItem('currentUser');
                            window.location.href = 'login.php';
                        }, 2000);
                    } else {
                        showNotification('Error deleting document: ' + error.message, 'error');
                    }
                });
            }
        }

        function showNotification(message, type) {
            // Remove any existing notifications
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                uploadModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>