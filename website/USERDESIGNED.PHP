<?php
session_start();

// STEP 3: Reading session data

if (isset($_SESSION['mode'])) {
    // Initialize these variables with default values
    $fullname = $_SESSION['name'] ?? 'Guest';
    $email = $_SESSION['email'] ?? 'No email provided';
    $user_id = $_SESSION['user_id'] ?? '';
    $user_role = $_SESSION['user_role'] ?? '';
    $contact = $_SESSION['contact'] ?? '';
    $program = $_SESSION['program'] ?? '';
    $block = $_SESSION['block'] ?? '';
    $section = $_SESSION['section'] ?? '';

    if ($_SESSION['mode'] === 'guest') {
        // echo "<h2>Welcome Guest: $fullname</h2>";
        // echo "<p>ID: $user_id</p>";
        // echo "<p>Email: $email</p>";
        // echo "<p>Contact: $contact</p>";
        // echo "<p>Program: $program, Block: $block, Section: $section</p>";

    } elseif ($_SESSION['mode'] === 'login') {
        // echo "<h2>Welcome back $user_id (Logged In)</h2>";
    } elseif ($_SESSION['mode'] === 'create') {
        // echo "<h2>Account created for $user_id</h2>";
    }

} else {
    // No session: Redirect back to signup
    header("Location: ../main/signup.php");
    exit();
}

