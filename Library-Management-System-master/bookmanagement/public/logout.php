-- /public/logout.php (Logout Functionality)
<?php
session_start();
session_destroy();
header('Location: login.php');
exit();
?>