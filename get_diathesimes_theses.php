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

// Build the query to get all theses that concern the professor
$query = "SELECT t.*, 
                 CASE 
                     WHEN t.created_by = ? THEN 'Επιβλέπων'
                     WHEN c.prof_role IS NOT NULL THEN c.prof_role
                     ELSE NULL
                 END AS prof_role
          FROM thesis t
          LEFT JOIN committee c ON t.thesis_id = c.thesis_id AND c.prof_id = ?
          WHERE t.status = 'Διαθέσιμη' 
          AND (t.created_by = ? OR c.prof_id = ?)";

$params = [$user_id, $prof_id, $user_id, $prof_id];
$param_types = "iiii";

// Add search filter
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $search_term = "%" . trim($_GET['search']) . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= "ss";
}

// Add role filter (only applies to committee roles)
if (isset($_GET['prof_role']) && !empty($_GET['prof_role'])) {
    $query .= " AND (c.prof_role = ? OR (t.created_by = ? AND ? = 'Επιβλέπων'))";
    $params[] = $_GET['prof_role'];
    $params[] = $user_id;
    $params[] = $_GET['prof_role'];
    $param_types .= "sis";
}

$query .= " GROUP BY t.thesis_id"; 
$query .= " ORDER BY t.created_at DESC";

// Execute query
$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
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
        'prof_role' => $row['prof_role'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
        'created_by' => $row['created_by'] 
    ];
}

echo json_encode($theses);

$stmt->close();
$conn->close();
?>