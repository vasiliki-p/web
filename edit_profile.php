<?php
// Ξεκινάμε το session για να έχουμε πρόσβαση στο user_id
include 'restricted.php';
include 'connection.php';
include 'menu.html';

// Αν δεν υπάρχει χρήστης συνδεδεμένος, τον ανακατευθύνουμε στη σελίδα σύνδεσης
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Παίρνουμε το user_id από το session
$message = "";         // Μήνυμα για το χρήστη
$message_type = "";    // Τύπος μηνύματος (π.χ. επιτυχία ή σφάλμα)

// Αν η φόρμα υποβλήθηκε (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Παίρνουμε και καθαρίζουμε τα δεδομένα που στάλθηκαν από τη φόρμα
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile_phone = trim($_POST['mobile_phone'] ?? '');
    $landline_phone = trim($_POST['landline_phone'] ?? '');

    // Έλεγχος εγκυρότητας email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Το email δεν είναι έγκυρο.";
        $message_type = "error";
    } else {
        // Προετοιμασία και εκτέλεση SQL για ενημέρωση στοιχείων επικοινωνίας
        $stmt = $conn->prepare("UPDATE students SET address = ?, email = ?, mobile_phone = ?, landline_phone = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $address, $email, $mobile_phone, $landline_phone, $user_id);

        if ($stmt->execute()) {
            // Αν έγινε αλλαγή σε τουλάχιστον ένα πεδίο
            if ($stmt->affected_rows > 0) {
                $message = "Τα στοιχεία ενημερώθηκαν επιτυχώς.";
                $message_type = "success";
            } else {
                $message = "Δεν υπήρξε καμία αλλαγή στα στοιχεία.";
                $message_type = "error";
            }
        } else {
            // Αν υπήρξε σφάλμα κατά την εκτέλεση του ερωτήματος
            $message = "Σφάλμα κατά την ενημέρωση: " . $stmt->error;
            $message_type = "error";
        }

        $stmt->close();
    }
}

// Φόρτωση των τρεχόντων στοιχείων του φοιτητή από τη βάση δεδομένων
$stmt = $conn->prepare("SELECT address, email, mobile_phone, landline_phone FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($address, $email, $mobile_phone, $landline_phone);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8" />
    <title>Επεξεργασία Προφίλ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
    <h2>Επεξεργασία Προφίλ</h2>

    <!-- Εμφάνιση μηνύματος επιτυχίας ή σφάλματος -->
    <?php if (!empty($message)): ?>
        <div>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Φόρμα επεξεργασίας προφίλ -->
    <form method="POST" action="edit_profile.php" id="editProfileForm" novalidate>
        <label for="address">Πλήρης Ταχυδρομική Διεύθυνση:</label><br>
        <input type="text" id="address" name="address" value="<?= htmlspecialchars($address) ?>" required autocomplete="address-line1" /><br><br>

        <label for="email">Email Επικοινωνίας:</label><br>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required autocomplete="email" /><br><br>

        <label for="mobile_phone">Κινητό Τηλέφωνο Επικοινωνίας:</label><br>
        <input type="text" id="mobile_phone" name="mobile_phone" value="<?= htmlspecialchars($mobile_phone) ?>" required autocomplete="tel" pattern="\d*" /><br><br>

        <label for="landline_phone">Σταθερό Τηλέφωνο Επικοινωνίας:</label><br>
        <input type="text" id="landline_phone" name="landline_phone" value="<?= htmlspecialchars($landline_phone) ?>" autocomplete="tel" pattern="\d*" /><br><br>

        <input type="submit" value="Αποθήκευση" />
    </form>

    <!-- Σύνδεσμος επιστροφής στην αρχική σελίδα του φοιτητή -->
    <p>
        <a href="student.php">&larr; Επιστροφή στην Αρχική</a>
    </p>
    </div>
    
    <!-- Εισαγωγή του αρχείου JavaScript για επιπλέον έλεγχο εγκυρότητας -->
    <script src="edit_profile.js"></script>
</body>
</html>
