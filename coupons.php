<?php
// Start output buffering
ob_start();
// Start session for notifications and user data
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TT Dashboard - Coupon Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS styles (same as your original) */
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

        .btn-success {
            background: var(--accent-color);
            color: white;
        }

        .btn-success:hover {
            background: #27ae60;
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

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: var(--card-shadow);
            animation: modalFadeIn 0.3s ease-out;
            overflow-y: auto;
            max-height: 80vh;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
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

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #777;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
        }

        .form-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .form-input[readonly] {
            background: rgba(255, 255, 255, 0.7);
            cursor: not-allowed;
        }

        .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            min-height: 100px;
            resize: vertical;
            transition: var(--transition);
        }

        .form-textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
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

            .modal-content {
                padding: 20px;
                width: 95%;
                max-height: 80vh;
                overflow-y: auto;
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
    </style>
</head>
<body>
    <?php
    // Database connection
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
    
    // Handle form submissions
    $success_message = $_SESSION['success_message'] ?? '';
    $error_message = $_SESSION['error_message'] ?? '';
    
    // Clear session messages after displaying
    unset($_SESSION['success_message']);
    unset($_SESSION['error_message']);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['create_coupon'])) {
            // Create new coupon
            $redeem_code = $conn->real_escape_string($_POST['redeem_code']);
            $description = $conn->real_escape_string($_POST['description']);
            $partner_name = $conn->real_escape_string($_POST['partner_name']);
            $discount_rate = floatval($_POST['discount_rate']);
            $expiry_date = $conn->real_escape_string($_POST['expiry_date']);
            $issued_by = $user_id;
            
            $sql = "INSERT INTO coupons (coupon_id, redeem_code, description, coupon_code, partner_name, discount_rate, expiry_date, issued_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $result = executeQuery($conn, $sql, [$redeem_code, $redeem_code, $description, $redeem_code, $partner_name, $discount_rate, $expiry_date, $issued_by]);
            
            if ($result['success']) {
                $_SESSION['success_message'] = "Coupon created successfully!";
                header("Location: coupons.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Error creating coupon: " . ($result['error'] ?? $conn->error);
                header("Location: coupons.php");
                exit();
            }
        } 
        elseif (isset($_POST['update_coupon'])) {
            // Update existing coupon
            $coupon_id = $conn->real_escape_string($_POST['coupon_id']);
            $redeem_code = $conn->real_escape_string($_POST['redeem_code']);
            $description = $conn->real_escape_string($_POST['description']);
            $partner_name = $conn->real_escape_string($_POST['partner_name']);
            $discount_rate = floatval($_POST['discount_rate']);
            $expiry_date = $conn->real_escape_string($_POST['expiry_date']);
            
            $sql = "UPDATE coupons SET 
                    redeem_code = ?,
                    coupon_code = ?,
                    description = ?, 
                    partner_name = ?, 
                    discount_rate = ?, 
                    expiry_date = ? 
                    WHERE coupon_id = ?";
            $result = executeQuery($conn, $sql, [$redeem_code, $redeem_code, $description, $partner_name, $discount_rate, $expiry_date, $coupon_id]);
            
            if ($result['success']) {
                $_SESSION['success_message'] = "Coupon updated successfully!";
            } else {
                $_SESSION['error_message'] = "Error updating coupon: " . ($result['error'] ?? $conn->error);
            }
            header("Location: coupons.php");
            exit();
        } 
        elseif (isset($_POST['delete_coupon'])) {
            // Delete coupon
            $coupon_id = $conn->real_escape_string($_POST['coupon_id']);
            
            $sql = "DELETE FROM coupon_redemptions WHERE coupon_id = ?";
            executeQuery($conn, $sql, [$coupon_id]);
            
            $sql = "DELETE FROM coupons WHERE coupon_id = ?";
            $result = executeQuery($conn, $sql, [$coupon_id]);
            
            if ($result['success']) {
                $_SESSION['success_message'] = "Coupon deleted successfully!";
            } else {
                $_SESSION['error_message'] = "Error deleting coupon: " . ($result['error'] ?? $conn->error);
            }
            header("Location: coupons.php");
            exit();
        }
    }
    
    // Fetch all coupons
    $sql = "SELECT * FROM coupons ORDER BY created_at DESC";
    $result = executeQuery($conn, $sql);
    $coupons = $result['success'] ? $result['result']->fetch_all(MYSQLI_ASSOC) : [];
    
    // Get coupon details for editing
    $edit_coupon = null;
    if (isset($_GET['edit'])) {
        $coupon_id = $conn->real_escape_string($_GET['edit']);
        $sql = "SELECT * FROM coupons WHERE coupon_id = ?";
        $result = executeQuery($conn, $sql, [$coupon_id]);
        if ($result['success'] && $row = $result['result']->fetch_assoc()) {
            $edit_coupon = $row;
        }
    }
    ?>
    
    <!-- Notification Messages -->
    <?php if ($success_message): ?>
        <div class="notification success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="notification error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
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
                    <a href="content.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Content</span>
                    </a>
                </li>
                <li class="nav-item admin-view">
                    <a href="coupons.php" class="nav-link active">
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
                <h1 class="page-title">Coupon Management</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" id="createCouponBtn">
                        <i class="fas fa-plus"></i>
                        <span>Create Coupon</span>
                    </button>
                    <button class="btn btn-logout" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
            
            <!-- Coupons Section -->
            <div class="content-section admin-view">
                <div class="section-header">
                    <h2 class="section-title">Active Coupons</h2>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Redeem Code</th>
                                <th>Partner</th>
                                <th>Discount</th>
                                <th>Valid Until</th>
                                <th>Usage Count</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="couponsTableBody">
                            <?php foreach ($coupons as $coupon): 
                                $today = date('Y-m-d');
                                $expiry_date = $coupon['expiry_date'];
                                $status = 'Active';
                                $statusClass = 'published';
                                
                                if ($expiry_date < $today) {
                                    $status = 'Expired';
                                    $statusClass = 'expired';
                                } elseif ($coupon['usage_count'] >= 10) {
                                    $status = 'Limit Reached';
                                    $statusClass = 'draft';
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($coupon['redeem_code']); ?></td>
                                <td><?php echo htmlspecialchars($coupon['partner_name']); ?></td>
                                <td><?php echo htmlspecialchars($coupon['discount_rate']); ?>%</td>
                                <td><?php echo date('M j, Y', strtotime($coupon['expiry_date'])); ?></td>
                                <td><?php echo htmlspecialchars($coupon['usage_count']); ?></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                <td>
                                    <button class="action-btn btn-view" data-id="<?php echo $coupon['coupon_id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn btn-edit" onclick="editCoupon('<?php echo $coupon['coupon_id']; ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="coupon_id" value="<?php echo $coupon['coupon_id']; ?>">
                                        <input type="hidden" name="delete_coupon" value="1">
                                        <button type="submit" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this coupon?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Create/Edit Coupon Modal -->
    <div class="modal" id="couponModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle"><?php echo isset($edit_coupon) ? 'Edit Coupon' : 'Create Coupon'; ?></h2>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <form method="POST" id="couponForm">
                <?php if (isset($edit_coupon)): ?>
                    <input type="hidden" name="coupon_id" value="<?php echo $edit_coupon['coupon_id']; ?>">
                    <input type="hidden" name="update_coupon" value="1">
                <?php else: ?>
                    <input type="hidden" name="create_coupon" value="1">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="redeem_code" class="form-label">Redeem Code</label>
                    <input type="text" id="redeem_code" name="redeem_code" class="form-input" 
                           value="<?php echo isset($edit_coupon) ? htmlspecialchars($edit_coupon['redeem_code']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="partner_name" class="form-label">Partner Name</label>
                    <input type="text" id="partner_name" name="partner_name" class="form-input" 
                           value="<?php echo isset($edit_coupon) ? htmlspecialchars($edit_coupon['partner_name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-textarea"><?php 
                        echo isset($edit_coupon) ? htmlspecialchars($edit_coupon['description']) : ''; 
                    ?></textarea>
                </div>
                <div class="form-group">
                    <label for="discount_rate" class="form-label">Discount Rate (%)</label>
                    <input type="number" id="discount_rate" name="discount_rate" class="form-input" 
                           min="1" max="100" step="0.01" 
                           value="<?php echo isset($edit_coupon) ? htmlspecialchars($edit_coupon['discount_rate']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="expiry_date" class="form-label">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-input" 
                           value="<?php echo isset($edit_coupon) ? htmlspecialchars($edit_coupon['expiry_date']) : ''; ?>" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-logout" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary"><?php echo isset($edit_coupon) ? 'Update' : 'Create'; ?> Coupon</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Coupon Modal -->
    <div class="modal" id="viewCouponModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">View Coupon</h2>
                <button class="close-modal" id="viewModalClose">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Redeem Code</label>
                    <input class="form-input" id="viewCouponId" readonly type="text">
                </div>
                <div class="form-group">
                    <label class="form-label">Partner Name</label>
                    <input class="form-input" id="viewPartnerName" readonly type="text">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" id="viewDescription" readonly></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Discount Rate (%)</label>
                    <input class="form-input" id="viewDiscountRate" readonly type="number">
                </div>
                <div class="form-group">
                    <label class="form-label">Expiry Date</label>
                    <input class="form-input" id="viewExpiryDate" readonly type="date">
                </div>
                <div class="form-group">
                    <label class="form-label">Usage Count</label>
                    <input class="form-input" id="viewUsageCount" readonly type="number">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-logout" id="viewCancelBtn">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Profile picture loading functionality
        document.addEventListener('DOMContentLoaded', function() {
            const userData = <?php echo json_encode($current_user); ?>;
            const nameInitial = userData.name.charAt(0).toUpperCase();
            const userAvatar = document.getElementById('userAvatar');
            
            if (userData.profile_picture) {
                loadProfilePicture(userData.profile_picture, userAvatar, nameInitial);
            } else {
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

        // Check if user is logged in and setup UI
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
                document.querySelectorAll('.btn-edit, .btn-delete, #createCouponBtn')
                    .forEach(btn => btn.disabled = true);
            }
            
            setupEventListeners();
            
            <?php if (isset($_GET['edit'])): ?>
                document.getElementById('couponModal').style.display = 'flex';
            <?php endif; ?>
            
            setTimeout(() => {
                document.querySelectorAll('.notification').forEach(notification => {
                    notification.style.display = 'none';
                });
            }, 5000);
        });

        // Set up event listeners
        function setupEventListeners() {
            document.getElementById('createCouponBtn').addEventListener('click', showCreateModal);
            document.getElementById('closeModal').addEventListener('click', hideModal);
            document.getElementById('cancelBtn').addEventListener('click', hideModal);
            document.getElementById('viewModalClose').addEventListener('click', () => document.getElementById('viewCouponModal').style.display = 'none');
            document.getElementById('viewCancelBtn').addEventListener('click', () => document.getElementById('viewCouponModal').style.display = 'none');
            
            document.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const couponId = this.dataset.id;
                    fetch(`coupon_actions.php?action=get&coupon_id=${couponId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('viewCouponId').value = data.coupon.redeem_code;
                                document.getElementById('viewPartnerName').value = data.coupon.partner_name;
                                document.getElementById('viewDescription').value = data.coupon.description;
                                document.getElementById('viewDiscountRate').value = data.coupon.discount_rate;
                                document.getElementById('viewExpiryDate').value = data.coupon.expiry_date;
                                document.getElementById('viewUsageCount').value = data.coupon.usage_count;
                                document.getElementById('viewCouponModal').style.display = 'flex';
                                document.getElementById('couponModal').style.display = 'none';
                            } else {
                                const notification = document.createElement('div');
                                notification.className = 'notification error';
                                notification.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error fetching coupon data: ${data.error}`;
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
                            notification.innerHTML = `<i class="fas fa-exclamation-circle"></i> An error occurred while fetching coupon data.`;
                            document.body.appendChild(notification);
                            setTimeout(() => {
                                notification.style.display = 'none';
                            }, 5000);
                        });
                });
            });
            
            document.getElementById('logoutBtn').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.removeItem('currentUser');
                    sessionStorage.clear();
                    window.location.href = 'logout.php';
                }
            });
        }

        // Show create coupon modal
        function showCreateModal() {
            document.getElementById('couponForm').reset();
            const hiddenInputs = document.querySelectorAll('#couponForm input[type="hidden"]');
            hiddenInputs.forEach(input => {
                if (input.name !== 'create_coupon') {
                    input.remove();
                }
            });
            
            if (!document.querySelector('input[name="create_coupon"]')) {
                const createInput = document.createElement('input');
                createInput.type = 'hidden';
                createInput.name = 'create_coupon';
                createInput.value = '1';
                document.getElementById('couponForm').appendChild(createInput);
            }
            
            document.getElementById('modalTitle').textContent = 'Create Coupon';
            document.getElementById('couponModal').style.display = 'flex';
        }

        // Edit coupon
        function editCoupon(couponId) {
            window.location.href = 'coupons.php?edit=' + couponId;
        }

        // Hide modal
        function hideModal() {
            document.getElementById('couponModal').style.display = 'none';
            if (window.location.search.includes('edit=')) {
                window.location.href = 'coupons.php';
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>