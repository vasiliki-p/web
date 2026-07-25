<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html';

if ($_SESSION['role'] !== 'professor') {
    header("Location: login.php");
    exit();
}

// Get the current professor's ID
$prof_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Λίστα Ενεργών Πτυχιακών Εργασιών</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Λίστα Ενεργών Πτυχιακών Εργασιών</h1>
        
        <div id="responseMessage"></div>
        
        <div class="search-container">
            <form id="searchForm" class="search-form">
                <input type="text" id="searchInput" class="search-input" 
                       placeholder="Αναζήτηση με τίτλο ή περιγραφή...">
                <button type="submit" class="btn search-btn">Αναζήτηση</button>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Τίτλος</th>
                    <th>Περιγραφή</th>
                    <th>Κατάσταση</th>
                    <th>Σημειώσεις</th>
                    <th>PDF</th>
                    <th>Ημερομηνία Δημιουργίας</th>
                    <th>Τελευταία Ενημέρωση</th>
                    <th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody id="thesesBody">
                <!-- Θα γεμίσει μέσω AJAX -->
            </tbody>
        </table>
        <button onclick="window.history.back()" class="btn btn-secondary">Επιστροφή</button>
    </div>

    <script src="list_energes.js"></script>
</body>
</html>