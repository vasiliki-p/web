<?php
include 'restricted.php';
include 'connection.php';

if ($_SESSION['role'] !== 'secretary') {
    header("Location: login.php");
    exit();
}

header('Content-Type: text/plain');

// Διάβασμα raw JSON από POST
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!$data) {
    http_response_code(400);
    die("Άκυρο JSON");
}

// Έλεγχος πεδίων χρήστη
if (!isset($data['user']['user_id'], $data['user']['username'], $data['user']['password'], $data['user']['role'])) {
    http_response_code(400);
    die("Λείπουν δεδομένα χρήστη");
}

// Εισαγωγή στον πίνακα users
$stmtUser = $conn->prepare("INSERT INTO users (user_id, username, password, role) 
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE username=VALUES(username), password=VALUES(password), role=VALUES(role)");
$stmtUser->bind_param(
    "isss",
    $data['user']['user_id'],
    $data['user']['username'],
    $data['user']['password'],
    $data['user']['role']
);

if (!$stmtUser->execute()) {
    http_response_code(500);
    die("Σφάλμα στην εισαγωγή χρήστη: " . $stmtUser->error);
}
$stmtUser->close();

// Αν είναι student
if (isset($data['student'])) {
    if (!isset(
        $data['student']['student_id'], 
        $data['student']['user_id'], 
        $data['student']['AM'], 
        $data['student']['name'], 
        $data['student']['lastname'], 
        $data['student']['address'], 
        $data['student']['email'],
        $data['student']['mobile_phone'],
        $data['student']['landline_phone']
    )) {
        http_response_code(400);
        die("Λείπουν δεδομένα φοιτητή");
    }

    $stmtStudent = $conn->prepare("INSERT INTO students (student_id, user_id, AM, name, lastname, address, email, mobile_phone, landline_phone)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE user_id=VALUES(user_id), name=VALUES(name), lastname=VALUES(lastname), email=VALUES(email)");

    $stmtStudent->bind_param(
        "iiissssss",
        $data['student']['student_id'],
        $data['student']['user_id'],
        $data['student']['AM'],
        $data['student']['name'],
        $data['student']['lastname'],
        $data['student']['address'],
        $data['student']['email'],
        $data['student']['mobile_phone'],
        $data['student']['landline_phone']
    );

    if (!$stmtStudent->execute()) {
        http_response_code(500);
        die("Σφάλμα στην εισαγωγή φοιτητή: " . $stmtStudent->error);
    }

    $stmtStudent->close();
}

// Αν είναι professors
if (isset($data['professors'])) {
    if (!isset(
        $data['professors']['prof_id'],
        $data['professors']['user_id'],
        $data['professors']['first_name'],
        $data['professors']['last_name'],
        $data['professors']['department']
    )) {
        http_response_code(400);
        die("Λείπουν δεδομένα καθηγητή");
    }

    $stmtprofessors = $conn->prepare("INSERT INTO professors (prof_id, user_id, first_name, last_name, department)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE user_id=VALUES(user_id), first_name=VALUES(first_name), last_name=VALUES(last_name), department=VALUES(department)");

    $stmtprofessors->bind_param(
        "iisss",
        $data['professors']['prof_id'],
        $data['professors']['user_id'],
        $data['professors']['first_name'],
        $data['professors']['last_name'],
        $data['professors']['department']
    );

    if (!$stmtprofessors->execute()) {
        http_response_code(500);
        die("Σφάλμα στην εισαγωγή καθηγητή: " . $stmtprofessors->error);
    }

    $stmtprofessors->close();
}

$conn->close();
echo "Επιτυχής αποθήκευση στη βάση!";
?>