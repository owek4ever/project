<?php
// Start output buffering
ob_start();
// Start session for notifications and user data
session_start();
?>
<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>
   TT Dashboard - User Management
  </title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
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
            --transition: all 0.3s ease;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
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
            height: 100%;
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
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
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

        .content-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            animation: fadeIn 0.8s ease-out;
            overflow: hidden;
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

        .table-container {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .table-responsive {
            flex: 1;
            overflow: hidden;
            border-radius: 15px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-text);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
            background: white;
            color: var(--dark-text);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .form-control[readonly] {
            background: rgba(255, 255, 255, 0.7);
            cursor: not-allowed;
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
                white-space: nowrap;
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
                white-space: nowrap;
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

            .data-table th,
            .data-table td {
                padding: 12px 10px;
                font-size: 14px;
                min-width: 120px;
            }

            .action-btn {
                padding: 6px 12px;
                font-size: 12px;
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
            
            .main-content {
                padding: 15px;
            }
            
            .content-section {
                padding: 20px;
            }

            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
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
            display: none;
        }

        .admin-view {
            display: none;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: var(--card-shadow);
            max-height: 90vh;
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #777;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .modal-body {
            overflow: hidden;
            height: calc(100% - 100px);
        }

        .modal-form {
            height: 100%;
            overflow: hidden;
        }
  </style>
 </head>
 <body>
  <?php
    require_once 'db.php';
    
    // Check if user is logged in and get user_id
    $current_user = $_SESSION['currentUser'] ?? [];
    if (is_string($current_user)) {
        $current_user = json_decode($current_user, true);
    }
    $user_id = $current_user['user_id'] ?? null;

    if (!$user_id) {
        $_SESSION['error_message'] = "User not authenticated. Please log in.";
        header("Location: login.php");
        exit();
    }

    // Verify user exists in the database
    $result = executeQuery($conn, "SELECT user_id FROM users WHERE user_id = ?", [$user_id]);
    if (!$result['success'] || !$result['result']->fetch_assoc()) {
        $_SESSION['error_message'] = "User not found in database.";
        header("Location: login.php");
        exit();
    }

    // Fetch users from database
    $result = executeQuery($conn, "SELECT user_id, name, email, role FROM users");
    $users = $result['success'] ? $result['result']->fetch_all(MYSQLI_ASSOC) : [];

    // Handle session messages
    $success_message = $_SESSION['success_message'] ?? '';
    $error_message = $_SESSION['error_message'] ?? '';
    
    // Clear session messages after displaying
    unset($_SESSION['success_message']);
    unset($_SESSION['error_message']);
    ?>
  <!-- Notification Messages -->
  <?php if ($success_message): ?>
  <div class="notification success">
   <i class="fas fa-check-circle">
   </i>
   <?php echo htmlspecialchars($success_message); ?>
  </div>
  <?php endif; ?>
  <?php if ($error_message): ?>
  <div class="notification error">
   <i class="fas fa-exclamation-circle">
   </i>
   <?php echo htmlspecialchars($error_message); ?>
  </div>
  <?php endif; ?>
  <div class="dashboard-container">
   <aside class="sidebar">
    <div class="logo-area">
     <img alt="Tunisie Telecom Logo" src="logo.png"/>
    </div>
    <div class="user-info">
     <div class="user-avatar" id="userAvatar">
      A
     </div>
     <div class="user-details">
      <div class="user-name" id="userName">
       Administrator
      </div>
      <div class="user-role" id="userRole">
       admin
      </div>
     </div>
    </div>
    <ul class="nav-menu">
     <li class="nav-item">
      <a class="nav-link" href="dashboard.php">
       <i class="fas fa-home">
       </i>
       <span>
        Dashboard
       </span>
      </a>
     </li>
     <li class="nav-header admin-view">
      Admin Tools
     </li>
     <li class="nav-item admin-view">
      <a class="nav-link" href="news.php">
       <i class="fas fa-bullhorn">
       </i>
       <span>
        Announcements
       </span>
      </a>
     </li>
     <li class="nav-item admin-view">
      <a class="nav-link" href="content.php">
       <i class="fas fa-file-alt">
       </i>
       <span>
        Content
       </span>
      </a>
     </li>
     <li class="nav-item admin-view">
      <a class="nav-link" href="coupons.php">
       <i class="fas fa-ticket-alt">
       </i>
       <span>
        Coupons
       </span>
      </a>
     </li>
     <li class="nav-item admin-view">
      <a class="nav-link active" href="users.php">
       <i class="fas fa-users">
       </i>
       <span>
        Users
       </span>
      </a>
     </li>
     <li class="nav-header employee-view">
      Employee Tools
     </li>
     <li class="nav-item employee-view">
      <a class="nav-link" href="available-coupons.php">
       <i class="fas fa-tags">
       </i>
       <span>
        Available Coupons
       </span>
      </a>
     </li>
     <li class="nav-item employee-view">
      <a class="nav-link" href="my-coupons.php">
       <i class="fas fa-ticket-alt">
       </i>
       <span>
        My Coupons
       </span>
      </a>
     </li>
     <li class="nav-header">
      Support
     </li>
     <li class="nav-item">
      <a class="nav-link" href="feedback.php">
       <i class="fas fa-comment-dots">
       </i>
       <span>
        Feedback
       </span>
      </a>
     </li>
     <li class="nav-item">
      <a class="nav-link" href="profile.php">
       <i class="fas fa-user-circle">
       </i>
       <span>
        Profile
       </span>
      </a>
     </li>
    </ul>
   </aside>
   <main class="main-content">
    <div class="header">
     <h1 class="page-title">
      User Management
     </h1>
     <div class="header-actions">
      <button class="btn btn-primary" id="addUserBtn">
       <i class="fas fa-plus">
       </i>
       <span>
        Add User
       </span>
      </button>
      <button class="btn btn-logout" id="logoutBtn">
       <i class="fas fa-sign-out-alt">
       </i>
       <span>
        Logout
       </span>
      </button>
     </div>
    </div>
    <div class="content-section admin-view">
     <div class="section-header">
      <h2 class="section-title">
       System Users
      </h2>
     </div>
     <div class="table-responsive">
      <table class="data-table">
       <thead>
        <tr>
         <th>Name</th>
         <th>Email</th>
         <th>Role</th>
         <th>Actions</th>
        </tr>
       </thead>
       <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
         <td><?php echo htmlspecialchars($user['name']); ?></td>
         <td><?php echo htmlspecialchars($user['email']); ?></td>
         <td><?php echo htmlspecialchars($user['role']); ?></td>
         <td>
          <button class="action-btn btn-view" data-id="<?php echo $user['user_id']; ?>">
           <i class="fas fa-eye"></i>
          </button>
          <button class="action-btn btn-edit" data-id="<?php echo $user['user_id']; ?>">
           <i class="fas fa-edit"></i>
          </button>
          <button class="action-btn btn-delete" data-id="<?php echo $user['user_id']; ?>">
           <i class="fas fa-trash"></i>
          </button>
         </td>
        </tr>
        <?php endforeach; ?>
       </tbody>
      </table>
     </div>
    </div>
   </main>
  </div>
  <div class="modal" id="userModal">
   <div class="modal-content">
    <div class="modal-header">
     <h2 class="modal-title" id="modalTitle">
      Add User
     </h2>
     <button class="modal-close" id="modalClose">
      ×
     </button>
    </div>
    <div class="modal-body">
     <form id="userForm" class="modal-form">
      <input id="userId" name="user_id" type="hidden"/>
      <div class="form-group">
       <label class="form-label" for="name">
        Name
       </label>
       <input class="form-control" id="name" name="name" required="" type="text"/>
      </div>
      <div class="form-group">
       <label class="form-label" for="email">
        Email
       </label>
       <input class="form-control" id="email" name="email" required="" type="email"/>
      </div>
      <div class="form-group">
       <label class="form-label" for="password">
        Password
       </label>
       <input class="form-control" id="password" name="password" required="" type="password"/>
      </div>
      <div class="form-group">
       <label class="form-label" for="role">
        Role
       </label>
       <select class="form-control" id="role" name="role" required="">
        <option value="admin">
         Admin
        </option>
        <option value="employee">
         Employee
        </option>
       </select>
      </div>
     </form>
    </div>
    <div class="modal-footer">
     <button class="btn btn-logout" id="cancelBtn" type="button">
      Cancel
     </button>
     <button class="btn btn-primary" type="submit" form="userForm">
      Save
     </button>
    </div>
   </div>
  </div>
  <div class="modal" id="viewUserModal">
   <div class="modal-content">
    <div class="modal-header">
     <h2 class="modal-title">
      View User
     </h2>
     <button class="modal-close" id="viewModalClose">
      ×
     </button>
    </div>
    <div class="modal-body">
     <div class="form-group">
      <label class="form-label">
       Name
      </label>
      <input class="form-control" id="viewName" readonly="" type="text"/>
     </div>
     <div class="form-group">
      <label class="form-label">
       Email
      </label>
      <input class="form-control" id="viewEmail" readonly="" type="email"/>
     </div>
     <div class="form-group">
      <label class="form-label">
       Password
      </label>
      <input class="form-control" id="viewPassword" readonly="" type="text"/>
     </div>
     <div class="form-group">
      <label class="form-label">
       Role
      </label>
      <input class="form-control" id="viewRole" readonly="" type="text"/>
     </div>
    </div>
    <div class="modal-footer">
     <button class="btn btn-logout" id="viewCancelBtn" type="button">
      Close
     </button>
    </div>
   </div>
  </div>
  <script>
   // Profile picture loading functionality
   document.addEventListener('DOMContentLoaded', function() {
       const userData = <?php echo json_encode($current_user); ?>;
       const nameInitial = userData.name.charAt(0).toUpperCase();
       const userAvatar = document.getElementById('userAvatar');
       
       // Load profile picture if exists
       if (userData.profile_picture) {
           loadProfilePicture(userData.profile_picture, userAvatar, nameInitial);
       } else {
           // Set initial as fallback
           userAvatar.textContent = nameInitial;
       }
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

   // Main application logic
   document.addEventListener('DOMContentLoaded', function() {
       const currentUser = <?php echo json_encode($current_user); ?>;
       
       if (!currentUser) {
           window.location.href = 'login.php';
           return;
       }
       
       document.getElementById('userName').textContent = currentUser.name;
       document.getElementById('userRole').textContent = currentUser.role;
       
       const adminElements = document.querySelectorAll('.admin-view');
       const employeeElements = document.querySelectorAll('.employee-view');
       
       if (currentUser.role === 'admin') {
           adminElements.forEach(el => el.style.display = 'block');
           employeeElements.forEach(el => el.style.display = 'none');
       } else {
           adminElements.forEach(el => el.style.display = 'none');
           employeeElements.forEach(el => el.style.display = 'block');
           document.querySelectorAll('.btn-edit, .btn-delete, #addUserBtn')
               .forEach(btn => btn.disabled = true);
       }
       
       setTimeout(() => {
           document.querySelectorAll('.notification').forEach(notification => {
               notification.style.display = 'none';
           });
       }, 5000);

       document.getElementById('logoutBtn').addEventListener('click', function() {
           localStorage.removeItem('currentUser');
           sessionStorage.clear();
           window.location.href = 'login.php';
       });

       const modal = document.getElementById('userModal');
       const userForm = document.getElementById('userForm');
       const modalTitle = document.getElementById('modalTitle');
       const addUserBtn = document.getElementById('addUserBtn');
       const cancelBtn = document.getElementById('cancelBtn');
       const modalClose = document.getElementById('modalClose');
       const viewModal = document.getElementById('viewUserModal');
       const viewCancelBtn = document.getElementById('viewCancelBtn');
       const viewModalClose = document.getElementById('viewModalClose');

       addUserBtn.addEventListener('click', openAddModal);

       function openAddModal() {
           if (currentUser.role !== 'admin') return;
           modalTitle.textContent = 'Add User';
           userForm.reset();
           document.getElementById('userId').value = '';
           document.getElementById('password').required = true;
           modal.style.display = 'flex';
           viewModal.style.display = 'none';
       }

       document.querySelectorAll('.btn-edit').forEach(btn => {
           btn.addEventListener('click', function() {
               if (currentUser.role !== 'admin') return;
               const userId = this.dataset.id;
               fetch(`user_actions.php?action=get&user_id=${userId}`)
                   .then(response => response.json())
                   .then(data => {
                       if (data.success) {
                           modalTitle.textContent = 'Edit User';
                           document.getElementById('userId').value = data.user.user_id;
                           document.getElementById('name').value = data.user.name;
                           document.getElementById('email').value = data.user.email;
                           document.getElementById('role').value = data.user.role;
                           document.getElementById('password').required = false;
                           modal.style.display = 'flex';
                           viewModal.style.display = 'none';
                       } else {
                           alert('Error fetching user data: ' + data.error);
                       }
                   });
           });
       });

       document.querySelectorAll('.btn-view').forEach(btn => {
           btn.addEventListener('click', function() {
               const userId = this.dataset.id;
               fetch(`user_actions.php?action=get&user_id=${userId}`)
                   .then(response => response.json())
                   .then(data => {
                       if (data.success) {
                           document.getElementById('viewName').value = data.user.name;
                           document.getElementById('viewEmail').value = data.user.email;
                           document.getElementById('viewPassword').value = data.user.plain_password || 'Password not available';
                           document.getElementById('viewRole').value = data.user.role;
                           viewModal.style.display = 'flex';
                           modal.style.display = 'none';
                       } else {
                           alert('Error fetching user data: ' + data.error);
                       }
                   })
                   .catch(error => {
                       console.error('Fetch error:', error);
                       alert('An error occurred while fetching user data.');
                   });
           });
       });

       document.querySelectorAll('.btn-delete').forEach(btn => {
           btn.addEventListener('click', function() {
               if (currentUser.role !== 'admin') return;
               const userId = this.dataset.id;
               fetch('user_actions.php', {
                   method: 'POST',
                   headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                   body: `action=delete&user_id=${userId}`
               })
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       location.reload();
                   } else {
                       alert('Error deleting user: ' + data.error);
                   }
               });
           });
       });

       cancelBtn.addEventListener('click', () => modal.style.display = 'none');
       modalClose.addEventListener('click', () => modal.style.display = 'none');

       viewCancelBtn.addEventListener('click', () => viewModal.style.display = 'none');
       viewModalClose.addEventListener('click', () => viewModal.style.display = 'none');

       userForm.addEventListener('submit', function(e) {
           e.preventDefault();
           const userId = document.getElementById('userId').value;
           const action = userId ? 'update' : 'add';
           const formData = new FormData(userForm);
           formData.append('action', action);

           fetch('user_actions.php', {
               method: 'POST',
               body: formData
           })
           .then(response => response.json())
           .then(data => {
               if (data.success) {
                   modal.style.display = 'none';
                   window.location.href = 'users.php';
               } else {
                   const notification = document.createElement('div');
                   notification.className = 'notification error';
                   notification.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error: ${data.error}`;
                   document.body.appendChild(notification);
                   setTimeout(() => {
                       notification.style.display = 'none';
                   }, 5000);
               }
           })
           .catch(error => {
               console.error('Fetch error:', error);
               const notification = document.createElement('div');
               notification.className = 'notification error';
               notification.innerHTML = `<i class="fas fa-exclamation-circle"></i> An error occurred while processing the request.`;
               document.body.appendChild(notification);
               setTimeout(() => {
                   notification.style.display = 'none';
               }, 5000);
           });
       });
   });
  </script>
 </body>
</html>
<?php
// Close database connection
$conn->close();
// Flush output buffer
ob_end_flush();
?>