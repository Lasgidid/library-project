<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

// Fetch out-of-stock books
$query = "SELECT * FROM books WHERE quantity = 0 ORDER BY title ASC";
$result = $conn->query($query);
?>

<?php include('./includes/header.php'); ?>
<main class="main-content">
<div class="top">
<div class="top-bar">
        <h1>Out of Stock Books</h1>
        <div class="user-info">
            <span class="admin-name"><?php echo $_SESSION['name']; ?></span>
            <i class="fas fa-user-circle"></i>
        </div>
    </div>

    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search books...">
        <i class="fas fa-search"></i>
    </div>
</div>

    <div class="table-container">
        <table class="books-table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                </tr>
            </thead>
            <tbody id="booksTableBody">
                <?php while ($book = $result->fetch_assoc()): ?>
                <tr>
                    <td class="book-cover"><img src="<?php if($book['cover']){echo $book['cover'];}else{echo '../assets/images/login-bg.jpg';}; ?>" alt="Book Cover"></td>
                    <td class="book-title"><?php echo $book['title']; ?></td>
                    <td class="book-author"><?php echo $book['author']; ?></td>
                    <td><?php echo $book['genre']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let searchText = this.value.toLowerCase();
        document.querySelectorAll("#booksTableBody tr").forEach(function(row) {
            let title = row.querySelector(".book-title").textContent.toLowerCase();
            let author = row.querySelector(".book-author").textContent.toLowerCase();
            row.style.display = (title.includes(searchText) || author.includes(searchText)) ? "" : "none";
        });
    });
</script>
</body>
</html>
