<?php
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Database connection
@include 'config.php';

// Function to secure SQL queries
function secureInput($conn, $input) {
    if (is_array($input)) {
        // Recursively secure array inputs
        return array_map(function($value) use ($conn) {
            return secureInput($conn, $value);
        }, $input);
    }
    // Escape input and prevent malicious characters
    return mysqli_real_escape_string($conn, htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

$error = [];
$registration_success = false;
$login_success = false;

if (isset($_POST['submit'])) { // Registration logic
    // Sanitize inputs using the secureInput function
    $fName = secureInput($conn, $_POST['fName']);
    $uName = secureInput($conn, $_POST['uName']);
    $email = secureInput($conn, $_POST['email']);
    $password = $_POST['password'];
    $cpass = $_POST['cpassword'];
    $user_type = secureInput($conn, $_POST['user_type']);
 $department_name = $user_type !== 'professor' ? secureInput($conn, $_POST['department']) : null;
    $course_name = $user_type !== 'professor' ? secureInput($conn, $_POST['course']) : null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Invalid email format';
    }

    // Check if the user already exists
    $select = "SELECT * FROM user_form WHERE email = ?";
    $stmt = $conn->prepare($select);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error[] = 'User already exists';
    }

    if ($password !== $cpass) {
        $error[] = 'Passwords do not match';
    }

    if (empty($error)) {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(16)); // Generate a unique token

        mysqli_begin_transaction($conn);

        if ($user_type === 'professor') {
            // If the user type is 'professor', set department_id and course_id to NULL
            $department_id = NULL;
            $course_id = NULL;
        } else {
            // 1. Check if the department exists, if not, insert it
            $select_department = "SELECT id FROM department WHERE name = ?";
            $stmt = $conn->prepare($select_department);
            $stmt->bind_param("s", $department_name);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                // Insert the new department
                $insert_department = "INSERT INTO department (name) VALUES (?)";
                $stmt = $conn->prepare($insert_department);
                $stmt->bind_param("s", $department_name);
                $stmt->execute();
                $department_id = $stmt->insert_id; // Get the newly inserted department ID
            } else {
                $row = $result->fetch_assoc();
                $department_id = $row['id']; // Use the existing department ID
            }

            // 2. Check if the course exists, if not, insert it
            $select_course = "SELECT id FROM course WHERE name = ? AND department_id = ?";
            $stmt = $conn->prepare($select_course);
            $stmt->bind_param("si", $course_name, $department_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                // Insert the new course
                $insert_course = "INSERT INTO course (name, department_id) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_course);
                $stmt->bind_param("si", $course_name, $department_id);
                $stmt->execute();
                $course_id = $stmt->insert_id; // Get the newly inserted course ID
            } else {
                $row = $result->fetch_assoc();
                $course_id = $row['id']; // Use the existing course ID
            }
        }

        // 3. Insert the user data into the user_form table
        $insert_user = "INSERT INTO user_form (fName, uName, email, password, user_type, department_id, course_id, verification_token, verified) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($insert_user);
        $stmt->bind_param("ssssssis", $fName, $uName, $email, $hashed_pass, $user_type, $department_id, $course_id, $verification_token);

        if ($stmt->execute()) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'rgigantone11@gmail.com'; // Gmail email address
                $mail->Password = 'lmlg xmva gywn qavg';    // Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('rgigantone11@gmail.com', 'Your Website');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Account Verification';
                $mail->Body = "
                    <h1>Verify Your Account</h1>
                    <p>Click the link below to verify your account:</p>
                    <a href='http://localhost/management-system/verify.php?token=$verification_token'>Verify Email</a>";

                if ($mail->send()) {
                    mysqli_commit($conn); // Commit transaction after successful email sending
                    echo "<script>alert('Verification email sent! Please check your inbox.');</script>";
                    $registration_success = true;
                } else {
                    throw new Exception('Mail sending failed.');
                }
            } catch (Exception $e) {
                mysqli_rollback($conn); // Rollback transaction if email fails
                $error[] = 'Error sending email: ' . $mail->ErrorInfo;
            }
        } else {
            mysqli_rollback($conn);
            $error[] = 'Failed to register user: ' . mysqli_error($conn);
        }
    }
}



