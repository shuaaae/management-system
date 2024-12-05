<?php
session_start();

// Redirect to login page if the user is not logged in or is not an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: /management-system/base.php");
    exit;
}

// Implement session timeout (30 minutes)
$timeout_duration = 30 * 60; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();     // Unset session variables
    session_destroy();   // Destroy session
    header("Location: /management-system/base.php");
    exit;
}
$_SESSION['last_activity'] = time(); // Update last activity time
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <link rel="stylesheet" href="style-admin.css">
</head>
<body>
  <nav id="sidebar">
    <ul>
      <li>
        <span class="logo">Admin</span>
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
      <li class="active">
        <a href="/management-system/admin/index.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M240-200h120v-200q0-17 11.5-28.5T400-440h160q17 0 28.5 11.5T600-400v200h120v-360L480-740 240-560v360Zm-80 0v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H560q-17 0-28.5-11.5T520-160v-200h-80v200q0 17-11.5 28.5T400-120H240q-33 0-56.5-23.5T160-200Zm320-270Z"/></svg>
          <span>Home</span>
        </a>
      </li>
      <li >
        <a href="/management-system/admin/student-page.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M520-640v-160q0-17 11.5-28.5T560-840h240q17 0 28.5 11.5T840-800v160q0 17-11.5 28.5T800-600H560q-17 0-28.5-11.5T520-640ZM120-480v-320q0-17 11.5-28.5T160-840h240q17 0 28.5 11.5T440-800v320q0 17-11.5 28.5T400-440H160q-17 0-28.5-11.5T120-480Zm400 320v-320q0-17 11.5-28.5T560-520h240q17 0 28.5 11.5T840-480v320q0 17-11.5 28.5T800-120H560q-17 0-28.5-11.5T520-160Zm-400 0v-160q0-17 11.5-28.5T160-360h240q17 0 28.5 11.5T440-320v160q0 17-11.5 28.5T400-120H160q-17 0-28.5-11.5T120-160Zm80-360h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z"/></svg>
          <span>Students Info</span>
        </a>
      </li>
      <li>
        <a href="/management-system/admin/prof-page.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M520-640v-160q0-17 11.5-28.5T560-840h240q17 0 28.5 11.5T840-800v160q0 17-11.5 28.5T800-600H560q-17 0-28.5-11.5T520-640ZM120-480v-320q0-17 11.5-28.5T160-840h240q17 0 28.5 11.5T440-800v320q0 17-11.5 28.5T400-440H160q-17 0-28.5-11.5T120-480Zm400 320v-320q0-17 11.5-28.5T560-520h240q17 0 28.5 11.5T840-480v320q0 17-11.5 28.5T800-120H560q-17 0-28.5-11.5T520-160Zm-400 0v-160q0-17 11.5-28.5T160-360h240q17 0 28.5 11.5T440-320v160q0 17-11.5 28.5T400-120H160q-17 0-28.5-11.5T120-160Zm80-360h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z"/></svg>
          <span>Professors Info</span>
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
    <div class="container">
      <h2>Welcome, Sorsueños!</h2>
      <p>We are excited to have you here at the Sorsogon State University - Bulan Campus!
        This platform is designed to streamline your academic experience, giving you quick access to important resources,
        student information, and updates. Our goal is to support your journey toward academic excellence and personal growth.
        Dive in, explore, and make the most out of this system. Together, let’s achieve great things!</p>
    </div>
<style>
  .container > div:hover {
    transform: scale(1.05);
  }
</style>

<div class="container" style="display: grid; grid-template-columns: repeat(3, minmax(200px, 1fr)); gap: 20px; margin-left: 150px; border: none;">
  <div style="border-radius: 8px; overflow: hidden; margin: 10px; width: 200px; height: 350px; background-color: #3C0008; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
    cursor: pointer;
    }">
    <img src="/management-system/img/rexcel.jpg" alt="Image 1" style="width: 100%; height: 200px; object-fit: cover;">
    <div style="padding: 15px;">
      <h3 style="margin: 0;">Rexcel Gigantone</h3>
      <p style="margin: 10px 0;">BSIT 3-1</p>
    </div>
  </div>

  <div style="border-radius: 8px; overflow: hidden; margin: 10px; width: 200px; height: 350px; background-color: #3C0008; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
    cursor: pointer;
    }">
    <img src="/management-system/img/joshua.jpg" alt="Image 4" style="width: 100%; height: 200px; object-fit: cover;">
    <div style="padding: 15px;">
      <h3 style="margin: 0;">Joshua Godalle</h3>
      <p style="margin: 10px 0;">BSIT 3-1</p>
    </div>
  </div>

  <div style="border-radius: 8px; overflow: hidden; margin: 10px; width: 200px; height: 350px; background-color: #3C0008; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
    cursor: pointer;
    }">
    <img src="/management-system/img/andro.jpg" alt="Image 3" style="width: 100%; height: 200px; object-fit: cover;">
    <div style="padding: 15px;">
      <h3 style="margin: 0;">Marc Andro Levantino</h3>
      <p style="margin: 10px 0;">BSIT 3-1</p>
    </div>
  </div>

  <div style="border-radius: 8px; overflow: hidden; margin: 10px; width: 200px; height: 350px; background-color: #3C0008; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
    cursor: pointer;
    }">
    <img src="/management-system/img/jam.jpg" alt="Image 4" style="width: 100%; height: 200px; object-fit: cover;">
    <div style="padding: 15px;">
      <h3 style="margin: 0;">Jamaica Allysa Gimao</h3>
      <p style="margin: 10px 0;">BSIT 3-1</p>
    </div>
  </div>

  <div style="border-radius: 8px; overflow: hidden; margin: 10px; width: 200px; height: 350px; background-color: #3C0008; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
    cursor: pointer;
    }">
    <img src="/management-system/img/andro.jpg" alt="Image 5" style="width: 100%; height: 200px; object-fit: cover;">
    <div style="padding: 15px;">
      <h3 style="margin: 0;">John Paul Garlan</h3>
      <p style="margin: 10px 0;">BSIT 3-1</p>
    </div>
  </div>

  <div style="border-radius: 8px; overflow: hidden; margin: 10px; width: 200px; height: 350px; background-color: #3C0008; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
    cursor: pointer;
    }">
    <img src="/management-system/img/andro.jpg" alt="Image 6" style="width: 100%; height: 200px; object-fit: cover;">
    <div style="padding: 15px;">
      <h3 style="margin: 0;">Althea Mary Neil Lanuza</h3>
      <p style="margin: 10px 0;">BSIT 3-1</p>
    </div>
  </div>
  </main>
  <script src="app.js"></script>
</body>
</html>