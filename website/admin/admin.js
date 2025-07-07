// Function to switch between views
function showView(viewName) {
    // Get all main content sections
    const views = ['reservationsView', 'roomsView', 'usersView'];
   
    // Hide all views first
    views.forEach(view => {
        const element = document.getElementById(view);
        if (element) {
            element.style.display = 'none';
        }
    });
   
    // Show the selected view
    const selectedView = document.getElementById(viewName + 'View');
    if (selectedView) {
        selectedView.style.display = 'block';
    }
   
    // Update active menu item
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
    });
    const menuItem = document.querySelector(`.menu-item[onclick*="${viewName}"]`);
    if (menuItem) {
        menuItem.classList.add('active');
    }

    // Reset filters when switching to reservations view
    if (viewName === 'reservations') {
        const reservationSearch = document.querySelector('#reservationsView .search-box input');
        const reservationFilters = document.querySelectorAll('#reservationsView .filter-box select');
        
        // Reset search
        if (reservationSearch) {
            reservationSearch.value = '';
        }
        
        // Reset filters
        reservationFilters.forEach(filter => {
            if (filter) {
                filter.value = '';
            }
        });

        // Log the number of visible reservations
        const visibleReservations = document.querySelectorAll('.reservation-table tbody tr:not([style*="display: none"])');
        console.log('Total visible reservations:', visibleReservations.length);
    }

    // On mobile, close sidebar after selection
    if (window.innerWidth <= 768) {
        toggleSidebar();
    }
}

// Show modal function
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        
        // Initialize particulars checkboxes and quantity inputs when opening the modal
        if (modalId === 'addReservationModal') {
            initializeParticulars();
        }
    }
}

// Hide modal function
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        
        // Reset form if present
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}

// Initialize particulars functionality
function initializeParticulars() {
    const particularsContainer = document.querySelector('.particulars-container');
    if (!particularsContainer) return;

    const particularItems = particularsContainer.querySelectorAll('.particular-item');
    particularItems.forEach(item => {
        const checkbox = item.querySelector('input[type="checkbox"]');
        const quantityInput = item.querySelector('.quantity-input');
        
        if (!checkbox || !quantityInput) return;

        // Reset initial state
        checkbox.checked = false;
        quantityInput.disabled = true;
        quantityInput.value = '';
        
        // Remove existing event listener if any
        checkbox.removeEventListener('change', handleCheckboxChange);
        
        // Add new event listener
        checkbox.addEventListener('change', handleCheckboxChange);
    });
}

// Handle checkbox change
function handleCheckboxChange(event) {
    const checkbox = event.target;
    const quantityInput = checkbox.closest('.particular-item').querySelector('.quantity-input');
    
    if (!quantityInput) return;
    
    quantityInput.disabled = !checkbox.checked;
    if (checkbox.checked) {
        quantityInput.value = '1';
        quantityInput.focus();
    } else {
        quantityInput.value = '';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        const modalId = event.target.id;
        hideModal(modalId);
    }
});

// Close modal with escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const visibleModal = document.querySelector('.modal.show');
        if (visibleModal) {
            hideModal(visibleModal.id);
        }
    }
});

// Toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Function to toggle mobile navigation
function toggleMobileNav() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('active');
}