try {
    require_once __DIR__ . '/../config/db_connect.php';

    // Initialize variables with default values
    $total_count = 0;
    $current_count = 0;

    // Fetch reservation statistics
    $user_id = $_SESSION['user_id'];

    // Total bookings (all time)
    $total_sql = "SELECT COUNT(*) as total_count 
                  FROM reservation 
                  WHERE user_id = ?";
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->execute([$user_id]);
    $total_count = $total_stmt->fetch(PDO::FETCH_ASSOC)['total_count'];

    // Current/Future bookings
    $current_sql = "SELECT COUNT(*) as current_count 
                    FROM reservation 
                    WHERE user_id = ? 
                    AND date >= CURDATE()";
    $current_stmt = $conn->prepare($current_sql);
    $current_stmt->execute([$user_id]);
    $current_count = $current_stmt->fetch(PDO::FETCH_ASSOC)['current_count'];
} catch (PDOException $e) {
    // Log the error but don't show it to users
    error_log("Database error: " . $e->getMessage());
    // Set default values
    $total_count = 0;
    $current_count = 0;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>usermain</title>
  <link rel="stylesheet" href="../users/userstyle.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
  <div class="wrapper">
    <!-- side nav bar -->
    <header>
      <div class="sidebar-container">
        <div class="sidebar">
          <img src="../images/pupwestologo.png" alt="pup-logo" id="pup-logo">
          <ul>
            <li><img src="../images/home-icon.png" alt="home-icon" class="nav-icon nav-target" id="home-icon"></li>
            <li><img src="../images/new-icon.png" alt="book-icon" class="nav-icon nav-target" id="book-icon"></li>
            <li><img src="../images/request-icon.png" alt="history-icon" class="nav-icon nav-target" id="history-icon"></li>
            <li><img src="../images/message-icon.png" alt="message-icon" class="nav-icon nav-target" id="message-icon"></li>
          </ul>
        </div>
        <div class="sidebar-labels">
          <ul>
            <li id="home-label" class="nav-target">Home</li>
            <li id="book-label" class="nav-target">Book</li>
            <li id="history-label" class="nav-target">History</li>
            <li id="message-label" class="nav-target">Message</li>
          </ul>
        </div>
      </div>
    
    </header>


    <main>
      <!-- header -->
      <div class="header-section">
        <div class="title-header">
          <h2>PUP Online Laboratory/Classroom Booking System</h2>
          <p>Your go-to platform for hassle-free campus space reservations</p>
        </div>


        <div class="profile-header">
          <div class="profile-name">
            <p><?php echo htmlspecialchars($fullname); ?></p>
            <p><?php echo htmlspecialchars($email); ?></p>
          </div>
          <img src="../images/profile-icon.png" alt="profile-icon" class="nav-target" id="profile-icon">
        </div>
      </div>


      <div class="main-wrapper">
        <div class="main-container">
          <h1>Welcome <?php echo htmlspecialchars($fullname); ?>!</h1>
          <div class="welcome-message">
            <p>Welcome to the PUP Online Laboratory/Classroom Booking System. We're here to help you manage your space reservations efficiently.</p>
          </div>

          <div class="quick-stats">
            <div class="stat-card">
              <i class="fas fa-calendar-check"></i>
              <h3>Total Bookings</h3>
              <p><?php echo htmlspecialchars($total_count); ?></p>
            </div>
            <div class="stat-card">
              <i class="fas fa-calendar-alt"></i>
              <h3>Current/Future Bookings</h3>
              <p><?php echo htmlspecialchars($current_count); ?></p>
            </div>
          </div>

          <div class="guidelines-section">
            <h3><i class="fas fa-info-circle"></i> Guidelines</h3>
            <div class="guidelines-content">
              <div class="guideline-item">
                <i class="fas fa-clock"></i>
                <div>
                  <h4>Booking Time</h4>
                  <p>Reservations can be made up to 7 days in advance. Each booking slot is limited to 4 hours maximum.</p>
                </div>
              </div>
              
              <div class="guideline-item">
                <i class="fas fa-users"></i>
                <div>
                  <h4>Group Size</h4>
                  <p>Please ensure the number of attendees matches the room capacity. Maximum capacity varies by room type.</p>
                </div>
              </div>

              <div class="guideline-item">
                <i class="fas fa-clipboard-check"></i>
                <div>
                  <h4>Requirements</h4>
                  <p>Always bring your PUP ID and reservation confirmation. Equipment requests must be made during booking.</p>
                </div>
              </div>

              <div class="guideline-item">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                  <h4>Cancellation Policy</h4>
                  <p>Cancellations must be made at least 24 hours before the scheduled time. Late cancellations may affect future bookings.</p>
                </div>
              </div>
            </div>
          </div>
        </div>


        <div class="new-request-container">
          <div class="new-request-wrapper">    
            <div class="container-title">
              <h2>New Request</h2>
              <img src="../images/plus-icon.png" alt="plus-icon">
            </div>


            <form id="filter-form">
              <div class="filter-title">
                <h4>Reservation Details</h4>
                <button class="filter-button" type="submit" id="filter-btn">Filter</button>
              </div>


              <div class="row-div">
                <label for="selectdate">Date: </label>
                <input type="date" name="selectdate" id="selectdate" placeholder="select date" required>


                <label for="starttime">Start Time: </label>
                <input type="time" name="starttime" id="starttime" placeholder="select time" required min="00:00" max="23:59">


                <label for="endtime">End Time: </label>
                <input type="time" name="endtime" id="endtime" placeholder="select time" required min="00:00" max="23:59">
              </div>


              <div class="row-div">
                <label for="building-option">Building: </label>
                <select name="building-option" id="building-option" required>
                  <option value="" disabled selected>select building</option>
                  <option value="North">North</option>
                  <option value="South">South</option>
                  <option value="East">East</option>
                  <option value="West">West</option>
                </select>


                <label for="room-type-option">Type: </label>
                <select name="room-type-option" id="room-type-option" required>
                  <option value="" disabled selected>select room type</option>          
                  <option value="Laboratory">Laboratory</option>
                  <option value="Classroom">Lecture Room</option>
                </select>
              </div>
            </form>


            <div class="room-container">
              <div class="form-title">
                <h4>Available Rooms </h4>
                <p><i>Choose your preferred room</i></p>
              </div>


              <div class="room-lists" id="room-lists">
                <!-- Rooms will be dynamically added here -->
              </div>
            </div>


            <div class="order-detail-container">
              <div class="modal">
                <div class="order-detail-wrapper">
                  <div class="form-title">
                    <h2>Additional Details</h2>
                    <p>Fill up additional details</p>
                  </div>

                  <input type="hidden" id="selected-room" name="selected-room">
                  
                  <form action="submit_reservation.php" method="POST">
                    <div class="col-div">
                      <div class="row-div">
                        <label for="purpose-detail">Purpose of Reservation: </label>
                        <input type="text" name="purpose-detail" id="purpose-detail" required>
                      </div>


                      <div class="row-div">
                        <label for="prof-in-charge">Professor in Charge: </label>
                        <input type="text" name="prof-in-charge" id="prof-in-charge" required>
                      </div>


                      <div class="row-div">
                        <label for="attendees">Expected Attendees: </label>
                        <input type="number" name="attendees" id="attendees" required>
                      </div>


                      <div class="row-div">
                        <label for="particulars-detail">Particulars</label>
                        <div id="particulars-container">
                          <div class="particular-item">
                          <select name="particulars[]" class="particulars-detail-select">
                              <option value="" selected disabled>Select Particulars</option>
                              <option value="LT1">LED TV</option>
                              <option value="VH2">VGA/HDMI</option>
                              <option value="PJ3">Projector</option>
                              <option value="ET4">Extension</option>
                            </select>
                            <input type="number" name="quantities[]" class="particulars-quantity-input" placeholder="Qty" min="1" value="1">
                          </div>
                        </div>
                        <button type="button" id="add-particular-btn">Add Particular</button>
                      </div>
                    
                    <div class="row-div">
                      <button class="back-btn" type="button">Back</button>
                      <button class="confirm-button" type="submit">Confirm</button>
                    </div>          
                  </div>          
                  </form>
                </div>
              </div>
            </div>
          </div>  
          
        
          <div class="summary-container">
              <div class="reservation-summary" id="reservation-summary-display">
                <!-- All content within this div will be dynamically generated by JavaScript -->
              </div>
          </div>      
        </div>


        <div class="request-container">
          <div class="col-div">
            <h2>Your Requests</h2>
            <hr>
          </div>


          <div class="table-container">
            <table class="reservation-table">
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
                </tr>
              </thead>
              <tbody> 

                <?php
                  $sql = "
                      SELECT 
                          r.reservation_id, 
                          r.room_number,
                          r.date, 
                          r.user_id, 
                          r.start_time, 
                          r.end_time, 
                          rm.room_type, 
                          r.expected_num_people, 
                          r.professor_in_charge, 
                          r.purpose_of_reservation,
                          GROUP_CONCAT(CONCAT(p.particulars, ' (Qty: ', q.quantity, ')') SEPARATOR ', ') AS particulars_with_quantity,
                          u.full_name, 
                          u.contact_num
                      FROM reservation r
                      LEFT JOIN room rm ON r.room_number = rm.room_number
                      LEFT JOIN user u ON r.user_id = u.user_id
                      LEFT JOIN quantity q ON r.reservation_id = q.reservation_id
                      LEFT JOIN particular p ON q.particulars_code = p.particulars_code
                      WHERE r.user_id = ?
                      GROUP BY 
                          r.reservation_id";

                  // Prepare & bind
                  $user_id = $_SESSION['user_id'];
                  $stmt = $conn->prepare($sql);
                  $stmt->execute([$user_id]);

                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                  ?>
                      <tr>
                          <td><?= htmlspecialchars($row['reservation_id']) ?></td>
                          <td><?= htmlspecialchars($row['room_number']) ?></td>
                          <td><?= htmlspecialchars($row['room_type'] ?? 'N/A') ?></td>
                          <td><?= htmlspecialchars($row['purpose_of_reservation'] ?? 'N/A') ?></td>
                          <td><?= htmlspecialchars($row['full_name'] ?? 'N/A') ?></td>
                          <td><?= htmlspecialchars($row['contact_num'] ?? 'N/A') ?></td>
                          <td><?= htmlspecialchars($row['date']) ?></td>
                          <td><?= htmlspecialchars($row['start_time']) ?> - <?= htmlspecialchars($row['end_time']) ?></td>
                          <td><?= htmlspecialchars($row['expected_num_people']) ?></td>
                          <td><?= htmlspecialchars($row['particulars_with_quantity'] ?? 'N/A') ?></td>
                      </tr>
                  <?php endwhile; ?>

             </tbody>
            </table>
          </div>
          <button class="new-button nav-target" id="new-button">Add New</button>
        </div>


        <div class="message-container">
          <div class="col-div">
            <h2>Notifications</h2>
            <hr>
          </div>


          <div class="notification-panel">
            <div class="message-instance">
              Good Morning ____, <br>
              Your booking request, Reservation ID, has been approved/declined.<br>
              Thank you for using the program!
            </div>
          </div>
        </div>


        <!-- profile container -->
        <div class="profile-container">
          <div class="profile-wrapper">
          
            <div class="profile-wrapper2">


              <div class="row-div">
                <h6>User Profile</h6>
                <img src="" alt="edit-icon">
              </div>


              <img src="" alt="profile" id="profile-pic">


              <div class="row-div">
                <form action="">
                  <label for="user-name"></label>
                  <input type="text" name="user-name" id="user-name">
                </form>
                <img src="" alt="edit-icon">
              </div>


            </div>


            <div class="form-wrapper">
              <form action="">


                  <div class="col-div">
                    <label for="user-id">User ID</label>
                    <input type="text" name="user-id" id="user-id">
                  </div>


                  <div class="row-div">
                    <div class="col-div">
                      <label for="user-program">Program</label>
                      <input type="text" name="user-program" id="user-program">
                    </div>


                    <div class="col-div">
                      <label for="user-year">Year</label>
                      <input type="text" name="user-year" id="user-year">
                    </div>


                    <div class="col-div">
                      <label for="user-block">Block</label>
                      <input type="text" name="user-block" id="user-block">
                    </div>
                  </div>


                  <div class="row-div">
                    <div class="col-div">
                      <label for="user-contact">Contact</label>
                      <input type="number" name="user-contact" id="user-contact">
                    </div>


                    <div class="col-div">
                      <label for="user-email">E-mail</label>
                      <input type="email" name="user-email" id="user-email">
                    </div>      
                  </div>
              
                  <label for="user-department">Department</label>
                  <input type="text" name="user-department" id="user-department">


                  <div class="button-row-div">
                    <button class="edit-btn">Edit</button>
                    <button class="save-btn">Save</button>
                  </div>                  
              </form>        
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>  

  <script src="../users/script.js"></script>
  <script>
// PHP variables passed to JavaScript
const USER_FULLNAME = "<?php echo htmlspecialchars($fullname); ?>";
const USER_EMAIL = "<?php echo htmlspecialchars($email); ?>";
const USER_CONTACT = "<?php echo htmlspecialchars($contact); ?>";
const USER_PROGRAM = "<?php echo htmlspecialchars($program); ?>";
const USER_BLOCK = "<?php echo htmlspecialchars($block); ?>";
const USER_SECTION = "<?php echo htmlspecialchars($section); ?>";
const USER_ROLE = "<?php echo htmlspecialchars($_SESSION['user_role'] ?? 'N/A'); ?>"; // Added USER_ROLE and fallback

// Particulars dynamic addition
document.addEventListener('DOMContentLoaded', function() {
    const addParticularBtn = document.getElementById('add-particular-btn');
    const particularsContainer = document.getElementById('particulars-container');

    if (addParticularBtn && particularsContainer) {
        addParticularBtn.addEventListener('click', function() {
            const newParticularItem = document.createElement('div');
            newParticularItem.className = 'particular-item';
            newParticularItem.innerHTML = `
                <select name="particulars[]" class="particulars-detail-select">
                    <option value="" selected disabled>Select Particulars</option>
                    <option value="LT1">LED TV</option>
                    <option value="VH2">VGA/HDMI</option>
                    <option value="PJ3">Projector</option>
                    <option value="ET4">Extension</option>
                </select>
                <input type="number" name="quantities[]" class="particulars-quantity-input" placeholder="Qty" min="1" value="1">
                <button type="button" class="remove-particular-btn"><i class="fas fa-times"></i></button>
            `;
            particularsContainer.appendChild(newParticularItem);
        });

        // Event listener for removing particular items (delegation)
        particularsContainer.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-particular-btn') || event.target.closest('.remove-particular-btn')) {
                const button = event.target.closest('.remove-particular-btn');
                if (button && button.parentNode) {
                    button.parentNode.remove();
                }
            }
        });
    }
});

