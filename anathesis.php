<?php
// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και έχει ρόλο καθηγητή
include 'restricted.php';
include 'connection.php';
include 'menu.html';

// Αν ο χρήστης δεν είναι καθηγητής, ανακατεύθυνση στη σελίδα login
if ($_SESSION['role'] !== 'professor') {
    header("Location: login.php");
    exit();
}

/*
    Στη συνέχεια, λαμβάνει τον αριθμό μητρώου του φοιτητή από το query string.
    Αν δεν υπάρχει αριθμός μητρώου, γίνεται ανακατεύθυνση στη σελίδα αναζήτησης φοιτητών.
*/
$student_am = $_GET['am'] ?? null;
if (!$student_am) {
    header("Location: search_students.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ανάθεση Διπλωματικής</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Ανάθεση Διπλωματικής</h1>
        <!-- Εδώ θα εμφανιστούν τα στοιχεία του φοιτητή -->
        <div id="studentInfo"></div>
        <!-- Εδώ θα εμφανίζονται μηνύματα επιτυχίας/σφάλματος -->
        <div id="responseMessage"></div>
        <!-- Εδώ θα εμφανίζονται οι διαθέσιμες διπλωματικές -->
        <div id="thesesContainer"></div>
    </div>
    <!-- Εισαγωγή του αντίστοιχου JavaScript αρχείου -->
    <script src="anathesis.js"></script>
</body>
</html>