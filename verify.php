<?php
// Include the database connection
@include 'config.php';

// Check if a token is provided in the URL
if (isset($_GET['token'])) {
    // Sanitize the token from the URL
    $token = mysqli_real_escape_string($conn, $_GET['token']);

    // Query to check if the token exists
    $query = "SELECT * FROM user_form WHERE verification_token = '$token'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Check if the account is already verified
        if ($user['verified'] == 1) {
            echo "<script>alert('Your account is already verified. You can log in now.');</script>";
        } else {
            // Update the 'verified' status to 1
            $update_query = "UPDATE user_form SET verified = 1 WHERE verification_token = '$token'";

            if (mysqli_query($conn, $update_query)) {
                echo "<script>alert('Your account has been successfully verified! You can log in now.');</script>";
                echo "<script>window.location = '/management-system/login.php';</script>";
            } else {
                echo "<script>alert('Failed to update verification status. Please try again later.');</script>";
            }
        }
    } else {
        echo "<script>alert('Invalid or expired token. Please check your email for the correct verification link.');</script>";
    }
} else {
    echo "<script>alert('No token provided. Verification link is invalid.');</script>";
}
?>
