<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

// 1. Έλεγχος δικαιωμάτων (μόνο καθηγητές)
if ($_SESSION['role'] !== 'professor') {
    echo json_encode(['success' => false, 'message' => 'Δεν έχετε δικαίωμα πρόσβασης']);
    exit();
}

// 2. Λήψη και επικύρωση δεδομένων
$data = json_decode(file_get_contents('php://input'), true);
$thesis_id = intval($data['thesis_id'] ?? 0);
$am = $data['am'] ?? '';

if ($thesis_id <= 0 || empty($am)) {
    echo json_encode(['success' => false, 'message' => 'Λείπουν απαραίτητα δεδομένα']);
    exit();
}

// 3. Έλεγχος αν ο καθηγητής είναι επιβλέπων της διπλωματικής
$prof_id = $_SESSION['prof_id'];
$check_stmt = $conn->prepare("SELECT 1 FROM thesis WHERE thesis_id = ? AND prof_id = ?");
$check_stmt->bind_param("ii", $thesis_id, $prof_id);
$check_stmt->execute();

if ($check_stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Δεν έχετε δικαίωμα ακύρωσης']);
    exit();
}

// 4. Εκτέλεση ακύρωσης (με Prepared Statement για ασφάλεια)
$update_stmt = $conn->prepare("
    UPDATE thesis 
    SET 
        student_id = NULL, 
        status = 'Ακυρωμένη',
        cancellation_reason = 'Ακύρωση από καθηγητή',
        cancelled_at = NOW()
    WHERE thesis_id = ?
");
$update_stmt->bind_param("i", $thesis_id);
$update_success = $update_stmt->execute();

if (!$update_success) {
    echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την ακύρωση: ' . $conn->error]);
    exit();
}

// 5. Διαγραφή προσκλήσεων (Prepared Statement)
$delete_invitations = $conn->prepare("DELETE FROM invitations WHERE thesis_id = ?");
$delete_invitations->bind_param("i", $thesis_id);
$delete_invitations->execute();

// 6. Διαγραφή επιτροπής (Prepared Statement)
$delete_committee = $conn->prepare("DELETE FROM committee WHERE thesis_id = ?");
$delete_committee->bind_param("i", $thesis_id);
$delete_committee->execute();

// 7. Καταγραφή ιστορικού (Prepared Statement)
$history_stmt = $conn->prepare("
    INSERT INTO thesis_status_history 
    (thesis_id, old_status, new_status, changed_by, notes) 
    VALUES (?, 'Ενεργή', 'Ακυρωμένη', ?, 'Ακύρωση από καθηγητή')
");
$history_stmt->bind_param("ii", $thesis_id, $prof_id);
$history_stmt->execute();

// 8. Απάντηση επιτυχίας
echo json_encode([
    'success' => true,
    'message' => 'Η ανάθεση ακυρώθηκε επιτυχώς'
]);

// Κλείσιμο connections
$update_stmt->close();
$delete_invitations->close();
$delete_committee->close();
$history_stmt->close();
$conn->close();
?>