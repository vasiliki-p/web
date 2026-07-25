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

$query = "SELECT 
            t.thesis_id,
            t.title,
            t.description,
            t.status,
            CONCAT(s.name, ' ', s.lastname) AS student_name,
            t.final_grade,
            t.final_report_file,
            t.examination_minutes_file,
            t.external_links,
            t.created_at,
            t.completed_at,
            t.notes AS thesis_notes,
            t.repository_link,
            GROUP_CONCAT(DISTINCT CONCAT(p.first_name, ' ', p.last_name, ' (', c.prof_role, ')') SEPARATOR ', ') AS committee_members,
            (SELECT GROUP_CONCAT(CONCAT(n.notes, ' (', DATE_FORMAT(n.created_at, '%d/%m/%Y'), ')') SEPARATOR ' | ') 
             FROM notes n 
             WHERE n.thesis_id = t.thesis_id) AS all_notes
          FROM thesis t
          LEFT JOIN students s ON t.student_id = s.student_id
          LEFT JOIN committee c ON t.thesis_id = c.thesis_id
          LEFT JOIN professors p ON c.prof_id = p.prof_id
          WHERE t.status = 'Περατωμένη'
          AND (t.prof_id = ? OR c.prof_id = ?)";

if (!empty($search)) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $search_term = "%$search%";
}

$query .= " GROUP BY t.thesis_id
            ORDER BY t.completed_at DESC";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $stmt->bind_param("iiss", $prof_id, $prof_id, $search_term, $search_term);
} else {
    $stmt->bind_param("ii", $prof_id, $prof_id);
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
        'student_name' => $row['student_name'],
        'committee' => $row['committee_members'],
        'notes' => $row['all_notes'],
        'final_report_file' => $row['final_report_file'],
        'final_grade' => $row['final_grade'],
        'external_links' => $row['external_links'],
        'created_at' => $row['created_at'],
        'completed_at' => $row['completed_at'],
        'thesis_notes' => $row['thesis_notes'],
        'repository_link' => $row['repository_link'],
        'examination_minutes_file' => $row['examination_minutes_file']
    ];
}

echo json_encode(['theses' => $theses]);

$stmt->close();
$conn->close();
?>