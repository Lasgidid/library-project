<!-- -- /user/history.php (User Borrow & Return History) -->
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

$user_id = $_SESSION['user_id'];

// Count overdue books for the logged-in user
$today = date('Y-m-d');
$overdue_query = "SELECT COUNT(*) AS total FROM loans WHERE due_date < ? AND user_id = ? AND return_date IS NULL";

$overdue_stmt = $conn->prepare($overdue_query);
$overdue_stmt->bind_param('si', $today, $user_id); // Corrected parameter types (string, integer)
$overdue_stmt->execute();

$overdue_result = $overdue_stmt->get_result();
$overdue_count = $overdue_result->fetch_assoc()['total'];


// count for total borrowed books
$borrowed_query = $conn->prepare("SELECT COUNT(*) AS total FROM loans WHERE user_id = ? AND return_date IS NULL");
$borrowed_query->bind_param('i', $user_id);
$borrowed_query->execute();
$borrowed_books = $borrowed_query->get_result()->fetch_assoc()['total'];

// Fetch the latest 5 borrow and return history
$query = "SELECT books.title, books.cover, books.author, loans.borrow_date, loans.due_date, loans.return_date, 
                 CASE WHEN loans.return_date IS NOT NULL THEN 'Returned' ELSE 'Not Returned' END AS status 
          FROM loans 
          JOIN books ON loans.book_id = books.id 
          WHERE loans.user_id = ? 
          ORDER BY loans.return_date ASC 
          LIMIT 3";  // Fetch only the latest 5 records

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Count total borrow/return history
$total_query = "SELECT COUNT(*) AS total FROM loans WHERE user_id = ?";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param('i', $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_borrow_return = $total_result->fetch_assoc()['total'];



?>

<?php include('./includes/header.php'); ?>
<main class="main-content">
  
  <div class="top-bar">
        <h1>Dashboard</h1>
        <div class="user-info">
            <span class="user-name"><?php echo $_SESSION['name']; ?></span>
            <i class="fas fa-user-circle"></i>
        </div>
    </div>

    <div class="row">
    <div class="not-box books">
    <h2><?php echo $borrowed_books; ?> / 5</h2>
    <br>

        <p>Books Borrowed</p>
        <br>

        <a href="books.php" class='primary-link'>Borrow Book</a>
    </div>

    <div class="not-box overdue">
    <h2><?php echo $overdue_count; ?></h2>
    <br>

        <p>Overdue Returns</p>
        <br>

        <a class='quick-link' href="overdue_report.php">View Detail</a>
    </div>
    
    <div class="not-box overdue">
    <h2><?php echo $total_borrow_return; ?></h2>
    <br>

        <p>Borrowed & Returns History</p>
        <br>

        <a class='quick-link' href="history.php">View Detail</a>
    </div>
    
</div>

    <div class="" style='margin-top:3em; color:gray'>
        <p>Borrow and Return History</p>
    </div>
  

    <div class="table-containe" style='margin-top:2.7em;'>
        <table class="history-table books-table">
            <thead>
                <tr>
                    <th>Book Cover</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="historyTableBody">
                <?php while ($record = $result->fetch_assoc()): ?>
                <tr>
                    <td class="book-cover"><img src="<?php if($record['cover']){echo $record['cover'];}else{echo '../assets/images/login-bg.jpg';}; ?>" alt="Book Cover">                    </td>
                    <td class="book-title"><?php echo $record['title']; ?></td>
                    <td class="book-author"><?php echo $record['author']; ?></td>
                    <td><?php echo $record['borrow_date']; ?></td>
                    <td><?php echo $record['due_date']; ?></td>
                    <td><?php echo $record['return_date'] ? $record['return_date'] : 'Not Returned'; ?></td>
                    <td><?php echo $record['status']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let searchText = this.value.toLowerCase();
        document.querySelectorAll("#historyTableBody tr").forEach(function(row) {
            let title = row.querySelector(".book-title").textContent.toLowerCase();
            let author = row.querySelector(".book-author").textContent.toLowerCase();
            row.style.display = (title.includes(searchText) || author.includes(searchText)) ? "" : "none";
        });
    });
</script>
</body>
</html>
