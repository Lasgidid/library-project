<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

// Handle book return via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'return_book') {
    $loan_id = $_POST['loan_id'];
    $return_date = date('Y-m-d');
    
    // Update loan record
    $stmt = $conn->prepare("UPDATE loans SET return_date = ? WHERE id = ?");
    $stmt->bind_param('si', $return_date, $loan_id);
    if ($stmt->execute()) {
        // Increase book quantity
        $update_book = $conn->prepare("UPDATE books SET quantity = quantity + 1 WHERE id = (SELECT book_id FROM loans WHERE id = ?)");
        $update_book->bind_param('i', $loan_id);
        $update_book->execute();
        echo json_encode(["status" => "success", "message" => "Book returned successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to return book. Try again."]);
    }
    exit();
}

// Fetch full history of borrowed books
$query = "SELECT loans.id AS loan_id, users.name AS user_name, users.email, books.title, books.author, loans.borrow_date, loans.due_date, loans.return_date,
                 CASE WHEN loans.return_date IS NULL THEN 'Not Returned' ELSE 'Returned' END AS status 
          FROM loans 
          JOIN users ON loans.user_id = users.id 
          JOIN books ON loans.book_id = books.id 
          ORDER BY loans.return_date IS NULL DESC, loans.borrow_date DESC";
$result = $conn->query($query);
?>

<?php include('./includes/header.php'); ?>
<main class="main-content">
    <div class="top">
    <div class="top-bar">
        <h1>Borrowed Books History</h1>
        <div class="user-info">
            <span class="admin-name"><?php echo $_SESSION['name']; ?></span>
            <i class="fas fa-user-circle"></i>
        </div>
    </div>

    <div class="action-bar">
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search books or users...">
            <i class="fas fa-search"></i>
        </div>
    </div>
    </div>

    <div class="table-container">
        <table class="books-table">
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="booksTableBody">
                <?php while ($book = $result->fetch_assoc()): ?>
                <tr>
                    <td class="user-name"><?php echo $book['user_name']; ?></td>
                    <td class="user-email"><?php echo $book['email']; ?></td>
                    <td class="book-title"><?php echo $book['title']; ?></td>
                    <td class="book-author"><?php echo $book['author']; ?></td>
                    <td><?php echo formatBorrowDate($book['borrow_date']); ?></td>
                    <td class='danger-color'><?php echo formatDate($book['due_date']); ?></td>
                    <td><?php echo $book['return_date'] ? formatDate($book['return_date']) : 'Not Returned'; ?></td>
                    <td><?php echo $book['status']; ?></td>
                    <td>
                        <?php if ($book['status'] === 'Not Returned'): ?>
                            <button class="btn return-btn edit-btn return-book" data-id="<?php echo $book['loan_id']; ?>">Return</button>
                        <?php else: ?>
                            <p class="btn return-btn " disabled>Returned</p>
                        <?php endif; ?>
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
        document.querySelectorAll("#booksTableBody tr").forEach(function(row) {
            let userName = row.querySelector(".user-name").textContent.toLowerCase();
            let userEmail = row.querySelector(".user-email").textContent.toLowerCase();
            let title = row.querySelector(".book-title").textContent.toLowerCase();
            let author = row.querySelector(".book-author").textContent.toLowerCase();
            row.style.display = (userName.includes(searchText) || userEmail.includes(searchText) || title.includes(searchText) || author.includes(searchText)) ? "" : "none";
        });
    });

    document.querySelectorAll(".return-book").forEach(button => {
        button.addEventListener("click", function() {
            let loanId = this.getAttribute("data-id");
            if (confirm("Are you sure you want to return this book?")) {
                fetch("borrowed_books.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=return_book&loan_id=${loanId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert("Failed to return book. Please try again.");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Error processing request. Please try again.");
                });
            }
        });
    });
</script>
</body>
</html>