document.getElementById('filter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Log the form data being sent
    console.log('Sending form data:', Object.fromEntries(formData));
    
    fetch('filter_rooms.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.error || 'Error fetching rooms');
            });
        }
        return response.json();
    })
    .then(rooms => {
        console.log('Received rooms:', rooms); // Debug log
        const roomLists = document.getElementById('room-lists');
        roomLists.innerHTML = ''; // Clear existing rooms
        
        if (rooms.length === 0) {
            roomLists.innerHTML = '<p class="no-rooms">No available rooms found for the selected criteria.</p>';
            return;
        }
        
        rooms.forEach(room => {
            const roomDiv = document.createElement('div');
            roomDiv.className = 'room-card';

            // Room status badge color
            let statusColor = 'gray';
            if (room.status && room.status.toLowerCase() === 'available') statusColor = 'green';
            else if (room.status && room.status.toLowerCase() === 'booked') statusColor = 'red';

            const roomNumber = room.room_number || 'N/A';
            const building = room.building || 'N/A';
            const type = room.room_type || 'N/A';
            const status = room.status || 'N/A';

            roomDiv.innerHTML = `
                <div class="room-header">
                    <div class="room-num-main">${roomNumber}</div>
                    <span class="room-status-badge" style="background:${statusColor}">${status}</span>
                </div>
                <div class="room-info">
                    <div class="info-item">
                        <i class="fas fa-building"></i>
                        <span class="building-name">${building}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-door-open"></i>
                        <span class="room-type-name">${type}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-info-circle"></i>
                        <span class="room-status-label">Status: <span class="room-status-text" style="color:${statusColor}">${status}</span></span>
                    </div>
                </div>
                <button class="select-room-btn" data-room="${roomNumber}">Select Room</button>
            `;
            roomLists.appendChild(roomDiv);
        });
        
        // Add event listeners to select room buttons
        document.querySelectorAll('.select-room-btn').forEach(button => {
            button.addEventListener('click', function() {
                const selectedRoom = this.getAttribute('data-room');
                if (selectedRoom && selectedRoom !== 'N/A') {
                    document.getElementById('selected-room').value = selectedRoom;
                    // Show the additional details form
                    document.querySelector('.order-detail-container').style.display = 'block';
                }
            });
        });
    })
    .catch(error => {
        console.error('Error:', error);
        const roomLists = document.getElementById('room-lists');
        roomLists.innerHTML = `<p class="error-message">Error: ${error.message}</p>`;
    });
});

