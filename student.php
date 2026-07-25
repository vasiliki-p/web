<?php 
// Περιλαμβάνει το αρχείο που ελέγχει αν ο χρήστης έχει δικαίωμα πρόσβασης (π.χ. αν είναι συνδεδεμένος)
include 'restricted.php';  

// Περιλαμβάνει το αρχείο σύνδεσης με τη βάση δεδομένων
include 'connection.php';  

// Περιλαμβάνει το αρχείο μενού για την εμφάνιση του μενού πλοήγησης
include 'menu.html';

// Έλεγχος αν υπάρχει το session user_id, δηλαδή αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id'])) {
    // Αν δεν είναι συνδεδεμένος, γίνεται ανακατεύθυνση στη σελίδα login
    header("Location: login.php");  
    exit(); // Σταματάει η εκτέλεση του script μετά την ανακατεύθυνση
}

// Αποθηκεύει το user_id από το session για χρήση στη συνέχεια
$user_id = $_SESSION['user_id'];  

// SQL ερώτημα για ανάκτηση του ονόματος και επωνύμου του φοιτητή με βάση το user_id
$sql = "SELECT name, lastname FROM students WHERE user_id = ?";
$stmt = $conn->prepare($sql);   // Προετοιμάζει το SQL statement για αποφυγή SQL injection
$stmt->bind_param("i", $user_id);  // Συνδέει το user_id ως ακέραιο (integer) παράμετρο
$stmt->execute();  // Εκτελεί το ερώτημα
$stmt->bind_result($name, $lastname);  // Αποθηκεύει τα αποτελέσματα στις μεταβλητές $name και $lastname
$stmt->fetch();  // Λαμβάνει την πρώτη (και μοναδική) γραμμή αποτελεσμάτων
$stmt->close();  // Κλείνει το prepared statement για απελευθέρωση πόρων
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αρχική Φοιτητή</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Κύριο μπλοκ καλωσορίσματος με όνομα φοιτητή -->
<div class="main">
    <!-- Χρήση της htmlspecialchars για αποφυγή επιθέσεων XSS -->
    <h1>Καλώς ήρθες, <?php echo htmlspecialchars($name . ' ' . $lastname); ?>!</h1>
</div>

<!-- Μενού επιλογών του φοιτητή -->
<div class="homepage" id="myhomepage" style="display: flex; flex-direction: column; gap: 10px;">
    <!-- Σύνδεσμος για προβολή θέματος διπλωματικής -->
    <a href="student_view.php">Προβολή Θέματος</a>

    <!-- Σύνδεσμος για επεξεργασία στοιχείων προφίλ -->
    <a href="edit_profile.php">Επεξεργασία Προφίλ</a>

    <!-- Σύνδεσμος για διαχείριση διπλωματικής εργασίας -->
    <a href="manage_thesis.php">Διαχείριση διπλωματικής εργασίας</a>

    <!-- Σύνδεσμος αποσύνδεσης από το σύστημα -->
    <a href="logout.php">Αποσύνδεση</a>
</div>

</body>
</html>
