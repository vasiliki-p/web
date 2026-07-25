<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['invitation_id']) || !isset($data['thesis_id']) || !isset($data['prof_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit();
}

$invitation_id = $data['invitation_id'];
$thesis_id = $data['thesis_id'];
$professor_id = $data['prof_id'];

// Verify the professor exists
$check_prof = "SELECT prof_id FROM professors WHERE prof_id = ?";
$stmt = $conn->prepare($check_prof);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

$stmt->bind_param("i", $professor_id);
$stmt->execute();
$prof_result = $stmt->get_result();
if ($prof_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Professor not found']);
    exit();
}

// Start transaction
$conn->begin_transaction();

// 1. Update the invitation status
$update_invitation = "UPDATE invitations SET 
                 status = 'accepted',
                 responded_at = NOW()
                 WHERE invitation_id = ?";
$stmt = $conn->prepare($update_invitation);
if (!$stmt || !$stmt->bind_param("i", $invitation_id) || !$stmt->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update invitation']);
    exit();
}

// 2. Add professor to committee
$insert_committee = "INSERT INTO committee (thesis_id, prof_id, prof_role) 
                    VALUES (?, ?, 'Μέλος')";
$stmt = $conn->prepare($insert_committee);
if (!$stmt || !$stmt->bind_param("ii", $thesis_id, $professor_id) || !$stmt->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to add to committee']);
    exit();
}

// 3. Update thesis status
$update_thesis = "UPDATE thesis SET status = 'Ενεργή' WHERE thesis_id = ?";
$stmt = $conn->prepare($update_thesis);
if (!$stmt || !$stmt->bind_param("i", $thesis_id) || !$stmt->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update thesis status']);
    exit();
}

$conn->commit();
echo json_encode(['success' => true]);
?>