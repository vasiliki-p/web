// Περιμένει να φορτωθεί το DOM πριν εκτελέσει τον κώδικα
document.addEventListener('DOMContentLoaded', () => {
    // Παίρνει το ΑΜ φοιτητή από τα query params της διεύθυνσης
    const urlParams = new URLSearchParams(window.location.search);
    const studentAM = urlParams.get('am');
    const studentInfo = document.getElementById('studentInfo');
    const thesesContainer = document.getElementById('thesesContainer');
    const responseMessage = document.getElementById('responseMessage');

    // Αν δεν υπάρχει ΑΜ φοιτητή στη διεύθυνση, επιστρέφει στη σελίδα αναζήτησης
    if (!studentAM) {
        window.location.href = 'search_students.php';
        return;
    }

    // Καλεί τη συνάρτηση για να φορτώσει τα στοιχεία του φοιτητή
    loadStudentInfo();

    // Φόρτωση στοιχείων φοιτητή από το backend
    function loadStudentInfo() {
        fetch(`get_student.php?am=${studentAM}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Σφάλμα φόρτωσης στοιχείων φοιτητή');
                }
                return response.json();
            })
            .then(student => {
                showStudentInfo(student);
                // Αν ο φοιτητής δεν έχει ήδη ανάθεση, φόρτωσε διαθέσιμες διπλωματικές
                if (!student.has_assignment) {
                    loadDiathesimesTheses();
                } else {
                    // Αν έχει ήδη ανάθεση, εμφάνισε σχετικό μήνυμα
                    thesesContainer.innerHTML = `
                        <div class="alert alert-info">
                            Ο φοιτητής έχει ήδη ανάθεση διπλωματικής.
                            <a href=".php?am=${studentAM}">Προβολή λεπτομερειών</a>
                        </div>
                        <div class="action-links">
                            <a href="search_students.php" class="btn">Επιστροφή</a>
                        </div>
                    `;
                }
            })
            .catch(error => {
                // Εμφάνιση μηνύματος σφάλματος αν αποτύχει η φόρτωση
                showMessage('error', error.message);
            });
    }

    // Εμφάνιση στοιχείων φοιτητή στην οθόνη
    function showStudentInfo(student) {
        studentInfo.innerHTML = `
            <h2>Φοιτητής: ${escapeHtml(student.name)} ${escapeHtml(student.lastname)} (ΑΜ: ${escapeHtml(student.AM)})</h2>
        `;
    }

    // Φόρτωση διαθέσιμων διπλωματικών για ανάθεση
    function loadDiathesimesTheses() {
        fetch('get_diathesimes_theses.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Σφάλμα φόρτωσης διπλωματικών');
                }
                return response.json();
            })
            .then(theses => {
                showTheses(theses);
            })
            .catch(error => {
                // Εμφάνιση μηνύματος σφάλματος αν αποτύχει η φόρτωση
                showMessage('error', error.message);
            });
    }

    // Εμφάνιση διαθέσιμων διπλωματικών και φόρμας ανάθεσης
    function showTheses(theses) {
        // Αν δεν υπάρχουν διαθέσιμες διπλωματικές, εμφάνισε σχετικό μήνυμα
        if (theses.length === 0) {
            thesesContainer.innerHTML = `
                <p>Δεν υπάρχουν διαθέσιμες διπλωματικές για ανάθεση.</p>
                <p><a href="search_students.php">Επιστροφή</a></p>
            `;
            return;
        }

        // Εμφάνιση πίνακα με τις διαθέσιμες διπλωματικές και φόρμα επιλογής
        thesesContainer.innerHTML = `
            <form id="assignmentForm">
                <table>
                    <thead>
                        <tr>
                            <th>Επιλογή</th>
                            <th>Τίτλος</th>
                            <th>Περιγραφή</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${theses.map(thesis => `
                            <tr>
                                <td><input type="radio" name="thesis_id" value="${thesis.thesis_id}" required></td>
                                <td>${escapeHtml(thesis.title)}</td>
                                <td>${escapeHtml(thesis.description)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <button type="submit">Ανάθεση</button>
                <a href="search_students.php" class="btn cancel">Ακύρωση</a>
            </form>
        `;

        // Προσθήκη event listener στη φόρμα ανάθεσης
        document.getElementById('assignmentForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await assignThesis();
        });
    }

    // Ανάθεση διπλωματικής στον φοιτητή
    function assignThesis() {
        const formData = new FormData(document.getElementById('assignmentForm'));
        const thesisId = formData.get('thesis_id');
        
        fetch('assign_thesis.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                student_am: studentAM,
                thesis_id: thesisId
            })
        })
        .then(response => {
            if (!response.ok) {
                // Αν η απάντηση δεν είναι OK, διαβάζουμε το JSON για το μήνυμα λάθους
                return response.json().then(errorData => {
                    throw new Error(errorData.error || 'Σφάλμα ανάθεσης');
                });
            }
            return response.json();
        })
        .then(result => {
            // Εμφάνιση μηνύματος επιτυχίας και επιστροφή στην αναζήτηση
            showMessage('success', result.message);
            setTimeout(() => {
                window.location.href = 'search_students.php';
            }, 1500);
        })
        .catch(error => {
            // Εμφάνιση μηνύματος σφάλματος αν αποτύχει η ανάθεση
            showMessage('error', error.message);
            console.error('Σφάλμα:', error);
        });
    }

    // Βοηθητική συνάρτηση για αποφυγή XSS (εισαγωγή html με ασφάλεια)
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

      // Εμφάνιση μηνυμάτων
    function showMessage(type, text) {
        responseMessage.innerHTML = `<div class="${type}">${text}</div>`;
        setTimeout(() => responseMessage.innerHTML = '', 3000);
    }

 
});