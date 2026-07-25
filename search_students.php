<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html';

if ($_SESSION['role'] !== 'professor') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αναζήτηση Φοιτητών</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Αναζήτηση Φοιτητών</h1>
        <div id="responseMessage"></div>
        <div class="search-container">
            <form id="searchForm">
                <input type="text" id="searchInput" placeholder="ΑΜ ή Όνομα ή Επώνυμο">
                <button type="submit">Αναζήτηση</button>
            </form>
        </div>
        <div id="resultsContainer"></div>
       
        <button onclick="window.history.back()" class="btn btn-secondary">Επιστροφή</button>

    </div>
    <script src="search_students.js"></script>
</body>
</html>