<?php
// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
include 'restricted.php';
include 'connection.php';
include 'menu.html';

// Αν δεν υπάρχει user_id στη συνεδρία, ανακατεύθυνση στη login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Έλεγχος αν δόθηκε id πτυχιακής στη διεύθυνση
if (!isset($_GET['thesis_id'])) {
    die("Λάθος αναγνωριστικό διπλωματικής");
}

// Νέα συνάρτηση για φόρτωση ιστορικού κατάστασης της πτυχιακής
function getStatusHistory($thesis_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM thesis_status_history WHERE thesis_id = ? ORDER BY changed_at DESC");
    $stmt->bind_param("i", $thesis_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Λεπτομέρειες Πτυχιακής</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1 id="thesisTitle">Φόρτωση πτυχιακής...</h1>
        
        <div class="details-section">
            <h2>Βασικές Πληροφορίες</h2>
            <!-- Εδώ θα εμφανιστούν τα στοιχεία του φοιτητή -->
            <div id="studentInfo"></div>
            <!-- Εδώ θα εμφανιστεί η κατάσταση της πτυχιακής -->
            <div id="thesisStatus"></div>
            <!-- Εδώ θα εμφανιστούν οι ημερομηνίες -->
            <div id="thesisDates"></div>
            <!-- Εδώ θα εμφανιστεί η περιγραφή -->
            <div id="thesisDescription"></div>
        </div>

        <div class="details-section">
            <h2>Αρχεία</h2>
            <!-- Εδώ θα εμφανιστούν τα αρχεία της πτυχιακής -->
            <div id="pdfContainer"></div>
            <!-- Εδώ θα εμφανιστεί η φόρμα βαθμολόγησης αν χρειάζεται -->
            <div id="gradingFormContainer"></div>
        </div>

        <div class="details-section">
            <h2>Επιτροπή</h2>
            <!-- Εδώ θα εμφανιστεί η επιτροπή -->
            <div id="committeeList"></div>
        </div>

        <div class="details-section">
            <h2>Βαθμολογία</h2>
            <!-- Εδώ θα εμφανιστεί η βαθμολογία -->
            <div id="thesisGrade"></div>
        </div>

        <div class="details-section">
            <h2>Ιστορικό Κατάστασης</h2>
            <div id="statusHistory">
                <?php 
                // Εμφάνιση ιστορικού κατάστασης της πτυχιακής
                $history = getStatusHistory($_GET['thesis_id'], $conn);
                if (!empty($history)) {
                    echo '<table class="history-table">';
                    echo '<tr><th>Ημερομηνία</th><th>Παλιά Κατάσταση</th><th>Νέα Κατάσταση</th><th>Σημειώσεις</th></tr>';
                    foreach ($history as $entry) {
                        echo '<tr>';
                        echo '<td>'.htmlspecialchars($entry['changed_at']).'</td>';
                        echo '<td>'.htmlspecialchars($entry['old_status'] ?? '-').'</td>';
                        echo '<td>'.htmlspecialchars($entry['new_status'] ?? '-').'</td>';
                        echo '<td>'.htmlspecialchars($entry['notes'] ?? '').'</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p>Δεν υπάρχει ιστορικό καταστάσεων</p>';
                }
                ?>
            </div>
        </div>
        
    </div>

    <!-- Εισαγωγή του αντίστοιχου JavaScript αρχείου -->
    <script src="thesis_details.js"></script>
</body>
</html>
