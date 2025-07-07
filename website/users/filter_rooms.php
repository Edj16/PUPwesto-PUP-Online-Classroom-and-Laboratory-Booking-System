<?php
// filter_rooms.php
require_once __DIR__ . '/../config/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add at the top of the file
error_log("=== Starting room filter process ===");

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Accept both JSON and form data
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if ($data) {
            $date = $data['date'] ?? '';
            $start_time = $data['start_time'] ?? '';
            $end_time = $data['end_time'] ?? '';
            $building = $data['building'] ?? '';
            $room_type = $data['type'] ?? '';
        } else {
            $date = $_POST['selectdate'] ?? '';
            $start_time = $_POST['starttime'] ?? '';
            $end_time = $_POST['endtime'] ?? '';
            $building = $_POST['building-option'] ?? '';
            $room_type = $_POST['room-type-option'] ?? '';
        }

        // Debug: Print the received parameters
        error_log("Filter Parameters:");
        error_log("Building: " . $building);
        error_log("Room Type: " . $room_type);
        error_log("Date: " . $date);
        error_log("Start Time: " . $start_time);
        error_log("End Time: " . $end_time);

        // First, let's check the table structure
        $check_sql = "DESCRIBE room";
        $check_stmt = $conn->query($check_sql);
        if (!$check_stmt) {
            throw new Exception("Cannot get table structure");
        }
        
        error_log("Table structure:");
        while ($row = $check_stmt->fetch(PDO::FETCH_ASSOC)) {
            error_log(print_r($row, true));
        }

        // Get a sample row to see the actual data
        $sample_sql = "SELECT * FROM room LIMIT 1";
        $sample_stmt = $conn->query($sample_sql);
        if ($sample_stmt && $sample_row = $sample_stmt->fetch(PDO::FETCH_ASSOC)) {
            error_log("Sample row data:");
            error_log(print_r($sample_row, true));
        }

        // Query available rooms from the room table
        $sql = "SELECT DISTINCT r.* FROM room r 
                WHERE r.building = ? 
                AND r.room_type = ? 
                AND r.status = 'Available'
                AND r.room_number IN (
                    SELECT room_number FROM room WHERE building = ? AND room_type = ?
                )
                AND r.room_number NOT IN (
                    SELECT room_number FROM reservation 
                    WHERE date = ? AND (
                        (start_time < ? AND end_time > ?) OR
                        (start_time < ? AND end_time > ?) OR
                        (start_time >= ? AND end_time <= ?)
                    )
                )";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed");
        }

        // Before executing the query
        $params = [
            $building,
            $room_type,
            $building,  
            $room_type,
            $date,
            $end_time, $start_time,
            $end_time, $start_time,
            $start_time, $end_time
        ];
        error_log("Query parameters: " . print_r($params, true));

        $stmt->execute($params);
        $rooms = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Additional validation
            if (empty($row['room_number']) || empty($row['room_type']) || empty($row['building'])) {
                error_log("Skipping invalid room data: " . print_r($row, true));
                continue;
            }
            
            error_log("Found valid room: " . print_r($row, true));
            $rooms[] = array(
                'room_number' => $row['room_number'],
                'room_type' => $row['room_type'],
                'building' => $row['building'],
                'capacity' => $row['capacity'] ?? 'N/A',
                'status' => $row['status'] ?? 'Available'
            );
        }

        if (empty($rooms)) {
            error_log("No rooms found matching criteria: Building=$building, Type=$room_type");
        }

        error_log("Total rooms found: " . count($rooms));

        header('Content-Type: application/json');
        echo json_encode($rooms);
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    error_log("Error in filter_rooms.php: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}

?>