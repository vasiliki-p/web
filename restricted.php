<?php
session_start();

// Βασικός έλεγος σύνδεσης
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>