// Create and add hamburger menu button for main navigation
function createMainNavHamburger() {
    const navbar = document.querySelector('.navbar');
    const hamburger = document.createElement('button');
    hamburger.className = 'hamburger-menu nav-hamburger';
    hamburger.innerHTML = '☰';
    hamburger.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 10px;
        display: none;
    `;
    hamburger.onclick = toggleMobileNav;
    
    // Insert after logo section
    const logoSection = navbar.querySelector('.logo-section');
    if (logoSection) {
        logoSection.parentNode.insertBefore(hamburger, logoSection.nextSibling);
    } else {
    navbar.insertBefore(hamburger, navbar.firstChild);
    }

    // Show/hide hamburger based on screen size
    function updateMainNavHamburgerVisibility() {
        hamburger.style.display = window.innerWidth <= 768 ? 'block' : 'none';
        if (window.innerWidth > 768) {
            document.querySelector('.nav-links').classList.remove('active');
        }
    }
    window.addEventListener('resize', updateMainNavHamburgerVisibility);
    updateMainNavHamburgerVisibility();
}

// When the page loads
document.addEventListener('DOMContentLoaded', function() {
    showView('reservations');
    createMainNavHamburger();
    initializeParticulars();

    // Add Reservation Form Handler
    const addReservationForm = document.getElementById('addReservationForm');
    if (addReservationForm) {
        addReservationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                
                // Add the action parameter
                formData.append('action', 'add');
                
                // Handle particulars and quantities
                const selectedParticulars = [];
                const quantities = {};
                
                document.querySelectorAll('.particular-item').forEach(item => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    const quantityInput = item.querySelector('.quantity-input');
                    
                    if (checkbox && checkbox.checked) {
                        selectedParticulars.push(checkbox.value);
                        quantities[checkbox.value] = quantityInput.value || 1;
                    }
                });
                
                // Add particulars as individual form fields
                selectedParticulars.forEach((particular, index) => {
                    formData.append(`particulars[${index}]`, particular);
                    formData.append(`quantities[${particular}]`, quantities[particular]);
                });

                const response = await fetch('process_reservation.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                
                if (data.success) {
                    hideModal('addReservationModal');
                    location.reload(); // Reload only on success
                } else {
                    alert(data.message || 'Failed to add reservation');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while adding the reservation');
            }
        });
    }
});

// User Management Functions
function editUser(userId) {
    // Fetch user details
    fetch(`process_user.php?action=get&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.data;
                
                // Populate the edit form
                document.getElementById('edit_original_user_id').value = user.user_id;
                document.getElementById('edit_user_id').value = user.user_id;
                document.getElementById('edit_full_name').value = user.full_name;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_contact_num').value = user.contact_num;
                document.getElementById('edit_user_role').value = user.user_role;
                document.getElementById('edit_program').value = user.program || '';
                document.getElementById('edit_department').value = user.department;
                document.getElementById('edit_block').value = user.block || '';
                document.getElementById('edit_section').value = user.section || '';
                
                // Show/hide student-specific fields
                toggleStudentFields('edit_user_role');
                
                // Show the edit modal
                showModal('editUserModal');
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching user details');
        });
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`process_user.php?action=delete&user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the user');
            });
    }
}

function toggleStudentFields(roleSelectId) {
    const roleSelect = document.getElementById(roleSelectId);
    const studentFields = document.querySelectorAll('.student-only');
    
    studentFields.forEach(field => {
        if (roleSelect.value === 'Student') {
            field.style.display = 'block';
            field.querySelector('input').required = true;
        } else {
            field.style.display = 'none';
            field.querySelector('input').required = false;
            field.querySelector('input').value = '';
        }
    });
}

// Add event listeners when the document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add form submission handlers
    const addUserForm = document.getElementById('addUserForm');
    const editUserForm = document.getElementById('editUserForm');
    const addReservationForm = document.getElementById('addReservationForm');
    
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('process_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideModal('addUserModal');
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the user');
            });
        });
    }
    
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'edit');
            
            fetch('process_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideModal('editUserModal');
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the user');
            });
        });
    }
    
    // Add role change handlers
    const addRoleSelect = document.querySelector('#addUserForm select[name="user_role"]');
    const editRoleSelect = document.querySelector('#editUserForm select[name="user_role"]');
    
    if (addRoleSelect) {
        addRoleSelect.addEventListener('change', () => toggleStudentFields('addUserForm'));
        toggleStudentFields('addUserForm'); // Initial toggle
    }
    
    if (editRoleSelect) {
        editRoleSelect.addEventListener('change', () => toggleStudentFields('editUserForm'));
    }

    // User Search and Filter
    const userSearch = document.querySelector('#usersView .search-box input');
    const userFilters = document.querySelectorAll('#usersView .filter-box select');
    
    function filterUsers() {
        const searchTerm = userSearch.value.toLowerCase();
        const roleFilter = userFilters[0].value.toLowerCase();
        const programFilter = userFilters[1].value.toLowerCase();
        const departmentFilter = userFilters[2].value.toLowerCase();
        
        const userRows = document.querySelectorAll('.users-table tbody tr');
        
        userRows.forEach(row => {
            const userId = row.cells[0].textContent.toLowerCase();
            const fullName = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            const contact = row.cells[3].textContent.toLowerCase();
            const role = row.cells[4].textContent.toLowerCase();
            const program = row.cells[5].textContent.toLowerCase();
            const department = row.cells[6].textContent.toLowerCase();
            
            const matchesSearch = userId.includes(searchTerm) || 
                                fullName.includes(searchTerm) || 
                                email.includes(searchTerm) || 
                                contact.includes(searchTerm);
            const matchesRole = !roleFilter || role === roleFilter;
            const matchesProgram = !programFilter || program === programFilter;
            const matchesDepartment = !departmentFilter || department === departmentFilter;
            
            row.style.display = (matchesSearch && matchesRole && matchesProgram && matchesDepartment) ? '' : 'none';
        });
    }
    
    if (userSearch) userSearch.addEventListener('input', filterUsers);
    userFilters.forEach(filter => {
        if (filter) filter.addEventListener('change', filterUsers);
    });

    // Reservation Search and Filter
    const reservationSearch = document.querySelector('#reservationsView .search-box input');
    const reservationFilters = document.querySelectorAll('#reservationsView .filter-box select');
    
    function filterReservations() {
        const searchTerm = reservationSearch.value.toLowerCase();
        const buildingFilter = reservationFilters[0].value;
        const dateFilter = reservationFilters[1].value;
        
        const reservationRows = document.querySelectorAll('.reservation-table tbody tr');
        const today = new Date();
        
        reservationRows.forEach(row => {
            const reservationId = row.cells[0].textContent.toLowerCase();
            const room = row.cells[1].textContent.toLowerCase();
            const type = row.cells[2].textContent.toLowerCase();
            const purpose = row.cells[3].textContent.toLowerCase();
            const reservedBy = row.cells[4].textContent.toLowerCase();
            const contact = row.cells[5].textContent.toLowerCase();
            const date = new Date(row.cells[6].textContent);
            
            // Search matching
            const matchesSearch = searchTerm === '' || 
                reservationId.includes(searchTerm) || 
                room.includes(searchTerm) || 
                type.includes(searchTerm) || 
                purpose.includes(searchTerm) || 
                reservedBy.includes(searchTerm) || 
                contact.includes(searchTerm);
            
            // Building filter matching
            const matchesBuilding = !buildingFilter || room.startsWith(buildingFilter[0].toLowerCase());
            
            // Date filter matching
            let matchesDate = true;
            if (dateFilter) {
                const reservationDate = new Date(date);
                
                switch(dateFilter) {
                    case 'today':
                        matchesDate = isSameDay(reservationDate, today);
                        break;
                    case 'week':
                        matchesDate = isInCurrentWeek(reservationDate);
                        break;
                    case 'month':
                        matchesDate = isInCurrentMonth(reservationDate);
                        break;
                }
            }
            
            row.style.display = (matchesSearch && matchesBuilding && matchesDate) ? '' : 'none';
        });
    }
    
    // Helper functions for date filtering
    function isSameDay(date1, date2) {
        return date1.getDate() === date2.getDate() &&
               date1.getMonth() === date2.getMonth() &&
               date1.getFullYear() === date2.getFullYear();
    }
    
    function isInCurrentWeek(date) {
        const now = new Date();
        const weekStart = new Date(now.setDate(now.getDate() - now.getDay()));
        const weekEnd = new Date(now.setDate(now.getDate() - now.getDay() + 6));
        return date >= weekStart && date <= weekEnd;
    }
    
    function isInCurrentMonth(date) {
        const now = new Date();
        return date.getMonth() === now.getMonth() && 
               date.getFullYear() === now.getFullYear();
    }
    
    if (reservationSearch) {
        reservationSearch.addEventListener('input', filterReservations);
    }
    
    reservationFilters.forEach(filter => {
        if (filter) {
            filter.addEventListener('change', filterReservations);
        }
    });

    // Room Search and Filter
    const roomSearch = document.querySelector('#roomsView .search-box input');
    const roomFilters = document.querySelectorAll('#roomsView .filter-box select');
    
    function filterRooms() {
        const searchTerm = roomSearch.value.toLowerCase();
        const buildingFilter = roomFilters[0].value;
        const typeFilter = roomFilters[1].value;
        const statusFilter = roomFilters[2].value;
        
        const roomCards = document.querySelectorAll('.room-card');
        
        roomCards.forEach(card => {
            const roomNumber = card.querySelector('h3').textContent.toLowerCase();
            const building = card.querySelector('p:nth-child(1)').textContent.toLowerCase();
            const type = card.querySelector('p:nth-child(2)').textContent.toLowerCase();
            const status = card.querySelector('.room-status').textContent.toLowerCase();
            
            const matchesSearch = roomNumber.includes(searchTerm) || building.includes(searchTerm);
            const matchesBuilding = !buildingFilter || building.includes(buildingFilter.toLowerCase());
            const matchesType = !typeFilter || type.includes(typeFilter.toLowerCase());
            const matchesStatus = !statusFilter || status === statusFilter.toLowerCase();
            
            card.style.display = (matchesSearch && matchesBuilding && matchesType && matchesStatus) ? 'block' : 'none';
        });
    }
    
    if (roomSearch) roomSearch.addEventListener('input', filterRooms);
    roomFilters.forEach(filter => {
        if (filter) filter.addEventListener('change', filterRooms);
    });

    // Handle particulars checkboxes
    const checkboxes = document.querySelectorAll('.particular-item input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const quantityInput = this.closest('.particular-item').querySelector('.quantity-input');
            quantityInput.disabled = !this.checked;
            if (!this.checked) {
                quantityInput.value = '';
            } else {
                quantityInput.value = '1';
            }
        });
    });

    // Update room type when room is selected
    const roomSelect = document.getElementById('room_number');
    if (roomSelect) {
        roomSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const roomType = selectedOption.getAttribute('data-type');
            const roomTypeInput = document.getElementById('room_type');
            if (roomTypeInput) {
                roomTypeInput.value = roomType;
            }
        });
    }

    // Add event listeners for close buttons
    const closeButtons = document.querySelectorAll('.close-btn, .btn-cancel');
    closeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = this.closest('.modal');
            if (modal) {
                hideModal(modal.id);
            }
        });
    });

    // Add Room Form Submit
    const addRoomForm = document.querySelector('#addRoomModal form');
    if (addRoomForm) {
        addRoomForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('process_room.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideModal('addRoomModal');
                    location.reload(); // Refresh the page to show the new room
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add room');
            });
        });
    }

    // Edit Room Form Submit
    const editRoomForm = document.querySelector('#editRoomModal form');
    if (editRoomForm) {
        editRoomForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'edit');
            
            fetch('process_room.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideModal('editRoomModal');
                    location.reload(); // Refresh the page to show the updated room
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update room');
            });
        });
    }

    // Add event listeners for edit reservation buttons
    const editButtons = document.querySelectorAll('.edit-reservation-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const reservationId = this.getAttribute('data-reservation-id');
            if (reservationId) {
                editReservation(reservationId);
            }
        });
    });
});

// Function to handle reservation deletion
function deleteReservation(reservationId) {
    if (!confirm('Are you sure you want to delete this reservation? This action cannot be undone.')) {
        return;
    }

    fetch(`process_reservation.php?action=delete&reservation_id=${encodeURIComponent(reservationId)}`)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            location.reload(); // Reload only on success
        } else {
            alert(data.message || 'Error deleting reservation');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the reservation');
    });
}

// Function to handle reservation editing
function editReservation(reservationId) {
    // Prevent event bubbling
    event.preventDefault();
    event.stopPropagation();

    // First fetch the reservation details
    fetch(`process_reservation.php?action=get&reservation_id=${encodeURIComponent(reservationId)}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate the edit form with the reservation data
            document.getElementById('edit_reservation_id').value = data.data.reservation_id;
            document.getElementById('edit_user_id').value = data.data.user_id;
            document.getElementById('edit_room_number').value = data.data.room_number;
            document.getElementById('edit_purpose').value = data.data.purpose_of_reservation;
            document.getElementById('edit_date').value = data.data.date;
            document.getElementById('edit_start_time').value = data.data.start_time;
            document.getElementById('edit_end_time').value = data.data.end_time;
            document.getElementById('edit_num_attendees').value = data.data.expected_num_people;

            // Show the edit modal
            showModal('editReservationModal');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fetching reservation details');
    });
}

