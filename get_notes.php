<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

// Validate session
if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

// Validate input
$thesis_id = filter_input(INPUT_GET, 'thesis_id', FILTER_VALIDATE_INT);
$professor_id = filter_input(INPUT_GET, 'prof_id', FILTER_VALIDATE_INT);

if (!$thesis_id || !$professor_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit();
}

// Get notes with prepared statement
$query = "
    SELECT n.note_id, n.notes, n.created_at, 
           u.username AS name, 
           u.role 
    FROM notes n
    JOIN professors p ON n.prof_id = p.prof_id
    JOIN users u ON p.user_id = u.user_id
    WHERE n.thesis_id = ? AND n.prof_id = ?
    ORDER BY n.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $thesis_id, $professor_id);
$stmt->execute();

$result = $stmt->get_result();
$notes = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $notes
]);