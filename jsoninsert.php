<?php 
include 'restricted.php';
include 'connection.php';
include 'menu.html'; 
?>

<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8" />
  <title>Upload JSON και Αποθήκευση στη Βάση</title>
   <link rel="stylesheet" href="style.css">
</head>
<body>

  <h2>Ανέβασε JSON Αρχείο</h2>
  <input type="file" id="jsonFile" accept=".json" />
  <button onclick="sendToServer()">Αποθήκευση στη Βάση</button>

  <script>
    let parsedJSON = null;

    document.getElementById('jsonFile').addEventListener('change', function(event) {
      const file = event.target.files[0];
      if (!file) return alert("Επίλεξε ένα αρχείο JSON!");

      const reader = new FileReader();
      reader.onload = function(e) {
        try {
          parsedJSON = JSON.parse(e.target.result);
          alert("JSON αρχείο διαβασμένο επιτυχώς!");
        } catch (err) {
          parsedJSON = null;
          alert("Σφάλμα στο JSON αρχείο!");
        }
      };
      reader.readAsText(file);
    });

    function sendToServer() {
      if (!parsedJSON) return alert("Δεν έχεις φορτώσει σωστό JSON.");

      fetch('save_users.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(parsedJSON)
      })
      .then(response => response.text())
      .then(msg => alert("Απάντηση server: " + msg))
      .catch(err => alert("Σφάλμα δικτύου: " + err));
    }
  </script>

</body>
</html>