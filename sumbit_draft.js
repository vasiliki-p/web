// draft.js

// Περιμένουμε το DOM να φορτώσει πριν τρέξουμε τον κώδικα
document.addEventListener('DOMContentLoaded', () => {
    // Παίρνουμε τη φόρμα με το querySelector
    const form = document.querySelector('form');
  
    // Προσθέτουμε event listener για το submit της φόρμας
    form.addEventListener('submit', async (event) => {
      event.preventDefault(); // Αποτρέπουμε την default υποβολή για να κάνουμε AJAX
  
      // Δημιουργούμε ένα αντικείμενο FormData από τη φόρμα
      const formData = new FormData(form);
  
      try {
        // Στέλνουμε τα δεδομένα με fetch στη ίδια σελίδα (empty string σημαίνει current URL)
        const response = await fetch('', {
          method: 'POST',
          body: formData,
        });
  
        // Παίρνουμε το κείμενο της απόκρισης (μπορεί να είναι HTML ή απλό κείμενο)
        const resultText = await response.text();
  
        // Εμφανίζουμε το αποτέλεσμα σε alert (μπορείς να το βάλεις και σε div)
        alert('Απάντηση από τον server:\n' + resultText);
  
        // Αν θέλεις, εδώ μπορείς να κάνεις reset τη φόρμα ή άλλα πράγματα
        // form.reset();
  
      } catch (error) {
        // Αν συμβεί κάποιο σφάλμα στην αίτηση, το εμφανίζουμε
        alert('Σφάλμα κατά την αποστολή: ' + error.message);
      }
    });
  });
  