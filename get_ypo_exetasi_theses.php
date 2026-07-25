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


$query = "SELECT t.thesis_id, t.title, t.description, t.status, 
                 t.pdf_file, t.created_at, t.updated_at, t.draft_file,
                 t.final_grade, t.final_report_file, t.examination_minutes_file,
                 s.name as student_name, s.lastname as student_lastname,
                 c.prof_role
          FROM thesis t
          JOIN committee c ON t.thesis_id = c.thesis_id
          JOIN professors p ON c.prof_id = p.prof_id
          LEFT JOIN students s ON t.student_id = s.student_id
          WHERE t.status = 'Υπό Εξέταση'
          AND p.user_id = ?";

$params = [$user_id];
$param_types = "i";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $search_term = "%" . trim($_GET['search']) . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= "ss";
}

$query .= " GROUP BY t.thesis_id ORDER BY t.updated_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$theses = [];
while ($row = $result->fetch_assoc()) {
    $committee_query = "SELECT p.first_name, p.last_name, c.prof_role, c.grade 
                       FROM committee c
                       JOIN professors p ON c.prof_id = p.prof_id
                       WHERE c.thesis_id = ?";
    $stmt_committee = $conn->prepare($committee_query);
    $stmt_committee->bind_param("i", $row['thesis_id']);
    $stmt_committee->execute();
    $committee_result = $stmt_committee->get_result();
    
    $committee = [];
    $grades_list = [];
    while ($member = $committee_result->fetch_assoc()) {
        $committee[] = $member;
        if ($member['grade'] !== null) {
            $grades_list[] = $member['first_name'] . ' ' . $member['last_name'] . ':' . $member['grade'];
        }
    }
    
    $row['committee'] = $committee;
    $row['grades_list'] = implode('|', $grades_list);
    $theses[] = $row;
    $stmt_committee->close();
}

echo json_encode(['theses' => $theses]);

$stmt->close();
$conn->close();
?>