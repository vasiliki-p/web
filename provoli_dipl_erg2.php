<?php
include 'restricted.php';
include 'connection.php';
include 'menu.html';    

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Μη έγκυρο ID.");
}

$id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT 
        t.title,
        t.description,
        t.status,
        t.pdf_file,
        t.created_at,
        s.name AS student_name,
        s.lastname AS student_lastname,
        s.AM,
        GROUP_CONCAT(DISTINCT CONCAT(p.first_name, ' ', p.last_name) SEPARATOR ', ') AS committee_members
    FROM thesis t
    LEFT JOIN students s ON t.student_id = s.student_id
    LEFT JOIN committee c ON t.thesis_id = c.thesis_id
    LEFT JOIN professors p ON c.prof_id = p.prof_id
    WHERE t.thesis_id = ?
    GROUP BY t.thesis_id
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$thesis = $result->fetch_assoc();

if (!$thesis) {
    die("Δεν βρέθηκε η πτυχιακή.");
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($thesis['title']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <table id="thesesTable">
        <thead>
            <tr>
                <th>Τίτλος</th>
                <th>Περιγραφή</th>
                <th>Κατάσταση</th>
                <th>Φοιτητής</th>
                <th>ΑΜ</th>
                <th>Ημερομηνία</th>
                <th>Επιτροπή</th>
                <th>PDF</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($thesis['title']) ?></td>
                <td><?= nl2br(htmlspecialchars($thesis['description'])) ?></td>
                <td><?= htmlspecialchars($thesis['status']) ?></td>
                <td><?= htmlspecialchars($thesis['student_name'] . ' ' . $thesis['student_lastname']) ?></td>
                <td><?= htmlspecialchars($thesis['AM']) ?></td>
                <td><?= date('d/m/Y', strtotime($thesis['created_at'])) ?></td>
                <td><?= htmlspecialchars($thesis['committee_members'] ?: '—') ?></td>
                <td>
                    <?php if ($thesis['pdf_file']): ?>
                        <a href="<?= htmlspecialchars($thesis['pdf_file']) ?>" target="_blank">Προβολή PDF</a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>