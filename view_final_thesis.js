// view_final_thesis.js

// Συνάρτηση για το άνοιγμα αρχείου σε νέο παράθυρο με απλό έλεγχο ύπαρξης URL
function openFile(url) {
    if (!url) {
        alert("Το αρχείο δεν είναι διαθέσιμο.");  // Ενημέρωση αν το αρχείο λείπει
        return;
    }
    window.open(url, '_blank');  // Άνοιγμα νέου παραθύρου/καρτέλας
}

// Περιμένουμε να φορτώσει όλο το DOM
document.addEventListener('DOMContentLoaded', function() {
    // Παίρνουμε το link για το πρακτικό εξέτασης
    const examLink = document.getElementById('examLink');
    if (examLink) {
        // Αποτρέπουμε το default κλικ και καλούμε την openFile
        examLink.addEventListener('click', function(event) {
            event.preventDefault();
            const url = examLink.getAttribute('href');
            openFile(url);
        });
    }

    // Παρόμοια για την τελική αναφορά, αν υπάρχει
    const finalReportLink = document.getElementById('finalReportLink');
    if (finalReportLink) {
        finalReportLink.addEventListener('click', function(event) {
            event.preventDefault();
            const url = finalReportLink.getAttribute('href');
            openFile(url);
        });
    }
});
