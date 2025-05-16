<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Library Admin</h2>
            </div>
            <nav class="sidebar-nav">
            <ul>
    <li>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    </li>
    <li>
        <a href="manage_books.php"><i class="fas fa-book"></i> Books</a>
    </li>
    <li>
        <a href="out_of_stock.php"><i class="fas fa-box-open"></i> Out of Stock</a>
    </li>
    <li>
        <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
    </li>
    <li>
        <a href="borrowed_books.php"><i class="fas fa-hand-holding"></i> Borrowed</a>
    </li> 
    <li>
        <a href="overdue_reports.php"><i class="fas fa-exclamation-circle"></i> Overdue</a>
    </li>
    <li>
        <a href="history.php"><i class="fas fa-history"></i> History</a>
    </li>
    <li>
        <a href="edit_profile.php"><i class="fas fa-cog"></i> Settings</a>
    </li>
</ul>

            </nav>
            <div class="sidebar-footer">
                <a id="logoutBtn" href="../public/logout.php" style='text-decoration:none' class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>


<!-- to format the dates  -->
<?php
function formatBorrowDate($date) {
    $timestamp = strtotime($date);
    $today = strtotime(date('Y-m-d'));
    $yesterday = strtotime('-1 day', $today);
    $sevenDaysAgo = strtotime('-7 days', $today);

    if ($timestamp == $today) {
        return "Today";
    } elseif ($timestamp == $yesterday) {
        return "Yesterday";
    } elseif ($timestamp > $sevenDaysAgo) {
        return floor(($today - $timestamp) / (60 * 60 * 24)) . " days ago";
    } else {
        return date('d F Y', $timestamp);
    }
}

function formatDate($date) {
    return date('d F Y', strtotime($date));  // Always format as "21 March 2024"
}
?>

    <!--  to make navigation menus active  -->
<script>
  
    document.addEventListener("DOMContentLoaded", function () {
    let currentUrl = window.location.pathname.split("/").pop(); 
    let navLinks = document.querySelectorAll(".sidebar-nav ul li a");

    navLinks.forEach(link => {
        let linkUrl = link.getAttribute("href").split("/").pop(); 
        if (linkUrl === currentUrl) {
            link.parentElement.classList.add("active");
        } else {
            link.parentElement.classList.remove("active");
        }
    });
});
</script>
