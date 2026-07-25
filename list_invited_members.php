<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html';

if ($_SESSION['role'] !== 'professor') {
    header("Location: login.php");
    exit();
}

// Λήψη του id της πτυχιακής από το query string
$thesis_id = isset($_GET['thesis_id']) ? intval($_GET['thesis_id']) : 0;
if ($thesis_id <= 0) {
    header("Location: professor.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Προσκεκλημένοι Καθηγητές</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Προσκεκλημένοι Καθηγητές</h1>

        <!-- Εμφάνιση μηνυμάτων επιτυχίας/σφάλματος -->
        <div id="responseMessage"></div>
        
        <table>
            <thead>
                <tr>
                    <th>Καθηγητής</th>
                    <th>Ρόλος</th>
                    <th>Ημερομηνία Πρόσκλησης</th> 
                    <th>Κατάσταση</th>
                    <th>Ημερομηνία Απάντησης</th>
                </tr>
            </thead>
            <tbody id="invitedMembersTableBody">
                <!-- Θα γεμίσει μέσω AJAX -->
            </tbody>
        </table>
        
        <button onclick="window.history.back()" class="btn btn-secondary">Επιστροφή</button>
    </div>

    <script>
        // Περνάμε το thesisId στο JavaScript για χρήση στα AJAX
        const thesisId = <?php echo $thesis_id; ?>;
    </script>
    <script src="list_invited_members.js"></script>
</body>
</html>