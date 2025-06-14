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

$result = $conn->query("SELECT * FROM books ");
?>
<?php include('./includes/header.php'); ?>
<main class="main-content">
<div class="toper">
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
        echo "You have Borrowed <span class='alert-btn'>$borrowed_books/5</span> Books!";
    }
    ?> 
</span> 
    <div class="search-bar" style='margin-bottom:1em'>
        <input type="text" id="searchInput" placeholder="Browse Books...">
        <i class="fas fa-search"></i>
    </div>
</div>
</div>

    <div class="card-container">
        <?php while ($book = $result->fetch_assoc()): ?>
        <div class="book-card">
            <img src="<?php if($book['cover']){echo $book['cover'];}else{echo '../assets/images/login-bg.jpg';}; ?>" alt="Book Cover">
            <h3 class="book-title"><?php echo $book['title']; ?></h3>
            <p class="book-author"><?php echo $book['author']; ?></p>
            <!-- <p class="book-status">
            </p> -->
            <div class="down-card">
                <span>
                <?php echo ($book['quantity'] > 0) ? "Available" : "<span style='color:red'>Out of Stock</span>"; ?>
                </span>
                <a class="btn primary-link borrow-book" data-id="<?php echo $book['id']; ?>">Borrow</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</main>

<style>
    .card-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 23%));
        gap: 20px;
        padding: 20px;
    }
    
    .book-card {
        background: white;
        padding-bottom: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .book-card img {
        width: 100%;
        height: 150px;
        object-fit: fit;
        border-radius: 5px;
    }

    .down-card{
        display:flex;
        flex-wrap:no-wrap;
        width:100%;
        padding:5px 8px;
        font-size:14px;
        justify-content:space-between;
        align-items:center;
    }

    .book-card h3 {
        margin: 10px 0;
    }

    .book-status {
        font-weight: light;
    }

    @media (max-width: 700px) {
    .card-container {
        grid-template-columns: repeat(2,1fr);
        gap: 20px;
        padding: 10px;
    }
    .down-card{
        flex-wrap:wrap;
    }
    }

</style>

<script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let searchText = this.value.toLowerCase();
        document.querySelectorAll(".book-card").forEach(function(card) {
            let title = card.querySelector(".book-title").textContent.toLowerCase();
            let author = card.querySelector(".book-author").textContent.toLowerCase();
            card.style.display = (title.includes(searchText) || author.includes(searchText)) ? "" : "none";
        });
    });

    $(document).on('click', '.borrow-book', function() {
        let bookId = $(this).data('id');
        $.post('books.php', { action: 'borrow', book_id: bookId }, function(response) {
            alert(response.message);
            location.reload();
        }, 'json').fail(function() {
            alert('Error processing request. Please try again.');
        });
    });
</script>

</body>
</html>
