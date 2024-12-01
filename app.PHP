<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "complaint_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper function to handle JSON responses
function jsonResponse($status, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            // Check if user is an admin
            $sql = "SELECT * FROM admins WHERE username='$username'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    jsonResponse(true, 'Login successful', ['is_admin' => true]);
                }
            }

            // Check if user is a regular user
            $sql = "SELECT * FROM users WHERE username='$username'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    jsonResponse(true, 'Login successful', ['is_admin' => false, 'user_id' => $user['id']]);
                }
            }
            
            jsonResponse(false, 'Invalid login credentials');
            break;

        case 'register':
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $email = $_POST['email'];
            
            // Register as a regular user
            $sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";
            if ($conn->query($sql) === TRUE) {
                jsonResponse(true, 'Registration successful');
            } else {
                jsonResponse(false, 'Registration failed', ['error' => $conn->error]);
            }
            break;

        case 'submit_complaint':
            $user_id = $_POST['user_id'];
            $subject = $_POST['subject'];
            $description = $_POST['description'];
            
            $sql = "INSERT INTO complaints (user_id, subject, description) VALUES ('$user_id', '$subject', '$description')";
            if ($conn->query($sql) === TRUE) {
                jsonResponse(true, 'Complaint submitted successfully');
            } else {
                jsonResponse(false, 'Failed to submit complaint', ['error' => $conn->error]);
            }
            break;

        case 'resolve_complaint':
            $complaint_id = $_POST['complaint_id'];
            
            $sql = "UPDATE complaints SET status='Resolved' WHERE id='$complaint_id'";
            if ($conn->query($sql) === TRUE) {
                jsonResponse(true, 'Complaint resolved successfully');
            } else {
                jsonResponse(false, 'Failed to resolve complaint', ['error' => $conn->error]);
            }
            break;

        default:
            jsonResponse(false, 'Invalid action');
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        switch ($action) {
            case 'fetch_user_complaints':
                $user_id = $_GET['user_id'];
                
                $sql = "SELECT * FROM complaints WHERE user_id='$user_id'";
                $result = $conn->query($sql);
                
                $complaints = [];
                while ($row = $result->fetch_assoc()) {
                    $complaints[] = $row;
                }
                
                jsonResponse(true, '', ['complaints' => $complaints]);
                break;

            case 'fetch_admin_complaints':
                $sql = "SELECT complaints.*, users.username FROM complaints JOIN users ON complaints.user_id = users.id";
                $result = $conn->query($sql);
                
                $complaints = [];
                while ($row = $result->fetch_assoc()) {
                    $complaints[] = $row;
                }
                
                jsonResponse(true, '', ['complaints' => $complaints]);
                break;

            default:
                jsonResponse(false, 'Invalid action');
                break;
        }
    }
} else {
    jsonResponse(false, 'Invalid request method');
}
?>
