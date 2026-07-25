<?php
// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και σύνδεση με τη βάση
include 'restricted.php';
include 'connection.php';

header('Content-Type: application/json');

// Επιτρέπεται μόνο POST αίτημα
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Λήψη του id της πτυχιακής από το URL
$thesis_id = $_GET['id'] ?? 0;
if ($thesis_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid thesis ID']);
    exit();
}

// Έλεγχος αν ο χρήστης έχει δικαίωμα να επεξεργαστεί τη συγκεκριμένη πτυχιακή
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT thesis_id, pdf_file FROM thesis WHERE thesis_id = ? AND prof_id = ?");
$stmt->bind_param("ii", $thesis_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Thesis not found or access denied']);
    exit();
}

$current_data = $result->fetch_assoc();
$current_pdf = $current_data['pdf_file'];

// Αρχικοποίηση μεταβλητών
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$status = $_POST['status'] ?? '';
$pdf_file = $current_pdf;

// Έλεγχος αν έχουν συμπληρωθεί τα απαιτούμενα πεδία
if (empty($title) || empty($description) || empty($status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
    $conn->close();
    exit();
}

// Διαχείριση μεταφόρτωσης αρχείου PDF αν υπάρχει
if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    // Έλεγχος τύπου αρχείου
    $file_type = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
    if ($file_type !== 'pdf') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Only PDF files are allowed']);
        $conn->close();
        exit();
    }

    // Έλεγχος μεγέθους αρχείου (μέγιστο 5MB)
    if ($_FILES['pdf_file']['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'File size exceeds 5MB limit']);
        $conn->close();
        exit();
    }

    // Δημιουργία φακέλου uploads αν δεν υπάρχει
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
        $conn->close();
        exit();
    }

    // Δημιουργία μοναδικού ονόματος αρχείου
    $new_filename = uniqid() . '_' . basename($_FILES['pdf_file']['name']);
    $target_path = $upload_dir . $new_filename;

    // Μετακίνηση του ανεβασμένου αρχείου
    if (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $target_path)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
        $conn->close();
        exit();
    }

    // Διαγραφή παλιού αρχείου αν υπάρχει
    if (!empty($current_pdf) && file_exists($current_pdf) && !unlink($current_pdf)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete old file']);
        $conn->close();
        exit();
    }

    $pdf_file = $target_path;
}

// Ενημέρωση της εγγραφής στη βάση
$stmt = $conn->prepare("UPDATE thesis SET title = ?, description = ?, status = ?, pdf_file = ?, updated_at = NOW() WHERE thesis_id = ?");
$stmt->bind_param("ssssi", $title, $description, $status, $pdf_file, $thesis_id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database update failed: ' . $stmt->error]);
    $conn->close();
    exit();
}

// Επιστροφή επιτυχούς απάντησης με τα ενημερωμένα δεδομένα
echo json_encode([
    'success' => true,
    'message' => 'Thesis updated successfully',
    'thesis' => [
        'title' => $title,
        'description' => $description,
        'status' => $status,
        'pdf_file' => $pdf_file
    ]
]);

$conn->close();
?>