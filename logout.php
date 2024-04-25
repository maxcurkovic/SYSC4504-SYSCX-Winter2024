<?php
    // Logout: Kill all session variables and redirect to login
    session_start();
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
    exit();
?>