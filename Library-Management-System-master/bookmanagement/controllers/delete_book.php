<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}
include '../config/db.php';
// Process confirmed delete request
if (isset($_GET['confirm_delete'])) {
    $book_id = $_GET['confirm_delete'];
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param('i', $book_id);
    $stmt->execute();
    header('Location: ../admin/dashboard.php');
    exit();
}?>