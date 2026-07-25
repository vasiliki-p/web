<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

function getUsernameByProfId($conn, $prof_id) {
    $stmt = $conn->prepare("SELECT u.username FROM users u JOIN professors p ON u.user_id = p.user_id WHERE p.prof_id = ? LIMIT 1");
    $stmt->bind_param("i", $prof_id);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
    return $username;
}

// Βρες το student_id του τρέχοντος χρήστη (φοιτητή)
$stmt = $conn->prepare("SELECT s.student_id FROM students s WHERE s.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($student_id);
if (!$stmt->fetch()) {
    echo "<h2>Δεν βρέθηκε φοιτητής.</h2>";
    exit();
}
$stmt->close();

// Βρες την διπλωματική του φοιτητή
$stmt = $conn->prepare("SELECT thesis_id, title, description, pdf_file, status, assigned_at, prof_id FROM thesis WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    echo "<h2>Δεν έχει ανατεθεί κάποια διπλωματική εργασία.</h2>";
    exit();
}

$stmt->bind_result($thesis_id, $title, $description, $pdf_file, $status, $assigned_at, $prof_id);
$stmt->fetch();
$stmt->close();

// Αυτόματος έλεγχος αποδοχής 2 μελών επιτροπής και αλλαγή κατάστασης
if ($status === 'Υπό Ανάθεση') {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM invitations WHERE thesis_id = ? AND role = 'Μέλος' AND status = 'accepted'");
    $stmt->bind_param("i", $thesis_id);
    $stmt->execute();
    $stmt->bind_result($accepted_count);
    $stmt->fetch();
    $stmt->close();

    if ($accepted_count >= 2) {
        $now = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE thesis SET status = 'Ενεργή', assigned_at = ? WHERE thesis_id = ?");
        $stmt->bind_param("si", $now, $thesis_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE invitations SET status = 'cancelled' WHERE thesis_id = ? AND role = 'Μέλος' AND status = 'pending'");
        $stmt->bind_param("i", $thesis_id);
        $stmt->execute();
        $stmt->close();

        $status = 'Ενεργή';
        $assigned_at = $now;
    }
}

$days_passed = '';
if ($assigned_at) {
    $assigned_date = new DateTime($assigned_at);
    $now = new DateTime();
    $interval = $assigned_date->diff($now);
    $days_passed = $interval->days . ' ημέρες';
}

$supervisor = getUsernameByProfId($conn, $prof_id);

$committee_members = [];
$stmt = $conn->prepare("
    SELECT u.username 
    FROM committee c
    JOIN professors p ON c.prof_id = p.prof_id
    JOIN users u ON p.user_id = u.user_id
    WHERE c.thesis_id = ? AND c.prof_role = 'Μέλος' AND c.prof_id != ?
");
$stmt->bind_param("ii", $thesis_id, $prof_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $committee_members[] = $row['username'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Διαχείριση Διπλωματικής Εργασίας</title>
<link rel="stylesheet" href="/style.css"> 
</head>
<body>
    <div class="container">
    <h1>Διαχείριση Διπλωματικής</h1>

    <p><strong>Τίτλος:</strong> <?= htmlspecialchars($title) ?></p>
    <p><strong>Περιγραφή:</strong> <?= nl2br(htmlspecialchars($description)) ?></p>
    <p><strong>Κατάσταση:</strong> <?= htmlspecialchars($status) ?></p>
    <p><strong>Ημ. Ανάθεσης:</strong> <?= $assigned_at ? htmlspecialchars($assigned_at) : 'Δεν έχει οριστικοποιηθεί' ?></p>
    <p><strong>Ημέρες από ανάθεση:</strong> <?= $assigned_at ? $days_passed : '—' ?></p>
    <p><strong>Επιβλέπων:</strong> <?= htmlspecialchars($supervisor) ?></p>

    <p><strong>Μέλη Επιτροπής:</strong></p>
    <?php if (empty($committee_members)): ?>
        <p>Δεν έχουν οριστεί ακόμη.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($committee_members as $member): ?>
                <li><?= htmlspecialchars($member) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
   
    <?php 
$status_clean = trim($status); // Αφαιρεί κενά πριν/μετά
?>

<?php if ($status_clean === 'Υπό Ανάθεση' && count($committee_members) < 2): ?>
    <a href="select_commitee.php?thesis_id=<?= $thesis_id ?>">Επιλογή Τριμελούς Επιτροπής</a>
<?php elseif ($status_clean === 'Υπό Εξέταση'): ?>
    <a href="submit_draft.php?thesis_id=<?= $thesis_id ?>">Ανάρτηση Πρόχειρου Κειμένου</a>
<?php elseif ($status_clean === 'Περατωμένη'): ?>
    <a href="view_final_thesis.php?thesis_id=<?= $thesis_id ?>">Προβολή Τελικής Αναφοράς</a>
<?php endif; ?>


    <br><br>
    <a href="student.php">Επιστροφή στην αρχική</a>
</div>

</body>
</html>