<?php
session_start();
header("Content-Type: application/json");

// Correct database configuration
$host = "localhost";
$dbname = "Doctorly"; // Updated with correct database name
$username = "root"; // Make sure this is your correct MySQL username
$password = ""; // If you have a password for root, set it here

$response = ["success" => false, "message" => ""];

try {
    // Create connection
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Validate request method
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }

    // Get and sanitize inputs
    $user = isset($_POST['username']) ? trim($conn->real_escape_string($_POST['username'])) : '';
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate inputs
    if (empty($user) || empty($pass)) {
        throw new Exception("Username and password are required");
    }

    // Prepare statement
    $stmt = $conn->prepare("SELECT Username, Password FROM Patient WHERE Username = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $user);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        if ($pass === $row['Password']) { // Assuming passwords are stored in plain text (not recommended)
            $_SESSION['user'] = $user;
            $response["success"] = true;
            $response["message"] = "Login successful";
        } else {
            $response["message"] = "Incorrect password";
        }
    } else {
        $response["message"] = "User not found";
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log error to file
    error_log($e->getMessage());
    $response["message"] = "Server error: " . $e->getMessage();
}

echo json_encode($response);
exit();
?>
