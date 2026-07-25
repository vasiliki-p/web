<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

// Verify professor role
if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "
    SELECT 
        s.AM, 
        s.name, 
        s.lastname, 
        t.thesis_id AS assigned_thesis_id
    FROM 
        students s
    LEFT JOIN 
        thesis t ON s.student_id = t.student_id
    WHERE 
        s.AM LIKE ? OR 
        s.name LIKE ? OR 
        s.lastname LIKE ?
";
$stmt = $conn->prepare($query);
$searchTerm = "%$search%";
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = [
        'AM' => $row['AM'],
        'name' => $row['name'],
        'lastname' => $row['lastname'],
        'assigned_thesis_id' => $row['assigned_thesis_id'] 
    ];
}

echo json_encode($students);
?>