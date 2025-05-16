<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>Library Management System</h1>
            <div class="message" style='text-align:center;color:red;padding:5px 2px'>
            <?php
            session_start();
                if (isset($_SESSION['error_message'])) {
                    echo "<p>" . $_SESSION['error_message'] . "</p>";
                    unset($_SESSION['error_message']); // Remove message after displaying it
                }
            ?>
            </div>
            <form id="loginForm" action="../controllers/AuthController.php" method="POST">
                <div class="form-group">
                    <select id="roleSelect" name='role' required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>
    <!-- <script src="../js/login.js"></script> -->
</body>
</html>