// Restore CSS for card layout
const style = document.createElement('style');
style.textContent = `
    .room-lists {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px;
    }
    .room-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        padding: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-width: 0;
        word-break: break-word;
    }
    .room-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .room-header {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        min-width: 0;
    }
    .room-num-main {
        font-size: 1.5em;
        font-weight: bold;
        color: #800000;
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .room-status-badge {
        color: white;
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 0.95em;
        font-weight: bold;
        text-transform: capitalize;
        margin-left: 10px;
        flex-shrink: 0;
    }
    .room-info {
        margin-bottom: 20px;
        width: 100%;
        min-width: 0;
    }
    .info-item {
        display: flex;
        align-items: center;
        margin: 10px 0;
        color: #666;
        font-size: 1em;
        min-width: 0;
    }
    .info-item i {
        margin-right: 10px;
        color: #800000;
        width: 20px;
        flex-shrink: 0;
    }
    .building-name, .room-type-name {
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
        vertical-align: bottom;
    }
    .room-status-label {
        font-weight: 500;
    }
    .room-status-text {
        font-weight: bold;
        text-transform: capitalize;
    }
    .select-room-btn {
        width: 100%;
        padding: 10px;
        background: #800000;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.2s;
        font-size: 1em;
    }
    .select-room-btn:hover {
        background: #600000;
    }
    .no-rooms {
        text-align: center;
        color: #666;
        padding: 20px;
        grid-column: 1 / -1;
    }
    .error-message {
        text-align: center;
        color: #dc3545;
        padding: 20px;
        grid-column: 1 / -1;
    }
`;
document.head.appendChild(style);

