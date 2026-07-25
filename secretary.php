<?php 
include 'restricted.php';
include 'connection.php';
include 'menu.html'; 

if ($_SESSION['role'] !== 'secretary') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html style="overflow: hidden;">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
       <link rel="stylesheet" href="style.css">
    <title>Home Page</title>
</head>
<body>
  
<div class="main">
    <h1>Καλώς ήρθατε!</h1>
</div>

<div class="homepage" id="myhomepage">
        <a href="provoli_dipl_erg.php"> Προβολή Διπλωματικών Εργασιών </a><br>
        <a href="jsoninsert.php">Εισαγωγή αρχείου JSON</a><br>
        <a href="diaxeirishdipl.php">Διαχείριση Διπλωματικών Εργασιών</a><br>
    </div>
   

</body>
</html>