<?php
session_start();

require_once __DIR__ . '/../config/db_connect.php';

// LOGIN HANDLER
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login_submit"])) {
    $username = trim($_POST["login_username"]);
    $password = trim($_POST["login_password"]);

    $sql = "SELECT * FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if ($row) {
        if ($row['password'] === $password) {
            $_SESSION['mode'] = 'login';
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_role'] = $row['user_role'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['contact'] = $row['contact_num'];
            $_SESSION['program'] = $row['program'];
            $_SESSION['block'] = $row['block'];
            $_SESSION['section'] = $row['section'];
            $_SESSION['department'] = $row['department'];

            header("Location: ../users/usermain.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('User not found.'); window.history.back();</script>";
        exit();
    }
}

// CREATE ACCOUNT HANDLER
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_submit"])) {
    $user_id = trim($_POST["id"]);
    $user_role = trim($_POST["id-type"]);
    $surname = trim($_POST["surname"]);
    $givenname = trim($_POST["givenname"]);
    $fullname = $givenname . " " . $surname;
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);
    $password = trim($_POST["create_account_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Initialize role-specific fields to NULL
    $program = NULL;
    $block = NULL;
    $section = NULL;
    $department = NULL;

    if ($user_role === 'student') {
        $program = trim($_POST["program"] ?? '');
        $block = trim($_POST["block"] ?? '');
        $section = trim($_POST["section"] ?? '');
        $department = trim($_POST["department"] ?? '');
    } elseif ($user_role === 'professor') {
        $department = trim($_POST["department"] ?? '');
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    // Check if user already exists
    $check_sql = "SELECT * FROM user WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$user_id]);
    
    if ($check_stmt->fetch()) {
        echo "<script>alert('Username already exists.'); window.history.back();</script>";
        exit();
    }

    // Insert into MySQL
    $insert_sql = "INSERT INTO user (user_id, password, user_role, full_name, email, contact_num, program, block, section, department) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    
    try {
        $insert_stmt->execute([
            $user_id, 
            $password,
            $user_role, 
            $fullname, 
            $email, 
            $contact, 
            $program, 
            $block, 
            $section, 
            $department
        ]);
        
        $_SESSION['mode'] = 'create';
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_role'] = $user_role;
        $_SESSION['name'] = $fullname;
        $_SESSION['email'] = $email;
        $_SESSION['contact'] = $contact;
        $_SESSION['program'] = $program;
        $_SESSION['block'] = $block;
        $_SESSION['section'] = $section;
        header("Location: ../users/usermain.php");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Error creating account: " . $e->getMessage() . "'); window.history.back();</script>";
        exit();
    }
}

// GUEST HANDLER
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["guest_submit"])) {
    $user_id = trim($_POST["id"]);
    $user_role = trim($_POST["id-type"]);
    $surname = trim($_POST["surname"]);
    $givenname = trim($_POST["givenname"]);
    $fullname = $givenname . " " . $surname;
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);
    $program = trim($_POST["program"]);
    $block = trim($_POST["block"]);
    $section = trim($_POST["section"]);

    $_SESSION['mode'] = 'guest';
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_role'] = $user_role;
    $_SESSION['name'] = $fullname;
    $_SESSION['email'] = $email;
    $_SESSION['contact'] = $contact;
    $_SESSION['program'] = $program;
    $_SESSION['block'] = $block;
    $_SESSION['section'] = $section;

    header("Location: ../users/usermain.php");
    exit();
}

$conn = null;  // Option 1: Explicitly set to null if you want to close it manually
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LogIn</title> 
  <link rel="stylesheet" href="signup.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<!-- LOGIN CONTAINER -->
<div class="container" id="login-container">
  <div class="background-picture">
    <div class="login-header">
      <a href=""><img src="../images/pupwestologo.png" alt="pup-logo"></a>
      <h3>Polytechnic University of the Philippines</h3>
      <h6>The Country's first Polytechnic University</h6>
    </div>
  </div>

  <div class="login-panel">
    <h3>Log In</h3>
    <div class="form-container">
      <form action="signup.php" method="post">
        <button type="button" class="guest-button" id="as-guest-btn">As Guest</button>

        <label for="username">Username</label>
        <input type="text" name="login_username" id="username" required>

        <label for="password">Password</label>
        <div class="password-input-container">
          <input type="password" name="login_password" id="login-password-input" required>
          <i class="fas fa-eye password-toggle" id="login-password-toggle"></i>
        </div>

        <p><a href="#">Forgot Password?</a></p>

        <button type="submit" class="sign-in-button" name="login_submit">Sign In</button>

        <p>Don't have an account yet? <b id="no-account-signup">Sign Up</b></p>
      </form> 
    </div>
  </div>
