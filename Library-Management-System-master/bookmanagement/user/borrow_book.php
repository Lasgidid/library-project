-- borrow_book.php (Borrow Book Logic with AJAX)
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];
$max_books = 5;
$due_days = 14;

// Check how many books the user has already borrowed
$loan_check = $conn->prepare("SELECT COUNT(*) AS total FROM loans WHERE user_id = ? AND return_date IS NULL");
$loan_check->bind_param('i', $user_id);
$loan_check->execute();
$loan_result = $loan_check->get_result()->fetch_assoc();

if ($loan_result['total'] >= $max_books) {
    echo json_encode(["status" => "error", "message" => "You have reached the maximum limit of borrowed books (5)."]);
    exit();
}

// Borrow book if available
$borrow_date = date('Y-m-d');
$due_date = date('Y-m-d', strtotime("+$due_days days"));

$insert_loan = $conn->prepare("INSERT INTO loans (user_id, book_id, borrow_date, due_date) VALUES (?, ?, ?, ?)");
$insert_loan->bind_param('iiss', $user_id, $book_id, $borrow_date, $due_date);
$insert_loan->execute();

// Update book availability
$update_book = $conn->prepare("UPDATE books SET availability = FALSE WHERE id = ?");
$update_book->bind_param('i', $book_id);
$update_book->execute();

echo json_encode(["status" => "success", "message" => "Book borrowed successfully! Due date: $due_date"]);
?>