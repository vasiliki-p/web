<?php
include 'restricted.php';
include 'connection.php';

// Έλεγχος αν ο χρήστης είναι καθηγητής
if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    die(json_encode(['error' => 'Access denied']));
}

// Έλεγχος μορφής εξαγωγής
if (!isset($_GET['format']) || !in_array($_GET['format'], ['csv', 'json'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Μη έγκυρη μορφή εξαγωγής']));
}

$prof_id = $_SESSION['user_id'];
$export_format = $_GET['format'];

// Βασικό query με χρήση prepared statements
$query = "SELECT t.thesis_id, t.title, t.description, t.status, 
                 t.pdf_file, c.prof_role, t.created_at, t.updated_at
          FROM committee c 
          JOIN thesis t ON c.thesis_id = t.thesis_id
          WHERE c.prof_id = ?";

$params = [$prof_id];
$param_types = "i";

// Προσθήκη φίλτρων αναζήτησης
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $search_param = "%" . trim($_GET['search']) . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

// Προσθήκη φίλτρου κατάστασης
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $query .= " AND t.status = ?";
    $params[] = $_GET['status'];
    $param_types .= "s";
}

// Προσθήκη φίλτρου ρόλου
if (isset($_GET['prof_role']) && !empty($_GET['prof_role'])) {
    $query .= " AND c.prof_role = ?";
    $params[] = $_GET['prof_role'];
    $param_types .= "s";
}

$query .= " ORDER BY t.created_at DESC";

// Εκτέλεση query
$stmt = $conn->prepare($query);
if ($stmt === false) {
    http_response_code(500);
    die(json_encode(['error' => 'Database preparation error']));
}

$stmt->bind_param($param_types, ...$params);
if (!$stmt->execute()) {
    http_response_code(500);
    die(json_encode(['error' => 'Database execution error']));
}

$result = $stmt->get_result();
$data = [];

// Εξαγωγή μόνο των απαραίτητων πεδίων
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'thesis_id' => $row['thesis_id'],
        'title' => $row['title'],
        'description' => $row['description'],
        'status' => $row['status'],
        'prof_role' => $row['prof_role'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
        'pdf_file' => $row['pdf_file'] ? 'Διαθέσιμο' : 'Ανύπαρκτο'
    ];
}

if (empty($data)) {
    http_response_code(404);
    die(json_encode(['error' => 'Δεν υπάρχουν δεδομένα προς εξαγωγή']));
}

// Εξαγωγή σε CSV
if ($export_format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=thesis_export_' . date('Y-m-d_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Headers
    fputcsv($output, array_keys($data[0]));
    
    // Data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

// Εξαγωγή σε JSON
if ($export_format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=thesis_export_' . date('Y-m-d_His') . '.json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

$stmt->close();
$conn->close();
?>