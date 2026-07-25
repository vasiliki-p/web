<?php
// Φορτώνουμε αρχεία ελέγχου σύνδεσης και βάσης
include 'restricted.php';
include 'connection.php';
include 'menu.html';

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος, αλλιώς ανακατεύθυνση στο login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Έλεγχος αν έχει δοθεί το thesis_id μέσω GET
if (!isset($_GET['thesis_id'])) {
    echo "Δεν καθορίστηκε διπλωματική.";
    exit();
}

$thesis_id = intval($_GET['thesis_id']);

// Βρίσκουμε το student_id που αντιστοιχεί στον τρέχοντα χρήστη
$stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($student_id);
if (!$stmt->fetch()) {
    // Αν δεν βρεθεί φοιτητής, σταματάμε
    echo "Δεν βρέθηκε φοιτητής.";
    exit();
}
$stmt->close();

// Ελέγχουμε αν η διπλωματική ανήκει στον συγκεκριμένο φοιτητή
$stmt = $conn->prepare("SELECT title, final_report_file, examination_minutes_file, status FROM thesis WHERE thesis_id = ? AND student_id = ?");
$stmt->bind_param("ii", $thesis_id, $student_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // Αν η διπλωματική δεν ανήκει ή δεν υπάρχει, σταματάμε
    echo "Δεν έχετε πρόσβαση σε αυτή τη διπλωματική.";
    exit();
}

$stmt->bind_result($title, $final_report_file, $examination_minutes_file, $status);
$stmt->fetch();
$stmt->close();

// Φάκελος όπου βρίσκονται τα αρχεία
$upload_dir = 'uploads/';

// Παίρνουμε το όνομα αρχείου από το path (για ασφάλεια)
$examination_file_name = basename($examination_minutes_file);
$final_report_file_name = basename($final_report_file);
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Πληροφορίες Διπλωματικής</title>
</head>
<body>

<!-- Εμφανίζουμε τίτλο και κατάσταση -->
<h2>Διπλωματική: <?php echo htmlspecialchars($title); ?></h2>
<p>Κατάσταση: <strong><?php echo htmlspecialchars($status); ?></strong></p>

<?php
// Αν η κατάσταση είναι "Περατωμένη", επιτρέπουμε μόνο προβολή στοιχείων και πρακτικού
if (mb_strtolower($status, 'UTF-8') === 'περατωμένη') {
    if (!empty($examination_file_name)) {
        // Link για το πρακτικό εξέτασης με id για το JS
        echo '<p><a id="examLink" href="' . htmlspecialchars($upload_dir . $examination_file_name) . '" target="_blank" download>📄 Προβολή Πρακτικού Εξέτασης</a></p>';
    } else {
        echo '<p>Δεν έχει ανεβεί το πρακτικό εξέτασης.</p>';
    }
    // Μήνυμα ότι δεν είναι διαθέσιμη η τελική αναφορά σε περατωμένη διπλωματική
    echo '<p><em>Η διπλωματική έχει περατωθεί. Δεν είναι πλέον διαθέσιμη η τελική αναφορά.</em></p>';
} else {
    // Αν δεν είναι "Περατωμένη", δείχνουμε και το πρακτικό και την τελική αναφορά (αν υπάρχουν)
    if (!empty($examination_file_name)) {
        echo '<p><a id="examLink" href="' . htmlspecialchars($upload_dir . $examination_file_name) . '" target="_blank" download>📄 Προβολή Πρακτικού Εξέτασης</a></p>';
    } else {
        echo '<p>Δεν έχει ανεβεί το πρακτικό εξέτασης.</p>';
    }

    if (!empty($final_report_file_name)) {
        // Link για τελική αναφορά με id για το JS
        echo '<p><a id="finalReportLink" href="' . htmlspecialchars($upload_dir . $final_report_file_name) . '" target="_blank" download>📘 Προβολή Τελικής Αναφοράς</a></p>';
    } else {
        echo '<p>Δεν έχει ανεβεί η τελική αναφορά.</p>';
    }
}
?>

<!-- Link επιστροφής -->
<p><a href="student.php">⟵ Επιστροφή</a></p>

<!-- Φόρτωση του JS που διαχειρίζεται τα links -->
<script src="view_final_thesis.js"></script>
</body>
</html>
