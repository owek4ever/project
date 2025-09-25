<?php
// feedback.php
session_start();
require_once 'db.php';

// Check if user is authenticated
if (!isset($_SESSION['currentUser'])) {
    header('Location: login.php');
    exit;
}

$currentUser = $_SESSION['currentUser'];
$role = $currentUser['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback System - Tunisie Telecom</title>
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
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            min-height: 100vh;
            color: var(--dark-text);
            position: relative;
            overflow-x: hidden;
            overflow-y: hidden; /* Added to remove browser vertical scrollbar */
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
            height: 100vh; /* Ensure it takes full viewport height */
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
            flex: 1 1 auto;
            padding: 30px;
            overflow-y: auto; /* Internal scrolling for main content */
            height: 100%; /* Take full height of container */
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

        /* Feedback specific styles */
        .feedback-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            margin-bottom: 20px;
        }

        .feedback-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .feedback-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .feedback-employee {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }

        .feedback-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .feedback-category {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .category-suggestion {
            background: rgba(52, 152, 219, 0.2);
            color: var(--primary-color);
        }

        .category-complaint {
            background: rgba(231, 76, 60, 0.2);
            color: var(--danger-color);
        }

        .category-question {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }

        .category-other {
            background: rgba(46, 204, 113, 0.2);
            color: var(--accent-color);
        }

        .feedback-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-open {
            background: rgba(52, 152, 219, 0.2);
            color: var(--primary-color);
        }

        .status-in_progress {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }

        .status-resolved {
            background: rgba(46, 204, 113, 0.2);
            color: var(--accent-color);
        }

        .status-closed {
            background: rgba(127, 140, 141, 0.2);
            color: #7f8c8d;
        }

        .feedback-date {
            font-size: 12px;
            color: #7f8c8d;
        }

        .feedback-content {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .feedback-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
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
            padding: 12px 15px;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 16px;
            background: #f9f9f9;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .response {
            border: 1px solid #eee;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: #f9f9f9;
        }

        .response strong {
            color: var(--secondary-color);
        }

        .response small {
            color: #7f8c8d;
            display: block;
            margin-top: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 25px;
            position: relative;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }

        .modal-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--secondary-color);
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

        .text-center {
            text-align: center;
        }
        
        .mt-20 {
            margin-top: 20px;
        }
        
        .mb-20 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar - Updated to match profile.php -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="logo.png" alt="Tunisie Telecom Logo">
            </div>
            
            <div class="user-info">
                <div class="user-avatar" id="userAvatar"><?php echo htmlspecialchars($currentUser['name'][0]); ?></div>
                <div class="user-details">
                    <div class="user-name" id="userName"><?php echo htmlspecialchars($currentUser['name']); ?></div>
                    <div class="user-role" id="userRole"><?php echo htmlspecialchars($currentUser['role']); ?></div>
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
                    <a href="feedback.php" class="nav-link active">
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
                <h1 class="page-title">Feedback System</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" id="newFeedbackBtn" style="display: <?php echo $role === 'admin' ? 'none' : 'flex'; ?>;">
                        <i class="fas fa-plus"></i>
                        <span>New Feedback</span>
                    </button>
                    <button class="btn btn-logout" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
            
            <!-- Feedback List Section -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title"><?php echo $role === 'admin' ? 'All' : 'My'; ?> Feedback Items</h2>
                    <div>
                        <select class="form-control" id="filterCategory" style="width: auto; display: inline-block;">
                            <option value="">All Categories</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="complaint">Complaint</option>
                            <option value="question">Question</option>
                            <option value="other">Other</option>
                        </select>
                        <select class="form-control" id="filterStatus" style="width: auto; display: inline-block; margin-left: 10px;">
                            <option value="">All Statuses</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
                
                <div class="feedback-list" id="feedbackList">
                    <p class="text-center">Loading feedback history...</p>
                </div>
            </div>
        </main>
    </div>

    <!-- New Feedback Modal -->
    <div class="modal" id="feedbackModal">
        <div class="modal-content">
            <button class="modal-close" id="closeModal">&times;</button>
            <h2 class="modal-title">Submit New Feedback</h2>
            
            <form id="feedbackForm">
                <div class="form-group">
                    <label class="form-label" for="category">Category</label>
                    <select class="form-control" id="category" required>
                        <option value="">Select a category</option>
                        <option value="suggestion">Suggestion</option>
                        <option value="complaint">Complaint</option>
                        <option value="question">Question</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="subject">Subject</label>
                    <input type="text" class="form-control" id="subject" placeholder="Brief summary of your feedback" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="message">Message</label>
                    <textarea class="form-control" id="message" placeholder="Please provide details about your feedback..." required rows="5"></textarea>
                </div>
                
                <div class="form-group" style="text-align: right;">
                    <button type="button" class="btn btn-danger" id="cancelFeedback">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Feedback Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <button class="modal-close" id="closeDetails">&times;</button>
            <h2 class="modal-title">Feedback Details</h2>
            
            <div id="feedbackDetails"></div>
            <div id="responsesList"></div>
            
            <?php if ($role === 'admin'): ?>
            <form id="statusForm" class="mt-20">
                <div class="form-group">
                    <label class="form-label" for="updateStatus">Update Status</label>
                    <select class="form-control" id="updateStatus">
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="form-group" style="text-align: right;">
                    <button type="submit" class="btn btn-warning">Update Status</button>
                </div>
            </form>
            <form id="responseForm" class="mt-20">
                <div class="form-group">
                    <label class="form-label" for="responseMessage">Add Response</label>
                    <textarea class="form-control" id="responseMessage" placeholder="Provide your response here..." required rows="4"></textarea>
                </div>
                <div class="form-group" style="text-align: right;">
                    <button type="submit" class="btn btn-success">Submit Response</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const userRole = '<?php echo $role; ?>';
        const currentUser = <?php echo json_encode($currentUser); ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing feedback system');
            
            // Set profile picture or initial
            const userAvatar = document.getElementById('userAvatar');
            const nameInitial = currentUser.name.charAt(0).toUpperCase();
            
            if (currentUser.profile_picture) {
                loadProfilePicture(currentUser.profile_picture, userAvatar, nameInitial);
            } else {
                userAvatar.textContent = nameInitial;
            }
            
            // Show/hide elements based on user role
            const adminElements = document.querySelectorAll('.admin-view');
            const employeeElements = document.querySelectorAll('.employee-view');
            
            if (userRole === 'admin') {
                adminElements.forEach(el => el.style.display = 'block');
                employeeElements.forEach(el => el.style.display = 'none');
            } else {
                adminElements.forEach(el => el.style.display = 'none');
                employeeElements.forEach(el => el.style.display = 'block');
            }
            
            // Logout functionality
            document.getElementById('logoutBtn').addEventListener('click', function() {
                console.log('Logout button clicked');
                if (confirm('Are you sure you want to logout?')) {
                    localStorage.removeItem('currentUser');
                    window.location.href = 'login.php';
                }
            });
            
            // Modal functionality for new feedback
            const feedbackModal = document.getElementById('feedbackModal');
            const newFeedbackBtn = document.getElementById('newFeedbackBtn');
            const closeModalBtn = document.getElementById('closeModal');
            const cancelFeedbackBtn = document.getElementById('cancelFeedback');
            
            if (newFeedbackBtn.style.display !== 'none') {
                newFeedbackBtn.addEventListener('click', function() {
                    console.log('New Feedback button clicked');
                    feedbackModal.style.display = 'flex';
                });
            }
            
            closeModalBtn.addEventListener('click', function() {
                console.log('Close modal button clicked');
                feedbackModal.style.display = 'none';
            });
            
            cancelFeedbackBtn.addEventListener('click', function() {
                console.log('Cancel feedback button clicked');
                feedbackModal.style.display = 'none';
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === feedbackModal) {
                    console.log('Clicked outside feedback modal');
                    feedbackModal.style.display = 'none';
                }
            });
            
            // Form submission via AJAX for new feedback
            document.getElementById('feedbackForm').addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Feedback form submitted');
                
                const category = document.getElementById('category').value;
                const subject = document.getElementById('subject').value;
                const message = document.getElementById('message').value;
                
                fetch('feedback_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ category, subject, message })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Thank you for your feedback! It has been submitted successfully.');
                        document.getElementById('feedbackForm').reset();
                        feedbackModal.style.display = 'none';
                        loadFeedback();
                    } else {
                        console.error('Feedback submission failed:', data.message);
                        alert('Error submitting feedback: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error submitting feedback:', error);
                    alert('An error occurred while submitting feedback: ' + error.message);
                });
            });
            
            // Details modal functionality
            const detailsModal = document.getElementById('detailsModal');
            const closeDetailsBtn = document.getElementById('closeDetails');
            
            closeDetailsBtn.addEventListener('click', function() {
                console.log('Close details modal button clicked');
                detailsModal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === detailsModal) {
                    console.log('Clicked outside details modal');
                    detailsModal.style.display = 'none';
                }
            });
            
            // Load feedback function
            function loadFeedback(category = '', status = '') {
                console.log(`Loading feedback with category: ${category}, status: ${status}`);
                const params = new URLSearchParams();
                if (category) params.append('category', category);
                if (status) params.append('status', status);
                
                fetch(`feedback_handler.php?${params.toString()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        populateFeedbackContainer(data.feedbacks, document.getElementById('feedbackList'));
                    } else {
                        console.error('Error fetching feedback:', data.message);
                        alert('Error fetching feedback: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching feedback:', error);
                    alert('An error occurred while fetching feedback: ' + error.message);
                });
            }
            
            // Populate feedback container
            function populateFeedbackContainer(feedbacks, container) {
                console.log('Populating feedback container with', feedbacks.length, 'items');
                if (feedbacks.length === 0) {
                    container.innerHTML = '<p class="text-center">No feedback found.</p>';
                    return;
                }
                
                let html = '';
                feedbacks.forEach(feedback => {
                    const employeeHtml = (userRole === 'admin' && feedback.employee_name) ? `<div class="feedback-employee">By ${feedback.employee_name}</div>` : '';
                    const categoryText = feedback.category.charAt(0).toUpperCase() + feedback.category.slice(1);
                    const statusText = feedback.status.replace('_', ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                    
                    html += `
                        <div class="feedback-card">
                            <div class="feedback-header">
                                <div>
                                    <div class="feedback-title">${feedback.subject}</div>
                                    ${employeeHtml}
                                    <div class="feedback-meta">
                                        <span class="feedback-category category-${feedback.category}">${categoryText}</span>
                                        <span class="feedback-status status-${feedback.status}">${statusText}</span>
                                    </div>
                                </div>
                                <div class="feedback-date">${calculateDaysAgo(feedback.created_at)}</div>
                            </div>
                            <div class="feedback-content">
                                <p>${feedback.message}</p>
                            </div>
                            <div class="feedback-actions">
                                <button class="btn btn-primary view-details-btn" data-feedback-id="${feedback.feedback_id}">View Details</button>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;

                // Attach event listeners to View Details buttons
                document.querySelectorAll('.view-details-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const feedbackId = this.getAttribute('data-feedback-id');
                        console.log('View Details clicked for feedback ID:', feedbackId);
                        viewFeedbackDetails(feedbackId);
                    });
                });
            }
            
            // Calculate days ago
            function calculateDaysAgo(dateString) {
                try {
                    const createdDate = new Date(dateString);
                    const currentDate = new Date();
                    const diffTime = Math.abs(currentDate - createdDate);
                    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    return `${diffDays} days ago`;
                } catch (e) {
                    console.error('Error calculating days ago:', e);
                    return 'Unknown date';
                }
            }
            
            // Profile Picture Function
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
            
            // Filter event listeners
            document.getElementById('filterCategory').addEventListener('change', function() {
                console.log('Category filter changed to:', this.value);
                loadFeedback(this.value, document.getElementById('filterStatus').value);
            });
            
            document.getElementById('filterStatus').addEventListener('change', function() {
                console.log('Status filter changed to:', this.value);
                loadFeedback(document.getElementById('filterCategory').value, this.value);
            });
            
            // View feedback details
            function viewFeedbackDetails(feedbackId) {
                console.log('Fetching details for feedback ID:', feedbackId);
                if (!feedbackId) {
                    console.error('No feedback ID provided');
                    alert('Error: No feedback ID provided');
                    return;
                }
                
                fetch(`feedback_handler.php?action=details&id=${encodeURIComponent(feedbackId)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        console.log('Feedback details received:', data.feedback);
                        const fb = data.feedback;
                        const employeeHtml = (userRole === 'admin' && fb.employee_name) ? `<div class="feedback-employee">By ${fb.employee_name}</div>` : '';
                        const categoryText = fb.category.charAt(0).toUpperCase() + fb.category.slice(1);
                        const statusText = fb.status.replace('_', ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                        
                        let detailsHtml = `
                            <div class="feedback-title">${fb.subject}</div>
                            ${employeeHtml}
                            <div class="feedback-meta">
                                <span class="feedback-category category-${fb.category}">${categoryText}</span>
                                <span class="feedback-status status-${fb.status}">${statusText}</span>
                            </div>
                            <div class="feedback-date">${calculateDaysAgo(fb.created_at)}</div>
                            <div class="feedback-content">
                                <p>${fb.message}</p>
                            </div>
                        `;
                        document.getElementById('feedbackDetails').innerHTML = detailsHtml;
                        
                        // Responses
                        let responsesHtml = '<h3>Responses:</h3>';
                        if (data.responses.length === 0) {
                            responsesHtml += '<p>No responses yet.</p>';
                        } else {
                            data.responses.forEach(response => {
                                responsesHtml += `
                                    <div class="response">
                                        <strong>${response.admin_name}:</strong>
                                        <p>${response.message}</p>
                                        <small>${calculateDaysAgo(response.created_at)}</small>
                                    </div>
                                `;
                            });
                        }
                        document.getElementById('responsesList').innerHTML = responsesHtml;
                        
                        // Set current status in select if admin
                        if (userRole === 'admin') {
                            document.getElementById('updateStatus').value = fb.status;
                        }
                        
                        // Show modal
                        document.getElementById('detailsModal').style.display = 'flex';
                        
                        // Attach submit events if admin
                        if (userRole === 'admin') {
                            const statusForm = document.getElementById('statusForm');
                            statusForm.onsubmit = function(e) {
                                e.preventDefault();
                                console.log('Status form submitted for feedback ID:', feedbackId);
                                const newStatus = document.getElementById('updateStatus').value;
                                
                                fetch('feedback_handler.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({ action: 'update_status', feedback_id: feedbackId, status: newStatus })
                                })
                                .then(res => {
                                    if (!res.ok) {
                                        throw new Error(`HTTP error! Status: ${res.status}`);
                                    }
                                    return res.json();
                                })
                                .then(d => {
                                    if (d.success) {
                                        alert('Status updated successfully!');
                                        viewFeedbackDetails(feedbackId);
                                        loadFeedback(document.getElementById('filterCategory').value, document.getElementById('filterStatus').value);
                                    } else {
                                        console.error('Error updating status:', d.message);
                                        alert('Error updating status: ' + d.message);
                                    }
                                })
                                .catch(err => {
                                    console.error('Error updating status:', err);
                                    alert('An error occurred while updating status: ' + err.message);
                                });
                            };

                            const responseForm = document.getElementById('responseForm');
                            responseForm.onsubmit = function(e) {
                                e.preventDefault();
                                console.log('Response form submitted for feedback ID:', feedbackId);
                                const message = document.getElementById('responseMessage').value;
                                if (!message.trim()) {
                                    alert('Response message cannot be empty');
                                    return;
                                }
                                
                                fetch('feedback_handler.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({ action: 'response', feedback_id: feedbackId, message })
                                })
                                .then(res => {
                                    if (!res.ok) {
                                        throw new Error(`HTTP error! Status: ${res.status}`);
                                    }
                                    return res.json();
                                })
                                .then(d => {
                                    if (d.success) {
                                        alert('Response added successfully!');
                                        responseForm.reset();
                                        viewFeedbackDetails(feedbackId);
                                        loadFeedback(document.getElementById('filterCategory').value, document.getElementById('filterStatus').value);
                                    } else {
                                        console.error('Error adding response:', d.message);
                                        alert('Error adding response: ' + d.message);
                                    }
                                })
                                .catch(err => {
                                    console.error('Error adding response:', err);
                                    alert('An error occurred while adding response: ' + err.message);
                                });
                            };
                        }
                    } else {
                        console.error('Error fetching details:', data.message);
                        alert('Error fetching details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching details:', error);
                    alert('An error occurred while fetching details: ' + error.message);
                });
            }
            
            // Initial load
            loadFeedback();
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>