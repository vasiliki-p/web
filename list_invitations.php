<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html';

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
    <title>Προσκλήσεις Συμμετοχής</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Προσκλήσεις Συμμετοχής σε Τριμελείς Επιτροπές</h1>
        
        <div id="responseMessage"></div>
        
        <table>
            <thead>
                <tr>
                    <th>Τίτλος Πτυχιακής</th>
                    <th>Περιγραφή</th>
                    <th>Φοιτητής</th>
                    <th>ΑΜ Φοιτητή</th>
                    <th>Κατάσταση</th>
                    <th>Επιβλέπων Καθηγητής</th>
                    <th>Ημερομηνία Δημιουργίας</th>
                    <th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody id="invitationsTableBody">
                <!-- Will be filled via AJAX -->
            </tbody>
        </table>
       
        <button onclick="window.history.back()" class="btn btn-secondary">Επιστροφή</button>

    </div>

    <script src="list_invitations.js"></script>
</body>
</html>