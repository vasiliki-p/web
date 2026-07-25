<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['invitation_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}
$update_invitation = "UPDATE invitations SET 
                     status = 'rejected',
                     responded_at = NOW()
                     WHERE invitation_id = ? AND invited_prof_id = ?";
$stmt = $conn->prepare($update_invitation);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database prepare error']);
    exit();
}

//$stmt->bind_param("ii", $input['invitation_id'], $_SESSION['user_id']);
$stmt->bind_param("ii", $input['invitation_id'], $input['prof_id']);
$result = $stmt->execute();

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database update error']);
    exit();
}

echo json_encode(['success' => true]);
?>