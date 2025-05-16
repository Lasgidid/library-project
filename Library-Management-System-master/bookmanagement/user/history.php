<!-- -- /user/history.php (User Borrow & Return History) -->
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch user borrow and return history
$query = "SELECT books.title,books.cover, books.author, loans.borrow_date, loans.due_date, loans.return_date, 
                 CASE WHEN loans.return_date IS NOT NULL THEN 'Returned' ELSE 'Not Returned' END AS status 
          FROM loans 
          JOIN books ON loans.book_id = books.id 
          WHERE loans.user_id = ? ORDER BY loans.return_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include('./includes/header.php'); ?>
<main class="main-content">
  <div class="top">
  <div class="top-bar">
        <h1>Borrow & Return History</h1>
        <div class="user-info">
            <span class="user-name"><?php echo $_SESSION['name']; ?></span>
            <i class="fas fa-user-circle"></i>
        </div>
    </div>

    <div class="search-bar" style='margin-bottom:1em'>
        <input type="text" id="searchInput" placeholder="Search history...">
        <i class="fas fa-search"></i>
    </div>
  </div>

    <div class="table-container">
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
                    <td><?php echo formatBorrowDate($record['borrow_date']); ?></td>
                    <td><?php echo formatDate($record['due_date']); ?></td>
                    <td><?php echo $record['return_date'] ? formatDate($record['return_date']) : 'Not Returned'; ?></td>
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
