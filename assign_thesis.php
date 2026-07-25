<?php
// Έλεγχος αν ο χρήστης είναι καθηγητής
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

// Λήψη δεδομένων από το αίτημα (JSON)
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['student_am']) || !isset($data['thesis_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$student_am = $data['student_am'];
$thesis_id = $data['thesis_id'];
$error = null;
$success = false;

// Έναρξη transaction για ασφάλεια δεδομένων
$conn->begin_transaction();

// 1. Έλεγχος αν υπάρχει ο φοιτητής
$student_query = "SELECT student_id FROM students WHERE AM = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("s", $student_am);
$stmt->execute();
$student_result = $stmt->get_result();

if ($student_result->num_rows === 0) {
    $error = "Δεν βρέθηκε ο φοιτητής";
    goto error_handler;
}

$student = $student_result->fetch_assoc();
$student_id = $student['student_id'];

// 2. Έλεγχος αν υπάρχει η διπλωματική και αν είναι διαθέσιμη
$thesis_query = "SELECT status, prof_id FROM thesis WHERE thesis_id = ? FOR UPDATE";
$stmt = $conn->prepare($thesis_query);
$stmt->bind_param("i", $thesis_id);
$stmt->execute();
$thesis_result = $stmt->get_result();

if ($thesis_result->num_rows === 0) {
    $error = "Δεν βρέθηκε η διπλωματική";
    goto error_handler;
}

$thesis = $thesis_result->fetch_assoc();

if ($thesis['status'] !== 'Διαθέσιμη') {
    $error = "Η διπλωματική δεν είναι διαθέσιμη";
    goto error_handler;
}

// 3. Έλεγχος αν ο καθηγητής είναι ο υπεύθυνος της διπλωματικής
$prof_id = $_SESSION['user_id'];
$prof_check = "SELECT prof_id FROM professors WHERE user_id = ?";
$stmt = $conn->prepare($prof_check);
$stmt->bind_param("i", $prof_id);
$stmt->execute();
$prof_result = $stmt->get_result();
$prof = $prof_result->fetch_assoc();

if ($thesis['prof_id'] != $prof['prof_id']) {
    $error = "Δεν έχετε δικαίωμα να αναθέσετε αυτή τη διπλωματική";
    goto error_handler;
}

// 4. Ενημέρωση διπλωματικής - αλλάζει status σε 'Υπό Ανάθεση' και προσθέτει ημερομηνία ανάθεσης
$update_query = "UPDATE thesis SET 
                student_id = ?, 
                status = 'Υπό Ανάθεση',
                assigned_at = NOW()
                WHERE thesis_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("ii", $student_id, $thesis_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    $error = "Αποτυχία ανάθεσης";
    goto error_handler;
}

// 5. Καταγραφή ιστορικού αλλαγής κατάστασης
$history_query = "INSERT INTO thesis_status_history 
                 (thesis_id, old_status, new_status, changed_by) 
                 VALUES (?, 'Διαθέσιμη', 'Υπό Ανάθεση', ?)";
$stmt = $conn->prepare($history_query);
$stmt->bind_param("ii", $thesis_id, $prof_id);
$stmt->execute();

// Επιτυχής ολοκλήρωση transaction
$conn->commit();
$success = true;

// Διαχείριση σφαλμάτων και απάντηση
error_handler:
if ($error) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $error
    ]);
} elseif ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Επιτυχής ανάθεση διπλωματικής'
    ]);
}
?>