<?php
require_once '../config/db_connect.php';

// Function to validate user data
function validateUserData($data) {
    $errors = [];
    
    // Validate User ID format (YYYY-XXXXX-XX-X)
    if (!preg_match('/^\d{4}-\d{5}-[A-Z]{2}-\d$/', $data['user_id'])) {
        $errors[] = "Invalid User ID format";
    }
    
    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate contact number (11 digits)
    if (!preg_match('/^[0-9]{11}$/', $data['contact_num'])) {
        $errors[] = "Invalid contact number format";
    }
    
    return $errors;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : 'add';
        
        // Validate the data
        $errors = validateUserData($_POST);
        
        if (!empty($errors)) {
            throw new Exception(implode(", ", $errors));
        }
        
        if ($action === 'add') {
            // Add new user
            $sql = "INSERT INTO user (user_id, user_role, full_name, contact_num, email, program, block, section, department) 
                    VALUES (:user_id, :user_role, :full_name, :contact_num, :email, :program, :block, :section, :department)";
        } else {
            // Update existing user
            $sql = "UPDATE user SET 
                    user_id = :user_id,
                    user_role = :user_role,
                    full_name = :full_name,
                    contact_num = :contact_num,
                    email = :email,
                    program = :program,
                    block = :block,
                    section = :section,
                    department = :department
                    WHERE user_id = :original_user_id";
        }
        
        $stmt = $conn->prepare($sql);
        
        $params = [
            'user_id' => $_POST['user_id'],
            'user_role' => $_POST['user_role'],
            'full_name' => $_POST['full_name'],
            'contact_num' => $_POST['contact_num'],
            'email' => $_POST['email'],
            'program' => $_POST['program'] ?: null,
            'block' => $_POST['block'] ?: null,
            'section' => $_POST['section'] ?: null,
            'department' => $_POST['department']
        ];
        
        if ($action === 'edit') {
            $params['original_user_id'] = $_POST['original_user_id'];
        }
        
        $stmt->execute($params);
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $action === 'add' ? 'User added successfully' : 'User updated successfully']);
        exit;
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
        // Handle user deletion
        if (!isset($_GET['user_id'])) {
            throw new Exception('User ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
        $stmt->execute([$_GET['user_id']]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        exit;
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
        // Handle fetching user details for editing
        if (!isset($_GET['user_id'])) {
            throw new Exception('User ID is required');
        }
        
        $stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
        $stmt->execute([$_GET['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $user]);
        exit;
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
} 