// Helper: Get value by id
function getVal(id) {
    const el = document.getElementById(id);
    console.log(`Getting value for ID: ${id}, Element found: ${!!el}, Value: ${el ? el.value : 'N/A'}`);
    return el ? el.value : '';
}

// Show summary before final confirmation
function showReservationSummary() {
    console.log('showReservationSummary called');
    // Get selected room and filter details
    const roomNumber = getVal('selected-room');
    const date = getVal('selectdate');
    const startTime = getVal('starttime');
    const endTime = getVal('endtime');
    const building = getVal('building-option');
    const type = getVal('room-type-option');
    // Additional details
    const purpose = getVal('purpose-detail');
    const professor = getVal('prof-in-charge');
    const attendees = getVal('attendees');
    
    // Collect all particulars and quantities
    const particularsElements = document.querySelectorAll('.particulars-detail-select');
    const quantitiesElements = document.querySelectorAll('.particulars-quantity-input');
    let particularsDetails = [];

    particularsElements.forEach((select, index) => {
        const particularValue = select.value;
        const particularText = select.options[select.selectedIndex].text;
        const quantity = quantitiesElements[index] ? quantitiesElements[index].value : '0';
        if (particularValue && particularValue !== '' && particularValue !== 'Select Particulars' && quantity > 0) {
            particularsDetails.push({ text: particularText, quantity: quantity });
        }
    });

    let particularsHtml = '';
    if (particularsDetails.length > 0) {
        particularsHtml = particularsDetails.map(p => `<div>${p.text}</div><div>${p.quantity}</div>`).join('');
    } else {
        particularsHtml = '<div>N/A</div><div></div>';
    }

    const reservationSummaryDisplay = document.getElementById('reservation-summary-display');
    console.log('reservationSummaryDisplay element:', reservationSummaryDisplay);

    if (reservationSummaryDisplay) {
        console.log('Populating reservationSummaryDisplay innerHTML');
        reservationSummaryDisplay.innerHTML = `
            <div class="col-div">
                <h2>Reservation Summary</h2>
                <hr class="divider">
            </div>
            <div class="summary-section">
                <h3>Personal Information</h3>
                <div class="info-grid">
                    <div><strong>Name:</strong></div><div>${USER_FULLNAME || 'N/A'}</div>
                    <div><strong>Yr & Sec:</strong></div><div>${USER_PROGRAM || 'N/A'} ${USER_BLOCK || 'N/A'} ${USER_SECTION || 'N/A'}</div>
                    <div><strong>Type:</strong></div><div>${USER_ROLE || 'N/A'}</div>
                    <div><strong>Contact No.:</strong></div><div>${USER_CONTACT || 'N/A'}</div>
                    <div><strong>Gmail:</strong></div><div>${USER_EMAIL || 'N/A'}</div>
                </div>
            </div>
            <div class="summary-section">
                <h3>Reservation Details</h3>
                <div class="info-grid">
                    <div><strong>Room Number:</strong></div><div>${roomNumber}</div>
                    <div><strong>Building:</strong></div><div>${building}</div>
                    <div><strong>Room Type:</strong></div><div>${type}</div>
                    <div><strong>Date:</strong></div><div>${date}</div>
                    <div><strong>Time:</strong></div><div>${startTime} - ${endTime}</div>
                    <div><strong>Purpose:</strong></div><div>${purpose}</div>
                    <div><strong>No. of Attendees:</strong></div><div>${attendees}</div>
                    <div><strong>Professor-in-charge:</strong></div><div>${professor}</div>
                </div>
            </div>
            <div class="summary-section">
                <h3>Particulars Details</h3>
                <div class="particulars-header">
                    <div><strong>Particulars</strong></div>
                    <div><strong>Qty</strong></div>
                </div>        
                ${particularsHtml}
            </div>
            <div class="button-group">
                <button class="back-btn" id="summary-back-btn">Back</button>
                <button class="confirm-btn" id="final-submit-btn">Submit Reservation</button>
            </div>
        `;
        // Show summary, hide form
        document.querySelector('.order-detail-container').style.display = 'none';
        const summaryContainer = document.querySelector('.summary-container');
        if (summaryContainer) {
            summaryContainer.style.display = 'flex'; // Use flex to center the modal
            console.log('Summary container display set to flex');
        } else {
            console.error('Summary container not found!');
        }

        // Add event listeners for back and submit
        document.getElementById('summary-back-btn').onclick = function() {
            document.querySelector('.summary-container').style.display = 'none';
            document.querySelector('.order-detail-container').style.display = 'flex'; // Use flex to show the modal
        };
        document.getElementById('final-submit-btn').onclick = function() {
            submitReservationToBackend();
        };
    }
}

