<?php
include 'restricted.php'; 
include 'connection.php'; 

header('Content-Type: application/json');

// Έλεγχος εγκυρότητας του id της διπλωματικής
$thesis_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($thesis_id <= 0) {
    http_response_code(400); 
    echo json_encode(['error' => 'Invalid thesis ID']); 
    exit();
}

// Έλεγχος αν ο χρήστης έχει δικαίωμα πρόσβασης στη διπλωματική
$user_id = $_SESSION['user_id'];
$query = "SELECT t.*, c.prof_role 
          FROM thesis t
          LEFT JOIN committee c ON t.thesis_id = c.thesis_id AND c.prof_id IN (SELECT prof_id FROM professors WHERE user_id = ?)
          WHERE t.thesis_id = ? AND (t.created_by = ? OR c.prof_role IS NOT NULL)";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $thesis_id, $user_id); 

$stmt->execute();
$result = $stmt->get_result(); 

// Έλεγχος αν βρέθηκε η διπλωματική ή αν ο χρήστης έχει δικαίωμα πρόσβασης
if ($result->num_rows === 0) {
    http_response_code(404); 
    echo json_encode(['error' => 'Thesis not found or access denied']); 
    exit();
}

$thesis = $result->fetch_assoc(); // Λήψη δεδομένων διπλωματικής
echo json_encode($thesis); // Επιστροφή δεδομένων σε μορφή JSON

// Κλείσιμο του statement και της σύνδεσης με τη βάση
$stmt->close(); 
$conn->close(); 
?>