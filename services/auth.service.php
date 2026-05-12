<?php
    require_once __DIR__ . '/../config/db.config.php';
    session_start();

    function checkAuth() {
        if(isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        } else 
            return 0;
    }
?>
