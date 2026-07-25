<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$user_id = $_SESSION['user_id'];
$prof_query = "SELECT prof_id FROM professors WHERE user_id = ?";
$stmt_prof = $conn->prepare($prof_query);
$stmt_prof->bind_param("i", $user_id);
$stmt_prof->execute();
$prof_result = $stmt_prof->get_result();

if ($prof_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Professor not found']);
    exit();
}

$prof_data = $prof_result->fetch_assoc();
$prof_id = $prof_data['prof_id'];
$stmt_prof->close();

$search = isset($_GET['search']) ? $_GET['search'] : '';


// Κύριο ερώτημα: Φέρνει διπλωματικές "Ενεργή" που αφορούν τον καθηγητή
$query = "SELECT t.thesis_id, t.title, t.description, t.status, t.pdf_file, t.created_at, t.updated_at,
                 s.name as student_name, s.lastname as student_lastname, s.AM,
                 (SELECT notes FROM notes WHERE thesis_id = t.thesis_id AND prof_id = ? LIMIT 1) AS professor_notes,
                 CASE
                     WHEN t.created_by = ? THEN 'Επιβλέπων'
                     ELSE c.prof_role
                 END AS prof_role
          FROM thesis t
          LEFT JOIN students s ON t.student_id = s.student_id
          LEFT JOIN committee c ON t.thesis_id = c.thesis_id AND c.prof_id = ?
          WHERE t.status = 'Ενεργή'
          AND (t.created_by = ? OR c.prof_id = ?)";

if (!empty($search)) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $searchParam = "%$search%";
}

$query .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $stmt->bind_param("iiiiiss", $prof_id, $user_id, $prof_id, $user_id, $prof_id, $searchParam, $searchParam);
} else {
    $stmt->bind_param("iiiii", $prof_id, $user_id, $prof_id, $user_id, $prof_id);
}

$stmt->execute();
$result = $stmt->get_result();

$theses = [];
while ($row = $result->fetch_assoc()) {
    $theses[] = [
        'thesis_id' => $row['thesis_id'],
        'title' => $row['title'],
        'description' => $row['description'],
        'status' => $row['status'],
        'pdf_file' => $row['pdf_file'],
        'professor_notes' => $row['professor_notes'],
        'prof_role' => $row['prof_role'],
        'student_name' => $row['student_name'],
        'student_lastname' => $row['student_lastname'],
        'AM' => $row['AM'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
}

echo json_encode(['theses' => $theses]);

$stmt->close();
$conn->close();
?>