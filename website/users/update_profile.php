<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    require_once __DIR__ . '/../config/db_connect.php';

    // Get the user ID from session
    $user_id = $_SESSION['user_id'];

    // Prepare update data
    $updateData = [
        'full_name' => $_POST['user-name'] ?? '',
        'email' => $_POST['user-email'] ?? '',
        'contact_num' => $_POST['user-contact'] ?? '',
        'program' => $_POST['user-program'] ?? '',
        'block' => $_POST['user-block'] ?? '',
        'section' => $_POST['user-section'] ?? '',
        'department' => $_POST['user-department'] ?? '',
    ];

    // Build SQL query
    $sql = "UPDATE user SET ";
    $params = [];
    foreach ($updateData as $field => $value) {
        if (!empty($value)) {
            $sql .= "$field = ?, ";
            $params[] = $value;
        }
    }
    $sql = rtrim($sql, ', ') . " WHERE user_id = ?";
    $params[] = $user_id;

    // Execute update
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);

    if ($result) {
        // Update session variables
        foreach ($updateData as $key => $value) {
            if (!empty($value)) {
                $_SESSION[str_replace('full_name', 'name', $key)] = $value;
            }
        }

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?> 