document.addEventListener('DOMContentLoaded', function() {
    // Περιμένει να φορτωθεί το DOM πριν εκτελέσει τον κώδικα
    
    // Φόρτωση των επιλογών status όταν φορτώνει η σελίδα
    loadStatusOptions();
    
    // Υποβολή της φόρμας δημιουργίας θέματος
    document.getElementById('thesisForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const responseMessage = document.getElementById('responseMessage');
        
        // Έλεγχος αν το αρχείο PDF υπερβαίνει το μέγιστο μέγεθος (5MB)
        const fileInput = document.getElementById('pdf_file');
        if (fileInput.files[0] && fileInput.files[0].size > 5 * 1024 * 1024) {
            responseMessage.innerHTML = '<div class="error">Το αρχείο υπερβαίνει το μέγιστο μέγεθος 5MB</div>';
            return;
        }
        
        // Αποστολή των δεδομένων της φόρμας στο backend
        fetch('create_theme.php', {
            method: 'POST',
            body: formData,// διότι το FormData χρεάζεται για τη σωστή αποστολή αρχείων pdf
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(data => {
            responseMessage.innerHTML = data;
            // Επαναφορά της φόρμας αν η υποβολή ήταν επιτυχής
            if (data.includes('success')) {
                this.reset();
            }
        })
        .catch(error => {
            responseMessage.innerHTML = `<div class="error">Σφάλμα κατά την υποβολή: ${error.message}</div>`;
        });
    });
});

// Φόρτωση των επιλογών status από το backend και προσθήκη τους στο dropdown select
function loadStatusOptions() {
    fetch('status_options.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const select = document.getElementById('status');
                data.options.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option;
                    opt.textContent = option;
                    select.appendChild(opt);
                });
            } else {
                console.error('Failed to load options:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading status options:', error);
        });
}