if (isset($_POST['login'])) {
    $username = secureInput($conn, $_POST['username']);
    $password = $_POST['password'];

    // Check for existing lockout
    if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
        $remaining_time = ($_SESSION['lockout_time'] - time());
        $error_message = "Too many failed attempts. Please wait $remaining_time seconds before trying again.";
        showModal($error_message); // Show modal instead of redirect
        exit();
    }

    $select = "SELECT * FROM user_form WHERE uName = ?";
    $stmt = $conn->prepare($select);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['verified'] == 0) {
            $error_message = "Please verify your email before logging in. A verification email was sent to your email address.";
            showModal($error_message);
        } else {
            if (password_verify($password, $row['password'])) {
                // Reset login attempts on successful login
                unset($_SESSION['login_attempts']);
                unset($_SESSION['lockout_time']);

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_type'] = $row['user_type'];
                $_SESSION['last_activity'] = time();

                if ($row['user_type'] == 'admin') {
                    header("Location: /management-system/admin/index.php");
                    exit();
                } elseif ($row['user_type'] == 'student') {
                    header("Location: /management-system/student/student-home.php");
                    exit();
                } elseif ($row['user_type'] == 'professor') {
                    header("Location: /management-system/teacher/prof-home.php");
                    exit();
                }
            } else {
                // Increment login attempts
                if (!isset($_SESSION['login_attempts'])) {
                    $_SESSION['login_attempts'] = 1;
                } else {
                    $_SESSION['login_attempts']++;
                }

                if ($_SESSION['login_attempts'] >= 3) {
                    $_SESSION['lockout_time'] = time() + 180; // 3-minute lockout
                    $error_message = "Too many failed attempts. Please wait 3 minutes before trying again.";
                } else {
                    $error_message = "Incorrect Username or Password";
                }
                showModal($error_message);
            }
        }
    } else {
        // Increment login attempts
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 1;
        } else {
            $_SESSION['login_attempts']++;
        }

        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['lockout_time'] = time() + 180; // 3-minute lockout
            $error_message = "Too many failed attempts. Please wait 3 minutes before trying again.";
        } else {
            $error_message = "Incorrect Username or Password";
        }
        showModal($error_message);
    }
}

