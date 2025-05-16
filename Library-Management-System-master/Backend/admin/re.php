-- /admin/manage_books.php (Book Management - CRUD with Separate Edit Page)
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

// Handle book creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $quantity = (int) $_POST['quantity'];
    
    $stmt = $conn->prepare("INSERT INTO books (title, author, genre, quantity, availability) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param('sssi', $title, $author, $genre, $quantity);
    $stmt->execute();
    header('Location: manage_books.php');
    exit();
}

// Handle book deletion
if (isset($_GET['delete'])) {
    $book_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param('i', $book_id);
    $stmt->execute();
    header('Location: manage_books.php');
    exit();
}

// Fetch books
$result = $conn->query("SELECT * FROM books");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Books</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Manage Books</h2>
        <form method="POST" class="mb-3">
            <input type="text" name="title" placeholder="Title" required class="form-control mb-2">
            <input type="text" name="author" placeholder="Author" required class="form-control mb-2">
            <input type="text" name="genre" placeholder="Genre" required class="form-control mb-2">
            <input type="number" name="quantity" placeholder="Quantity" required class="form-control mb-2" min="1">
            <button type="submit" name="add_book" class="btn btn-success">Add Book</button>
        </form>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Quantity</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($book = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $book['id']; ?></td>
                    <td><?php echo $book['title']; ?></td>
                    <td><?php echo $book['author']; ?></td>
                    <td><?php echo $book['genre']; ?></td>
                    <td><?php echo $book['quantity']; ?></td>
                    <td><?php echo ($book['quantity'] > 0) ? 'Available' : 'Out of Stock'; ?></td>
                    <td>
                        <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="manage_books.php?delete=<?php echo $book['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>