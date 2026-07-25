<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html'; 

if ($_SESSION['role'] !== 'professor') {
    header("Location: login.php");
    exit();
}

// Πάρε το prof_id από τον πίνακα professors
$user_id = $_SESSION['user_id']; 
$stmt = $conn->prepare("SELECT prof_id FROM professors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$professor = $result->fetch_assoc();

if (!$professor) {
    die("Δεν βρέθηκε καθηγητής με αυτό το user_id");
}

// Αποθήκευσε το prof_id σε ξεχωριστό session variable για σαφήνεια
$_SESSION['prof_id'] = $professor['prof_id']; 
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Λίστα Πτυχιακών Εργασιών Προς Ανάθεση</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Λίστα Πτυχιακών Εργασιών Προς Ανάθεση</h1>
        
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
                    <th>Κατάσταση</th>
                    <th>PDF</th>
                    <th>Ημερομηνία Δημιουργίας</th>
                    <th>Τελευταία Ενημέρωση</th>
                    <th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody id="thesesBody">
            </tbody>
        </table>
        <button onclick="window.history.back()" class="btn btn-secondary">Επιστροφή</button>

        </div>

    <script src="list_pros_anathesis.js"></script>
</body>
</html>