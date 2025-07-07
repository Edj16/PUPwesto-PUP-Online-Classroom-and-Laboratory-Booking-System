<?php
session_start();
require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        exit();
    }

    // Get form data from input
    $user_id = $_SESSION['user_id']; // Still get user_id from session
    $room_number = $input['room_number'];
    $date = $input['date'];
    $start_time = $input['start_time'];
    $end_time = $input['end_time'];
    $purpose = $input['purpose'];
    $professor = $input['professor'];
    $attendees = $input['attendees'];
    $particulars = $input['particulars'] ?? []; // This will now be an array of objects

    try {
        // First check if the room exists in the room table
        $check_room_sql = "SELECT room_number, room_type, status FROM room WHERE room_number = ?";
        $check_stmt = $conn->prepare($check_room_sql);
        $check_stmt->execute([$room_number]);
        
        if ($check_stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid room number. This room does not exist in the system.']);
            exit();
        }

        $room_data = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if ($room_data['status'] !== 'Available') {
            echo json_encode(['success' => false, 'error' => 'This room is not available for booking.']);
            exit();
        }

        // Start transaction
        $conn->beginTransaction();
        
        // Generate the next reservation ID
        $reservation_id = generateNextReservationId($conn);
        
        // Insert into reservation table
        $sql = "INSERT INTO reservation (
            reservation_id, user_id, date, start_time, end_time, 
            room_number, expected_num_people, professor_in_charge, 
            purpose_of_reservation
        ) VALUES (
            :reservation_id, :user_id, :date, :start_time, :end_time,
            :room_number, :attendees, :professor, :purpose
        )";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':reservation_id' => $reservation_id,
            ':user_id' => $user_id,
            ':date' => $date,
            ':start_time' => $start_time,
            ':end_time' => $end_time,
            ':room_number' => $room_number,
            ':attendees' => $attendees,
            ':professor' => $professor,
            ':purpose' => $purpose
        ]);

        // If particulars were selected, insert into quantity table for each
        if (!empty($particulars)) {
            $sql_quantity = "INSERT INTO quantity (reservation_id, particulars_code, quantity) 
                            VALUES (?, ?, ?)";
            $stmt_quantity = $conn->prepare($sql_quantity);
            
            foreach ($particulars as $particular_item) {
                $particulars_code = $particular_item['code'];
                $quantity = $particular_item['quantity'];
                
                $stmt_quantity->execute([
                    $reservation_id,
                    $particulars_code,
                    $quantity
                ]);
            }
        }

        // Commit transaction
        $conn->commit();
        
        // Send success response
        echo json_encode(['success' => true, 'reservation_id' => $reservation_id]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => "Error: " . $e->getMessage()]);
    }
} else {
    // If not POST request, return error
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>