function showModal($error_message) {
    // Display the error message in a modal
    echo "
    <div class='modal' id='errorModal' style='display:block;'>
        <div class='modal-content'>
            <span class='close' onclick='document.getElementById(\"errorModal\").style.display=\"none\"'>&times;</span>
            <h2>Error</h2>
            <p>$error_message</p>
        </div>
    </div>

    <script>
        // Ensure the modal is displayed
        window.onload = function() {
            document.getElementById('errorModal').style.display = 'block';
        }
    </script>
    ";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="base.css">
</head>
<body>
    <div class="container">
        <img src="/management-system/img/sorsu-removebg-preview.png" alt="sorsulogo" class="logo">
        <h1 class="system-title">Student Management <br>System</h1>
    </div>   
    <form action="#" class="login-form" id="login" method="POST">
        <h1 class="login-title">Login</h1>

        <div class="input-box">
            <i class='bx bxs-user'></i>
            <input type="text" name="username" id="loginfName" placeholder="Username" required>
        </div>
        <div class="input-box">
            <i class='bx bxs-lock-alt'></i>
            <input type="password" name="password" id="loginPassword" placeholder="Password" required>
            <i class='bx bx-hide' id="togglePassword"></i>
        </div>
        <div id="countdown-container" style="display: none; color: red;">
    <p>Too many failed attempts. Please wait <span id="countdown"></span> to try again.</p>
    </div>
        <div class="remember-forgot-box">
            <label for="remember">
                <input type="checkbox" id="remember">
                Remember me
            </label>
            <a href="/management-system/forgot-pass/forgot.php">Forgot Password?</a>
        </div>

        <button class="login-btn" name="login" id="loginButton" disabled>Login</button>


        <p class="register">
            Don't have an account?
            <button class="button" id="signUpButton">Sign Up</button>
        </p>
    </form>

    <form action="" method="POST" class="register-form" id="register" style="display:none;">
        <h1 class="login-title2">Register</h1>

        <?php
        if (isset($error)) {
            foreach ($error as $msg) {
                echo '<span class="error-msg">' . $msg . '</span>';
            }
        }
        ?>
<form action="your_php_script.php" method="POST" class="register-form" id="register" style="display:none;">
        <div class="input-box">
            <i class='bx bxs-user'></i>
            <input type="text" name="fName" id="fName" placeholder="Full Name" value="<?= isset($_POST['fName']) ? $_POST['fName'] : '' ?>" required>
        </div>
        <div class="input-box">
            <i class='bx bxs-user'></i>
            <input type="text" name="uName" id="uName" placeholder="Username" value="<?= isset($_POST['uName']) ? $_POST['uName'] : '' ?>" required>
        </div>
        <div class="input-box">
            <i class='bx bxs-envelope'></i>
            <input type="text" name="email" id="email" placeholder="Email" value="<?= isset($_POST['email']) ? $_POST['email'] : '' ?>" required>
        </div>
        <div class="input-box">
            <i class='bx bxs-lock-alt'></i>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <i class='bx bx-hide' id="togglePasswordRegister"></i>
        </div>
        <div class="input-box">
            <p id="password-warning" style="color: red; font-size: 12px;">
                <ul id="password-requirements">
                    <li id="length-warning" style="color: red;">Must be at least 8 characters long.</li>
                    <li id="special-warning" style="color: red;">Must include at least 1 special character.</li>
                </ul>
            </p>
        </div>
        <div class="input-box">
            <i class='bx bxs-lock-alt'></i>
            <input type="password" name="cpassword" id="cpassword" placeholder="Confirm Password" required>
        </div>
        <div class="input-box">
            <i class='bx bxs-user'></i>
            <select name="user_type" required>
                <option value="student" <?= (isset($_POST['user_type']) && $_POST['user_type'] == 'student') ? 'selected' : '' ?>>Student</option>
                <option value="professor" <?= (isset($_POST['user_type']) && $_POST['user_type'] == 'professor') ? 'selected' : '' ?>>Professor</option>
            </select>
        </div>

        <div class="input-box">
    <i class='bx bxs-buildings'></i>
    <select id="department" name="department" disabled required>
        <option value="" disabled selected>Select Department</option>
        <option value="DICT" <?= (isset($_POST['department']) && $_POST['department'] == 'DICT') ? 'selected' : '' ?>>DICT</option>
        <option value="BME" <?= (isset($_POST['department']) && $_POST['department'] == 'BME') ? 'selected' : '' ?>>BME</option>
    </select>
</div>
<div class="input-box">
    <i class='bx bxs-user'></i>
    <select id="course" name="course" disabled required>
        <option value="" disabled selected>Select Program</option>
    </select>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const departmentSelect = document.getElementById('department');
        const courseSelect = document.getElementById('course');

        const coursesByDepartment = {
            "DICT": ["BSIT(Bachelor of Science in Information Technology)", "BSCS(Bachelor of Science in Computer Science)", "BSIS(Bachelor of Science in Information System)", "BTVTED(Bachelor of Technical Vocational Education and Training)"],
            "BME": ["BSA(Bachelor of Science in Accountancy)", "BSAIS(Bachelor of Science in Accountancy and Information System)", "BSE(Bachelor of Science in Entrepreneurship)", "BPA(Bachelor of Public Administration)"]
        };

        departmentSelect.addEventListener('change', function () {
            const selectedDepartment = departmentSelect.value;

            // Clear previous course options
            courseSelect.innerHTML = '<option value="" disabled selected>Select Program</option>';

            // Add courses based on the selected department
            if (coursesByDepartment[selectedDepartment]) {
                coursesByDepartment[selectedDepartment].forEach(function (course) {
                    const option = document.createElement('option');
                    option.value = course;
                    option.textContent = course;
                    courseSelect.appendChild(option);
                });
            }
        });

        // Retain selected value after form submission
        <?php if (isset($_POST['department']) && isset($_POST['course'])): ?>
        const savedDepartment = "<?= $_POST['department'] ?>";
        const savedCourse = "<?= $_POST['course'] ?>";

        if (coursesByDepartment[savedDepartment]) {
            coursesByDepartment[savedDepartment].forEach(function (course) {
                const option = document.createElement('option');
                option.value = course;
                option.textContent = course;
                if (course === savedCourse) {
                    option.selected = true;
                }
                courseSelect.appendChild(option);
            });
        }
        <?php endif; ?>
    });

    document.addEventListener('DOMContentLoaded', function () {
    const userTypeSelect = document.querySelector('select[name="user_type"]');
    const departmentSelect = document.getElementById('department');
    const courseSelect = document.getElementById('course');
    const departmentIcon = departmentSelect.previousElementSibling; // This gets the icon before the department select
    const courseIcon = courseSelect.previousElementSibling; // This gets the icon before the course select

    // Function to show/hide department and course dropdowns based on user type
    function toggleDropdowns() {
        if (userTypeSelect.value === 'student') {
            departmentSelect.removeAttribute('disabled');
            courseSelect.removeAttribute('disabled');
            departmentSelect.style.display = 'block';
            courseSelect.style.display = 'block';
            departmentIcon.style.display = 'block'; // Show the department icon
            courseIcon.style.display = 'block'; // Show the course icon
        } else {
            departmentSelect.setAttribute('disabled', 'disabled');
            courseSelect.setAttribute('disabled', 'disabled');
            departmentSelect.style.display = 'none';
            courseSelect.style.display = 'none';
            departmentIcon.style.display = 'none'; // Hide the department icon
            courseIcon.style.display = 'none'; // Hide the course icon
        }
    }

    // Initially set the dropdowns state
    toggleDropdowns();

    // Add event listener to user type dropdown
    userTypeSelect.addEventListener('change', toggleDropdowns);
});

