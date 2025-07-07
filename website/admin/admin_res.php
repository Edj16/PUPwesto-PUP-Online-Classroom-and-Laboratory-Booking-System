<?php require_once '../config/db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PUP Booking System</title>
    <link rel="stylesheet" href="admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-container">
        <nav class="navbar">
            <div class="logo-section">
                <img src="../images/puplogo.png" alt="PUP Logo" class="logo">
                <div class="logo-text">
                    <span>Polytechnic University of the Philippines</span>
                    <span class="subtitle">The Country's 1st Polytechnic University</span>
                </div>
            </div>
            <div class="nav-links">
                <a href="#" class="active">Home</a>
                <a href="#">About</a>
                <a href="#">Contact Us</a>
            </div>
        </nav>
        <div class="sidebar">
            <h2>Welcome, Admin</h2>
            <div class="menu-items">
                <a href="#" class="menu-item active" onclick="showView('reservations')">
                    <img src="../images/PUPWESTO.png" alt="Calendar">
                    Manage Reservations
                </a>
                <a href="#" class="menu-item" onclick="showView('rooms')">
                    <img src="../images/SETTINGS.png" alt="Room">
                    Manage Rooms
                </a>
                <a href="#" class="menu-item" onclick="showView('users')">
                    <img src="../images/profile-icon1.png" alt="Users">
                    Manage Users
                </a>
            </div>
        </div>
        <!-- Reservations View -->
        <main class="main-content" id="reservationsView">
            <div class="reservations-header">
            <h1>Manage Reservations</h1>
                <button class="btn-add-reservation" onclick="showModal('addReservationModal')">Add New Reservation</button>
            </div>
            <div class="table-controls">
                    <div class="search-box">
                    <input type="text" placeholder="Search reservations...">
                    </div>
                    <div class="filter-box">
                    <select>
                            <option value="">Building</option>
                        <option value="South">South</option>
                        <option value="West">West</option>
                        <option value="East">East</option>
                        <option value="North">North</option>
                        </select>
                    <select>
                            <option value="">All Dates</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        </select>
                    </div>
            </div>
            <div class="reservation-table">
                <table>
                    <thead>
                        <tr>
                            <th>Reservation ID</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Reserved By</th>
                            <th>Contact No.</th>
                            <th>Date</th>
                            <th>Time Slot</th>
                            <th>No. of Attendees</th>
                            <th>Particulars</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT r.*, u.full_name, u.contact_num, rm.room_type, rm.building
                                FROM reservation r
                                LEFT JOIN user u ON r.user_id = u.user_id
                                LEFT JOIN room rm ON r.room_number = rm.room_number
                                ORDER BY r.date DESC";

                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        
                        while ($row = $stmt->fetch()):
                            // Fetch particulars for this reservation
                            $particulars_sql = "SELECT p.particulars, q.quantity FROM quantity q JOIN particular p ON q.particulars_code = p.particulars_code WHERE q.reservation_id = ?";
                            $particulars_stmt = $conn->prepare($particulars_sql);
                            $particulars_stmt->execute([$row['reservation_id']]);
                            $particulars = $particulars_stmt->fetchAll();
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['reservation_id']) ?></td>
                                <td><?= htmlspecialchars($row['room_number']) ?></td>
                                <td><?= htmlspecialchars($row['room_type'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['purpose_of_reservation']) ?></td>
                                <td><?= htmlspecialchars($row['full_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['contact_num'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><?= htmlspecialchars($row['start_time']) ?> - <?= htmlspecialchars($row['end_time']) ?></td>
                                <td><?= htmlspecialchars($row['expected_num_people']) ?></td>
                                <td>
                                    <?php foreach($particulars as $p): ?>
                                        <?= htmlspecialchars($p['particulars']) ?> (<?= htmlspecialchars($p['quantity']) ?>)<br>
                                    <?php endforeach; ?>
                                </td>
                                <td class="action-buttons">
                                    <button class="btn-edit" onclick="editReservation('<?= htmlspecialchars($row['reservation_id']) ?>')">Edit</button>
                                    <button class="btn-delete" onclick="deleteReservation('<?= htmlspecialchars($row['reservation_id']) ?>')">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <!-- Rooms View -->
        <main class="main-content" id="roomsView" style="display: none;">
            <div class="room-header">
                <h1>Manage Rooms</h1>
                <button class="btn-add-room" onclick="showModal('addRoomModal')">Add New Room</button>
            </div>
            <div class="table-controls">
                <div class="search-box">
                    <input type="text" placeholder="Search rooms...">
                </div>
                <div class="filter-box">
                    <select>
                        <option value="">All Buildings</option>
                        <option value="South">South</option>
                        <option value="West">West</option>
                        <option value="East">East</option>
                        <option value="North">North</option>
                    </select>
                    <select>
                        <option value="">All Types</option>
                        <option value="Laboratory">Laboratory</option>
                        <option value="Lecture">Lecture Room</option>
                        <option value="Conference">Conference Room</option>
                    </select>
                    <select>
                        <option value="">All Status</option>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Under Maintenance</option>
                    </select>
                </div>
            </div>

            <div class="room-grid">
                <?php
                $room_sql = "SELECT * FROM room ORDER BY building, room_number";
                $room_stmt = $conn->query($room_sql);
                while ($room = $room_stmt->fetch()):
                ?>
                <div class="room-card">
                    <div class="room-header">
                        <h3>Room <?= htmlspecialchars($room['room_number']) ?></h3>
                        <span class="room-status available">Available</span> <!-- Placeholder, you can make this dynamic if you add a status column -->
                    </div>
                    <div class="room-details">
                        <p><strong>Building:</strong> <?= htmlspecialchars($room['building']) ?></p>
                        <p><strong>Type:</strong> <?= htmlspecialchars($room['room_type']) ?></p>
                        <p><strong>Capacity:</strong> N/A</p> <!-- Placeholder, update if you add capacity to your DB -->
                        <p><strong>Facilities:</strong> N/A</p> <!-- Placeholder, update if you add facilities to your DB -->
                    </div>
                    <div class="room-schedule">
                        <h4>Today's Schedule</h4>
                        <div class="schedule-item">
                            <span>N/A</span>
                            <span class="available">Available</span>
                        </div>
                    </div>
                    <div class="room-actions">
                        <button class="btn-edit" onclick="showEditRoomModal('<?= htmlspecialchars($room['room_number']) ?>')">Edit Room</button>
                        <button class="btn-delete" onclick="deleteRoom('<?= htmlspecialchars($room['room_number']) ?>')">Delete Room</button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </main>

        <!-- Users View -->
        <main class="main-content" id="usersView" style="display: none;">
            <div class="users-header">
                <h1>Manage Users</h1>
                <button class="btn-add-user" onclick="showModal('addUserModal')">Add New User</button>
            </div>
            <div class="table-controls">
                <div class="search-box">
                    <input type="text" placeholder="Search users...">
                </div>
                <div class="filter-box">
                    <select>
                        <option value="">All Roles</option>
                        <option value="Student">Student</option>
                        <option value="Teacher">Teacher</option>
                    </select>
                    <select>
                        <option value="">All Programs</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSIT">BSIT</option>
                    </select>
                    <select>
                        <option value="">All Departments</option>
                        <option value="CCIS">CCIS</option>
                        <option value="BSCS">BSCS</option>
                    </select>
                </div>
            </div>

            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Contact No.</th>
                            <th>Role</th>
                            <th>Program</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $user_search = isset($_GET['user_search']) ? trim($_GET['user_search']) : '';
                        $role_filter = isset($_GET['role_filter']) ? $_GET['role_filter'] : '';
                        $program_filter = isset($_GET['program_filter']) ? $_GET['program_filter'] : '';
                        $department_filter = isset($_GET['department_filter']) ? $_GET['department_filter'] : '';

                        $where = [];
                        $params = [];

                        if ($user_search !== '') {
                            $where[] = "(user_id LIKE :search OR full_name LIKE :search OR email LIKE :search OR contact_num LIKE :search)";
                            $params['search'] = "%$user_search%";
                        }
                        if ($role_filter !== '') {
                            $where[] = "user_role = :role";
                            $params['role'] = $role_filter;
                        }
                        if ($program_filter !== '') {
                            $where[] = "program = :program";
                            $params['program'] = $program_filter;
                        }
                        if ($department_filter !== '') {
                            $where[] = "department = :department";
                            $params['department'] = $department_filter;
                        }

                        $sql = "SELECT * FROM user";
                        if ($where) {
                            $sql .= " WHERE " . implode(" AND ", $where);
                        }
                        $sql .= " ORDER BY full_name";

                        $stmt = $conn->prepare($sql);
                        $stmt->execute($params);

                        while ($user = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id']) ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['contact_num']) ?></td>
                            <td><?= htmlspecialchars($user['user_role']) ?></td>
                            <td><?= htmlspecialchars($user['program'] ?: 'N/A') ?></td>
                            <td><?= htmlspecialchars($user['department']) ?></td>
                            <td class="action-buttons">
                                <button class="btn-edit" onclick="editUser('<?= htmlspecialchars($user['user_id']) ?>')">Edit</button>
                                <button class="btn-delete" onclick="deleteUser('<?= htmlspecialchars($user['user_id']) ?>')">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add Room Modal -->
    <div class="modal" id="addRoomModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Room</h2>
                <button type="button" class="close-btn" onclick="hideModal('addRoomModal')">&times;</button>
            </div>
            <form class="modal-form" id="addRoomForm" method="post" action="process_room.php">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" name="room_number" required pattern="[NSEW][0-9]{3}[A-Za-z]?" 
                           title="Format: N101, S501A, etc. (Building initial + 3 digits + optional letter)">
                </div>

                <div class="form-group">
                    <label>Building</label>
                    <select name="building" required>
                        <option value="">Select Building</option>
                        <option value="South">South</option>
                        <option value="West">West</option>
                        <option value="East">East</option>
                        <option value="North">North</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Room Type</label>
                    <select name="room_type" required>
                        <option value="">Select Room Type</option>
                        <option value="Laboratory">Laboratory</option>
                        <option value="Classroom">Classroom</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideModal('addRoomModal')">Cancel</button>
                    <button type="submit" class="btn-save">Save Room</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal" id="addUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <button type="button" class="close-btn" onclick="hideModal('addUserModal')">&times;</button>
            </div>
            <form class="modal-form" id="addUserForm" method="post" action="process_user.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>User ID</label>
                    <input type="text" name="user_id" required pattern="\d{4}-\d{5}-[A-Z]{2}-\d" 
                           title="Format: YYYY-XXXXX-XX-X (e.g., 2023-00001-MN-0)" class="form-control"
                           placeholder="Enter user ID">
                </div>
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="Enter full name" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter email address" class="form-control">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="tel" name="contact_num" pattern="[0-9]{11}" 
                               title="Please enter a valid 11-digit phone number" required 
                               placeholder="Enter 11-digit number" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <select name="user_role" required class="form-control">
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty</option>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Program</label>
                    <select name="program" class="form-control">
                        <option value="">Select Program</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSIT">BSIT</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Department</label>
                    <select name="department" required class="form-control">
                        <option value="">Select Department</option>
                        <option value="engineering">College of Engineering</option>
                        <option value="science">College of Science</option>
                        <option value="education">College of Education</option>
                        <option value="business">College of Business</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required 
                               placeholder="Enter password" minlength="8" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required 
                               placeholder="Confirm password" minlength="8" class="form-control">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn-save">Save User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Reservation Modal -->
    <div class="modal" id="editReservationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Reservation</h2>
                <button type="button" class="close-btn" onclick="hideModal('editReservationModal')">&times;</button>
            </div>
            <form class="modal-form" id="editReservationForm" method="post" action="process_reservation.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="reservation_id" id="edit_reservation_id">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label>Room Number</label>
                    <select name="room_number" id="edit_room_number" required>
                        <?php
                        $room_sql = "SELECT room_number, building FROM room ORDER BY building, room_number";
                        $room_stmt = $conn->query($room_sql);
                        while ($room = $room_stmt->fetch()):
                        ?>
                        <option value="<?= htmlspecialchars($room['room_number']) ?>">
                            <?= htmlspecialchars($room['building'] . ' - Room ' . $room['room_number']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" id="edit_date" required>
                </div>

                <div class="form-group">
                    <label>Purpose of Reservation</label>
                    <input type="text" name="purpose" id="edit_purpose" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" id="edit_start_time" required>
                    </div>

                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" id="edit_end_time" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Number of Attendees</label>
                    <input type="number" name="expected_num_people" id="edit_num_attendees" min="1" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideModal('editReservationModal')">Cancel</button>
                    <button type="submit" class="btn-save">Update Reservation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal" id="editRoomModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Room</h2>
                <button type="button" class="close-btn" onclick="hideModal('editRoomModal')">&times;</button>
            </div>
            <form class="modal-form" id="editRoomForm">
                <input type="hidden" name="action" value="edit">
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" name="room_number" id="edit_room_number_modal" required readonly>
                </div>
                <div class="form-group">
                    <label>Building</label>
                    <select name="building" id="edit_building" required>
                        <option value="South">South</option>
                        <option value="West">West</option>
                        <option value="East">East</option>
                        <option value="North">North</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room Type</label>
                    <select name="room_type" id="edit_room_type" required>
                        <option value="Laboratory">Laboratory</option>
                        <option value="Classroom">Classroom</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status" required>
                        <option value="Available">Available</option>
                        <option value="Maintenance">Under Maintenance</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideModal('editRoomModal')">Cancel</button>
                    <button type="submit" class="btn-save">Update Room</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal" id="editUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <button type="button" class="close-btn" onclick="hideModal('editUserModal')">&times;</button>
            </div>
            <form class="modal-form" id="editUserForm" method="post" action="process_user.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="original_user_id" id="edit_original_user_id">
                
                <div class="form-group">
                    <label>User ID</label>
                    <input type="text" name="user_id" id="edit_user_id_modal" required pattern="\d{4}-\d{5}-[A-Z]{2}-\d" 
                           title="Format: YYYY-XXXXX-XX-X (e.g., 2023-00001-MN-0)">
                </div>
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit_full_name" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="tel" name="contact_num" id="edit_contact_num" pattern="[0-9]{11}" 
                           title="Please enter a valid 11-digit phone number" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Role</label>
                        <select name="user_role" id="edit_user_role" required>
                            <option value="Student">Student</option>
                            <option value="Teacher">Teacher</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Program</label>
                        <select name="program" id="edit_program">
                            <option value="">N/A</option>
                            <option value="BSCS">BSCS</option>
                            <option value="BSIT">BSIT</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Department</label>
                    <select name="department" id="edit_department" required>
                        <option value="CCIS">CCIS</option>
                        <option value="BSCS">BSCS</option>
                    </select>
                </div>
                
                <div class="student-fields">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Block</label>
                            <input type="number" name="block" id="edit_block" min="1" max="5">
                        </div>
                        
                        <div class="form-group">
                            <label>Section</label>
                            <input type="number" name="section" id="edit_section" min="1" max="10">
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideModal('editUserModal')">Cancel</button>
                    <button type="submit" class="btn-save">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Reservation Modal -->
    <div class="modal" id="addReservationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Reservation</h2>
                <button type="button" class="close-btn" onclick="hideModal('addReservationModal')">&times;</button>
            </div>
            <form class="modal-form" id="addReservationForm">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>User</label>
                    <select name="user_id" required>
                        <option value="">Select User</option>
                        <?php
                        $modal_user_sql = "SELECT user_id, full_name FROM user ORDER BY full_name";
                        $modal_user_stmt = $conn->query($modal_user_sql);
                        while ($modal_user = $modal_user_stmt->fetch()):
                        ?>
                        <option value="<?= htmlspecialchars($modal_user['user_id']) ?>">
                            <?= htmlspecialchars($modal_user['full_name']) ?> (<?= htmlspecialchars($modal_user['user_id']) ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Room Number</label>
                    <select name="room_number" id="room_number" required>
                        <?php
                        $room_sql = "SELECT room_number, building, room_type FROM room ORDER BY building, room_number";
                        $room_stmt = $conn->query($room_sql);
                        while ($room = $room_stmt->fetch()):
                        ?>
                        <option value="<?= htmlspecialchars($room['room_number']) ?>" data-type="<?= htmlspecialchars($room['room_type']) ?>">
                            <?= htmlspecialchars($room['building'] . ' - Room ' . $room['room_number']) ?> (<?= htmlspecialchars($room['room_type']) ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" required>
                </div>

                <div class="form-group">
                    <label>Purpose of Reservation</label>
                    <input type="text" name="purpose" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" required>
                    </div>

                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Number of Attendees</label>
                    <input type="number" name="expected_num_people" min="1" required>
                </div>

                <div class="form-group">
                    <label>Particulars</label>
                    <div class="particulars-container">
                        <?php
                        $particulars_sql = "SELECT * FROM particular ORDER BY particulars";
                        $particulars_stmt = $conn->query($particulars_sql);
                        while ($particular = $particulars_stmt->fetch()):
                        ?>
                        <div class="particular-item">
                            <label>
                                <input type="checkbox" name="particulars[]" value="<?= htmlspecialchars($particular['particulars_code']) ?>">
                                <?= htmlspecialchars($particular['particulars']) ?>
                            </label>
                            <input type="number" name="quantity[<?= htmlspecialchars($particular['particulars_code']) ?>]" 
                                   class="quantity-input" min="1" placeholder="Qty" disabled>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideModal('addReservationModal')">Cancel</button>
                    <button type="submit" class="btn-save">Add Reservation</button>
                </div>
            </form>
        </div>
    </div>

    <script src="admin.js"></script>
</body>
</html>

