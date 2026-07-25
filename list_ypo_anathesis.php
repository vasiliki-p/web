<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html';

if ($_SESSION['role'] !== 'professor') {
    header("Location: login.php");
    exit();
}

// Λήψη του user_id του καθηγητή από το session
$user_id = $_SESSION['user_id'];
// Εύρεση του prof_id του καθηγητή
$stmt = $conn->prepare("SELECT prof_id FROM professors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$professor = $result->fetch_assoc();

if (!$professor) {
    die("Δεν βρέθηκε καθηγητής με αυτό το user_id");
}

// Αποθήκευση του prof_id στο session για μελλοντική χρήση
$_SESSION['prof_id'] = $professor['prof_id']; 
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Πτυχιακές Υπό Ανάθεση</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Πτυχιακές Υπό Ανάθεση</h1>
        
        <!-- Εμφάνιση μηνυμάτων επιτυχίας/σφάλματος -->
        <div id="responseMessage"></div>
        
        <div class="search-container">
            <form id="searchForm" class="search-form">
                <input type="text" id="searchInput" class="search-input" 
                       placeholder="Αναζήτηση με τίτλο ή περιγραφή...">
                <button type="submit" class="search-btn">Αναζήτηση</button>
            </form>
        </div>
        
        <table id="thesesTable">
            <thead>
                <tr>
                    <th>Τίτλος</th>
                    <th>Περιγραφή</th>
                    <th>Φοιτητής</th>
                    <th>ΑΜ</th>
                    <th>PDF</th>
                    <th>Ημερομηνία Δημιουργίας</th>
                    <th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody id="thesesBody">
                <!-- Θα γεμίσει μέσω AJAX -->
            </tbody>
        </table>
        <button onclick="window.history.back()" class="btn btn-secondary">Επιστροφή</button>
        
    </div>

    <!-- Εισαγωγή του αντίστοιχου JavaScript αρχείου -->
    <script src="list_ypo_anathesis.js"></script>
</body>
</html>