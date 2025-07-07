<?php
require_once '../config/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('X-Debug-Message: Processing reservation request');

function generateNextReservationId($conn) {
    // Get the latest reservation_id
    $sql = "SELECT reservation_id FROM reservation ORDER BY reservation_id DESC LIMIT 1";
    $stmt = $conn->query($sql);
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $lastId = $row['reservation_id'];
        // Extract the number part and increment it
        $number = intval(substr($lastId, 1)) + 1;
        // Format it back with leading zeros
        return 'R' . str_pad($number, 3, '0', STR_PAD_LEFT);
    } else {
        // If no existing reservations, start with R001
        return 'R001';
    }
}

function sendResponse($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data
    ];
    
    // If it's an AJAX request, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode($response);
        exit;
    }
    
    // For regular form submissions, use session and redirect
    $_SESSION['reservation_message'] = $message;
    $_SESSION['reservation_status'] = $success ? 'success' : 'error';
    header('Location: admin_res.php');
    exit;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'add':
            // Validate required fields
            $required_fields = ['user_id', 'room_number', 'date', 'purpose', 'start_time', 'end_time', 'expected_num_people'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, "Missing required field: $field");
                }
            }

            // Check if the room is available for the requested time
            $check_sql = "SELECT COUNT(*) FROM reservation 
                         WHERE room_number = ? 
                         AND date = ? 
                         AND ((start_time BETWEEN ? AND ?) 
                         OR (end_time BETWEEN ? AND ?)
                         OR (start_time <= ? AND end_time >= ?))";
            
            $stmt = $conn->prepare($check_sql);
            $stmt->execute([
                $_POST['room_number'],
                $_POST['date'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['start_time'],
                $_POST['end_time']
            ]);

            if ($stmt->fetchColumn() > 0) {
                sendResponse(false, "Room is already reserved for this time slot");
            }

            // Start transaction
            $conn->beginTransaction();

            try {
                // Generate the next reservation ID
                $reservation_id = generateNextReservationId($conn);

                // Insert new reservation with the generated ID
                $sql = "INSERT INTO reservation (reservation_id, user_id, room_number, date, purpose_of_reservation, start_time, end_time, expected_num_people) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    $reservation_id,
                    $_POST['user_id'],
                    $_POST['room_number'],
                    $_POST['date'],
                    $_POST['purpose'],
                    $_POST['start_time'],
                    $_POST['end_time'],
                    $_POST['expected_num_people']
                ]);

                if (!$result) {
                    throw new Exception("Failed to insert reservation");
                }

                // Handle particulars if any are selected
                if (!empty($_POST['particulars']) && is_array($_POST['particulars'])) {
                    $particulars = $_POST['particulars'];
                    $quantities = isset($_POST['quantities']) ? $_POST['quantities'] : array();

                    $quantity_sql = "INSERT INTO quantity (reservation_id, particulars_code, quantity) VALUES (?, ?, ?)";
                    $quantity_stmt = $conn->prepare($quantity_sql);

                    foreach ($particulars as $particular_code) {
                        // Default to quantity 1 if not specified
                        $quantity = isset($quantities[$particular_code]) && is_numeric($quantities[$particular_code]) 
                                  ? (int)$quantities[$particular_code] 
                                  : 1;
                        
                        if ($quantity > 0) {
                            $quantity_stmt->execute([$reservation_id, $particular_code, $quantity]);
                        }
                    }
                }

                // Commit transaction
                $conn->commit();
                sendResponse(true, "Reservation added successfully");

            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollBack();
                throw $e;
            }
            break;

        case 'edit':
            // Similar validation as add
            $required_fields = ['reservation_id', 'user_id', 'room_number', 'date', 'purpose', 'start_time', 'end_time', 'expected_num_people'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
                    exit;
                }
            }

            // Check if the room is available (excluding current reservation)
            $check_sql = "SELECT COUNT(*) FROM reservation 
                         WHERE room_number = ? 
                         AND date = ? 
                         AND ((start_time BETWEEN ? AND ?) 
                         OR (end_time BETWEEN ? AND ?))
                         AND reservation_id != ?";
            
            $stmt = $conn->prepare($check_sql);
            $stmt->execute([
                $_POST['room_number'],
                $_POST['date'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['reservation_id']
            ]);

            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => "Room is already reserved for this time slot"]);
                exit;
            }

            // Start transaction
            $conn->beginTransaction();

            try {
                // Update reservation
                $sql = "UPDATE reservation 
                        SET user_id = ?,
                            room_number = ?, 
                            date = ?, 
                            purpose_of_reservation = ?, 
                            start_time = ?, 
                            end_time = ?, 
                            expected_num_people = ? 
                        WHERE reservation_id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $_POST['user_id'],
                    $_POST['room_number'],
                    $_POST['date'],
                    $_POST['purpose'],
                    $_POST['start_time'],
                    $_POST['end_time'],
                    $_POST['expected_num_people'],
                    $_POST['reservation_id']
                ]);

                // Delete existing particulars
                $delete_particulars = "DELETE FROM quantity WHERE reservation_id = ?";
                $stmt = $conn->prepare($delete_particulars);
                $stmt->execute([$_POST['reservation_id']]);

                // Add new particulars
                if (!empty($_POST['particulars']) && is_array($_POST['particulars'])) {
                    $quantity_sql = "INSERT INTO quantity (reservation_id, particulars_code, quantity) VALUES (?, ?, ?)";
                    $quantity_stmt = $conn->prepare($quantity_sql);

                    foreach ($_POST['particulars'] as $particular_code) {
                        $quantity = $_POST['quantity'][$particular_code] ?? 1;
                        if ($quantity > 0) {
                            $quantity_stmt->execute([$_POST['reservation_id'], $particular_code, $quantity]);
                        }
                    }
                }

                // Commit transaction
                $conn->commit();
                echo json_encode(['success' => true, 'message' => "Reservation updated successfully"]);
                exit;

            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollBack();
                throw $e;
            }
            break;

        case 'delete':
            if (empty($_GET['reservation_id'])) {
                sendResponse(false, "Reservation ID is required");
            }

            // Start transaction
            $conn->beginTransaction();

            try {
                // Delete particulars first
                $delete_particulars = "DELETE FROM quantity WHERE reservation_id = ?";
                $stmt = $conn->prepare($delete_particulars);
                $stmt->execute([$_GET['reservation_id']]);

                // Then delete the reservation
                $sql = "DELETE FROM reservation WHERE reservation_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_GET['reservation_id']]);

                // Commit transaction
                $conn->commit();
                sendResponse(true, "Reservation deleted successfully");

            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollBack();
                throw $e;
            }
            break;

        case 'get':
            if (empty($_GET['reservation_id'])) {
                echo json_encode(['success' => false, 'message' => 'Reservation ID is required']);
                exit;
            }

            $sql = "SELECT r.*, u.full_name, u.contact_num 
                    FROM reservation r 
                    LEFT JOIN user u ON r.user_id = u.user_id 
                    WHERE r.reservation_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_GET['reservation_id']]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reservation) {
                echo json_encode(['success' => false, 'message' => 'Reservation not found']);
                exit;
            }

            // Get particulars
            $particulars_sql = "SELECT p.particulars_code, p.particulars, q.quantity 
                              FROM quantity q 
                              JOIN particular p ON q.particulars_code = p.particulars_code 
                              WHERE q.reservation_id = ?";
            $stmt = $conn->prepare($particulars_sql);
            $stmt->execute([$_GET['reservation_id']]);
            $reservation['particulars'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'message' => 'Reservation found', 'data' => $reservation]);
            exit;
            break;

        default:
            sendResponse(false, "Invalid action");
    }
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage());
} catch (Exception $e) {
    sendResponse(false, "Error: " . $e->getMessage());
} 