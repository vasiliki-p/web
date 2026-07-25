<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'secretary') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get only theses with status "Ενεργές"
$query = "SELECT 
           
            t.thesis_id, 
            t.title, 

            t.status

          FROM thesis t
          LEFT JOIN students s ON t.student_id = s.student_id
         WHERE t.status IN ('Ενεργή', 'Υπό Εξέταση')";

if (!empty($search)) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $searchParam = "%$search%";
}

$query .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $stmt->bind_param("ss", $searchParam, $searchParam);
}

$stmt->execute();
$result = $stmt->get_result();

$theses = [];
while ($row = $result->fetch_assoc()) {
    $theses[] = $row;
}

echo json_encode(['success' => true, 'theses' => $theses]);

$stmt->close();
$conn->close();
?>