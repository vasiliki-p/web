<?php
// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και σύνδεση με τη βάση
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

// Έλεγχος εγκυρότητας του thesis_id
$thesis_id = isset($_GET['thesis_id']) ? (int)$_GET['thesis_id'] : 0;
if ($thesis_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid thesis ID']);
    exit();
}

// Ερώτημα για τα μέλη της επιτροπής της πτυχιακής
$query = "SELECT u.username, c.prof_role as role
          FROM committee c
          JOIN professors p ON c.prof_id = p.prof_id
          JOIN users u ON p.user_id = u.user_id
          WHERE c.thesis_id = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $thesis_id);
$stmt->execute();
$result = $stmt->get_result();

$committee = [];
while ($row = $result->fetch_assoc()) {
    $committee[] = $row;
}

// Επιστροφή των μελών της επιτροπής σε μορφή JSON
echo json_encode($committee);

$stmt->close();
$conn->close();
?>