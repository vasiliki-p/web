<?php
include 'restricted.php';  
include 'connection.php';

header('Content-Type: application/json');

if (!isset($_GET['thesis_id']) || !is_numeric($_GET['thesis_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Λάθος ή μη έγκυρο αναγνωριστικό διπλωματικής']);
    exit();
}

$thesisId = (int)$_GET['thesis_id'];
if ($thesisId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Λάθος αναγνωριστικό διπλωματικής']);
    exit();
}

// Κύριο ερώτημα για τη διπλωματική
$query = "SELECT t.*, 
                 s.name AS student_name, 
                 s.lastname AS student_lastname, 
                 s.AM,
                 u.user_id AS student_user_id,
                 creator.username AS created_by_username
          FROM thesis t 
          LEFT JOIN students s ON t.student_id = s.student_id 
          LEFT JOIN users u ON s.user_id = u.user_id
          LEFT JOIN users creator ON t.created_by = creator.user_id
          WHERE t.thesis_id = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $thesisId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Δεν βρέθηκε η διπλωματική']);
    exit();
}

$thesis = $result->fetch_assoc();

// Επιτροπή με βαθμούς 
$committeeQuery = "SELECT c.prof_id, c.prof_role, c.grade,
                          p.first_name, p.last_name, p.department,
                          u.username, u.role
                   FROM committee c
                   JOIN professors p ON c.prof_id = p.prof_id
                   JOIN users u ON p.user_id = u.user_id
                   WHERE c.thesis_id = ?";

$committeeStmt = $conn->prepare($committeeQuery);
if ($committeeStmt) {
    $committeeStmt->bind_param("i", $thesisId);
    $committeeStmt->execute();
    $committeeResult = $committeeStmt->get_result();
    
    $thesis['committee'] = [];
    while ($row = $committeeResult->fetch_assoc()) {
        $thesis['committee'][] = $row;
    }
    $committeeStmt->close();
    
    // Δημιουργία λίστας βαθμών για εύκολη προβολή
    $gradesList = [];
    foreach ($thesis['committee'] as $member) {
        if ($member['grade'] !== null) {
            $gradesList[] = $member['first_name'] . ' ' . $member['last_name'] . ':' . $member['grade'];
        }
    }
    $thesis['grades_list'] = implode('|', $gradesList);
}

// Ιστορικό κατάστασης
$historyQuery = "SELECT h.*, u.username AS changed_by_username
                 FROM thesis_status_history h
                 JOIN users u ON h.changed_by = u.user_id
                 WHERE h.thesis_id = ?
                 ORDER BY h.changed_at DESC";

$historyStmt = $conn->prepare($historyQuery);
if ($historyStmt) {
    $historyStmt->bind_param("i", $thesisId);
    $historyStmt->execute();
    $historyResult = $historyStmt->get_result();
    
    $thesis['status_history'] = [];
    while ($row = $historyResult->fetch_assoc()) {
        $thesis['status_history'][] = $row;
    }
    $historyStmt->close();
}

// Αρχεία
$uploadDir = 'uploads/';
$fileFields = [
    'pdf_file', 
    'grading_form',
    'final_report_file',
    'examination_minutes_file',
    'draft_file'
];

foreach ($fileFields as $field) {
    if (!empty($thesis[$field])) {
        $thesis[$field . '_url'] = $uploadDir . basename($thesis[$field]);
    } else {
        $thesis[$field . '_url'] = null;
    }
}

echo json_encode($thesis);

$stmt->close();
$conn->close();
?>