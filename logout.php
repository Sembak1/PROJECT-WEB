<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

session_unset();
session_destroy();

// Arahkan otomatis ke login.php di folder yang sama
header('Location: login.php');
exit;
