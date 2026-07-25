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

// Get the professor's ID
$prof_query = "SELECT prof_id FROM professors WHERE user_id = ?";
$stmt = $conn->prepare($prof_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$prof_result = $stmt->get_result();
$prof_row = $prof_result->fetch_assoc();
$professor_id = $prof_row['prof_id'];

// Get all invitations for this professor
$query = "SELECT 
            i.invitation_id, 
            i.thesis_id, 
            i.status,
            i.created_at,
            t.title, 
            t.description, 
            s.name as student_name, 
            s.lastname as student_lastname, 
            s.AM,
            u_supervisor.username as supervisor_username,
            p_supervisor.first_name as supervisor_first_name,
            p_supervisor.last_name as supervisor_last_name,
            p_supervisor.prof_id as supervisor_id
          FROM invitations i
          JOIN thesis t ON i.thesis_id = t.thesis_id
          LEFT JOIN students s ON t.student_id = s.student_id
          LEFT JOIN committee c ON t.thesis_id = c.thesis_id AND c.prof_role = 'Επιβλέπων'
          LEFT JOIN professors p_supervisor ON c.prof_id = p_supervisor.prof_id
          LEFT JOIN users u_supervisor ON p_supervisor.user_id = u_supervisor.user_id
          WHERE i.invited_prof_id = ?
          ORDER BY i.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();

$invitations = [];
while ($row = $result->fetch_assoc()) {
   $invitations[] = [
    'invitation_id' => $row['invitation_id'],
    'thesis_id' => $row['thesis_id'],
    'title' => $row['title'],
    'description' => $row['description'],
    'student_name' => $row['student_name'],
    'student_lastname' => $row['student_lastname'],
    'AM' => $row['AM'],
    'supervisor_name' => $row['supervisor_first_name'] . ' ' . $row['supervisor_last_name'], // Συνδυάζει first name και last name
    'supervisor_id' => $row['supervisor_id'],
    'created_at' => $row['created_at'],
    'status' => $row['status'],
    'prof_id' => $professor_id
];
}


echo json_encode(['success' => true, 'invitations' => $invitations]);

$stmt->close();
$conn->close();
?>