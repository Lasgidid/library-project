<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

// Handle AJAX user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $query->bind_param('s', $email);
    $query->execute();
    $result = $query->get_result();


    $chexkuser = $result->fetch_assoc();

    if($chexkuser == ''){
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $name, $email, $password, $role);
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "User added successfully"]);
    }else{
        echo json_encode(["status" => "error", "message" => "email address already exist"]);

    }

    
    
    exit();
}

// Handle AJAX user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    echo json_encode(["status" => "success", "message" => "User deleted successfully"]);
    exit();
}

// Fetch users
$result = $conn->query("SELECT * FROM users");
?>

<!-- header here -->

<?php include('./includes/header.php') ?>
        <!-- Main Content -->
        <main class="main-content">
           <div class="top">
             <!-- Top Bar -->
             <div class="top-bar">
                <h1>Users Management</h1>
                <div class="user-info">
                    <span class="admin-name"><?php echo $_SESSION['name']; ?> </span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <button id="addBookBtn" class="primary-btn">
                    <i class="fas fa-plus"></i> Add New User
                </button>
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search books...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
           </div>

            <!-- Books Table -->
            <div class="table-container">
                <table class="users-table books-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <td class="user-name"><?php echo $user['name']; ?></td>
                    <td class="user-email"><?php echo $user['email']; ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td>
                        <a href="update_user.php?id=<?php echo $user['id']; ?>" class="btn edit-btn">Edit</a>
                        <button class="btn delete-btn btn-sm delete-user" data-id="<?php echo $user['id']; ?>">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
            </div>
        </main>

        <!-- Add/Edit Book Modal -->
        <div id="formModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Add New User</h2>
                    <button class="close-btn"><i class="fas fa-times"></i></button>
                </div>
                <form id="modalForm" class="addUserForm" method="POST">
                    <div class="form-group">
                        <label for="Fullname">FullName</label>
                        <input type="text" name="name"  id="Fullname" required>
                    </div>
                    <div class="form-group">
                        <label for="userEmail">Email</label>
                        <input type="email"name="email"  id="userEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password"name="password"  id="password" required>
                    </div>

                    <div class="form-group">
                        <label for="role">User Role</label>
                        <select id="role"name="role"  required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="secondary-btn" id="cancelBtn">Cancel</button>
                        <button type="submit" name="add_user" class="primary-btn">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.addUserForm').submit(function(e) {
                e.preventDefault();
                $.post('manage_users.php', $(this).serialize() + '&action=add_user', function(response) {
                    alert(response.message);
                    location.reload();
                }, 'json');
            });

            $('.delete-user').click(function() {
                let userId = $(this).data('id');
                if (confirm('Are you sure you want to delete this user?')) {
                    $.post('manage_users.php', { action: 'delete_user', user_id: userId }, function(response) {
                        alert(response.message);
                        $('#user-' + userId).remove();
                        location.reload();
                    }, 'json');
                }
            });
        });


       
        
    </script>
     <script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let searchText = this.value.toLowerCase();
        document.querySelectorAll("#userTableBody tr").forEach(function(row) {
            let name = row.querySelector(".user-name").textContent.toLowerCase();
            let email = row.querySelector(".user-email").textContent.toLowerCase();
            row.style.display = (name.includes(searchText) || email.includes(searchText)) ? "" : "none";
        });
    });
</script>
</body>

<script src="../js/customadmin.js"></script>
</html>