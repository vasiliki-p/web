// Εκτελείται όταν φορτωθεί πλήρως το DOM
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form'); // Επιλογή της φόρμας
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="professors[]"]'); // Όλα τα checkbox των καθηγητών
  
    // Όταν ο χρήστης πατήσει "Υποβολή"
    form.addEventListener('submit', (e) => {
      // Υπολογίζει πόσα checkbox έχουν επιλεγεί
      const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
  
      // Αν είναι λιγότερα από 2, μπλοκάρει την υποβολή και εμφανίζει προειδοποίηση
      if (checkedCount < 2) {
        e.preventDefault(); // Ακυρώνει το submit
        alert('Πρέπει να επιλέξεις τουλάχιστον 2 μέλη για την επιτροπή.');
      }
    });
  });
  