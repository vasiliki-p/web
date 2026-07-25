<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html';

$thesis_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($thesis_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid thesis ID']);
    exit();
}

if ($_SESSION['role'] !== 'professor') {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Επεξεργασία Πτυχιακής</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Επεξεργασία Πτυχιακής Εργασίας</h1>
        
        <div id="responseMessage"></div>
        
        <form id="thesisForm" enctype="multipart/form-data">
            <input type="hidden" id="thesis_id" value="<?= htmlspecialchars($thesis_id) ?>">
            
            <div class="form-group">
                <label for="title">Τίτλος διπλωματικής:</label>
                <input type="text" id="title" name="title" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="description">Περιγραφή:</label>
                <textarea id="description" name="description" required class="form-control"></textarea>
            </div>
            
            <div class="form-group">
                <label for="status">Κατάσταση:</label>
                <select id="status" name="status" required class="form-control">
                    <option value="">-- Επιλέξτε Κατάσταση --</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="pdf_file">Ανέβασμα νέου PDF:</label>
                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" class="form-control">
                <small class="form-text">Μέγιστο μέγεθος αρχείου: 5MB</small>
            </div>
            
            <div id="fileInfo" class="file-info"></div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Αποθήκευση Αλλαγών</button>
            </div>
        <button onclick="window.history.back()" class="btn btn-secondary">Επιστροφή</button>

        </form>
    </div>

    <script src="edit_thesis.js"></script>
</body>
</html>