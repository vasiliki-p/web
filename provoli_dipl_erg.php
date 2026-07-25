<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html';

if ($_SESSION['role'] !== 'secretary') {
    header("Location: login.php");
    exit();
}

// Get secretary's ID
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT member_id FROM secretary WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$secretary = $result->fetch_assoc();

if (!$secretary) {
    die("Δεν βρέθηκε μέλος γραμματείας με αυτό το user_id.");
}

$_SESSION['member_id'] = $secretary['member_id']; 
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Πτυχιακές Εργασίες</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Πτυχιακές Εργασίες</h1>

        
        
        <div id="responseMessage"></div>
        
        
        <table id="thesesTable">
            <thead>
                <tr>
                    <th>Τίτλος</th>
                    <th>Κατάσταση</th>


                </tr>
            </thead>
            <tbody id="thesesBody">
                <!-- Will be filled via AJAX -->
            </tbody>
        </table>
        
    </div>


    
 
 <script src="list_energes_ypo_eksetasi.js"></script> 

</body>
</html>