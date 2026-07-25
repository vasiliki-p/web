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

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Βρίσκουμε το prof_id του συνδεδεμένου καθηγητή
$prof_query = "SELECT prof_id FROM professors WHERE user_id = ?";
$stmt_prof = $conn->prepare($prof_query);
$stmt_prof->bind_param("i", $user_id);
$stmt_prof->execute();
$prof_result = $stmt_prof->get_result();

if ($prof_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Professor not found']);
    exit();
}

$prof_data = $prof_result->fetch_assoc();
$prof_id = $prof_data['prof_id'];
$stmt_prof->close();

// Κύριο ερώτημα για πτυχιακές υπό ανάθεση που σχετίζονται με τον καθηγητή
$query = "SELECT t.thesis_id, t.title, t.description, t.pdf_file, t.created_at,
                 s.name as student_name, s.lastname as student_lastname, s.AM,
                 CASE
                     WHEN t.created_by = ? THEN 'Επιβλέπων'
                     ELSE c.prof_role
                 END AS prof_role
          FROM thesis t
          LEFT JOIN students s ON t.student_id = s.student_id
          LEFT JOIN committee c ON t.thesis_id = c.thesis_id AND c.prof_id = ?
          WHERE t.status = 'Υπό Ανάθεση'
          AND (t.created_by = ? OR c.prof_id = ?)";

// Αν υπάρχει αναζήτηση, προσθέτουμε φίλτρο τίτλου ή περιγραφής
if (!empty($search)) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $searchParam = "%$search%";
}

$query .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    // Αν υπάρχει αναζήτηση, περνάμε και τα παραμέτρους αναζήτησης
    $stmt->bind_param("iiiss", $user_id, $prof_id, $user_id, $prof_id, $searchParam, $searchParam);
} else {
    $stmt->bind_param("iiii", $user_id, $prof_id, $user_id, $prof_id);
}

$stmt->execute();
$result = $stmt->get_result();

$theses = [];
while ($row = $result->fetch_assoc()) {
    $theses[] = $row;
}

// Επιστρέφουμε τα αποτελέσματα σε JSON μορφή
echo json_encode(['success' => true, 'theses' => $theses]);

$stmt->close();
$conn->close();
?>