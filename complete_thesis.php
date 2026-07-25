<?php
include 'restricted.php';
include 'connection.php';


header('Content-Type: application/json');

if ($_SESSION['role'] !== 'secretary') {
    header("Location: login.php");
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['thesis_id'])) {
    echo json_encode(['success' => false, 'message' => 'Λείπει το thesis_id']);
    exit;
}

$thesis_id = intval($input['thesis_id']);

// Έλεγχος ύπαρξης final_grade και repository_link
$checkStmt = $conn->prepare("SELECT final_grade, repository_link FROM thesis WHERE thesis_id = ?");
$checkStmt->bind_param("i", $thesis_id);
$checkStmt->execute();
$result = $checkStmt->get_result();
$thesis = $result->fetch_assoc();

if (!$thesis) {
    echo json_encode(['success' => false, 'message' => 'Η εργασία δεν βρέθηκε']);
    exit;
}

if (empty($thesis['final_grade']) || empty($thesis['repository_link'])) {
    echo json_encode(['success' => false, 'message' => 'Απαιτείται τελικός βαθμός και σύνδεσμος αποθετηρίου']);
    exit;
}

// Ενημέρωση status σε Περατωμένη
$updateStmt = $conn->prepare("UPDATE thesis SET status = 'Περατωμένη', completed_at = NOW(), updated_at = NOW() WHERE thesis_id = ?");
$updateStmt->bind_param("i", $thesis_id);

if ($updateStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Η εργασία ορίστηκε ως Περατωμένη']);
} else {
    echo json_encode(['success' => false, 'message' => 'Σφάλμα κατά την ενημέρωση']);
}

$updateStmt->close();
$conn->close();