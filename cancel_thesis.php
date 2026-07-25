<?php
header('Content-Type: application/json');

include 'restricted.php';
include 'connection.php';

// Έλεγχος δικαιωμάτων
if ($_SESSION['role'] !== 'secretary') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

// Έλεγχος JSON εισόδου
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['thesis_id']) || !isset($input['cancellation_reason'])) {
    echo json_encode(['success' => false, 'message' => 'Λείπουν απαιτούμενα πεδία']);
    exit;
}

$thesis_id = intval($input['thesis_id']);
$reason = trim($input['cancellation_reason']);

// Προετοιμασία και εκτέλεση query ενημέρωσης
$stmt = $conn->prepare("
    UPDATE thesis
    SET status = 'Ακυρωμένη',
        cancellation_reason = ?,
        assignment_cancelled = 1,
        cancelled_at = NOW()
    WHERE thesis_id = ?
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Σφάλμα βάσης δεδομένων']);
    exit;
}

$stmt->bind_param("si", $reason, $thesis_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Η πτυχιακή ακυρώθηκε με επιτυχία']);
} else {
    echo json_encode(['success' => false, 'message' => 'Αποτυχία ενημέρωσης']);
}

$stmt->close();
$conn->close();