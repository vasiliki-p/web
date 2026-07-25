// Εκτελείται όταν φορτωθεί πλήρως η σελίδα
document.addEventListener('DOMContentLoaded', () => {

    // Παίρνουμε το στοιχείο της φόρμας επεξεργασίας προφίλ
    const form = document.getElementById('editProfileForm');

    // Όταν γίνει υποβολή της φόρμας
    form.addEventListener('submit', (e) => {
        // Παίρνουμε τις τιμές από τα πεδία της φόρμας
        const email = form.email.value.trim();
        const mobilePhone = form.mobile_phone.value.trim();
        const landlinePhone = form.landline_phone.value.trim();

        // Έλεγχος εγκυρότητας email
        if (!validateEmail(email)) {
            e.preventDefault(); // Ακυρώνει την αποστολή της φόρμας
            alert('Παρακαλώ εισάγετε ένα έγκυρο email.');
            form.email.focus(); // Εστιάζει στο πεδίο email
            return;
        }

        // Έλεγχος εγκυρότητας κινητού (πρέπει να είναι μόνο αριθμοί)
        if (!validatePhone(mobilePhone)) {
            e.preventDefault();
            alert('Παρακαλώ εισάγετε έγκυρο κινητό τηλέφωνο (μόνο αριθμοί).');
            form.mobile_phone.focus();
            return;
        }

        // Αν έχει δοθεί σταθερό τηλέφωνο, το ελέγχουμε και αυτό
        if (landlinePhone && !validatePhone(landlinePhone)) {
            e.preventDefault();
            alert('Παρακαλώ εισάγετε έγκυρο σταθερό τηλέφωνο (μόνο αριθμοί).');
            form.landline_phone.focus();
            return;
        }
    });

    // Συνάρτηση που ελέγχει την εγκυρότητα του email με χρήση κανονικής έκφρασης
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Συνάρτηση που ελέγχει αν ένα τηλέφωνο περιέχει μόνο αριθμούς
    function validatePhone(phone) {
        const re = /^\d+$/;
        return re.test(phone);
    }
});
