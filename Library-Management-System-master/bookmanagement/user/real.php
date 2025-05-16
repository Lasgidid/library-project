<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'borrow') {
    header('Content-Type: application/json');
    $book_id = $_POST['book_id'];
    $borrow_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+14 days'));
    
    $check_existing = $conn->prepare("SELECT COUNT(*) AS count FROM loans WHERE user_id = ? AND book_id = ? AND return_date IS NULL");
    $check_existing->bind_param('ii', $user_id, $book_id);
    $check_existing->execute();
    $existing_result = $check_existing->get_result()->fetch_assoc();
    if ($existing_result['count'] > 0) {
        echo json_encode(["status" => "error", "message" => "You have already borrowed this book. Please return it before borrowing again."]);
        exit();
    }
    
    $check_limit = $conn->prepare("SELECT COUNT(*) AS total FROM loans WHERE user_id = ? AND return_date IS NULL");
    $check_limit->bind_param('i', $user_id);
    $check_limit->execute();
    $limit_result = $check_limit->get_result()->fetch_assoc();
    if ($limit_result['total'] >= 5) {
        echo json_encode(["status" => "error", "message" => "You have reached the maximum borrowing limit (5 books). Return a book to borrow another."]);
        exit();
    }
    
    $check_quantity = $conn->prepare("SELECT quantity FROM books WHERE id = ?");
    $check_quantity->bind_param('i', $book_id);
    $check_quantity->execute();
    $quantity_result = $check_quantity->get_result()->fetch_assoc();
    if ($quantity_result['quantity'] <= 0) {
        echo json_encode(["status" => "error", "message" => "This book is currently out of stock."]);
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO loans (user_id, book_id, borrow_date, due_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iiss', $user_id, $book_id, $borrow_date, $due_date);
    if ($stmt->execute()) {
        $update_book = $conn->prepare("UPDATE books SET quantity = quantity - 1 WHERE id = ?");
        $update_book->bind_param('i', $book_id);
        $update_book->execute();
        echo json_encode(["status" => "success", "message" => "Book borrowed successfully. Due date: $due_date"]);
        exit();
    }
    echo json_encode(["status" => "error", "message" => "Error borrowing book. Please try again."]);
    exit();
}

$borrowed_query = $conn->prepare("SELECT COUNT(*) AS total FROM loans WHERE user_id = ? AND return_date IS NULL");
$borrowed_query->bind_param('i', $user_id);
$borrowed_query->execute();
$borrowed_books = $borrowed_query->get_result()->fetch_assoc()['total'];

$max_books = 5;
$available_for_borrowing = max(0, $max_books - $borrowed_books);

$result = $conn->query("SELECT * FROM books WHERE quantity > 0");
?>

<?php include('./includes/header.php'); ?>
<main class="main-content">
<div class="top">
<div class="top-bar">
        <h1>Browse Books</h1>
        <div class="user-info">
            <span class="user-name"><?php echo $_SESSION['name']; ?></span>
            <i class="fas fa-user-circle"></i>
        </div>
    </div>
<div class="action-bar">
<span id="a" class="alert">
    <?php if($borrowed_books < 1){
        echo '';
    }else{
        echo "You have <span class='alert-btn'>$borrowed_books</span> Books to return!";

    }
    ?>
</span>
    <div class="search-bar" style='margin-bottom:1em'>
        <input type="text" id="searchInput" placeholder="Browse Books...">
        <i class="fas fa-search"></i>
    </div>
</div>
</div>
   

    <div class="table-container">
        <table class="history-table books-table">
            <thead>
            <tr>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>status</th>
                            <th>Actions</th>
                        </tr>
            </thead>
            <tbody id="bookTable">
                        <?php while ($book = $result->fetch_assoc()): ?>
                        <tr>
                            <td ><img src="../assets/images/login-bg.jpg" alt=""></td>
                            <td class="book-title"><?php echo $book['title']; ?></td>
                            <td class="book-author"><?php echo $book['author']; ?></td>
                            <td><?php if ($book['quantity'] > 0) {
                                echo "Available";
                            }else{
                                echo "<span style='color:red'>Out of Stock</span>";
                            } ?></td>
                            <td>
                                <button class="btn primary-btn borrow-book" data-id="<?php echo $book['id']; ?>">Borrow</button>
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

    $(document).on('click', '.borrow-book', function() {
                let bookId = $(this).data('id');
                $.post('dashboard.php', { action: 'borrow', book_id: bookId }, function(response) {
                    alert(response.message);
                    location.reload();
                }, 'json').fail(function() {
                    alert('Error processing request. Please try again.');
                });
            });
</script>
</body>
</html>
