<?php
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$user_id = $_SESSION['user_id'];
$thesis_id = isset($_GET['thesis_id']) ? intval($_GET['thesis_id']) : 0;

if ($thesis_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid thesis ID']);
    exit();
}

// Get the professor's ID
$prof_query = "SELECT prof_id FROM professors WHERE user_id = ?";
$stmt = $conn->prepare($prof_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$prof_result = $stmt->get_result();
$prof_row = $prof_result->fetch_assoc();
$professor_id = $prof_row['prof_id'];

// Verify the professor is part of the committee for this thesis
$committee_check = "SELECT 1 FROM committee WHERE thesis_id = ? AND prof_id = ?";
$stmt = $conn->prepare($committee_check);
$stmt->bind_param("ii", $thesis_id, $professor_id);
$stmt->execute();
$committee_result = $stmt->get_result();

if ($committee_result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'You are not part of this thesis committee']);
    exit();
}

// Get all invitations for this thesis
$query = "SELECT 
            i.invitation_id,
            i.invited_prof_id,
            i.role,
            i.status,
            i.created_at,
            i.responded_at,
            p.prof_id,
            u.username,
            u.user_id
          FROM invitations i
          JOIN professors p ON i.invited_prof_id = p.prof_id
          JOIN users u ON p.user_id = u.user_id
          WHERE i.thesis_id = ?
          ORDER BY i.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $thesis_id);
$stmt->execute();
$result = $stmt->get_result();

$invitations = [];
while ($row = $result->fetch_assoc()) {
    $invitations[] = [
        'invitation_id' => $row['invitation_id'],
        'prof_id' => $row['prof_id'],
        'username' => $row['username'],
        'role' => $row['role'],
        'status' => $row['status'],
        'created_at' => $row['created_at'],
        'responded_at' => $row['responded_at'],
        'invited_prof_id' => $row['invited_prof_id']
    ];
}

echo json_encode(['success' => true, 'invitations' => $invitations]);

$stmt->close();
$conn->close();
?>