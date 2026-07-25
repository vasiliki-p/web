<?php
// Ξεκινάμε τη συνεδρία
session_start();

// Καταστρέφουμε τη συνεδρία
session_unset();
session_destroy();

// Ανακατεύθυνση του χρήστη στη σελίδα σύνδεσης 
header("Location: login.php");
exit;
?>
