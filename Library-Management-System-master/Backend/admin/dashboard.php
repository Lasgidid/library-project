<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';


// Handle book deletion
if (isset($_GET['delete'])) {
    $book_id = $_GET['delete'];
    echo "<script>
        if (confirm('Are you sure you want to delete this book?')) {
            window.location.href = '../controllers/delete_book.php?confirm_delete=$book_id';
        } else {
            window.location.href = 'dashboard.php';
        }
    </script>";
    exit();
}




// Fetch books
$result = $conn->query("SELECT * FROM books");

// Count out of stock books
$out_of_stock_query = "SELECT COUNT(*) AS total FROM books WHERE quantity = 0";
$out_of_stock_result = $conn->query($out_of_stock_query);
$out_of_stock_count = $out_of_stock_result->fetch_assoc()['total'];

// Count overdue books
$today = date('Y-m-d');
$overdue_query = "SELECT COUNT(*) AS total FROM loans WHERE due_date < ? AND return_date IS NULL ";
$overdue_stmt = $conn->prepare($overdue_query);
$overdue_stmt->bind_param('s', $today);
$overdue_stmt->execute();
$overdue_result = $overdue_stmt->get_result();
$overdue_count = $overdue_result->fetch_assoc()['total'];

// Count borrowed books
$borrowed_query = "SELECT COUNT(*) AS total FROM loans WHERE return_date IS NULL";
$borrowed_result = $conn->query($borrowed_query);
$borrowed_count = $borrowed_result->fetch_assoc()['total'];

// // Count total books
$query = "SELECT COUNT(*) AS total_books FROM books";
$total = $conn->query($query);
$row = $total->fetch_assoc();
$total_books = $row['total_books'];

?>

<!-- header here -->

<?php include('./includes/header.php') ?>
        <!-- Main Content -->
        <main class="main-content">
                <!-- Top Bar -->
                <div class="top-bar">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span class="admin-name"><?php echo $_SESSION['name']; ?> </span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>


<div class="row">
    <div class="not-box books">
    <h2><?php echo $total_books; ?></h2>
    <br>
        <p>Total Books</p>
        <br>
        <a href="manage_books.php"  class='primary-link'>Manage Books</a>

    </div>
    
    <div class="not-box stock">
    <h2><?php echo $out_of_stock_count; ?></h2>
    <br>
        <p>Out of Stock Books</p>
        <br>
        <a href="out_of_stock.php" class='quick-link'>View Detail</a>
    </div>

    <div class="not-box overdue">
    <h2><?php echo $overdue_count; ?></h2>
    <br>

        <p>Overdue Returns</p>
        <br>

        <a class='quick-link' href="overdue_reports.php">View Detail</a>
    </div>
    <div class="not-box borrowed">
    <h2><?php echo $borrowed_count; ?></h2>
    <br>

        <p>Borrowed Books</p>
        <br>

        <a href="borrowed_books.php" class='quick-link'>View Detail</a>
    </div>
</div>


            <!-- Action Bar -->
            <div class="action-bar" style='padding-top:1em; color:gray'>
            <p>List of all the books </p>
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search books...">
                    <i class="fas fa-search"></i>
                </div>
            </div>

            <!-- Books Table -->
            <div class="table-contain">
        <table class="books-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Quantity</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="booksTableBody">
                <?php while ($book = $result->fetch_assoc()): ?>
                <tr>
                    <td class="book-title"><?php echo $book['title']; ?></td>
                    <td class="book-author"><?php echo $book['author']; ?></td>
                    <td class="book-genre"><?php echo $book['genre']; ?></td>
                    <td><?php echo $book['quantity']; ?></td>
<td><?php if ($book['quantity'] > 0) {
                                echo "<span style='color:green'>Available</span>";
                            }else{
                                echo "<span style='color:red'>Out of Stock</span>";
                            } ?></td>                    <td>
                        <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn edit-btn">Edit</a>
                        <a href="dashboard.php?delete=<?php echo $book['id']; ?>" class="btn delete-btn">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
        </main>

       
    </div>
    <script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let searchText = this.value.toLowerCase();
        document.querySelectorAll("#booksTableBody tr").forEach(function(row) {
            let title = row.querySelector(".book-title").textContent.toLowerCase();
            let author = row.querySelector(".book-author").textContent.toLowerCase();
            let genre = row.querySelector(".book-genre").textContent.toLowerCase();
            row.style.display = (title.includes(searchText) || author.includes(searchText) || genre.includes(searchText)) ? "" : "none";
        });
    });
</script>
</body>
<script src="../js/customadmin.js"></script>
</html>