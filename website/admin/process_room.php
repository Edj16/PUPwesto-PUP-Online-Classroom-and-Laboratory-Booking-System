<?php
require_once '../config/db_connect.php';

try {
    // Handle GET requests (delete and fetch room details)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_GET['action'])) {
            throw new Exception('Action is required');
        }

        if ($_GET['action'] === 'delete') {
            if (!isset($_GET['room_number'])) {
                throw new Exception('Room number is required');
            }

            // Check if room has any active reservations
            $check_sql = "SELECT COUNT(*) FROM reservation WHERE room_number = ? AND date >= CURDATE()";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([$_GET['room_number']]);
            
            if ($check_stmt->fetchColumn() > 0) {
                throw new Exception('Cannot delete room: There are active or upcoming reservations for this room');
            }

            // Delete the room
            $sql = "DELETE FROM room WHERE room_number = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_GET['room_number']]);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);
            exit;
        }

        if ($_GET['action'] === 'get') {
            if (!isset($_GET['room_number'])) {
                throw new Exception('Room number is required');
            }

            // Fetch room details
            $sql = "SELECT * FROM room WHERE room_number = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_GET['room_number']]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                throw new Exception('Room not found');
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $room
            ]);
            exit;
        }
    }

    // Handle POST requests (add/edit room)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['room_number']) || !isset($_POST['building']) || !isset($_POST['room_type'])) {
            throw new Exception('Room number, building, and type are required');
        }

        // Validate room number format
        if (!preg_match('/^[NSEW][0-9]{3}[A-Za-z]?$/', $_POST['room_number'])) {
            throw new Exception('Invalid room number format. Must be like N101, S501A, etc.');
        }

        // Validate building
        $valid_buildings = ['North', 'South', 'East', 'West'];
        if (!in_array($_POST['building'], $valid_buildings)) {
            throw new Exception('Invalid building');
        }

        // Validate room type
        $valid_types = ['Classroom', 'Laboratory'];
        if (!in_array($_POST['room_type'], $valid_types)) {
            throw new Exception('Invalid room type');
        }

        if (isset($_POST['action']) && $_POST['action'] === 'edit') {
            // Update existing room
            $sql = "UPDATE room SET 
                    building = :building,
                    room_type = :room_type,
                    status = :status
                    WHERE room_number = :room_number";
        } else {
            // Check if room already exists
            $check_sql = "SELECT COUNT(*) FROM room WHERE room_number = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([$_POST['room_number']]);
            
            if ($check_stmt->fetchColumn() > 0) {
                throw new Exception('Room number already exists');
            }

            // Add new room
            $sql = "INSERT INTO room (room_number, building, room_type, status) 
                    VALUES (:room_number, :building, :room_type, :status)";
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'room_number' => $_POST['room_number'],
            'building' => $_POST['building'],
            'room_type' => $_POST['room_type'],
            'status' => 'Available'
        ]);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => isset($_POST['action']) ? 'Room updated successfully' : 'Room added successfully'
        ]);
        exit;
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
} 