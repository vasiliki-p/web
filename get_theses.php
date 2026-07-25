<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// Get professor ID
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

$query = "SELECT 
            t.thesis_id,
            t.title,
            t.description,
            t.status,
            t.pdf_file,
            c.prof_role,
            t.created_at,
            t.updated_at
          FROM 
            thesis t
          INNER JOIN 
            committee c ON t.thesis_id = c.thesis_id
          WHERE 
            c.prof_id = ?";

$params = [$prof_id];
$param_types = "i";

//  search filter
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $search_term = "%" . trim($_GET['search']) . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= "ss";
}

//  status filter 
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $query .= " AND t.status = ?";
    $params[] = $_GET['status'];
    $param_types .= "s";
}

// prof_role filter
if (isset($_GET['prof_role']) && !empty($_GET['prof_role'])) {
    $query .= " AND c.prof_role = ?";
    $params[] = $_GET['prof_role'];
    $param_types .= "s";
}

$query .= " ORDER BY t.created_at DESC";

// Execute query
$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
    exit();
}

$stmt->bind_param($param_types, ...$params);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Database execute error: ' . $stmt->error]);
    exit();
}

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
        'updated_at' => $row['updated_at']
    ];
}

echo json_encode($theses);

$stmt->close();
$conn->close();
?>