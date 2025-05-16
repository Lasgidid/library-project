<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

// Fetch overdue books
$today = date('Y-m-d');
$query = "SELECT loans.id, users.name AS user_name, books.title, books.cover, loans.due_date
          FROM loans 
          JOIN users ON loans.user_id = users.id 
          JOIN books ON loans.book_id = books.id 
          WHERE loans.return_date IS NULL AND loans.due_date < ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $today);
$stmt->execute();
$result = $stmt->get_result();

// Count overdue books
$overdue_query = "SELECT COUNT(*) AS total FROM loans WHERE due_date < ? AND return_date IS NULL ";
$overdue_stmt = $conn->prepare($overdue_query);
$overdue_stmt->bind_param('s', $today);
$overdue_stmt->execute();
$overdue_result = $overdue_stmt->get_result();
$overdue_count = $overdue_result->fetch_assoc()['total'];
?>

<?php include('./includes/header.php') ?>
<main class="main-content">
    <div class="top">
        <div class="top-bar">
            <h1>Overdue Returns</h1>
            <div class="user-info">
                <span class="admin-name"><?php echo $_SESSION['name']; ?> </span>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>

        <div class="action-bar">
            <button id="alertBox" class="delete-btn">
                <?php echo $overdue_count;?> books overdue for return!
            </button>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search books...">
                <i class="fas fa-search"></i>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="books-table">
            <thead>
                <tr>
                    
                    <th>User Name</th>
                    <th>Book Cover</th>
                    <th>Book Title</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  
                    <td><?php echo $row['user_name']; ?></td>
                    <td>
                        <img src="../uploads/<?php echo $row['cover']; ?>" alt="Book Cover" style="width: 80px; height: 100px;">
                    </td>
                    <td><?php echo $row['title']; ?></td>
                    <td class="danger-color overdue-book"><?php echo formatBorrowDate($row['due_date']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

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
