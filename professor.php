<?php 
include 'restricted.php';
include 'connection.php';
include 'menu.html'; 
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <title>Home Page</title>
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
  
<div class="main">
    <h1>Καλώς ήρθατε!</h1>
</div>



<a name="top">	
<div class="homepage" id="myhomepage">
        <a href="index.php"> Προβολή και Δημιουργία θεμάτων προς ανάθεση</a><br>
        <a href="search_students.php">Αρχική ανάθεση</a><br>
        <a href="list_all.php">Λίστα διπλωματικών</a><br>
        <a href="list_invitations.php">Προσκλήσεις συμμετοχής σε τριμελή</a><br>
        <a href=".php">Στατιστικά</a><br>
        <a href="manage_theses.php">Διαχείριση διπλωματικών εργασιών</a><br>
    </div>
</a>
</body>
</html>
