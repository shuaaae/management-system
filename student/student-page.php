<?php
// Start the session to access session variables
session_start();

// Database connection
@include '../config.php';

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    // Redirect to the login page if the user is not logged in or is not a student
    header("Location: /management-system/login.php");
    exit();
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// Fetch data for the logged-in student
$query = "SELECT * FROM user_form WHERE id = '$user_id' AND user_type = 'student'";
$result = mysqli_query($conn, $query);

// Check if the query was successful and fetch the user data
if ($result && mysqli_num_rows($result) > 0) {
    $student = mysqli_fetch_assoc($result);
} else {
    die("Failed to fetch student information: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Info</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <link rel="stylesheet" href="styleS.css">
    <style>
      
    </style>
</head>
<body>
    <!-- Top Bar -->
    <header class="top-bar">
        <div class="user-info">
            <img src="/management-system/img/sorsu-removebg-preview.png" alt="Admin Profile">
            <?php echo htmlspecialchars($student['fName']); ?>
            <a href="/management-system/logout.php" class="logout-icon">
                <i class="fa fa-sign-out-alt"></i> <!-- Logout Icon -->   
            </a>
        </div>
    </header>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="/management-system/img/sorsu-removebg-preview.png" alt="Logo">
                <h2>Student Management System</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="/management-system/student/student-page.php" class="menu-item active"><i class="icon"></i> Student Profile</a></li>
                </ul>
            </nav>
        </aside>

            <!-- Main Content -->
            <main class="main-content">
    <section class="content-header">
        <h2>Student Information</h2>
    </section>

        <section class="student-info">
            <div class="student-card">
                <h1><strong><?php echo htmlspecialchars($student['fName']); ?></strong></h1>
                <span class="student-info">
                <i class="icon student-icon"></i> student
                </span>
                <span class="student-info">
                <i class="icon location-icon"></i> Philippines
                </span>
                <span class="student-info">
                <i class="icon email-icon"></i><?php echo htmlspecialchars($student['email']); ?>
                </span>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($student['uName']); ?></p>
                <p><strong>Email:</strong> </p>
                <button class="btn-change-password">Change Password</button>
            </div>
        </section>
    </div>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
