<?php
include 'connection.php';
include 'restricted.php'; 

header('Content-Type: application/json');

// Ερώτημα για να πάρουμε τις δυνατές τιμές του πεδίου status από το schema
$result = $conn->query("SHOW COLUMNS FROM thesis LIKE 'status'");
$options = [];

if ($result && $result->num_rows > 0) {
    $type = $result->fetch_assoc()['Type'];
    // Εξαγωγή των τιμών enum από το schema
    if (preg_match("/^enum\('(.*)'\)/", $type, $matches)) {
        $options = explode("','", $matches[1]);
    }
}

// Επιστροφή των επιλογών σε JSON μορφή
echo json_encode([
    'success' => true,
    'options' => $options
]);

$conn->close();
?>