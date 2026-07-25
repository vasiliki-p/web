<?php
include 'restricted.php'; // Έλεγχος πρόσβασης
include 'connection.php';  // Σύνδεση με βάση δεδομένων
include 'menu.html'; // Εισαγωγή του μενού πλοήγησης

// Αν δεν υπάρχει συνδεδεμένος χρήστης, τον στέλνουμε στη login σελίδα
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Λαμβάνουμε το thesis_id από GET (επιβεβαίωση)
if (!isset($_GET['thesis_id'])) {
    echo "<h2>Δεν δόθηκε ID διπλωματικής.</h2>";
    exit();
}

$thesis_id = (int)$_GET['thesis_id'];

// Συνάρτηση για εύρεση username καθηγητή με βάση το prof_id
 function getUsernameByProfId($conn, $prof_id) {
    $stmt = $conn->prepare("SELECT u.username FROM users u JOIN professors p ON u.user_id = p.user_id WHERE p.prof_id = ? LIMIT 1");
    $stmt->bind_param("i", $prof_id);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
    return $username;
}

// Εύρεση του student_id που αντιστοιχεί στον συνδεδεμένο χρήστη
$stmt = $conn->prepare("SELECT s.student_id FROM students s WHERE s.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($student_id);
if (!$stmt->fetch()) {
    echo "<h2>Δεν βρέθηκε φοιτητής.</h2>";
    exit();
}
$stmt->close();

// Βρες τη διπλωματική που αντιστοιχεί στο student_id και το thesis_id που δόθηκε
$stmt = $conn->prepare("SELECT thesis_id, prof_id, status FROM thesis WHERE student_id = ? AND thesis_id = ?");
$stmt->bind_param("ii", $student_id, $thesis_id);
$stmt->execute();
$stmt->bind_result($db_thesis_id, $prof_id, $status);
if (!$stmt->fetch()) {
    echo "<h2>Η διπλωματική δεν βρέθηκε ή δεν ανήκει σε αυτόν τον φοιτητή.</h2>";
    exit();
}
$stmt->close();

if ($status !== 'Υπό Ανάθεση') {
    echo "<h2>Η διπλωματική δεν βρίσκεται σε κατάσταση 'Υπό Ανάθεση'.</h2>";
    exit();
}

// Λήψη διαθέσιμων καθηγητών (εκτός του επιβλέποντα)
$professors = [];
$stmt = $conn->prepare("SELECT p.prof_id, u.username FROM professors p JOIN users u ON p.user_id = u.user_id WHERE p.prof_id != ?");
$stmt->bind_param("i", $prof_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $professors[] = $row;
}
$stmt->close();

// Επεξεργασία υποβολής φόρμας επιλογής
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['professors'] ?? [];

    // Έλεγχος αν επιλέχθηκαν τουλάχιστον 2 μέλη
    if (count($selected) < 2) {
        echo "<p style='color:red;'>Πρέπει να επιλέξεις τουλάχιστον 2 μέλη για την επιτροπή.</p>";
    } else {
        // Καθάρισε υπάρχουσες εκκρεμείς προσκλήσεις για το thesis (προαιρετικό, αν θέλεις)
        $stmt = $conn->prepare("DELETE FROM invitations WHERE thesis_id = ? AND role = 'Μέλος' AND status = 'pending'");
        $stmt->bind_param("i", $thesis_id);
        $stmt->execute();
        $stmt->close();

        // Εισαγωγή νέων προσκλήσεων
        // Εισαγωγή νέων προσκλήσεων
$stmt = $conn->prepare("INSERT INTO invitations (thesis_id, prof_id, invited_prof_id, role, status) VALUES (?, ?, ?, 'Μέλος', 'pending')");

foreach ($selected as $prof_id_selected) {
    $pid = (int)$prof_id_selected;
    $stmt->bind_param("iii", $thesis_id, $prof_id, $pid);
    $stmt->execute();
}
$stmt->close();


        echo "<p>Οι προσκλήσεις στάλθηκαν με επιτυχία.</p>";
        echo "<p><a href='manage_thesis.php'>Επιστροφή στη διαχείριση διπλωματικής</a></p>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Επιλογή Τριμελούς Επιτροπής</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Επιλογή Τριμελούς Επιτροπής για τη Διπλωματική</h1>

    <form method="post">
        <p>Επίλεξε τουλάχιστον 2 μέλη:</p>
        <?php foreach ($professors as $prof): ?>
            <label>
                <input type="checkbox" name="professors[]" value="<?= $prof['prof_id'] ?>">
                <?= htmlspecialchars($prof['username']) ?>
            </label><br>
        <?php endforeach; ?>

        <br>
        <button type="submit">Αποστολή Προσκλήσεων</button>
    </form>

    <br>
    <a href="manage_thesis.php">Επιστροφή στη διαχείριση διπλωματικής</a>
</body>
</html>
<script src="select_committee.js"></script>
