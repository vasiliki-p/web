<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html'; 

// Έλεγχος αν ο χρήστης είναι καθηγητής
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
    <title>Λίστα Πτυχιακών Εργασιών</title>
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <div class="container">
        <h1>Λίστα Πτυχιακών Εργασιών</h1>
        
        <div id="responseMessage"></div>
        
        <div class="search-container">
            <form id="searchForm" class="search-form">
                <select id="statusFilter" class="search-input" style="width: 150px;">
                <option value="">Όλες οι καταστάσεις</option>
                <!-- Θα γεμίσει μέσω JavaScript -->
            </select>
              
                <select id="roleFilter" class="search-input" style="width: 150px;">
                    <option value="">Όλοι οι ρόλοι</option>
                    <option value="Μέλος">Μέλος τριμελούς</option>
                    <option value="Επιβλέπων">Επιβλέπων</option>
                </select>
                
                <input type="text" id="searchInput" class="search-input" 
                       placeholder="Αναζήτηση..." style="width: 200px;">
                <button type="submit" class="search-btn">Αναζήτηση</button>
                <button type="button" id="clearFilters" class="clear-btn">Καθαρισμός</button>
            </form>

            <div class="export-container">
                <select id="exportFormat" class="export-select">
                    <option value="">Εξαγωγή δεδομένων</option>
                    <option value="csv">CSV</option>
                    <option value="json">JSON</option>
                </select>
                <button id="exportBtn" class="export-btn">Εξαγωγή</button>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Τίτλος</th>
                    <th>Περιγραφή</th>
                    <th>Κατάσταση</th>
                    <th>PDF</th>
                    <th>Ρόλος</th>
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

    <script src="list_all.js"></script>
</body>
</html>