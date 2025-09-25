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

// Fetch available coupons from database
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM coupon_redemptions cr 
         WHERE cr.coupon_id = c.coupon_id 
         AND cr.employee_id = ?) as is_redeemed
        FROM coupons c
        WHERE c.expiry_date >= CURDATE()
        ORDER BY c.expiry_date ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error_message = 'Failed to prepare query: ' . $conn->error;
    error_log($error_message);
    $coupons = [];
} else {
    $stmt->bind_param("s", $userData['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $coupons = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $error_message = 'Failed to fetch coupons: ' . $conn->error;
        error_log($error_message);
        $coupons = [];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TT Dashboard - My Coupons</title>
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

        .coupon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            max-height: none;
            overflow-y: visible;
        }

        .coupon-grid::-webkit-scrollbar {
            width: 8px;
        }

        .coupon-grid::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .coupon-grid::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .coupon-grid::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        .coupon-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            cursor: pointer;
        }

        .coupon-card:hover {
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

            .coupon-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                max-height: none;
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

            .coupon-grid {
                grid-template-columns: 1fr;
                max-height: none;
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
                    <a href="employee_news.php" class="nav-link">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li class="nav-item employee-view">
                    <a href="my-coupons.php" class="nav-link active">
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
                <h1 class="page-title">My Coupons</h1>
                <div class="header-actions">
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Available Coupons</h2>
                </div>

                <div class="coupon-grid">
                    <?php if (!empty($coupons)): ?>
                        <?php foreach ($coupons as $coupon): ?>
                            <div class="coupon-card" data-id="<?php echo htmlspecialchars($coupon['coupon_id']); ?>">
                                <div class="card-header">
                                    <h3 class="card-title"><?php echo htmlspecialchars($coupon['partner_name']); ?> - <?php echo htmlspecialchars($coupon['discount_rate']); ?>% Off</h3>
                                </div>
                                <div class="card-content"><?php echo htmlspecialchars($coupon['description']); ?></div>
                                <div class="card-meta">
                                    <span><strong>Expiry Date:</strong> <?php echo date('M j, Y', strtotime($coupon['expiry_date'])); ?></span>
                                    <span><strong>Status:</strong> <?php echo $coupon['is_redeemed'] ? 'Redeemed' : 'Available'; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 20px;">
                            No available coupons found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- View Coupon Modal -->
    <div class="modal-overlay" id="viewCouponModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="viewTitle"></h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="view-modal-content">
                    <div class="view-modal-label">Description</div>
                    <div class="view-modal-value" id="viewDescription"></div>
                    <div class="view-modal-label">Partner Name</div>
                    <div class="view-modal-value" id="viewPartnerName"></div>
                    <div class="view-modal-label">Discount Rate</div>
                    <div class="view-modal-value" id="viewDiscountRate"></div>
                    <div class="view-modal-label">Expiry Date</div>
                    <div class="view-modal-value" id="viewExpiryDate"></div>
                    <div class="view-modal-label">Status</div>
                    <div class="view-modal-value" id="viewStatus"></div>
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
            const baseUrl = '<?php echo BASE_URL; ?>'; // Use PHP-defined BASE_URL
            const couponsData = <?php echo json_encode($coupons); ?>; // Local coupon data for fallback

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

            // Coupon view functionality
            const viewModal = document.getElementById('viewCouponModal');
            const closeViewBtn = document.getElementById('closeViewBtn');
            const modalCloseButtons = document.querySelectorAll('.modal-close');
            const viewTitle = document.getElementById('viewTitle');
            const viewDescription = document.getElementById('viewDescription');
            const viewPartnerName = document.getElementById('viewPartnerName');
            const viewDiscountRate = document.getElementById('viewDiscountRate');
            const viewExpiryDate = document.getElementById('viewExpiryDate');
            const viewStatus = document.getElementById('viewStatus');

            function showCouponDetails(couponId) {
                const couponUrl = `${baseUrl}/get_coupon.php?id=${couponId}`;
                console.log('Fetching coupon from:', couponUrl); // Debug URL
                fetch(couponUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            console.error('Server error:', data.error);
                            showNotification('error', 'Error: ' + data.error);
                            // Fallback to local data if available
                            const localCoupon = couponsData.find(c => c.coupon_id === couponId);
                            if (localCoupon) {
                                displayCouponDetails(localCoupon);
                            } else {
                                showNotification('error', 'Coupon data not available locally.');
                            }
                            return;
                        }
                        displayCouponDetails(data);
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        showNotification('error', `Error loading coupon details: ${error.message}. Please ensure get_coupon.php is in the correct directory.`);
                        // Fallback to local data
                        const localCoupon = couponsData.find(c => c.coupon_id === couponId);
                        if (localCoupon) {
                            displayCouponDetails(localCoupon);
                        } else {
                            showNotification('error', 'Coupon data not available locally.');
                        }
                    });
            }

            function displayCouponDetails(data) {
                viewTitle.textContent = `${data.partner_name} - ${data.discount_rate}% Off`;
                viewDescription.textContent = data.description || 'No description available';
                viewPartnerName.textContent = data.partner_name;
                viewDiscountRate.textContent = `${data.discount_rate}%`;
                viewExpiryDate.textContent = new Date(data.expiry_date).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                viewStatus.textContent = data.is_redeemed ? 'Redeemed' : 'Available';
                viewModal.style.display = 'flex';
            }

            function showNotification(type, message) {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.textContent = message;
                document.body.appendChild(notification);
                notification.style.display = 'flex';
                setTimeout(() => {
                    notification.style.display = 'none';
                    notification.remove();
                }, 5000);
            }

            document.querySelectorAll('.coupon-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    const couponId = this.getAttribute('data-id');
                    console.log('Coupon ID clicked:', couponId); // Debug coupon ID
                    showCouponDetails(couponId);
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