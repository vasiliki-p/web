<?php
// Έλεγχος αν ο χρήστης είναι καθηγητής και σύνδεση με τη βάση
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

// Έλεγχος αν ο χρήστης έχει ρόλο καθηγητή
if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// Έλεγχος αν δόθηκε ΑΜ φοιτητή
$am = isset($_GET['am']) ? $_GET['am'] : '';
if (empty($am)) {
    http_response_code(400);
    echo json_encode(['error' => 'Student AM required']);
    exit();
}

// Εκτέλεση ερωτήματος για τα στοιχεία του φοιτητή και αν έχει ανάθεση πτυχιακής
$query = "SELECT s.AM, s.name, s.lastname, t.thesis_id as assigned_thesis_id 
          FROM students s 
          LEFT JOIN thesis t ON s.AM = t.student_id 
          WHERE s.AM = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $am);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Student not found']);
    exit();
}

$student = $result->fetch_assoc();
echo json_encode($student);

$stmt->close();
$conn->close();
?>