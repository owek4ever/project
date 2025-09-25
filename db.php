<?php
// db.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "user_management_system";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Function to safely execute queries
function executeQuery($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ["success" => false, "error" => $conn->error];
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        return ["success" => true, "result" => $result];
    } else {
        return ["success" => false, "error" => $stmt->error];
    }
}
?>