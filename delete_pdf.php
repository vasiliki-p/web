<?php
// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και σύνδεση με τη βάση
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

// Επιτρέπεται μόνο μέθοδος DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Λήψη του id της πτυχιακής από το URL
$thesis_id = $_GET['id'] ?? 0;
if ($thesis_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid thesis ID']);
    exit();
}

// Έλεγχος αν ο χρήστης έχει δικαίωμα να επεξεργαστεί τη συγκεκριμένη πτυχιακή
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT thesis_id, pdf_file FROM thesis WHERE thesis_id = ? AND prof_id = ?");
$stmt->bind_param("ii", $thesis_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Thesis not found or access denied']);
    exit();
}

$thesis = $result->fetch_assoc();
$pdf_file = $thesis['pdf_file'];

// Έλεγχος αν υπάρχει αρχείο για διαγραφή
if (empty($pdf_file)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No PDF file exists for this thesis']);
    exit();
}

// Διαγραφή του φυσικού αρχείου από τον server
if (file_exists($pdf_file)) {
    if (!unlink($pdf_file)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete file from server']);
        exit();
    }
}

// Ενημέρωση της βάσης ώστε να αφαιρεθεί το αρχείο από την πτυχιακή
$update_stmt = $conn->prepare("UPDATE thesis SET pdf_file = NULL WHERE thesis_id = ?");
$update_stmt->bind_param("i", $thesis_id);

if (!$update_stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update database: ' . $update_stmt->error]);
    exit();
}

// Επιστροφή επιτυχούς απάντησης
echo json_encode([
    'success' => true,
    'message' => 'PDF file deleted successfully'
]);

$conn->close();
?>