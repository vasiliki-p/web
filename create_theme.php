<?php
// Έλεγχος αν ο χρήστης είναι καθηγητής
include 'restricted.php';
include 'connection.php';
include 'menu.html';

if ($_SESSION['role'] !== 'professor') {
    header("Location: login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];
$response = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Λήψη δεδομένων από τη φόρμα
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'Διαθέσιμη';
    $pdf_file = null;

    // Διαχείριση μεταφόρτωσης αρχείου PDF
    if (isset($_FILES['pdf_file']['error']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
        if ($file_ext === 'pdf') {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_path = $upload_dir . uniqid() . '_' . basename($_FILES['pdf_file']['name']);
            if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $file_path)) {
                $pdf_file = $file_path;
            } else {
                $response = "<div class='error'>Σφάλμα μεταφόρτωσης αρχείου</div>";
            }
        } else {
            $response = "<div class='error'>Μόνο PDF επιτρέπονται</div>";
        }
    }

    if (empty($response)) {
        // 1. Εισαγωγή της πτυχιακής στον πίνακα `thesis`
        $stmt = $conn->prepare("INSERT INTO thesis (title, description, status, pdf_file, prof_id, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $title, $description, $status, $pdf_file, $prof_id, $prof_id);

        if ($stmt->execute()) {
            $thesis_id = $conn->insert_id; // Παίρνουμε το ID της νέας πτυχιακής

            // 2. Προσθήκη του καθηγητή στην επιτροπή ως "Επιβλέπων"
            $committee_stmt = $conn->prepare("INSERT INTO committee (thesis_id, prof_id, prof_role) VALUES (?, (SELECT prof_id FROM professors WHERE user_id = ?), 'Επιβλέπων')");
            $committee_stmt->bind_param("ii", $thesis_id, $prof_id);

            if ($committee_stmt->execute()) {
                $response = "<div class='success'>Η πτυχιακή δημιουργήθηκε και ο καθηγητής προστέθηκε στην επιτροπή!</div>";
            } else {
                $response = "<div class='error'>Η πτυχιακή δημιουργήθηκε, αλλά αποτυχία προσθήκης στην επιτροπή: " . $conn->error . "</div>";
            }
            $committee_stmt->close();
        } else {
            $response = "<div class='error'>Αποτυχία δημιουργίας πτυχιακής: " . $conn->error . "</div>";
        }
        $stmt->close();
    }

    // Απάντηση για AJAX (αν το αίτημα είναι AJAX)
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo $response;
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Φόρμα Διαχείρισης Πτυχιακών</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Δημιουργία Θεμάτων Διπλωματικών Εργασιών</h1>
        <form id="thesisForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="thesis_id" value="">
            
            <div class="form">
                <label for="title">Τίτλος διπλωματικής:</label>
                <input type="text" id="title" name="title" required>
                
                <label for="description">Περιγραφή:</label>
                <textarea id="description" name="description" required></textarea>
                
                <label for="status">Κατάσταση:</label>
                <select id="status" name="status" required>
                    <option value="">-- Επιλέξτε Κατάσταση --</option>
                </select>
                
                <label for="pdf_file">Ανέβασμα PDF (Μέγιστο μέγεθος: 5MB):</label>
                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf">
                
                <button type="submit" class="btn">Υποβολή</button>
            </div>
        </form>
        <div id="responseMessage"></div>
        <button onclick="window.history.back()" class="btn btn-secondary">Επιστροφή</button>
        
    
    </div>
    <script src="create_theme.js"></script>
</body>
</html>