</div>

<!-- CREATE ACCOUNT CONTAINER -->
<div class="container" id="create-account-container" style="display: none;">
  <div class="background-picture">
    <div class="login-header">
      <a href=""><img src="../images/pupwestologo.png" alt="pup-logo"></a>
      <h3>Polytechnic University of the Philippines</h3>
      <h6>The Country's first Polytechnic University</h6>
    </div>
  </div>

  <div class="right-panel">
    <div class="account-container">
      <img src="../images/back-icon.png" alt="back-icon" class="back-icon">
      <form action="signup.php" method="post">
        <h2>Create Account<hr></h2>

        <div class="row-div">
          <div class="form-group">
            <label for="id">ID</label>
            <input type="text" name="id" id="id" required>
          </div>
          <div class="form-group">
            <label for="id-type">Type</label>
            <select name="id-type" id="id-type" required>
              <option value="" disabled selected>Select Type</option>
              <option value="student">Student</option>
              <option value="professor">Professor</option>
            </select>
          </div>
        </div>

        <div class="row-div">
          <div class="form-group">
            <label for="surname">Surname</label>
            <input type="text" name="surname" id="surname" required>
          </div>
          <div class="form-group">
            <label for="givenname">Givenname</label>
            <input type="text" name="givenname" id="givenname" required>
          </div>
        </div>

        <div class="row-div">
          <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" name="email" id="email" required>
          </div>
          <div class="form-group">
            <label for="contact">Contact No.</label>
            <input type="number" name="contact" id="contact" required>
          </div>
        </div>

        <div id="student-fields" style="display: none;">
          <div class="row-div">
            <div class="form-group">
              <label for="program">Program</label>
              <input type="text" name="program" id="program" required>
            </div>
            <div class="form-group">
              <label for="block">Block</label>
              <input type="text" name="block" id="block" required>
            </div>
            <div class="form-group">
              <label for="section">Section</label>
              <input type="text" name="section" id="section" required>
            </div>
          </div>
          <div class="row-div">
            <div class="form-group">
              <label for="department">Department</label>
              <input type="text" name="department" id="department" required>
            </div>
          </div>
        </div>

        <div id="professor-fields" style="display: none;">
          <div class="row-div">
            <div class="form-group">
              <label for="department">Department</label>
              <input type="text" name="department" id="department" required>
            </div>
          </div>
        </div>

        <div class="row-div">
          <div class="form-group">
            <label for="create-password">Password</label>
            <div class="password-input-container">
              <input type="password" name="create_account_password" id="create-password-input" required>
              <i class="fas fa-eye password-toggle" id="create-password-toggle"></i>
            </div>
          </div>
        </div>
        <div class="row-div">
          <div class="form-group">
            <label for="confirm-password">Confirm Password</label>
            <div class="password-input-container">
              <input type="password" name="confirm_password" id="confirm-password-input" required>
              <i class="fas fa-eye password-toggle" id="confirm-password-toggle"></i>
            </div>
          </div>
        </div>
        <button type="submit" class="signbutton" name="create_submit">Submit</button>
        <button type="button" class="back-to-login-btn" id="back-to-login">Back to Sign In</button>
      </form>
    </div>
  </div>
</div>

