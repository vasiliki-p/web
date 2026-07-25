<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Λήψη δεδομένων από JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['thesis_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing thesis_id parameter']);
    exit();
}

$thesis_id = $data['thesis_id'];
$student_am = $data['am'] ?? null;

// Έλεγχος αν ο καθηγητής είναι ο επιβλέπων
$prof_id = $_SESSION['user_id'];
$check_query = "
    SELECT t.thesis_id, t.status, t.student_id
    FROM thesis t
    JOIN committee c ON t.thesis_id = c.thesis_id
    JOIN professors p ON c.prof_id = p.prof_id
    JOIN users u ON p.user_id = u.user_id
    WHERE t.thesis_id = ? AND u.user_id = ? AND c.prof_role = 'Επιβλέπων'
";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $thesis_id, $prof_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Δεν έχετε δικαίωμα να ακυρώσετε αυτή την ανάθεση']);
    exit();
}

$thesis_data = $check_result->fetch_assoc();
$current_status = $thesis_data['status'];

// Έναρξη transaction
$conn->begin_transaction();

// 1. Ακύρωση ανάθεσης
$update_thesis = $conn->prepare("
    UPDATE thesis 
    SET 
        status = 'Ακυρωμένη',
        student_id = NULL,
        cancelled_at = NOW(),
        cancellation_reason = 'Ακύρωση από Διδάσκοντα'
    WHERE thesis_id = ?
");
$update_thesis->bind_param("i", $thesis_id);

if (!$update_thesis->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την ενημέρωση της διπλωματικής',
        'error' => $conn->error
    ]);
    exit();
}

// 2. Καταγραφή ιστορικού
$log_history = $conn->prepare("
    INSERT INTO thesis_status_history 
    (thesis_id, old_status, new_status, changed_by) 
    VALUES (?, ?, 'Ακυρωμένη', ?)
");
$log_history->bind_param("isi", $thesis_id, $current_status, $prof_id);

if (!$log_history->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Σφάλμα κατά την καταγραφή ιστορικού',
        'error' => $conn->error
    ]);
    exit();
}

$conn->commit();

echo json_encode([
    'success' => true,
    'message' => 'Η ανάθεση ακυρώθηκε επιτυχώς'
]);
?>