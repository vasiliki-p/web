<?php
// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και έχει πρόσβαση
include 'restricted.php';
// Σύνδεση με τη βάση δεδομένων
include 'connection.php';

// Εισαγωγή του μενού πλοήγησης
include 'menu.html';

// Αν δεν υπάρχει ενεργό session χρήστη, ανακατεύθυνση στη login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Βρες το student_id που αντιστοιχεί στον συνδεδεμένο χρήστη
$stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($student_id);
if (!$stmt->fetch()) {
    echo "<h2>Δεν βρέθηκε φοιτητής με αυτό το user_id.</h2>";
    exit();
}
$stmt->close();

// Βρες τα στοιχεία της διπλωματικής εργασίας του φοιτητή
$stmt = $conn->prepare("SELECT thesis_id, title, description, pdf_file, status, assigned_at, prof_id FROM thesis WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->store_result();

// Αν δεν υπάρχει εγγραφή, εμφάνισε μήνυμα
if ($stmt->num_rows == 0) {
    echo "<h2>Δεν έχει ανατεθεί κάποια διπλωματική εργασία.</h2>";
    exit();
}

$stmt->bind_result($thesis_id, $title, $description, $pdf_file, $status, $assigned_at, $prof_id);
$stmt->fetch();
$stmt->close();

// Υπολογισμός ημερών από την ανάθεση, αν υπάρχει ημερομηνία ανάθεσης
$days_passed = '—';
if ($assigned_at) {
    $assigned_date = new DateTime($assigned_at);
    $now = new DateTime();
    $interval = $assigned_date->diff($now);
    $days_passed = $interval->days . ' ημέρες';
}

// Ανάκτηση username του επιβλέποντα καθηγητή
$stmt = $conn->prepare("SELECT u.username FROM users u JOIN professors p ON u.user_id = p.user_id WHERE p.prof_id = ?");
$stmt->bind_param("i", $prof_id);
$stmt->execute();
$stmt->bind_result($professor);
$stmt->fetch();
$stmt->close();

// Λήψη ονομάτων μελών της επιτροπής που ΔΕΝ είναι ο επιβλέπων
$committee_members = [];
$stmt = $conn->prepare("
    SELECT u.username 
    FROM committee c 
    JOIN professors p ON c.prof_id = p.prof_id 
    JOIN users u ON p.user_id = u.user_id 
    WHERE c.thesis_id = ? AND c.prof_role = 'Μέλος' AND c.prof_id != ?
");
$stmt->bind_param("ii", $thesis_id, $prof_id);
$stmt->execute();
$result = $stmt->get_result();

// Προσθήκη των usernames στον πίνακα committee_members
while ($row = $result->fetch_assoc()) {
    $committee_members[] = $row['username'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8" />
    <title>Προβολή Θέματος</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Προβολή Θέματος</h1>
    <h2><?= htmlspecialchars($title) ?></h2>

    <p><strong>Περιγραφή:</strong><br><?= nl2br(htmlspecialchars($description)) ?></p>
    <p><strong>Κατάσταση:</strong> <?= htmlspecialchars($status ?: '—') ?></p>
    <p><strong>Ημερομηνία Ανάθεσης:</strong> <?= $assigned_at ? htmlspecialchars($assigned_at) : 'Δεν έχει οριστικοποιηθεί' ?></p>
    <p><strong>Χρόνος από Ανάθεση:</strong> <?= $assigned_at ? $days_passed : '—' ?></p>
    <p><strong>Επιβλέπων:</strong> <?= htmlspecialchars($professor) ?></p>

    <p><strong>Μέλη Επιτροπής:</strong><br>
        <?php
        if (empty($committee_members)) {
            echo 'Δεν έχουν οριστεί ακόμη.';
        } else {
            foreach ($committee_members as $member) {
                echo htmlspecialchars($member) . '<br>';
            }
        }
        ?>
    </p>

    <p><strong>Αρχείο Περιγραφής:</strong><br>
        <?php if ($pdf_file): ?>
            <a href="uploads/<?= urlencode($pdf_file) ?>" download>Λήψη Αρχείου</a>
        <?php else: ?>
            Δεν έχει επισυναφθεί αρχείο.
        <?php endif; ?>
    </p>

    <p><a href="student.php">Επιστροφή στην Αρχική</a></p>
</div>

<script src="student_view.js"></script>

</body>
</html>
