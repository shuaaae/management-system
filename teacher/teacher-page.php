<?php
// Start the session to access session variables
session_start();

// Database connection
@include '../config.php';

// Ensure the user is logged in and is a professor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'professor') {
    header("Location: /management-system/base.php");
    exit();
}

// Fetch the logged-in professor's data
$user_id = $_SESSION['user_id'];
$professor_query = "SELECT fName FROM user_form WHERE id = $user_id AND user_type = 'professor'";
$professor_result = mysqli_query($conn, $professor_query);

// Check if the query was successful and fetch the result
if ($professor_result && mysqli_num_rows($professor_result) > 0) {
    $professor = mysqli_fetch_assoc($professor_result);
    $professor_name = $professor['fName'];
} else {
    $professor_name = "Professor"; // Default name in case of an error
}

// Fetch data for users with user_type 'student'
$query = "SELECT * FROM user_form WHERE user_type = 'student'";
$result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professors Info</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <link rel="stylesheet" href="style-teacher.css">
</head>
<body>
    <!-- Top Bar -->
    <header class="top-bar">
        <div class="user-info">
            <img src="/management-system/img/sorsu-removebg-preview.png" alt="Profile Picture">
            <span><?= htmlspecialchars($professor_name); ?></span>
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
                    <li><a href="/management-system/teacher/teacher-page.php" class="menu-item active"><i class="icon"></i> Students</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <section class="content-header">
                <h2>Students information.</h2>
            </section>

            <section class="professor-data">
                <div class="gallery">
                    <?php
                    // Fetch and display each student record as a card
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='student-card'>";
                        echo "<h3>" . htmlspecialchars($row['fName']) . "</h3>";  // Display full name (fName)
                        // echo "<p><strong>Username:</strong> " . htmlspecialchars($row['uName']) . "</p>";  // Display username (uName)
                        // echo "<p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</p>";  // Display email
                        // echo "<p><strong>Verified:</strong> " . ($row['verified'] ? 'Yes' : 'No') . "</p>";  // Display verified status
                        echo "<div class='card-buttons'>";  // Container for buttons
                        echo "<button class='view-btn'>View</button>";
                        echo "<button class='edit-btn'>Edit</button>";
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
