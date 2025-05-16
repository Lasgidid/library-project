
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$book_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param('i', $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $quantity = (int) $_POST['quantity'];
    
    $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, genre = ?, quantity = ? WHERE id = ?");
    $stmt->bind_param('sssii', $title, $author, $genre, $quantity, $book_id);
    $stmt->execute();
    header('Location: dashboard.php');
    exit();
}
?>
<!-- header here -->

<?php include('./includes/header.php') ?>
        <!-- Main Content -->
        <main class="main-content" style='1.5em 0.3em'>
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Update Book </h1>
                <div class="user-info">
                    <span class="admin-name"><?php echo $_SESSION['name']; ?> </span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>

            
            

            <!-- form for edit here -->
            <div class="container profile-container  mt-5" style='marign:80px 1px;'>
            <div style='margin:2em 0.5em'>
            <p style='margin:2em 0.5em; color:green; text-transform:capitalize'><?php echo $book['title']; ?> by <?php echo $book['author']; ?></p>
            <hr>    
        </div>
            <form id="" method="POST">
                    <div class="form-group">
                        <label for="bookTitle">Title</label>
                        <input type="text"name="title" value="<?php echo $book['title']; ?>" id="bookTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="bookAuthor">Author</label>
                        <input type="text"name="author"  value="<?php echo $book['author']; ?>"  id="bookAuthor" required>
                    </div>
                    <div class="form-group">
                        <label for="bookGenre">Genre</label>
                        <select id="bookGenre"name="genre"  required>
                            <option value="<?php echo $book['genre']; ?>"><?php echo $book['genre']; ?></option>
                            <option value="Fiction">Fiction</option>
                            <option value="Non-Fiction">Non-Fiction</option>
                            <option value="Science">Science</option>
                            <option value="Technology">Technology</option>
                            <option value="History">History</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number"name="quantity"  value="<?php echo $book['quantity']; ?>"  id="quantity">
                    </div>
                    <div class="modal-actions">
                        <button type="submit" name="edit_book" class="primary-btn">Update Book</button>
                    </div>
                </form>
    </div>
        </main>

       
    </div>
   
</body>
<script src="../js/customadmin.js"></script>
</html>