// Show summary AFTER final confirmation
function showFinalReservationSummary(bookingData) {
    console.log('showFinalReservationSummary called with bookingData:', bookingData);
    const reservationSummaryDisplay = document.getElementById('reservation-summary-display');
    console.log('reservationSummaryDisplay element (final):', reservationSummaryDisplay);
    
    let finalParticularsHtml = '';
    if (bookingData.particulars && bookingData.particulars.length > 0) {
        finalParticularsHtml = `<div class="particulars-header"><div><strong>Particulars</strong></div><div><strong>Qty</strong></div></div>`;
        finalParticularsHtml += bookingData.particulars.map(p => `<div class="particulars-row"><div>${p.text}</div><div>${p.quantity}</div></div>`).join('');
    } else {
        finalParticularsHtml = '<div class="particulars-row"><div>N/A</div><div></div></div>';
    }

    if (reservationSummaryDisplay) {
        console.log('Populating final reservationSummaryDisplay innerHTML');
        reservationSummaryDisplay.innerHTML = `
            <div class="col-div">
                <h2>Reservation Confirmed!</h2>
                <hr class="divider">
            </div>
            <div class="summary-section">
                <h3>Personal Information</h3>
                <div class="info-grid">
                    <div><strong>Name:</strong></div><div>${USER_FULLNAME || 'N/A'}</div>
                    <div><strong>Yr & Sec:</strong></div><div>${USER_PROGRAM || 'N/A'} ${USER_BLOCK || 'N/A'} ${USER_SECTION || 'N/A'}</div>
                    <div><strong>Type:</strong></div><div>${USER_ROLE || 'N/A'}</div>
                    <div><strong>Contact No.:</strong></div><div>${USER_CONTACT || 'N/A'}</div>
                    <div><strong>Gmail:</strong></div><div>${USER_EMAIL || 'N/A'}</div>
                </div>
            </div>
            <div class="summary-section">
                <h3>Room Information</h3>
                <div class="info-grid">
                    <div><strong>Room Number:</strong></div><div>${bookingData.room_number || 'N/A'}</div>
                    <div><strong>Building:</strong></div><div>${bookingData.building || 'N/A'}</div>
                    <div><strong>Room Type:</strong></div><div>${bookingData.type || 'N/A'}</div>
                    <div><strong>Date:</strong></div><div>${bookingData.date || 'N/A'}</div>
                    <div><strong>Time:</strong></div><div>${bookingData.start_time || 'N/A'} - ${bookingData.end_time || 'N/A'}</div>
                </div>
            </div>
            <div class="summary-section">
                <h3>Reservation Details</h3>
                <div class="info-grid">
                    <div><strong>Purpose:</strong></div><div>${bookingData.purpose || 'N/A'}</div>
                    <div><strong>Professor-in-charge:</strong></div><div>${bookingData.professor || 'N/A'}</div>
                    <div><strong>No. of Attendees:</strong></div><div>${bookingData.attendees || 'N/A'}</div>
                </div>
            </div>
            <div class="summary-section">
                <h3>Particulars Details</h3>
                ${finalParticularsHtml}
            </div>
            <div class="button-group">
                <button class="confirm-btn" id="new-request-btn">Make Another Request</button>
            </div>
        `;
        document.querySelector('.order-detail-container').style.display = 'none';
        const summaryContainer = document.querySelector('.summary-container');
        if (summaryContainer) {
            summaryContainer.style.display = 'flex'; // Use flex to center the modal
            console.log('Final summary container display set to flex');
        } else {
            console.error('Final summary container not found!');
        }

        document.getElementById('new-request-btn').onclick = function() {
            window.location.reload(); // Reload to start a new request
        };
    }
}

