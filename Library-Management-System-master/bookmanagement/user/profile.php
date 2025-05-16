<!-- -- /admin/edit_profile.php (Admin Profile Edit) -->
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param('sssi', $name, $email, $password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param('ssi', $name, $email, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['name'] = $name;
        echo "<script>alert('Profile updated successfully.'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile. Please try again.');</script>";
    }
}

// Fetch current user details
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();



$borrowed_query = $conn->prepare("SELECT COUNT(*) AS total FROM loans WHERE user_id = ? AND return_date IS NULL");
$borrowed_query->bind_param('i', $user_id);
$borrowed_query->execute();
$borrowed_books = $borrowed_query->get_result()->fetch_assoc()['total']; 
?>

<?php include('./includes/header.php'); ?>
<main class="main-content">
    <div class="top-bar">
        <h1>Edit Profile</h1>
        <div class="user-info">
            <span class="admin-name"><?php echo $_SESSION['name']; ?></span>
            <i class="fas fa-user-circle"></i>
        </div>
    </div>

   <div class="row">
   <div id="profile-section" class="dashboard-section">
                <div class="profile-container">
                    <div class="profile-header">
                        <i class="fas fa-user-circle profile-avatar"></i>
                        <h4><?php echo $user['name']; ?></h4>
                        <!-- <p>Member since: January 2025</p> -->
                    </div>
                    <div class="profile-details">
                        <div class="detail-group">
                            <label>Email</label>
                            <p><?php echo $user['email']; ?></p>
                        </div>
                        <!-- <div class="detail-group">
                            <label>Library Card Number</label>
                            <p>LIB-2025-001</p>
                        </div> -->
                        <div class="detail-group">
                            <label>Current Borrowed Books</label>
                            <p><?php echo $borrowed_books;?> of 5 maximum</p>
                        </div>
                        <!-- <div class="detail-group">
                            <label>Account Status</label>
                            <p class="status-active">Active</p>
                        </div> -->
                    </div>
                    
                </div>
            </div>

    <div class="form-container profile-form">
        <form method="POST" action="profile.php">
            <p  style='color:red; padding:12px 0px'>Attention: <small style='color:gray'>To change your E-mail address reachout to the admin!!</small>            </p>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" value="<?php echo $user['name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" disabled value="<?php echo $user['email']; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">New Password (Optional)</label>
                <input type="password" name="password" id="password">
            </div>
            <div class="form-actions">
                <button type="submit" name="update_profile" class="btn primary-btn">Update Profile</button>
            </div>
        </form>
    </div>
   </div>
</main>
</body>
</html>
