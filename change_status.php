<?php
// Έλεγχος αν ο χρήστης είναι καθηγητής και σύνδεση με τη βάση
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

// Έλεγχος αν ο χρήστης έχει ρόλο καθηγητή
if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Έλεγχος αν υπάρχει το POST mark_review
if (!isset($_POST['mark_review'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$thesis_id = (int)$_POST['mark_review'];
$user_id = $_SESSION['user_id'];

// Εύρεση του prof_id του καθηγητή
$stmt_prof = $conn->prepare("SELECT prof_id FROM professors WHERE user_id = ?");
$stmt_prof->bind_param("i", $user_id);
$stmt_prof->execute();
$prof_result = $stmt_prof->get_result();

if ($prof_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Professor not found']);
    exit;
}

$prof_data = $prof_result->fetch_assoc();
$prof_id = $prof_data['prof_id'];

// Έλεγχος αν ο καθηγητής είναι επιβλέπων στη συγκεκριμένη πτυχιακή
$verify_stmt = $conn->prepare("SELECT committee_id FROM committee 
                             WHERE thesis_id = ? AND prof_id = ? AND prof_role = 'Επιβλέπων'");
$verify_stmt->bind_param("ii", $thesis_id, $prof_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Μόνο ο επιβλέπων καθηγητής μπορεί να αλλάξει την κατάσταση']);
    exit;
}

// Ενημέρωση κατάστασης πτυχιακής σε "Υπό Εξέταση"
$stmt = $conn->prepare("UPDATE thesis SET status = 'Υπό Εξέταση', updated_at = NOW() WHERE thesis_id = ?");
$stmt->bind_param("i", $thesis_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No changes made to the thesis']);
    exit;
}

// Επιστροφή επιτυχούς απάντησης
echo json_encode([
    'success' => true,
    'message' => 'Η κατάσταση της πτυχιακής άλλαξε επιτυχώς'
]);
?>