// Intercept the additional details form submit
const detailsForm = document.querySelector('.order-detail-container form');
if (detailsForm) {
    detailsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Additional details form submitted. Calling showReservationSummary.');
        showReservationSummary();
    });
}

// Actually submit to backend
function submitReservationToBackend() {
    console.log('submitReservationToBackend called');
    // Gather all data
    const data = {
        user_id: "<?php echo htmlspecialchars($_SESSION['user_id']); ?>", // Pass user_id
        room_number: getVal('selected-room'),
        date: getVal('selectdate'),
        start_time: getVal('starttime'),
        end_time: getVal('endtime'),
        building: getVal('building-option'), // Pass building and type for record
        type: getVal('room-type-option'),
        purpose: getVal('purpose-detail'),
        professor: getVal('prof-in-charge'),
        attendees: getVal('attendees'),
    };

    // Collect all particulars and quantities for submission
    const particularsElements = document.querySelectorAll('.particulars-detail-select');
    const quantitiesElements = document.querySelectorAll('.particulars-quantity-input');
    let particularsData = [];

    particularsElements.forEach((select, index) => {
        const particularValue = select.value;
        const particularText = select.options[select.selectedIndex].text;
        const quantity = quantitiesElements[index] ? quantitiesElements[index].value : '0';
        if (particularValue && particularValue !== '' && particularValue !== 'Select Particulars' && quantity > 0) {
            particularsData.push({ code: particularValue, text: particularText, quantity: quantity });
        }
    });
    data.particulars = particularsData; // Add particulars data to the main data object

    console.log('Data to be sent to backend:', data);
    // Send via fetch to submit_reservation.php
    fetch('submit_reservation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => {
        console.log('Received response from submit_reservation.php');
        if (!res.ok) {
            console.error('Network response was not ok:', res.status, res.statusText);
            return res.json().then(errorData => {
                throw new Error(errorData.error || 'Server error');
            }).catch(() => {
                throw new Error(`HTTP error! status: ${res.status}`);
            });
        }
        return res.json();
    })
    .then(response => {
        console.log('Response from backend (parsed):', response);
        if (response.success) {
            alert('Reservation successful!');
            // Display final summary with all details
            showFinalReservationSummary(data); // Pass all data to showFinalReservationSummary
        } else {
            alert('Error: ' + (response.error || 'Unknown error'));
        }
    })
    .catch(err => {
        console.error('Error submitting reservation:', err);
        alert('Error submitting reservation: ' + err.message);
    });
}
  </script>

  <style>
  /* Color Variables */
  :root {
      --primary-red: #800000;
      --secondary-red: #A52A2A;
      --light-red: #FFE4E1;
      --primary-gold: #FFD700;
      --secondary-gold: #FFA500;
      --light-gold: #FFF8DC;
      --white: #FFFFFF;
      --gray: #F5F5F5;
      --dark-gray: #333333;
  }

  /* Main Container Styles */
  .main-wrapper {
      flex: 1;
      padding: 1rem;
      overflow-y: auto;
      height: calc(100vh - 60px);
      scrollbar-width: none;
      -ms-overflow-style: none;
      background: var(--gray);
  }

  .main-container {
      padding: 2rem;
      background: var(--white);
      border-radius: 15px;
      max-width: 1200px;
      margin: 0 auto;
      overflow-y: auto;
      max-height: 100%;
      scrollbar-width: none;
      -ms-overflow-style: none;
  }

  /* Welcome Message */
  .welcome-message {
      margin: 1rem 0 2rem;
      padding: 1rem;
      background: var(--light-gold);
      border-radius: 8px;
      border-left: 4px solid var(--primary-gold);
  }

  /* Quick Stats */
  .quick-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
  }

  .stat-card {
      background: var(--white);
      padding: 1.5rem;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 4px 15px rgba(128, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: 2px solid var(--light-gold);
  }

  .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 20px rgba(128, 0, 0, 0.15);
      border-color: var(--primary-gold);
  }

  .stat-card i {
      font-size: 2rem;
      color: var(--primary-red);
      margin-bottom: 1rem;
  }

  .stat-card h3 {
      font-size: 1rem;
      color: var(--dark-gray);
      margin-bottom: 0.5rem;
  }

  .stat-card p {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--primary-red);
  }

  /* Guidelines Section */
  .guidelines-section {
      margin: 2rem 0;
  }

  .guidelines-section h3 {
      color: var(--primary-red);
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
  }

  .guidelines-content {
      display: grid;
      gap: 1rem;
  }

  .guideline-item {
      display: flex;
      gap: 1rem;
      padding: 1rem;
      background: var(--light-gold);
      border-radius: 8px;
      align-items: flex-start;
      border: 1px solid var(--primary-gold);
  }

  .guideline-item i {
      color: var(--primary-red);
      font-size: 1.2rem;
      margin-top: 0.2rem;
  }

  .guideline-item h4 {
      color: var(--primary-red);
      margin-bottom: 0.5rem;
  }

  .guideline-item p {
      color: var(--dark-gray);
      font-size: 0.9rem;
      line-height: 1.4;
  }

  /* Headers and Text */
  h1 {
      color: var(--primary-red);
      margin-bottom: 1rem;
  }

  h2 {
      color: var(--primary-red);
  }

  h3 {
      color: var(--primary-red);
  }

  /* Responsive Design */
  @media (max-width: 768px) {
      .quick-stats {
          grid-template-columns: 1fr;
      }
      
      .guideline-item {
          flex-direction: column;
          text-align: center;
      }
      
      .guideline-item i {
          margin-bottom: 0.5rem;
      }
  }

  /* Add these styles for the profile container */
  .profile-container {
      padding: 2rem;
      background: var(--white);
      border-radius: 15px;
      max-width: 1200px;
      margin: 0 auto;
      overflow-y: auto;
      max-height: 100%;
      scrollbar-width: none;
      -ms-overflow-style: none;
  }

  .profile-wrapper {
      background: var(--white);
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(128, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: 2px solid var(--light-gold);
  }

  .profile-wrapper:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 20px rgba(128, 0, 0, 0.15);
      border-color: var(--primary-gold);
  }

  .profile-wrapper2 {
      background: var(--light-gold);
      padding: 1.5rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      border: 1px solid var(--primary-gold);
  }

  .form-wrapper {
      padding: 1.5rem;
      background: var(--light-gold);
      border-radius: 8px;
      border: 1px solid var(--primary-gold);
  }

  .form-wrapper input {
      background: var(--white);
      border: 1px solid var(--primary-gold);
      border-radius: 5px;
      padding: 0.5rem;
      color: var(--dark-gray);
  }

  .form-wrapper input:focus {
      border-color: var(--primary-red);
      outline: none;
      box-shadow: 0 0 0 2px rgba(128, 0, 0, 0.1);
  }

  .form-wrapper label {
      color: var(--primary-red);
      font-weight: 500;
  }

  .edit-btn, .save-btn {
      background: var(--primary-red);
      color: var(--white);
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s ease;
  }

  .edit-btn:hover, .save-btn:hover {
      background: var(--secondary-red);
  }

  #profile-pic {
      border: 3px solid var(--primary-gold);
      border-radius: 50%;
      transition: transform 0.3s ease;
  }

  #profile-pic:hover {
      transform: scale(1.05);
  }

  .row-div h6 {
      color: var(--primary-red);
      font-size: 1.2rem;
      margin-bottom: 1rem;
  }

  .button-row-div {
      display: flex;
      gap: 1rem;
      margin-top: 1.5rem;
  }

  @media (max-width: 768px) {
      .profile-container {
          padding: 1rem;
      }
      
      .profile-wrapper2,
      .form-wrapper {
          padding: 1rem;
      }
  }
  </style>

</body>
</html>