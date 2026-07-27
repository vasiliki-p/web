<?php 
session_start();
include 'connection.php';

$error_msg = ""; // Αρχικοποιούμε μια άδεια μεταβλητή για το λάθος
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username'],$_POST['password'])){
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
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
                $error_msg = "Λανθασμένο όνομα χρήστη ή κωδικός"; // Αποθήκευση αντί για echo
            }
        } else {
            $error_msg = "Ο χρήστης δεν υπάρχει"; // Αποθήκευση αντί για echo
        }
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Login - Thesis Management</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .demo-box {
                margin-top: 20px;
                padding: 15px;
                background-color: rgba(0, 0, 0, 0.05);
                border: 1px dashed #777;
                border-radius: 8px;
                font-size: 14px;
                text-align: left;
            }
            .demo-box h4 {
                margin-top: 0;
                margin-bottom: 10px;
                text-align: center;
                font-size: 16px;
            }
            .demo-btn {
                display: block;
                width: 100%;
                margin-bottom: 8px;
                padding: 8px;
                background-color: transparent; 
                border: 1px solid #ccc;
                border-radius: 4px;
                cursor: pointer;
                text-align: left;
                transition: transform 0.2s, opacity 0.2s;
            }
            .demo-btn:hover {
                transform: scale(1.02);
                opacity: 0.9;
            }
        </style>
     </head>
    
    <body>
        <div class="login">
            <h2>Σύνδεση</h2>
            
            <!-- 💡 Το σταθερό "αόρατο" κουτάκι για τα λάθη -->
            <div style="min-height: 24px; color: #ff4c4c; text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 10px;">
                <?php echo $error_msg; ?>
            </div>

            <form id="Login" name="Login" method="POST"> 
                <input type="text" id="username" name="username" placeholder="Όνομα Χρήστη" required>
                <input type="password" id="password" name="password" placeholder="Κωδικός" required>
                <button type="submit">Είσοδος</button>
            </form>

            <div class="demo-box">
                <h4>🎓 Demo Access (RBAC)</h4>
                <p style="text-align: center; margin-bottom: 10px; color: #555; font-size: 12px;">Click a role to auto-fill credentials:</p>
                
                <button type="button" class="demo-btn" onclick="fillCredentials('student_demo', '1234')">
                    👨‍🎓 <b>Student:</b> student_demo
                </button>
                <button type="button" class="demo-btn" onclick="fillCredentials('prof_demo', '1234')">
                    👨‍🏫 <b>Professor:</b> prof_demo
                </button>
                <button type="button" class="demo-btn" style="margin-bottom: 0;" onclick="fillCredentials('sec_demo', '1234')">
                    🏢 <b>Secretariat:</b> sec_demo
                </button>
            </div>
            
        </div>
        <p style="position: fixed; bottom: 0; width: 100%; text-align: center;"><b>2024-2025 - Προγραμματισμός & Συστήματα στον Παγκόσμιο Ιστό</b></p>
        
        <script>
            function fillCredentials(user, pass) {
                document.getElementById('username').value = user;
                document.getElementById('password').value = pass;
            }
        </script>
    </body>
</html>
