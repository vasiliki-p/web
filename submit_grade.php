<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$thesis_id = $data['thesis_id'] ?? null;
$grade = $data['grade'] ?? null;

if (!$thesis_id || $grade === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

// Validate grade
if (!is_numeric($grade) || $grade < 0 || $grade > 10) {
    http_response_code(400);
    echo json_encode(['error' => 'Ο βαθμός πρέπει να είναι αριθμός μεταξύ 0 και 10']);
    exit();
}

try {
    // Get professor's role for this thesis
    $role_query = "SELECT prof_role FROM committee 
                   WHERE thesis_id = ? 
                   AND prof_id = (SELECT prof_id FROM professors WHERE user_id = ?)";
    $stmt_role = $conn->prepare($role_query);
    $stmt_role->bind_param("ii", $thesis_id, $_SESSION['user_id']);
    $stmt_role->execute();
    $role_result = $stmt_role->get_result();
    
    if ($role_result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Not part of committee']);
        exit();
    }
    
    $role_data = $role_result->fetch_assoc();
    $is_supervisor = ($role_data['prof_role'] === 'Επιβλέπων');
    
    // Update grade in committee table
    $update_query = "UPDATE committee SET grade = ? 
                     WHERE thesis_id = ? 
                     AND prof_id = (SELECT prof_id FROM professors WHERE user_id = ?)";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("dii", $grade, $thesis_id, $_SESSION['user_id']);
    $stmt->execute();
    
    // If supervisor, update final grade in thesis table
    if ($is_supervisor) {
        $update_final = $conn->prepare("UPDATE thesis SET final_grade = ? WHERE thesis_id = ?");
        $update_final->bind_param("di", $grade, $thesis_id);
        $update_final->execute();
        $update_final->close();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Ο βαθμός καταχωρήθηκε επιτυχώς',
        'is_supervisor' => $is_supervisor
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Σφάλμα βάσης δεδομένων: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>