<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TT Dashboard - Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Same CSS as provided */
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
            overflow: hidden;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeIn 0.8s ease-out;
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

        /* Profile Section Styles - Two Column Layout */
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin-bottom: 30px;
            align-items: start;
            min-height: 600px; /* Added minimum height to ensure cards have space to align */
        }

        /* Left Side - Edit Profile Card */
        .profile-edit-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
            display: flex;
            flex-direction: column;
            height: fit-content;
        }

        .profile-edit-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .profile-edit-header {
            margin-bottom: 30px;
        }

        .profile-edit-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-edit-subtitle {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 500;
        }

        .edit-form-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 25px;
            flex-grow: 1;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-text);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 15px 18px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 16px;
            background: #f9f9f9;
            transition: var(--transition);
            font-family: inherit;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.15);
            outline: none;
            transform: translateY(-1px);
        }

        .form-control:disabled {
            background: #f5f5f5;
            color: #999;
            cursor: not-allowed;
        }

        .error-message {
            color: var(--danger-color);
            font-size: 13px;
            margin-top: 8px;
            display: none;
            background: rgba(231, 76, 60, 0.1);
            padding: 8px 12px;
            border-radius: 6px;
            border-left: 3px solid var(--danger-color);
        }

        .edit-btn-group {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            margin-top: auto;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }

        .btn-edit {
            background: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        /* Right Side - Profile Picture Card */
        .profile-picture-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out 0.2s both;
            text-align: center;
            height: 100%; /* Changed from height: fit-content */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .profile-picture-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
        }

        .profile-avatar-section {
            margin-bottom: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 0; /* Added to allow proper flex shrinking */
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            font-weight: bold;
            color: white;
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
            position: relative;
            border: 5px solid white;
            transition: var(--transition);
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(52, 152, 219, 0.4);
        }

        .profile-avatar::after {
            content: '\f030';
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 35px;
            height: 35px;
            background: var(--accent-color);
            border-radius: 50%;
            border: 3px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        .profile-picture-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 10px;
        }

        .profile-picture-subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 25px;
            flex-grow: 1;
        }

        .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: auto;
            flex-shrink: 0; /* Added to prevent shrinking */
        }

        .btn-small {
            padding: 12px 20px;
            font-size: 14px;
            border-radius: 10px;
            width: 100%;
            justify-content: center;
        }

        .btn-danger {
            background: rgba(231, 76, 60, 0.8);
            color: white;
        }

        .btn-danger:hover {
            background: var(--danger-color);
            transform: translateY(-2px);
        }

        .btn-upload {
            background: var(--accent-color);
            color: white;
        }

        .btn-upload:hover {
            background: #27ae60;
            transform: translateY(-2px);
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
                overflow: hidden;
            }

            .profile-container {
                grid-template-columns: 1fr;
                gap: 20px;
                min-height: auto; /* Changed for mobile */
            }

            .profile-picture-card,
            .profile-edit-card {
                padding: 20px;
                height: auto; /* Changed for mobile */
            }

            .profile-avatar {
                width: 120px;
                height: 120px;
                font-size: 48px;
            }

            .profile-avatar::after {
                width: 25px;
                height: 25px;
                bottom: 5px;
                right: 5px;
                font-size: 12px;
            }

            .edit-form-grid {
                grid-template-columns: 1fr;
            }

            .edit-btn-group {
                flex-direction: column;
            }

            .btn-edit,
            .btn-secondary {
                width: 100%;
                justify-content: center;
            }

            .profile-actions {
                flex-direction: row;
                flex-wrap: wrap;
                margin-top: 15px;
            }

            .btn-small {
                flex: 1;
                min-width: 120px;
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
                justify-content: flex-end;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 40px;
            }

            .profile-avatar::after {
                width: 20px;
                height: 20px;
                bottom: 3px;
                right: 3px;
                font-size: 10px;
            }

            .profile-edit-card,
            .profile-picture-card {
                padding: 20px;
            }

            .form-control {
                padding: 12px 15px;
            }

            .form-label {
                font-size: 13px;
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

        .employee-view {
            display: block;
        }

        .admin-view {
            display: none;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            width: 0%;
            transition: width 0.3s ease;
        }

        #uploadStatus {
            margin-top: 5px;
            font-size: 12px;
            min-height: 16px;
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
                <div class="user-avatar" id="userAvatar">A</div>
                <div class="user-details">
                    <div class="user-name" id="userName">Employee</div>
                    <div class="user-role" id="userRole">employee</div>
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
                    <a href="profile.php" class="nav-link active">
                        <i class="fas fa-user-circle"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1 class="page-title">Profile Management</h1>
                <div class="header-actions">
                    <button class="btn btn-logout" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
            
            <!-- Profile Container -->
            <div class="profile-container">
                <!-- Left Side - Edit Profile -->
                <div class="profile-edit-card">
                    <div class="profile-edit-header">
                        <h2 class="profile-edit-title">
                            <i class="fas fa-user-edit"></i>
                            Edit Profile
                        </h2>
                        <p class="profile-edit-subtitle">Update your personal information</p>
                    </div>
                    
                    <form id="profileForm" class="edit-form-grid">
                        <div class="form-group">
                            <label class="form-label" for="profileName">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <input type="text" class="form-control" id="profileName" required minlength="2">
                            <div class="error-message" id="nameError"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="profileEmail">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="profileEmail" required>
                            <div class="error-message" id="emailError"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="profileDepartment">
                                <i class="fas fa-building"></i> Department
                            </label>
                            <input type="text" class="form-control" id="profileDepartment">
                            <div class="error-message" id="departmentError"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="profileRole">
                                <i class="fas fa-user-tag"></i> Role
                            </label>
                            <input type="text" class="form-control" id="profileRole" disabled>
                        </div>

                        <div class="edit-btn-group">
                            <button type="button" class="btn btn-secondary" id="cancelChanges">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-edit" id="saveProfile">
                                <i class="fas fa-save"></i>
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Side - Profile Picture -->
                <div class="profile-picture-card">
                    <div class="profile-avatar-section">
                        <div class="profile-avatar" id="profileAvatar">E</div>
                        <h3 class="profile-picture-title">Profile Picture</h3>
                        <p class="profile-picture-subtitle">Upload a professional photo to personalize your account</p>
                    </div>
                    
                    <!-- Hidden file input -->
                    <input type="file" id="profilePictureInput" accept="image/*" style="display: none;">
                    
                    <div class="profile-actions">
                        <button type="button" class="btn btn-small btn-upload" id="uploadPhotoBtn">
                            <i class="fas fa-camera"></i>
                            Upload Photo
                        </button>
                        <button type="button" class="btn btn-small btn-danger" id="removePhotoBtn" style="display: none;">
                            <i class="fas fa-trash"></i>
                            Remove Photo
                        </button>
                        <div id="uploadProgress" style="display: none; margin-top: 10px;">
                            <div class="progress-bar" style="width: 100%; height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden;">
                                <div class="progress-fill" style="height: 100%; background: var(--primary-color); width: 0%; transition: width 0.3s ease;"></div>
                            </div>
                            <small style="color: #666; font-size: 12px;">Uploading...</small>
                        </div>
                        <div id="uploadStatus" style="margin-top: 10px; font-size: 12px;"></div>
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
            
            // Set user info
            document.getElementById('userName').textContent = userData.name;
            document.getElementById('userRole').textContent = userData.role;
            document.getElementById('userAvatar').textContent = userData.name.charAt(0).toUpperCase();
            
            // Set profile info
            const nameInitial = userData.name.charAt(0).toUpperCase();
            document.getElementById('profileName').value = userData.name;
            document.getElementById('profileEmail').value = userData.email || 'employee@tt.com';
            document.getElementById('profileDepartment').value = userData.department || 'Operations';
            document.getElementById('profileRole').value = userData.role;
            document.getElementById('profileAvatar').textContent = nameInitial;
            
            // Load profile picture if exists
            if (userData.profile_picture) {
                loadProfilePicture(userData.profile_picture);
                document.getElementById('removePhotoBtn').style.display = 'block';
                document.getElementById('uploadPhotoBtn').innerHTML = '<i class="fas fa-sync-alt"></i> Change Photo';
            }
            
            // Logout functionality
            document.getElementById('logoutBtn').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.removeItem('currentUser');
                    window.location.href = 'login.php';
                }
            });

            // Profile picture upload functionality
            const uploadBtn = document.getElementById('uploadPhotoBtn');
            const fileInput = document.getElementById('profilePictureInput');
            const removeBtn = document.getElementById('removePhotoBtn');
            const uploadProgress = document.getElementById('uploadProgress');
            const progressFill = document.querySelector('.progress-fill');
            const uploadStatus = document.getElementById('uploadStatus');
            
            // Trigger file input when upload button is clicked
            uploadBtn.addEventListener('click', function() {
                fileInput.click();
            });
            
            // Handle file selection
            fileInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    // Validate file
                    if (!validateImageFile(file)) {
                        return;
                    }
                    
                    // Show preview and upload
                    showImagePreview(file);
                    uploadProfilePicture(file);
                }
            });
            
            // Handle file removal
            removeBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to remove your profile picture?')) {
                    removeProfilePicture();
                }
            });
            
            // Cancel changes functionality
            document.getElementById('cancelChanges').addEventListener('click', function() {
                document.getElementById('profileName').value = userData.name;
                document.getElementById('profileEmail').value = userData.email || 'employee@tt.com';
                document.getElementById('profileDepartment').value = userData.department || 'Operations';
                
                document.querySelectorAll('.error-message').forEach(el => {
                    el.style.display = 'none';
                });
            });

            // Form submission handling
            const form = document.getElementById('profileForm');
            const saveBtn = document.getElementById('saveProfile');
            let isSubmitting = false;

            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                
                if (isSubmitting) return;
                
                document.querySelectorAll('.error-message').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });

                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                saveBtn.disabled = true;
                isSubmitting = true;

                const name = document.getElementById('profileName').value.trim();
                const email = document.getElementById('profileEmail').value.trim();
                const department = document.getElementById('profileDepartment').value.trim();

                let hasError = false;

                if (!name || name.length < 2) {
                    showError('nameError', 'Full name must be at least 2 characters');
                    hasError = true;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email || !emailRegex.test(email)) {
                    showError('emailError', 'Please enter a valid email address');
                    hasError = true;
                }

                if (hasError) {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Profile';
                    saveBtn.disabled = false;
                    isSubmitting = false;
                    return;
                }

                const formData = new FormData();
                formData.append('user_id', userData.user_id);
                formData.append('name', name);
                formData.append('email', email);
                formData.append('department', department);

                try {
                    const response = await fetch('update_profile.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        const updatedUser = {
                            ...userData,
                            name: name,
                            email: email,
                            department: department
                        };
                        localStorage.setItem('currentUser', JSON.stringify(updatedUser));

                        document.getElementById('userName').textContent = name;
                        document.getElementById('userAvatar').textContent = name.charAt(0).toUpperCase();
                        document.getElementById('profileAvatar').textContent = name.charAt(0).toUpperCase();

                        alert('Profile updated successfully!');
                    } else {
                        showError('nameError', result.message || 'Failed to update profile');
                    }
                } catch (error) {
                    console.error('Profile update error:', error);
                    showError('nameError', 'Network error. Please check your connection and try again.');
                } finally {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Profile';
                    saveBtn.disabled = false;
                    isSubmitting = false;
                }
            });

            // Real-time form validation
            document.getElementById('profileName').addEventListener('blur', function() {
                const value = this.value.trim();
                const errorEl = document.getElementById('nameError');
                if (value.length < 2) {
                    errorEl.textContent = 'Full name must be at least 2 characters';
                    errorEl.style.display = 'block';
                } else {
                    errorEl.style.display = 'none';
                }
            });

            document.getElementById('profileEmail').addEventListener('blur', function() {
                const value = this.value.trim();
                const errorEl = document.getElementById('emailError');
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    errorEl.textContent = 'Please enter a valid email address';
                    errorEl.style.display = 'block';
                } else {
                    errorEl.style.display = 'none';
                }
            });

            // Helper function to show error messages
            function showError(errorId, message) {
                const errorEl = document.getElementById(errorId);
                errorEl.textContent = message;
                errorEl.style.display = 'block';
                errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Profile Picture Functions
            function validateImageFile(file) {
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                if (!allowedTypes.includes(file.type)) {
                    uploadStatus.textContent = 'Please select a valid image file (JPEG, PNG, GIF, WebP)';
                    uploadStatus.style.color = 'var(--danger-color)';
                    return false;
                }
                
                if (file.size > maxSize) {
                    uploadStatus.textContent = 'Image size must be less than 5MB';
                    uploadStatus.style.color = 'var(--danger-color)';
                    return false;
                }
                
                return true;
            }

            function showImagePreview(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatar = document.getElementById('profileAvatar');
                    avatar.style.backgroundImage = `url(${e.target.result})`;
                    avatar.style.backgroundSize = 'cover';
                    avatar.style.backgroundPosition = 'center';
                    avatar.textContent = '';
                };
                reader.readAsDataURL(file);
            }

            async function uploadProfilePicture(file) {
                const formData = new FormData();
                formData.append('user_id', userData.user_id);
                formData.append('profile_picture', file);
                
                try {
                    // Show upload progress
                    uploadProgress.style.display = 'block';
                    uploadStatus.textContent = 'Uploading...';
                    uploadStatus.style.color = 'var(--primary-color)';
                    
                    const response = await fetch('upload_profile_picture.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Update localStorage
                        const updatedUser = {
                            ...userData,
                            profile_picture: result.profile_picture_path
                        };
                        localStorage.setItem('currentUser', JSON.stringify(updatedUser));
                        
                        // Update sidebar avatar
                        document.getElementById('userAvatar').style.backgroundImage = `url(${result.profile_picture_path})`;
                        document.getElementById('userAvatar').style.backgroundSize = 'cover';
                        document.getElementById('userAvatar').style.backgroundPosition = 'center';
                        document.getElementById('userAvatar').textContent = '';
                        
                        // Update UI
                        uploadStatus.textContent = 'Upload successful!';
                        uploadStatus.style.color = 'var(--accent-color)';
                        removeBtn.style.display = 'block';
                        uploadBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Change Photo';
                        
                        setTimeout(() => {
                            uploadProgress.style.display = 'none';
                            uploadStatus.textContent = '';
                        }, 2000);
                        
                    } else {
                        throw new Error(result.message || 'Upload failed');
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    uploadStatus.textContent = error.message || 'Upload failed. Please try again.';
                    uploadStatus.style.color = 'var(--danger-color)';
                    
                    // Reset avatar on error
                    document.getElementById('profileAvatar').style.backgroundImage = '';
                    document.getElementById('profileAvatar').textContent = nameInitial;
                    
                    setTimeout(() => {
                        uploadProgress.style.display = 'none';
                        uploadStatus.textContent = '';
                    }, 3000);
                }
            }

            async function removeProfilePicture() {
                try {
                    const response = await fetch('remove_profile_picture.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ user_id: userData.user_id })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Update localStorage
                        const updatedUser = {
                            ...userData,
                            profile_picture: null
                        };
                        localStorage.setItem('currentUser', JSON.stringify(updatedUser));
                        
                        // Reset avatars
                        document.getElementById('profileAvatar').style.backgroundImage = '';
                        document.getElementById('profileAvatar').textContent = nameInitial;
                        document.getElementById('userAvatar').style.backgroundImage = '';
                        document.getElementById('userAvatar').textContent = nameInitial;
                        
                        // Update buttons
                        removeBtn.style.display = 'none';
                        uploadBtn.innerHTML = '<i class="fas fa-camera"></i> Upload Photo';
                        
                        alert('Profile picture removed successfully!');
                    } else {
                        alert('Failed to remove profile picture. Please try again.');
                    }
                } catch (error) {
                    console.error('Remove error:', error);
                    alert('Network error. Please try again.');
                }
            }

            function loadProfilePicture(imagePath) {
                const avatar = document.getElementById('profileAvatar');
                const userAvatar = document.getElementById('userAvatar');
                
                // Load image with error handling
                const img = new Image();
                img.onload = function() {
                    avatar.style.backgroundImage = `url(${imagePath})`;
                    avatar.style.backgroundSize = 'cover';
                    avatar.style.backgroundPosition = 'center';
                    avatar.textContent = '';
                    
                    userAvatar.style.backgroundImage = `url(${imagePath})`;
                    userAvatar.style.backgroundSize = 'cover';
                    userAvatar.style.backgroundPosition = 'center';
                    userAvatar.textContent = '';
                };
                img.onerror = function() {
                    // Fallback to initial
                    avatar.style.backgroundImage = '';
                    avatar.textContent = nameInitial;
                    userAvatar.style.backgroundImage = '';
                    userAvatar.textContent = nameInitial;
                };
                img.src = imagePath;
            }
        });
    </script>
</body>
</html>