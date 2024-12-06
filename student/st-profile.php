<?php
// Start the session to access session variables
session_start();

// Database connection
@include '../config.php';

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    // Redirect to the login page if the user is not logged in or is not a student
    
    // Check if the logout button is clicked and handle the logout event
    if (isset($_POST['logout'])) {
        session_destroy(); // Destroy the session
        header("Location: /management-system/base.php"); // Redirect to login page
        exit();
    }
    
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
  <title>Home</title>
  <link rel="stylesheet" href="style-st.css">
  <style>
    .change-password-btn {
      display: inline-block;
      background-color: #4B70F5;
      color: white;
      border-radius: 10px;
      padding: 10px 20px;
      border: none;
      cursor: pointer;
      font-size: 16px;
      transition: transform 0.2s;
      margin-top:10px;
      
    }
    .change-password-btn:hover {
      transform: scale(1.05);
    }
    
    .student-info {
      margin: 20px;
      padding: 20px;
      border-radius: 5px;
      background-color: #353941;
      box-shadow: 0 0 50px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 600px;
      box-sizing: border-box;
    }
    .student-info h3 {
      margin-bottom: 10px;
    }
    .student-info p {
      margin: 5px 0;
    }
    .student-info img {
      float:right;
      width: 150px;
      height: 150px;
      object-fit: cover;
      margin-top: 15px;
      border-radius: 10px;
    }
  </style>
</head>
<body>
  <nav id="sidebar">
    <ul>
      <li>
        <span class="logo">Student</span>
        <button onclick=toggleSidebar() id="toggle-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
        </button>
      </li>
      
<li>
  <div id="logo-container" class="logo">
    <img src="/management-system/img/sorsu-removebg-preview.png" alt="Logo">
    <h2>Student Management System</h2>
  </div>
</li>

      <li>
        <a href="/management-system/student/student-home.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M240-200h120v-200q0-17 11.5-28.5T400-440h160q17 0 28.5 11.5T600-400v200h120v-360L480-740 240-560v360Zm-80 0v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H560q-17 0-28.5-11.5T520-160v-200h-80v200q0 17-11.5 28.5T400-120H240q-33 0-56.5-23.5T160-200Zm320-270Z"/></svg>
          <span>Home</span>
        </a>
      </li>
      <li  class="active">
        <a href="/management-system/student/st-profile.php">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-240v-32q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v32q0 33-23.5 56.5T720-160H240q-33 0-56.5-23.5T160-240Zm80 0h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
        <span>My Profile</span>
        </a>
      </li>
      <li>
      <a href="/management-system/logout.php">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M10.09 15.59L11.5 17l5-5-5-5L10.09 15.59zM21 7h-3v1.5h-2V7H3v10.5h2V8.5h3v9H21V7z"/></svg>
    <span>Logout</span>
  </a>
</li>
    </ul>
  </nav>
  <main>
    <div class="main-content">
    <div class="student-info">
        <img class="student-profile-pic" src="/management-system/img/andro.jpg" alt="Profile Picture">
        <div class="student-info-content">
            <h3>Welcome, <?= htmlspecialchars($student['fName']); ?>!</h3>
            <p><strong>Username:</strong> <?= htmlspecialchars($student['uName']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($student['email']); ?></p>
            <p><strong>Account Type:</strong> <?= htmlspecialchars($student['user_type']); ?></p>
            <button 
                class="change-password-btn" 
                onclick="location.href='/management-system/forgot-pass/reset.php?email=<?= urlencode($student['email']); ?>&from=student-info'">
                Change Password
            </button>
        </div>
    </div>  
    </div>
  </main>
  <script src="app.js"></script>
</body>
</html>