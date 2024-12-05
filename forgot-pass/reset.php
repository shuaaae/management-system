<?php
@include '../config.php';

// Get email and source (`from`) from query string
$email = isset($_GET['email']) ? mysqli_real_escape_string($conn, $_GET['email']) : null;
$from = isset($_GET['from']) ? $_GET['from'] : null;

// Verify email exists in the database
if ($email) {
    $query = "SELECT * FROM user_form WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 0) {
        echo "<script>alert('Invalid email address.'); window.location.href = '/management-system/forgot-pass/forgot.php';</script>";
        exit();
    }
} else {
    echo "<script>window.location.href = '/management-system/forgot-pass/forgot.php';</script>";
    exit();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset-submit'])) {
    $newPassword = mysqli_real_escape_string($conn, $_POST['new-password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirm-password']);

    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password for the specific email
        $updateQuery = "UPDATE user_form SET password = '$hashedPassword' WHERE email = '$email'";
        $updateResult = mysqli_query($conn, $updateQuery);

        if ($updateResult) {
            // Redirect based on the source of the request
            if ($from === 'admin-dash') {
                echo "<script>alert('Password successfully reset.'); window.location.href = '/management-system/admin/student-dash.php';</script>";
            } elseif ($from === 'student-info') {
                echo "<script>alert('Password successfully reset.'); window.location.href = '/management-system/student/student-info.php';</script>";
            } else {
                echo "<script>alert('Password successfully reset.'); window.location.href = '/management-system/base.php';</script>";
            }
        } else {
            echo "<script>alert('Error resetting password. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Passwords do not match.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="pass.css">
    <style>
        .input-group {
            margin-bottom: 15px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .back {
            margin-top: 10px;
            display: block;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="text-center">
                    <h3><i class="fa fa-lock fa-4x"></i></h3>
                    <h2 class="text-center">Reset Password</h2>
                    <p>You can reset your password here.</p>
                    <div class="panel-body">
                        <form id="reset-form" role="form" autocomplete="off" class="form" method="post">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock color-blue"></i></span>
                                    <input id="new-password" name="new-password" placeholder="New Password" class="form-control" type="password" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock color-blue"></i></span>
                                    <input id="confirm-password" name="confirm-password" placeholder="Confirm New Password" class="form-control" type="password" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <input name="reset-submit" class="btn btn-lg btn-primary btn-block" value="Reset Password" type="submit">
                            </div>

                            <div class="back">
                                <label for="back">Go back to login? <a href="/management-system/base.php">Click here.</a></label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>