<!-- GUEST / PERSONAL DETAILS CONTAINER -->
<div class="container" id="signup-container" style="display: none;">
  <div class="background-picture">
    <div class="login-header">
      <a href=""><img src="../images/pupwestologo.png" alt="pup-logo"></a>
      <h3>Polytechnic University of the Philippines</h3>
      <h6>The Country's first Polytechnic University</h6>
    </div>
  </div>

  <div class="right-panel">
    <div class="form-container2">
      <img src="../images/back-icon.png" alt="back-icon" class="back-icon">
      <form action="signup.php" method="post">
        <h2>Personal Details<hr></h2>

        <div class="row-div">
          <div class="form-group">
            <label for="id">ID</label>
            <input type="text" name="id" id="id" required>
          </div>
          <div class="form-group">
            <label for="id-type">Type</label>
            <select name="id-type" id="id-type">
              <option value="student">Student</option>
              <option value="professor">Professor</option>
            </select>
          </div>
        </div>

        <div class="row-div">
          <div class="form-group">
            <label for="surname">Surname</label>
            <input type="text" name="surname" id="surname" required>
          </div>
          <div class="form-group">
            <label for="givenname">Givenname</label>
            <input type="text" name="givenname" id="givenname" required>
          </div>
        </div>

        <div class="row-div">
          <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" name="email" id="email" required>
          </div>
          <div class="form-group">
            <label for="contact">Contact No.</label>
            <input type="number" name="contact" id="contact" required>
          </div>
        </div>

        <div class="row-div">
          <div class="form-group">
            <label for="program">Program</label>
            <input type="text" name="program" id="program" required>
          </div>
          <div class="form-group">
            <label for="block">Block</label>
            <input type="text" name="block" id="block" required>
          </div>
          <div class="form-group">
            <label for="section">Section</label>
            <input type="text" name="section" id="section" required>
          </div>
        </div>
        
        <button type="submit" class="signbutton" name="guest_submit">Submit</button>
      </form>
    </div>
  </div>
</div>

<script>
  const loginContainer = document.getElementById('login-container');
  const signupContainer = document.getElementById('signup-container');
  const createAccountContainer = document.getElementById('create-account-container');
  const noAccountSignup = document.getElementById('no-account-signup');
  const asGuestBtn = document.getElementById('as-guest-btn');
  const backToLoginBtn = document.getElementById('back-to-login');

  document.getElementById('as-guest-btn').addEventListener('click', () => {
    loginContainer.style.display = 'none';
    signupContainer.style.display = 'flex';
  });

  document.getElementById('no-account-signup').addEventListener('click', () => {
    loginContainer.style.display = 'none';
    createAccountContainer.style.display = 'flex';
  });

  document.querySelectorAll('.back-icon').forEach(icon => {
    icon.addEventListener('click', () => {
      signupContainer.style.display = 'none';
      createAccountContainer.style.display = 'none';
      loginContainer.style.display = 'flex';
    });
  });

  // Back to Sign In button handler
  if (backToLoginBtn) {
    backToLoginBtn.addEventListener('click', function() {
      createAccountContainer.style.display = 'none';
      loginContainer.style.display = 'flex';
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    const idTypeSelect = document.getElementById('id-type');
    const studentFields = document.getElementById('student-fields');
    const professorFields = document.getElementById('professor-fields');

    function toggleFields() {
        const selectedRole = idTypeSelect.value;

        // Reset all fields and their required attributes
        const allStudentInputs = studentFields.querySelectorAll('input');
        allStudentInputs.forEach(input => {
            input.required = false;
            input.value = ''; // Clear values when hidden
        });
        const allProfessorInputs = professorFields.querySelectorAll('input');
        allProfessorInputs.forEach(input => {
            input.required = false;
            input.value = ''; // Clear values when hidden
        });

        studentFields.style.display = 'none';
        professorFields.style.display = 'none';

        if (selectedRole === 'student') {
            studentFields.style.display = 'block';
            allStudentInputs.forEach(input => {
                input.required = true;
                input.setAttribute('required', 'required');
            });
        } else if (selectedRole === 'professor') {
            professorFields.style.display = 'block';
            allProfessorInputs.forEach(input => {
                input.required = true;
                input.setAttribute('required', 'required');
            });
        }
    }

    // Initial call to set correct fields based on default selection (if any)
    toggleFields();

    // Add event listener for when the selection changes
    idTypeSelect.addEventListener('change', toggleFields);

    // Password toggle functionality
    function togglePasswordVisibility(inputId, toggleId) {
        const passwordInput = document.getElementById(inputId);
        const passwordToggle = document.getElementById(toggleId);

        if (passwordInput && passwordToggle) {
            passwordToggle.addEventListener('click', function() {
                // Toggle the type attribute
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle the eye icon
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    }

    // Apply toggle to login password
    togglePasswordVisibility('login-password-input', 'login-password-toggle');

    // Apply toggle to create account password
    togglePasswordVisibility('create-password-input', 'create-password-toggle');

    // Apply toggle to confirm password
    togglePasswordVisibility('confirm-password-input', 'confirm-password-toggle');
  });
</script>

<script src="signup.js"></script>
</body>
</html>