</script>



        <button class="login-btn" name="submit">Save</button>

        <p class="register">
            Already have an account?
            <button class="button" id="signInButton">Sign In</button>
        </p>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Form Visibility and Error Handling
        <?php if (!empty($error)) : ?>
            // Keep the register form visible if there are errors (provided by PHP)
            document.getElementById('login').style.display = 'none';
            document.getElementById('register').style.display = 'block';
        <?php elseif ($registration_success) : ?>
            // If registration is successful, hide register form, clear data and show login form
            document.getElementById('register').style.display = 'none';
            document.getElementById('login').style.display = 'block';

            // Clear all fields in the registration form
            document.getElementById('fName').value = '';
            document.getElementById('uName').value = '';
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('cpassword').value = '';
            document.querySelector('select[name="user_type"]').value = 'student';  // Reset user type selection
        <?php endif; ?>

        const signUpButton = document.getElementById('signUpButton');
        const signInButton = document.getElementById('signInButton');
        const login = document.getElementById('login');
        const register = document.getElementById('register');

        // Switch to register form when 'Sign Up' button is clicked
        signUpButton.addEventListener('click', function () {
            clearInputs(login);
            login.style.display = "none";
            register.style.display = "block";
        });

        // Switch to login form when 'Sign In' button is clicked
        signInButton.addEventListener('click', function () {
            clearInputs(register);
            login.style.display = "block";
            register.style.display = "none";
        });

        // Clear inputs and selects
       function clearInputs(form) {
            const inputs = form.querySelectorAll('input');
            const selects = form.querySelectorAll('select');
            inputs.forEach(input => input.value = '');
            selects.forEach(select => select.selectedIndex = 0);
        }

        // Clear all inputs and selects on page refresh
        window.onload = function () {
            allInputs.forEach(input => input.value = '');
            allSelects.forEach(select => select.selectedIndex = 0);
        };

        // Password validation logic
        const passwordField = document.getElementById('password');
        const lengthWarning = document.getElementById('length-warning');
        const specialWarning = document.getElementById('special-warning');
        const passwordRequirements = document.getElementById('password-requirements');

        // Regular expression for special character check
        const specialCharRegex = /[!@#$%^&*(),.?":{}|<>]/;

        passwordRequirements.style.display = 'none';

        // Add an event listener for input events
        passwordField.addEventListener('input', function () {
            const password = passwordField.value;

            // Hide warning if input is empty

            if(password.trim() === ""){
                passwordRequirements.style.display = 'none';
                return;
            }

            passwordRequirements.style.display = 'block';

            // Check if the password is at least 8 characters long
            if (password.length >= 8) {
                lengthWarning.style.color = 'green';
            } else {
                lengthWarning.style.color = 'red';
            }

            // Check if the password includes at least one special character
            if (specialCharRegex.test(password)) {
                specialWarning.style.color = 'green';
            } else {
                specialWarning.style.color = 'red';
            }

            // If both requirements are met, hide the warning
            if (password.length >= 8 && specialCharRegex.test(password)) {
                passwordRequirements.style.display = 'none';
            } else {
                passwordRequirements.style.display = 'block';
            }
        });

        // Add event listener for password toggle
        const togglePassword = document.getElementById('togglePassword');
        const togglePasswordRegister = document.getElementById('togglePasswordRegister');
        const loginPasswordField = document.getElementById('loginPassword');
        const registerPasswordField = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = loginPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
            loginPasswordField.setAttribute('type', type);
            this.classList.toggle('bx-hide');
            this.classList.toggle('bx-show');
        });

        togglePasswordRegister.addEventListener('click', function () {
            const type = registerPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
            registerPasswordField.setAttribute('type', type);
            this.classList.toggle('bx-hide');
            this.classList.toggle('bx-show');
        });
    });
    </script>
    <?php
// Check if lockout time is set and calculate the remaining time
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    $remaining_time = $_SESSION['lockout_time'] - time();
} else {
    $remaining_time = 0;
}
?>

<script>
// Get the remaining time from PHP
    // Get the remaining time from PHP
    let remainingTime = <?php echo $remaining_time; ?>;

    const countdownContainer = document.getElementById('countdown-container');
    const countdownElement = document.getElementById('countdown');
    const loginButton = document.getElementById('loginButton');

    if (remainingTime > 0) {
        // Disable the login button
        loginButton.disabled = true;

        // Show the countdown container
        countdownContainer.style.display = 'block';

        // Update the countdown every second
        const timer = setInterval(() => {
            if (remainingTime <= 0) {
                clearInterval(timer);
                countdownContainer.style.display = 'none';

                // Re-enable the login button
                loginButton.disabled = false;
                return;
            }

            // Convert seconds to minutes and seconds format
            const minutes = Math.floor(remainingTime / 60);
            const seconds = remainingTime % 60;
            countdownElement.textContent = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;

            remainingTime--;
        }, 1000);
    } else {
        // Ensure the login button is enabled if there's no countdown
        loginButton.disabled = false;
    }
</script>

</body>
</html>