<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html';

if ($_SESSION['role'] !== 'professor') {
    header("Location: login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Λίστα Περατωμένων Πτυχιακών</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Λίστα Περατωμένων Πτυχιακών Εργασιών</h1>
        
        <div id="responseMessage"></div>
        
        <div class="search-container">            
            <form id="searchForm" class="search-form">
                <input type="text" id="searchInput" placeholder="Αναζήτηση με τίτλο ή περιγραφή...">
                <button type="submit" class="btn search-btn">Αναζήτηση</button>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Τίτλος</th>
                    <th>Περιγραφή</th>
                    <th>Κατάσταση</th>
                    <th>Επιτροπή</th>
                    <th>Σημειώσεις</th>
                    <th>Τελικό Αρχείο</th>
                    <th>Τελικός Βαθμός</th>
                    <th>Σύνδεσμοι</th>
                    <th>Ημ/νία Δημιουργίας</th>
                    <th>Ημ/νία ολοκλήρωσης</th>
                </tr>
            </thead>
            <tbody id="thesesBody">
                <!-- Θα γεμίσει μέσω AJAX -->
            </tbody>
        </table>
    </div>

    <script src="list_peratwmenes.js"></script>
</body>
</html>