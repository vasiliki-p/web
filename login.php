<?php 
session_start();
include 'connection.php';
 
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['username'],$_POST['password'])){
                $username = $_POST['username'];
                $password = $_POST['password'];

                // Ερώτημα για έλεγχο εάν ο χρήστης υπάρχει ήδη στη βάση
                $stmt = $conn->prepare("SELECT * FROM users WHERE username=? ");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                // Εισαγωγή στοιχείων χρήστη αν δεν υπάρχει ήδη
                if ($result->num_rows > 0) {
                    // Υπάρχει εγγραφή με αυτό το όνομα χρήστη
                    $user = $result->fetch_assoc();
                    $user_id = $user['user_id'];
                    $stored_password = $user['password']; 
                    $role = $user['role']; 
                
                    if ($password === $stored_password) {
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['role'] = $role; 
                    
                        if ($role == "professor") {
                            header("Location: professor.php");
                        } else if ($role == "student") {
                            header("Location: student.php");
                        } else if ($role == "secretary") {
                            header("Location: secretary.php");
                        }
                        exit();
                    } else {
                        echo "\n\n Λανθασμένο όνομα χρήστη ή λανθασμένος κωδικός πρόσβασης";
                    }
                } else {
                    echo "\n\n Ο χρήστης δεν υπάρχει";
                }
            }
        }
        ?>

<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
        <link rel="stylesheet" href="style.css">
     </head>
    
    <body>
        <div class="login">
            <h2>Σύνδεση</h2>
            <form id="Login" name="Login" method="POST"> 
                <input type="text" id="Όνομα Χρήστη" name="username" placeholder="Όνομα Χρήστη" required>
                <input type="password" id="Κωδικός" name="password" placeholder="Κωδικός" required>
                <button type="submit">Είσοδος</button>
            </form>
      
    </div>
    <p style="position: fixed; bottom: 0; width: 100%; text-align: center;"><b>2024-2025 - Προγραμματισμός & Συστήματα στον Παγκόσμιο Ιστό</b></p>
    </body>
</html>


