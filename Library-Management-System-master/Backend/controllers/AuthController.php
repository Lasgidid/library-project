-- /controllers/AuthController.php (Authentication Logic)
<?php
session_start();
include '../config/db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    $query = $conn->prepare("SELECT * FROM users WHERE email = ? && role = ?");
    $query->bind_param('ss', $email,$role);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            if ($role === 'admin') {
                header('Location: ../admin/dashboard.php');
            } elseif($role === 'user') {
                header('Location: ../user/dashboard.php');
            }else{
                $_SESSION['error_message']= "invalid Credentials";
                header('location:../public/login.php');
            }
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid email or password.";
            header('location:../public/login.php');
        }
    } else {
        $_SESSION['error_message'] = "User not found."; 
        header('location:../public/login.php');
    }
}
?>
