<?php
// emp_feedback.php
session_start();
require_once 'db.php';

// Check if user is authenticated and is employee
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'employee') {
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
    <title>Employee Feedback - Tunisie Telecom</title>
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
            --card-hover-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --border-radius: 15px;
            --gradient-primary: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            --gradient-card: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            color: var(--dark-text);
            position: relative;
            overflow-x: hidden;
            overflow-y: hidden;
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
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
            overflow-y: auto;
            height: 100%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease-out;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: rgba(52, 152, 219, 0.9);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-primary:hover {
            background: rgba(52, 152, 219, 1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
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

        .welcome-banner {
            background: var(--gradient-card);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            text-align: center;
            animation: fadeInUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
        }

        .welcome-banner h2 {
            color: var(--secondary-color);
            margin-bottom: 10px;
            font-size: 24px;
        }

        .welcome-banner p {
            color: #6c757d;
            font-size: 16px;
            margin: 0;
        }

        .content-section {
            background: var(--gradient-card);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.6s ease-out;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
            position: relative;
        }

        .section-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: var(--accent-color);
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--accent-color);
            font-size: 20px;
        }

        .filters-container {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .form-control {
            padding: 10px 15px;
            border: 2px solid #e8e8e8;
            border-radius: 8px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.9);
            transition: var(--transition);
            backdrop-filter: blur(5px);
        }

        .form-control:focus {
            border-color: var(--accent-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
            outline: none;
        }

        /* Enhanced Feedback Cards */
        .feedback-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .feedback-card {
            background: var(--gradient-card);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            animation: slideInLeft 0.6s ease-out;
            animation-fill-mode: both;
            min-height: 280px;
            display: flex;
            flex-direction: column;
        }

        .feedback-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
        }

        .feedback-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-hover-shadow);
            border-color: rgba(46, 204, 113, 0.3);
        }

        .feedback-card:nth-child(1) { animation-delay: 0.1s; }
        .feedback-card:nth-child(2) { animation-delay: 0.2s; }
        .feedback-card:nth-child(3) { animation-delay: 0.3s; }
        .feedback-card:nth-child(4) { animation-delay: 0.4s; }
        .feedback-card:nth-child(5) { animation-delay: 0.5s; }
        .feedback-card:nth-child(6) { animation-delay: 0.6s; }

        .feedback-header {
            margin-bottom: 15px;
        }

        .feedback-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--secondary-color);
            line-height: 1.3;
        }

        .feedback-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .feedback-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .category-suggestion {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.15), rgba(52, 152, 219, 0.25));
            color: var(--primary-color);
        }

        .category-complaint {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.15), rgba(231, 76, 60, 0.25));
            color: var(--danger-color);
        }

        .category-question {
            background: linear-gradient(135deg, rgba(243, 156, 18, 0.15), rgba(243, 156, 18, 0.25));
            color: var(--warning-color);
        }

        .category-other {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.15), rgba(46, 204, 113, 0.25));
            color: var(--accent-color);
        }

        .feedback-status {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.15), rgba(108, 117, 125, 0.25));
            color: #6c757d;
        }

        .status-open {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.15), rgba(52, 152, 219, 0.25));
            color: var(--primary-color);
        }

        .status-in_progress {
            background: linear-gradient(135deg, rgba(243, 156, 18, 0.15), rgba(243, 156, 18, 0.25));
            color: var(--warning-color);
        }

        .status-resolved {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.15), rgba(46, 204, 113, 0.25));
            color: var(--accent-color);
        }

        .status-closed {
            background: linear-gradient(135deg, rgba(127, 140, 141, 0.15), rgba(127, 140, 141, 0.25));
            color: #7f8c8d;
        }

        .feedback-content {
            flex-grow: 1;
            margin-bottom: 20px;
            line-height: 1.6;
            color: #495057;
        }

        .feedback-content p {
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .feedback-footer {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .feedback-date {
            font-size: 12px;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 5px;
            background: rgba(108, 117, 125, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
        }

        .feedback-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 13px;
            border-radius: 6px;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--accent-color);
            color: var(--accent-color);
        }

        .btn-outline:hover {
            background: var(--accent-color);
            color: white;
            transform: translateY(-1px);
        }

        .priority-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--accent-color);
            box-shadow: 0 0 10px rgba(46, 204, 113, 0.5);
            animation: pulse 2s infinite;
        }

        .priority-high {
            background: var(--danger-color);
            box-shadow: 0 0 10px rgba(231, 76, 60, 0.5);
        }

        .priority-medium {
            background: var(--warning-color);
            box-shadow: 0 0 10px rgba(243, 156, 18, 0.5);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--secondary-color);
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 0;
        }

        /* Modal Styles */
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
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            border-radius: var(--border-radius);
            width: 100%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
            position: relative;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            animation: fadeInUp 0.3s ease-out;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
            transition: var(--transition);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .modal-title {
            font-size: 28px;
            margin-bottom: 25px;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary-color);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .response {
            border: 1px solid #eee;
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            background: var(--gradient-card);
            position: relative;
        }

        .response::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-color);
            border-radius: 2px 0 0 2px;
        }

        .response strong {
            color: var(--secondary-color);
            font-size: 16px;
        }

        .response small {
            color: #6c757d;
            display: block;
            margin-top: 8px;
            font-size: 12px;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .feedback-grid {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            }
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
                padding: 20px;
            }

            .feedback-grid {
                grid-template-columns: 1fr;
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

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .filters-container {
                width: 100%;
                flex-wrap: wrap;
            }

            .form-control {
                flex: 1;
                min-width: 120px;
            }
        }

        .text-center { text-align: center; }
        .mt-20 { margin-top: 20px; }
        .mb-20 { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar matching the dashboard file -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="logo.png" alt="Tunisie Telecom Logo">
            </div>
            
            <div class="user-info">
                <div class="user-avatar" id="userAvatar"><?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($currentUser['name']); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($currentUser['role']); ?></div>
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
                    <a href="emp_feedback.php" class="nav-link active">
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
                <h1 class="page-title">
                    <i class="fas fa-comment-heart"></i>
                    My Feedback
                </h1>
                <div class="header-actions">
                    <button class="btn btn-primary" id="newFeedbackBtn">
                        <i class="fas fa-plus"></i>
                        <span>Submit Feedback</span>
                    </button>
                    <button class="btn btn-logout" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-history"></i>
                        My Feedback History
                    </h2>
                    <div class="filters-container">
                        <select class="form-control" id="filterCategory">
                            <option value="">All Categories</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="complaint">Complaint</option>
                            <option value="question">Question</option>
                            <option value="other">Other</option>
                        </select>
                        <select class="form-control" id="filterStatus">
                            <option value="">All Statuses</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
                
                <div class="feedback-grid" id="feedbackGrid">
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>Loading your feedback...</h3>
                        <p>Please wait while we fetch your feedback history.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- New Feedback Modal -->
    <div class="modal" id="feedbackModal">
        <div class="modal-content">
            <button class="modal-close" id="closeModal">&times;</button>
            <h2 class="modal-title">
                <i class="fas fa-plus-circle"></i>
                Submit New Feedback
            </h2>
            
            <form id="feedbackForm">
                <div class="form-group">
                    <label class="form-label" for="category">
                        <i class="fas fa-tag"></i>
                        Category
                    </label>
                    <select class="form-control" id="category" required>
                        <option value="">Select a category</option>
                        <option value="suggestion">💡 Suggestion</option>
                        <option value="complaint">⚠️ Complaint</option>
                        <option value="question">❓ Question</option>
                        <option value="other">📝 Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="subject">
                        <i class="fas fa-heading"></i>
                        Subject
                    </label>
                    <input type="text" class="form-control" id="subject" placeholder="Brief summary of your feedback" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="message">
                        <i class="fas fa-edit"></i>
                        Message
                    </label>
                    <textarea class="form-control" id="message" placeholder="Describe your feedback in detail. Be specific and constructive to help us understand your perspective." required rows="6"></textarea>
                </div>
                
                <div class="form-group" style="text-align: right;">
                    <button type="button" class="btn btn-logout btn-small" id="cancelFeedback">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-small" style="background: var(--accent-color); margin-left: 10px;">
                        <i class="fas fa-paper-plane"></i>
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Feedback Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <button class="modal-close" id="closeDetails">&times;</button>
            <h2 class="modal-title">
                <i class="fas fa-info-circle"></i>
                Feedback Details
            </h2>
            
            <div id="feedbackDetails"></div>
            <div id="responsesList"></div>
        </div>
    </div>

    <script>
        const userRole = '<?php echo $role; ?>';
        const currentUser = <?php echo json_encode($currentUser); ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing employee feedback system');
            
            // Set profile picture or initial
            const userAvatar = document.getElementById('userAvatar');
            const nameInitial = currentUser.name.charAt(0).toUpperCase();
            
            if (currentUser.profile_picture) {
                loadProfilePicture(currentUser.profile_picture, userAvatar, nameInitial);
            } else {
                userAvatar.textContent = nameInitial;
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
            
            newFeedbackBtn.addEventListener('click', function() {
                console.log('New Feedback button clicked');
                feedbackModal.style.display = 'flex';
            });
            
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
                        populateFeedbackGrid(data.feedbacks, document.getElementById('feedbackGrid'));
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
            
            // Enhanced populate feedback grid with card layout
            function populateFeedbackGrid(feedbacks, container) {
                console.log('Populating feedback grid with', feedbacks.length, 'items');
                
                if (feedbacks.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-comment-plus"></i>
                            <h3>No feedback submitted yet</h3>
                            <p>Start sharing your thoughts and suggestions with us!</p>
                        </div>
                    `;
                    return;
                }
                
                let html = '';
                feedbacks.forEach((feedback, index) => {
                    const categoryText = feedback.category.charAt(0).toUpperCase() + feedback.category.slice(1);
                    const statusText = feedback.status.replace('_', ' ').split(' ').map(word => 
                        word.charAt(0).toUpperCase() + word.slice(1)
                    ).join(' ');
                    
                    // Determine priority indicator based on status
                    let priorityClass = '';
                    if (feedback.status === 'resolved') priorityClass = 'priority-indicator';
                    else if (feedback.category === 'complaint') priorityClass = 'priority-indicator priority-high';
                    else if (feedback.status === 'in_progress') priorityClass = 'priority-indicator priority-medium';
                    
                    const categoryIcon = {
                        'suggestion': 'fas fa-lightbulb',
                        'complaint': 'fas fa-exclamation-triangle',
                        'question': 'fas fa-question-circle',
                        'other': 'fas fa-file-alt'
                    };
                    
                    const statusIcon = {
                        'open': 'fas fa-circle',
                        'in_progress': 'fas fa-clock',
                        'resolved': 'fas fa-check-circle',
                        'closed': 'fas fa-times-circle'
                    };
                    
                    html += `
                        <div class="feedback-card" style="animation-delay: ${index * 0.1}s">
                            ${priorityClass ? `<div class="${priorityClass}"></div>` : ''}
                            <div class="feedback-header">
                                <div class="feedback-title">${feedback.subject}</div>
                                <div class="feedback-meta">
                                    <span class="feedback-badge category-${feedback.category}">
                                        <i class="${categoryIcon[feedback.category] || 'fas fa-tag'}"></i>
                                        ${categoryText}
                                    </span>
                                    <span class="feedback-badge feedback-status status-${feedback.status}">
                                        <i class="${statusIcon[feedback.status] || 'fas fa-circle'}"></i>
                                        ${statusText}
                                    </span>
                                </div>
                            </div>
                            <div class="feedback-content">
                                <p>${feedback.message}</p>
                            </div>
                            <div class="feedback-footer">
                                <div class="feedback-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    ${calculateDaysAgo(feedback.created_at)}
                                </div>
                                <div class="feedback-actions">
                                    <button class="btn btn-outline btn-small view-details-btn" data-feedback-id="${feedback.feedback_id}">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </div>
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
            
            // Calculate days ago with enhanced formatting
            function calculateDaysAgo(dateString) {
                try {
                    const createdDate = new Date(dateString);
                    const currentDate = new Date();
                    const diffTime = Math.abs(currentDate - createdDate);
                    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (diffDays === 0) return 'Today';
                    if (diffDays === 1) return 'Yesterday';
                    if (diffDays < 7) return `${diffDays} days ago`;
                    if (diffDays < 30) return `${Math.floor(diffDays / 7)} week${Math.floor(diffDays / 7) > 1 ? 's' : ''} ago`;
                    return `${Math.floor(diffDays / 30)} month${Math.floor(diffDays / 30) > 1 ? 's' : ''} ago`;
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
            
            // View feedback details function
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
                        const categoryText = fb.category.charAt(0).toUpperCase() + fb.category.slice(1);
                        const statusText = fb.status.replace('_', ' ').split(' ').map(word => 
                            word.charAt(0).toUpperCase() + word.slice(1)
                        ).join(' ');
                        
                        const categoryIcon = {
                            'suggestion': 'fas fa-lightbulb',
                            'complaint': 'fas fa-exclamation-triangle',
                            'question': 'fas fa-question-circle',
                            'other': 'fas fa-file-alt'
                        };
                        
                        const statusIcon = {
                            'open': 'fas fa-circle',
                            'in_progress': 'fas fa-clock',
                            'resolved': 'fas fa-check-circle',
                            'closed': 'fas fa-times-circle'
                        };
                        
                        let detailsHtml = `
                            <div class="feedback-title">${fb.subject}</div>
                            <div class="feedback-meta" style="margin-bottom: 15px;">
                                <span class="feedback-badge category-${fb.category}">
                                    <i class="${categoryIcon[fb.category] || 'fas fa-tag'}"></i>
                                    ${categoryText}
                                </span>
                                <span class="feedback-badge feedback-status status-${fb.status}">
                                    <i class="${statusIcon[fb.status] || 'fas fa-circle'}"></i>
                                    ${statusText}
                                </span>
                            </div>
                            <div class="feedback-date" style="margin-bottom: 20px;">
                                <i class="fas fa-calendar-alt"></i>
                                Submitted ${calculateDaysAgo(fb.created_at)}
                            </div>
                            <div class="feedback-content" style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid var(--accent-color);">
                                <p style="margin: 0; line-height: 1.6;">${fb.message}</p>
                            </div>
                        `;
                        document.getElementById('feedbackDetails').innerHTML = detailsHtml;
                        
                        // Enhanced responses section
                        let responsesHtml = '<h3 style="margin-top: 30px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;"><i class="fas fa-reply-all"></i>Admin Responses:</h3>';
                        if (data.responses.length === 0) {
                            responsesHtml += `
                                <div style="text-align: center; padding: 30px; color: #6c757d;">
                                    <i class="fas fa-comment-slash" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                                    <p>No responses yet. Our team will review your feedback and respond soon!</p>
                                </div>
                            `;
                        } else {
                            data.responses.forEach(response => {
                                responsesHtml += `
                                    <div class="response">
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                            <i class="fas fa-user-tie" style="color: var(--accent-color);"></i>
                                            <strong>${response.admin_name}:</strong>
                                        </div>
                                        <p style="margin: 10px 0;">${response.message}</p>
                                        <small>
                                            <i class="fas fa-clock"></i>
                                            Responded ${calculateDaysAgo(response.created_at)}
                                        </small>
                                    </div>
                                `;
                            });
                        }
                        document.getElementById('responsesList').innerHTML = responsesHtml;
                        
                        // Show modal
                        document.getElementById('detailsModal').style.display = 'flex';
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