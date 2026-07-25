// Όταν φορτωθεί ολόκληρη η σελίδα
document.addEventListener("DOMContentLoaded", () => {

    // 1. Βρίσκουμε όλα τα <p> της σελίδας
    const paragraphs = document.querySelectorAll('p');
    let descriptionParagraph = null;

    // 2. Εντοπίζουμε την παράγραφο που περιέχει την "Περιγραφή"
    paragraphs.forEach(p => {
        if (p.innerHTML.includes('<strong>Περιγραφή:</strong>')) {
            descriptionParagraph = p;
        }
    });

    // 3. Αν βρέθηκε η παράγραφος περιγραφής, προσθέτουμε κουμπί απόκρυψης/εμφάνισης
    if (descriptionParagraph) {
        const toggleBtn = document.createElement('button');
        toggleBtn.textContent = "Εμφάνιση/Απόκρυψη Περιγραφής";
        toggleBtn.style.marginBottom = '10px';
        toggleBtn.style.display = 'block';

        // Τοποθέτηση του κουμπιού πριν από την παράγραφο
        descriptionParagraph.parentNode.insertBefore(toggleBtn, descriptionParagraph);

        // 4. Όταν πατηθεί το κουμπί, εναλλάσσεται η εμφάνιση της περιγραφής
        toggleBtn.addEventListener('click', () => {
            if (descriptionParagraph.style.display === 'none') {
                descriptionParagraph.style.display = 'block';
            } else {
                descriptionParagraph.style.display = 'none';
            }
        });
    }

    // 5. Αναζήτηση της παραγράφου με την "Κατάσταση"
    let statusParagraph = null;
    paragraphs.forEach(p => {
        if (p.innerHTML.includes('<strong>Κατάσταση:</strong>')) {
            statusParagraph = p;
        }
    });

    // 6. Αν βρεθεί η παράγραφος κατάστασης, αλλάζουμε το χρώμα της ανάλογα με την τιμή
    if (statusParagraph) {
        const strongEl = statusParagraph.querySelector('strong');
        if (strongEl) {
            // Παίρνουμε το κείμενο μετά το <strong>
            let statusText = strongEl.nextSibling.textContent.trim().toLowerCase();

            // Αν η κατάσταση είναι "εγκεκριμένο", χρωματίζουμε πράσινα
            if (statusText === 'εγκεκριμένο') {
                statusParagraph.style.color = 'green';
                statusParagraph.style.fontWeight = 'bold';

            // Αν είναι "εκκρεμεί", χρωματίζουμε πορτοκαλί
            } else if (statusText === 'εκκρεμεί') {
                statusParagraph.style.color = 'orange';
                statusParagraph.style.fontWeight = 'bold';

            // Για οποιαδήποτε άλλη κατάσταση, δείχνουμε γκρι
            } else {
                statusParagraph.style.color = 'gray';
            }
        }
    }

});
