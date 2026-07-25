<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

// Έλεγχος ρόλου χρήστη
if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Λήψη ID από POST data
$thesisId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($thesisId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid thesis ID']);
    exit();
}

// Λήψη prof_id από τον πίνακα professors
$user_id = $_SESSION['user_id'];
$prof_query = "SELECT prof_id FROM professors WHERE user_id = ?";
$stmt = $conn->prepare($prof_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$prof_result = $stmt->get_result();

if ($prof_result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Professor not found']);
    exit();
}

$prof = $prof_result->fetch_assoc();
$prof_id = $prof['prof_id'];

// Έλεγχος ότι η πτυχιακή ανήκει στον καθηγητή
$checkStmt = $conn->prepare("SELECT thesis_id, pdf_file FROM thesis WHERE thesis_id = ? AND prof_id = ?");
$checkStmt->bind_param("ii", $thesisId, $prof_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Η πτυχιακή δεν βρέθηκε ή δεν έχετε δικαίωμα πρόσβασης']);
    exit();
}

$thesisData = $checkResult->fetch_assoc();

// Διαγραφή PDF αρχείου (αν υπάρχει)
if (!empty($thesisData['pdf_file']) && file_exists($thesisData['pdf_file'])) {
    @unlink($thesisData['pdf_file']);
}

// Διαγραφή από τη βάση δεδομένων
$deleteStmt = $conn->prepare("DELETE FROM thesis WHERE thesis_id = ?");
$deleteStmt->bind_param("i", $thesisId);
$deleteStmt->execute();

if ($deleteStmt->affected_rows > 0) {
    echo json_encode([
        'success' => true, 
        'message' => 'Η πτυχιακή διαγράφηκε επιτυχώς!'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Σφάλμα κατά τη διαγραφή από τη βάση δεδομένων'
    ]);
}

$conn->close();
?>