// Add event listener for edit reservation form submission
document.addEventListener('DOMContentLoaded', function() {
    const editReservationForm = document.getElementById('editReservationForm');
    if (editReservationForm) {
        editReservationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('process_reservation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reservation updated successfully!');
                    hideModal('editReservationModal');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update reservation'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the reservation');
            });
        });
    }
});

// Room Management Functions
function showAddRoomModal() {
    showModal('addRoomModal');
}

function showEditRoomModal(roomNumber) {
    // Fetch room details
    fetch(`process_room.php?action=get&room_number=${roomNumber}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const room = data.data;
                document.getElementById('edit_room_number').value = room.room_number;
                document.getElementById('edit_building').value = room.building;
                document.getElementById('edit_room_type').value = room.room_type;
                showModal('editRoomModal');
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to fetch room details');
        });
}

function deleteRoom(roomNumber) {
    if (confirm('Are you sure you want to delete this room? This action cannot be undone.')) {
        fetch(`process_room.php?action=delete&room_number=${roomNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload(); // Refresh the page to show updated room list
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete room');
            });
    }
}

// Function to validate particulars
function validateParticulars() {
    const checkedParticulars = document.querySelectorAll('.particular-item input[type="checkbox"]:checked');
    let isValid = true;

    checkedParticulars.forEach(checkbox => {
        const quantityInput = checkbox.closest('.particular-item').querySelector('.quantity-input');
        if (!quantityInput.value || parseInt(quantityInput.value) < 1) {
            isValid = false;
            quantityInput.classList.add('error');
        } else {
            quantityInput.classList.remove('error');
        }
    });

    if (!isValid) {
        alert('Please specify quantities for all selected particulars.');
    }
    return isValid;
}

// Handle particulars checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const particularsContainer = document.querySelector('.particulars-container');
    if (particularsContainer) {
        particularsContainer.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox') {
                const quantityInput = e.target.closest('.particular-item').querySelector('.quantity-input');
                if (quantityInput) {
                    quantityInput.disabled = !e.target.checked;
                    if (e.target.checked) {
                        quantityInput.value = '1';
                    } else {
                        quantityInput.value = '';
                    }
                }
            }
        });
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}