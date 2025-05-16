<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

// Ensure user is logged in
$user_id = $_SESSION['user_id'];

// fetching all the overdue books for the user
$today = date('Y-m-d');
$query = "SELECT loans.id, books.title, books.author, books.cover, loans.due_date
          FROM loans 
          JOIN books ON loans.book_id = books.id 
          WHERE loans.user_id = ? AND loans.return_date IS NULL AND loans.due_date < ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('is', $user_id, $today);
$stmt->execute();
$result = $stmt->get_result();

?>
<!-- header here -->

<?php include('./includes/header.php') ?>
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
           <div class="top">
           <div class="top-bar">
                <h1>Overdue Returns</h1>
                <div class="user-info">
                    <span class="admin-name"><?php echo $_SESSION['name']; ?> </span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <button id="alertBox" class="delete-btn">
                    0 books overdue for return!
                </button>
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search books...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
           </div>

            <!-- Books Table -->
            <div class="table-container">
        <table class="books-table">
            <thead>
            <tr>
                    <th>Book Cover</th>
                    <th>Book Title</th>
                    <th>Book Author</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><img src="<?php if($row['cover']){echo $row['cover'];}else{echo '../assets/images/login-bg.jpg';}; ?>" alt="Book Cover"></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['author']; ?></td>
                    <td class="danger-color overdue-book"><?php echo $row['due_date']; ?></td>
                </tr>
               
                
                <?php endwhile; ?>
                
            </tbody>
        </table>
    </div>
        </main>

        
    </div>
    <script>
        $(document).ready(function() {
            let overdueCount = $('.overdue-book').length;
            if (overdueCount > 0) {
                $('#alertBox').removeClass('d-none').text(`Warning: There are ${overdueCount} overdue books!`);
            }
        });
    </script>
</body>
<script src="../js/customadmin.js"></script>
</html>