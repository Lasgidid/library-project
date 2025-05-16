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
    
       // Handle file upload
       $cover = NULL;
       if (!empty($_FILES['cover']['name'])) {
           $target_dir = "../uploads/";
           $cover = $target_dir . basename($_FILES["cover"]["name"]);
           move_uploaded_file($_FILES["cover"]["tmp_name"], $cover);
       }
   
       $stmt = $conn->prepare("INSERT INTO books (title, author, genre, quantity, availability, cover) VALUES (?, ?, ?, ?, 1, ?)");
       $stmt->bind_param('sssis', $title, $author, $genre, $quantity, $cover);
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
$result = $conn->query("SELECT * FROM books ORDER BY id DESC");
?>

<!-- header here -->

<?php include('./includes/header.php') ?>
        <!-- Main Content -->
        <main class="main-content">
            <div class="top">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Books Management</h1>
                <div class="user-info">
                    <span class="admin-name"><?php echo $_SESSION['name']; ?> </span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <button id="addBookBtn" class="primary-btn">
                    <i class="fas fa-plus"></i> Add New Book
                </button>
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search books...">
                    <i class="fas fa-search"></i>
                </div>
            </div>

            </div>

            <!-- Books Table -->
            <div class="table-container">
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
                            } ?>
                    </td>  
                    <td>
                        <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn edit-btn">Edit</a>
                        <a href="dashboard.php?delete=<?php echo $book['id']; ?>" class="btn delete-btn">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
        </main>

        <!-- Add/Edit Book Modal -->
        <div id="formModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Add New Book</h2>
                    <button class="close-btn"><i class="fas fa-times"></i></button>
                </div>
                <form id="modalForm" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="bookTitle">Title</label>
                        <input type="text"name="title"  id="bookTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="bookAuthor">Author</label>
                        <input type="text"name="author"  id="bookAuthor" required>
                    </div> 
                    <div class="form-group">
                        <label for="bookcover">Author</label>
                        <input type="file" name="cover" id='bookcover' accept="image/*" class="form-control mb-2">
                    </div>
                    <div class="form-group">
                        <label for="bookGenre">Genre</label>
                        <select id="bookGenre"name="genre"  required>
                            <option value="">Select Genre</option>
                            <option value="Fiction">Fiction</option>
                            <option value="Non-Fiction">Non-Fiction</option>
                            <option value="Science">Science</option>
                            <option value="Technology">Technology</option>
                            <option value="History">History</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number"name="quantity"  id="quantity">
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="secondary-btn" id="cancelBtn">Cancel</button>
                        <button type="submit" name="add_book" class="primary-btn">Save Book</button>
                    </div>
                </form>
            </div>
        </div>
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