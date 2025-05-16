<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password']; // Retain old password if not updated

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
    $stmt->bind_param('sssi', $name, $email, $password, $user_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('User updated successfully!'); window.location.href = 'manage_users.php';</script>";
    } else {
        echo "<script>alert('Error updating user. Please try again.');</script>";
    }
}
?>

<?php include('./includes/header.php'); ?>
<main class="main-content">
    <div class="top-bar">
        <h1>Edit User Details</h1>
        <div class="user-info">
            <span class="admin-name"><?php echo $_SESSION['name']; ?> </span>
            <i class="fas fa-user-circle"></i>
        </div>
    </div>

    <div class="container  profile-container mt-5">
        <form method="POST">
            <div class="form-group">
                <label for="userName">Full Name</label>
                <input type="text" name="name" id="userName" value="<?php echo $user['name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="userEmail">Email</label>
                <input type="email" name="email" id="userEmail" value="<?php echo $user['email']; ?>" required>
            </div>
            <div class="form-group">
                <label for="userPassword">New Password (Leave blank to keep current)</label>
                <input type="password" name="password" id="userPassword">
            </div>
            <div class="modal-actions">
                <button type="submit" name="update_user" class="primary-btn">Update User</button>
            </div>
        </form>
    </div>
</main>
<script src="../js/customadmin.js"></script>
</body>
</html>
