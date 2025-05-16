<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'return') {
    $book_id = $_POST['book_id'];
    $return_date = date('Y-m-d');
    
    // Update loan record
    $stmt = $conn->prepare("UPDATE loans SET return_date = ? WHERE user_id = ? AND book_id = ? AND return_date IS NULL");
    $stmt->bind_param('sii', $return_date, $user_id, $book_id);
    if ($stmt->execute()) {
        // Increase book quantity
        $update_book = $conn->prepare("UPDATE books SET quantity = quantity + 1 WHERE id = ?");
        $update_book->bind_param('i', $book_id);
        $update_book->execute();
        echo json_encode(["status" => "success", "message" => "Book returned successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error returning book. Please try again."]);
    }
    exit();
}






// Fetch borrowed books
$borrowed_result = $conn->prepare("SELECT books.id,books.cover, books.title, books.author,loans.borrow_date, loans.due_date FROM loans 
                                   JOIN books ON loans.book_id = books.id 
                                   WHERE loans.user_id = ? AND loans.return_date IS NULL");
$borrowed_result->bind_param('i', $user_id);
$borrowed_result->execute();
$borrowed_books = $borrowed_result->get_result();
?>
<?php include('./includes/header.php'); ?>
<main class="main-content">
<div class="top">
<div class="top-bar">
        <h1>Borrowed Books</h1>
        <div class="user-info">
            <span class="user-name"><?php echo $_SESSION['name']; ?></span>
            <i class="fas fa-user-circle"></i>
        </div>
    </div>

    <div class="search-bar" style='margin-bottom:1em'>
        <input type="text" id="searchInput" placeholder="Browse Books...">
        <i class="fas fa-search"></i>
    </div>
</div>

    <div class="table-container">
        <table class=" books-table">
        <thead>
                <tr>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Borrowed Date</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id='bookTable'>
                <?php while ($book = $borrowed_books->fetch_assoc()): ?>
                <tr>
                    <td class='book-cover'><img src="<?php if($book['cover']){echo $book['cover'];}else{echo '../assets/images/login-bg.jpg';}; ?>" alt="Book Cover"></td>
                    <td class='book-title'><?php echo $book['title']; ?></td>
                    <td class='book-author'><?php echo $book['author']; ?></td>
                    <td><?php echo formatBorrowDate($book['borrow_date']); ?></td>
                    <td class="text-danger">Due: <?php echo formatDate($book['due_date']); ?></td>
                    <td>
                        <button class="btn edit-btn return-book" data-id="<?php echo $book['id']; ?>">Return</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let searchText = this.value.toLowerCase();
        document.querySelectorAll("#bookTable tr").forEach(function(row) {
            let title = row.querySelector(".book-title").textContent.toLowerCase();
            let author = row.querySelector(".book-author").textContent.toLowerCase();
            row.style.display = (title.includes(searchText) || author.includes(searchText)) ? "" : "none";
        });

    });

    $(document).ready(function() {
            $(document).on('click', '.return-book', function() {
                let bookId = $(this).data('id');
                $.post('borrowed_books.php', { action: 'return', book_id: bookId }, function(response) {
                    if (response.status === "success") {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                }, 'json').fail(function() {
                    alert('Error processing request. Please try again.');
                });
            });
        });
</script>
</body>
</html>
