<?php
// Έλεγχος αν ο χρήστης είναι καθηγητής
include 'restricted.php';
include 'connection.php';
include 'menu.html';

if ($_SESSION['role'] !== 'professor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$thesis_id = $_GET['id'] ?? 0;

// Βρίσκουμε το prof_id του καθηγητή
$prof_query = $conn->prepare("SELECT prof_id FROM professors WHERE user_id = ?");
$prof_query->bind_param("i", $user_id);
$prof_query->execute();
$prof_result = $prof_query->get_result();

if ($prof_result->num_rows === 0) {
    $_SESSION['message'] = "Σφάλμα: Δεν βρέθηκε καθηγητής για αυτόν τον χρήστη.";
    header("Location: list.php");
    exit();
}

$professor = $prof_result->fetch_assoc();
$prof_id = $professor['prof_id'];

// Διαχείριση POST αιτημάτων (δημιουργία/διαγραφή σημειώσεων)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Αποθήκευση νέας σημείωσης
    if (!empty($_POST['notes'])) {
        $notes = substr($_POST['notes'], 0, 300);
        
        // Έλεγχος ύπαρξης της διπλωματικής
        $thesis_check = $conn->prepare("SELECT thesis_id FROM thesis WHERE thesis_id = ?");
        $thesis_check->bind_param("i", $thesis_id);
        $thesis_check->execute();
        
        if ($thesis_check->get_result()->num_rows === 0) {
            $_SESSION['message'] = "Σφάλμα: Η διπλωματική δεν υπάρχει.";
            header("Location: list.php");
            exit();
        }
        
        $stmt = $conn->prepare("INSERT INTO notes (thesis_id, prof_id, notes) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $thesis_id, $prof_id, $notes);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Οι σημειώσεις αποθηκεύτηκαν επιτυχώς!";
        } else {
            $_SESSION['message'] = "Σφάλμα κατά την αποθήκευση των σημειώσεων.";
        }
        header("Location: notes.php?id=$thesis_id");
        exit();
    }
    
    // Διαγραφή σημείωσης
    if (isset($_POST['delete_note'])) {
        $note_id = $_POST['delete_note'];
        $delete_query = $conn->prepare("DELETE FROM notes WHERE note_id = ? AND prof_id = ?");
        $delete_query->bind_param("ii", $note_id, $prof_id);
        $delete_query->execute();
        
        if ($delete_query->affected_rows > 0) {
            $_SESSION['message'] = "Η σημείωση διαγράφηκε επιτυχώς!";
        } else {
            $_SESSION['message'] = "Σφάλμα κατά τη διαγραφή της σημείωσης.";
        }
        header("Location: notes.php?id=$thesis_id");
        exit();
    }
}

// Λήψη τίτλου πτυχιακής
$thesis_query = $conn->prepare("SELECT title FROM thesis WHERE thesis_id = ?");
$thesis_query->bind_param("i", $thesis_id);
$thesis_query->execute();
$thesis = $thesis_query->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Σημειώσεις Διπλωματικής</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Σημειώσεις Διπλωματικής: <span id="thesisTitle"><?= htmlspecialchars($thesis['title'] ?? '') ?></span></h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <!-- Εμφάνιση μηνύματος επιτυχίας ή σφάλματος -->
            <div class="message <?= strpos($_SESSION['message'], 'Σφάλμα') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($_SESSION['message']) ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Φόρμα προσθήκης νέας σημείωσης -->
        <form id="notesForm" method="POST">
            <textarea name="notes" id="notesTextarea" maxlength="300" 
                     placeholder="Γράψτε νέες σημειώσεις εδώ (μέχρι 300 χαρακτήρες)..."></textarea>
            <div>
                <button type="submit" class="btn">Αποθήκευση Σημειώσεων</button>
            </div>
            <small>Χαρακτήρες: <span id="charCount">0</span>/300</small>
        </form>

        <div class="notes-list">
            <h3>Οι Σημειώσεις μου</h3>
            <!-- Εδώ θα εμφανίζονται οι σημειώσεις του καθηγητή -->
            <div id="notesContainer">
                <p>Φόρτωση σημειώσεων...</p>
            </div>
        </div>
        <button onclick="window.history.back()" class="btn btn-secondary">Επιστροφή στη λίστα</button>

    </div>

<script>
    // Περνάμε τα IDs στο JavaScript για χρήση στα AJAX
    const thesisId = <?= isset($thesis_id) ? json_encode($thesis_id) : 'null' ?>;
    const professorId = <?= isset($prof_id) ? json_encode($prof_id) : 'null' ?>;
    
    if (!thesisId || !professorId) {
        console.error('Missing required IDs:', {
            thesisId: thesisId,
            professorId: professorId
        });
    }
</script>
    <script src="notes.js"></script>
</body>
</html>