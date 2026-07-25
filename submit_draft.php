<?php
// Εισαγωγή αρχείων ελέγχου πρόσβασης και σύνδεσης με βάση δεδομένων
include 'restricted.php';
include 'connection.php';
include 'menu.html';

// Έλεγχος αν υπάρχει παράμετρος thesis_id στο URL, αν όχι τερματίζουμε την εκτέλεση
if (!isset($_GET['thesis_id'])) {
    echo "Δεν βρέθηκε διπλωματική.";
    exit();
}

// Παίρνουμε το thesis_id από το URL και το κάνουμε integer για ασφάλεια
$thesis_id = (int) $_GET['thesis_id'];

// Έλεγχος αν έχει υποβληθεί η φόρμα με μέθοδο POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Αποθήκευση των τιμών από τη φόρμα σε μεταβλητές
    $external_links = $_POST['external_links'];
    $examination_date = $_POST['examination_date'];
    $examination_time = $_POST['examination_time'];
    $examination_mode = $_POST['examination_mode'];
    $room_or_link = $_POST['room_or_link'];
    // Ο σύνδεσμος αποθετηρίου μπορεί να είναι κενός, οπότε ορίζουμε null αν δεν υπάρχει
    $repository_link = $_POST['repository_link'] ?? null;

    // Διαχείριση ανέβασμα αρχείου draft
    $draft_file = null;
    if (!empty($_FILES['draft_file']['name'])) {
        // Ο φάκελος που θα αποθηκευτούν τα αρχεία
        $upload_dir = "uploads/";
        // Παίρνουμε την επέκταση αρχείου (πχ pdf, docx)
        $file_ext = pathinfo($_FILES["draft_file"]["name"], PATHINFO_EXTENSION);
        // Δημιουργούμε ένα μοναδικό όνομα αρχείου με βάση το thesis_id και το τρέχον timestamp
        $new_filename = 'draft_' . $thesis_id . '_' . time() . '.' . $file_ext;
        $draft_file = $upload_dir . $new_filename;
        // Μετακινούμε το προσωρινό αρχείο στην τελική θέση
        if (!move_uploaded_file($_FILES["draft_file"]["tmp_name"], $draft_file)) {
            // Αν υπάρχει πρόβλημα, ενημερώνουμε το χρήστη
            echo "<p>Σφάλμα κατά την αποθήκευση του αρχείου.</p>";
            $draft_file = null;
        }
    }

    // Αν δεν ανέβηκε νέο αρχείο, κρατάμε το παλιό από τη βάση δεδομένων
    if ($draft_file === null) {
        $stmt = $conn->prepare("SELECT draft_file FROM thesis WHERE thesis_id = ?");
        $stmt->bind_param("i", $thesis_id);
        $stmt->execute();
        $stmt->bind_result($draft_file);
        $stmt->fetch();
        $stmt->close();
    }

    // Ενημέρωση της εγγραφής της διπλωματικής στη βάση με τα νέα δεδομένα
    $stmt = $conn->prepare("UPDATE thesis SET draft_file = ?, external_links = ?, examination_date = ?, examination_time = ?, examination_mode = ?, room_or_link = ?, repository_link = ? WHERE thesis_id = ?");
    $stmt->bind_param("sssssssi", $draft_file, $external_links, $examination_date, $examination_time, $examination_mode, $room_or_link, $repository_link, $thesis_id);
    $stmt->execute();
    $stmt->close();

    // Ενημέρωση για επιτυχή αποθήκευση
    echo "<p>Η διπλωματική ενημερώθηκε επιτυχώς.</p>";
}

// Φόρτωση των υπαρχόντων δεδομένων για εμφάνιση στη φόρμα
$stmt = $conn->prepare("SELECT draft_file, external_links, examination_date, examination_time, examination_mode, room_or_link, html_minutes_file, repository_link FROM thesis WHERE thesis_id = ?");
$stmt->bind_param("i", $thesis_id);
$stmt->execute();
$stmt->bind_result($draft_file, $external_links, $examination_date, $examination_time, $examination_mode, $room_or_link, $html_minutes_file, $repository_link);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Υποβολή Πρόχειρου Κειμένου</title>
</head>
<body>

<h2>Υποβολή Πρόχειρου Κειμένου και Πληροφοριών Παρουσίασης</h2>

<!-- Φόρμα υποβολής με μέθοδο POST και υποστήριξη ανέβασμα αρχείων -->
<form action="" method="POST" enctype="multipart/form-data">
    <label>Ανέβασε Πρόχειρο Αρχείο:</label><br>
    <input type="file" name="draft_file"><br><br>

    <label>Εξωτερικοί Σύνδεσμοι (π.χ. Google Drive, YouTube):</label><br>
    <!-- textarea με προστασία HTML χαρακτήρων για αποφυγή XSS -->
    <textarea name="external_links" rows="4" cols="50"><?php echo htmlspecialchars($external_links); ?></textarea><br><br>

    <label>Ημερομηνία Εξέτασης:</label><br>
    <input type="date" name="examination_date" value="<?php echo htmlspecialchars($examination_date); ?>"><br><br>

    <label>Ώρα Εξέτασης:</label><br>
    <input type="time" name="examination_time" value="<?php echo htmlspecialchars($examination_time); ?>"><br><br>

    <label>Τρόπος Εξέτασης:</label><br>
    <select name="examination_mode">
        <!-- Επιλογή που επισημαίνεται ως επιλεγμένη αν ταιριάζει με την τιμή από βάση -->
        <option value="Δια ζώσης" <?php if ($examination_mode == "Δια ζώσης") echo "selected"; ?>>Δια ζώσης</option>
        <option value="Διαδικτυακά" <?php if ($examination_mode == "Διαδικτυακά") echo "selected"; ?>>Διαδικτυακά</option>
    </select><br><br>

    <label>Αίθουσα ή Σύνδεσμος Παρουσίασης:</label><br>
    <input type="text" name="room_or_link" value="<?php echo htmlspecialchars($room_or_link); ?>"><br><br>

    <label>Σύνδεσμος προς τη Νημερτή (αποθετήριο):</label><br>
    <input type="url" name="repository_link" value="<?php echo htmlspecialchars($repository_link); ?>"><br><br>

    <button type="submit">Αποθήκευση</button>
</form>

<!-- Εμφάνιση πρακτικού εξέτασης αν υπάρχει -->
<?php if ($html_minutes_file): ?>
    <hr>
    <h3>Πρακτικό Εξέτασης:</h3>
    <div>
        <?php echo $html_minutes_file; ?>
    </div>
<?php endif; ?>

<!-- Εμφάνιση συνδέσμου αποθετηρίου αν έχει δοθεί -->
<?php if (!empty($repository_link)): ?>
    <p><strong>Σύνδεσμος Νημερτής:</strong> <a href="<?php echo htmlspecialchars($repository_link); ?>" target="_blank"><?php echo htmlspecialchars($repository_link); ?></a></p>
<?php endif; ?>

<!-- Σύνδεσμος επιστροφής -->
<p><a href="manage_thesis.php">⟵ Επιστροφή</a></p>

</body>
</html>
