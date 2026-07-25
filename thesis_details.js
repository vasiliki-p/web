document.addEventListener('DOMContentLoaded', function() {
    // Λήψη του thesis_id από το query string
    const urlParams = new URLSearchParams(window.location.search);
    const thesisId = urlParams.get('thesis_id');
    
    // Έλεγχος αν υπάρχει αναγνωριστικό διπλωματικής
    if (!thesisId) {
        showMessage('error','Λάθος αναγνωριστικό διπλωματικής');
        return;
    }

    // Φόρτωση στοιχείων διπλωματικής και επιτροπής
    loadThesisDetails(thesisId);
    loadCommittee(thesisId);
});

// Φόρτωση στοιχείων διπλωματικής από το backend
function loadThesisDetails(thesisId) {
    // Κλήση στο backend για λήψη στοιχείων διπλωματικής
    fetch(`get_thesis_details.php?thesis_id=${thesisId}`)
        .then(response => {
            // Έλεγχος αν η απάντηση είναι επιτυχής
            if (!response.ok) {
                // Αν υπάρχει σφάλμα, επιστρέφει το κείμενο του σφάλματος
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            // Μετατροπή της απάντησης σε JSON
            return response.json();
        })
        .then(thesis => {
            // Έλεγχος αν υπάρχει σφάλμα στα δεδομένα
            if (thesis.error) {
                throw new Error(thesis.error);
            }
            
            // Ενημέρωση τίτλου πτυχιακής
            document.getElementById('thesisTitle').textContent = thesis.title || 'Χωρίς τίτλο';
            
            // Ενημέρωση πληροφοριών φοιτητή
            updateStudentInfo(thesis);
            
            // Ενημέρωση κατάστασης διπλωματικής
            document.getElementById('thesisStatus').innerHTML = `
                <span class="info-label">Κατάσταση:</span>
                ${escapeHtml(thesis.status || 'Δεν έχει οριστεί')}
            `;

            // Ενημέρωση ημερομηνιών
            updateDates(thesis);
            
            // Ενημέρωση περιγραφής
            document.getElementById('thesisDescription').innerHTML = `
                <span class="info-label">Περιγραφή:</span>
                ${thesis.description ? escapeHtml(thesis.description).replace(/\n/g, '<br>') : 'Δεν υπάρχει περιγραφή'}
            `;
            
            // Ενημέρωση αρχείων PDF
            updatePdfFiles(thesis);

            // Ενημέρωση πρακτικού βαθμολόγησης
            updateGradingForm(thesis);

            // Ενημέρωση τελικής βαθμολογίας
            updateFinalGrade(thesis);
        })
        .catch(error => {
            // Εμφάνιση μηνύματος σφάλματος στην οθόνη
            showMessage('error',`Σφάλμα φόρτωσης: ${error.message}`);
            console.error(error);
        });
}

// Ενημέρωση πληροφοριών φοιτητή
function updateStudentInfo(thesis) {
    let studentInfoHtml = '<div><span class="info-label">Φοιτητής:</span> ';
    
    if (thesis.student_name && thesis.student_lastname) {
        studentInfoHtml += `${escapeHtml(thesis.student_name)} ${escapeHtml(thesis.student_lastname)}`;
    } else {
        studentInfoHtml += '<span class="no-info">Δεν έχει ανατεθεί ακόμα</span>';
    }
    studentInfoHtml += '</div>';
    
    if (thesis.AM) {
        studentInfoHtml += `<div><span class="info-label">Αριθμός Μητρώου:</span> ${escapeHtml(thesis.AM)}</div>`;
    }
    
    document.getElementById('studentInfo').innerHTML = studentInfoHtml;
}

// Ενημέρωση ημερομηνιών πτυχιακής
function updateDates(thesis) {
    let datesHtml = '';
    if (thesis.started_at) {
        datesHtml += `<div><span class="info-label">Ημερομηνία Έναρξης:</span> ${formatDate(thesis.started_at)}</div>`;
    }
    if (thesis.completed_at) {
        datesHtml += `<div><span class="info-label">Ημερομηνία Ολοκλήρωσης:</span> ${formatDate(thesis.completed_at)}</div>`;
    }
    document.getElementById('thesisDates').innerHTML = datesHtml || '<div class="no-info">Δεν υπάρχουν διαθέσιμες ημερομηνίες</div>';
}

// Ενημέρωση αρχείων PDF πτυχιακής
function updatePdfFiles(thesis) {
    const pdfContainer = document.getElementById('pdfContainer');
    // Έλεγχος αν υπάρχει διαθέσιμο αρχείο PDF
    if (thesis.pdf_file) {
        pdfContainer.innerHTML = `
            <h3>Προσωρινό Κείμενο</h3>
            <embed src="${escapeHtml(thesis.pdf_file)}" type="application/pdf" class="pdf-embed">
            <a href="${escapeHtml(thesis.pdf_file)}" download class="btn">Κατέβασμα PDF</a>
        `;
    } else {
        // Αν δεν υπάρχει αρχείο, εμφάνιση σχετικού μηνύματος
        pdfContainer.innerHTML = '<div class="no-info">Δεν υπάρχει διαθέσιμο κείμενο</div>';
    }
}

// Ενημέρωση πρακτικού βαθμολόγησης
function updateGradingForm(thesis) {
    // Βρίσκουμε το πλαίσιο για το πρακτικό βαθμολόγησης
    const gradingFormContainer = document.getElementById('gradingFormContainer');
    
    // Έλεγχος αν υπάρχει διαθέσιμο πρακτικό βαθμολόγησης (PDF)
    if (thesis.grading_form) {
        let gradingFormHtml = '<h3>Πρακτικό Βαθμολόγησης</h3>';
        
        // Προσθήκη πληροφοριών φοιτητή αν υπάρχουν
        if (thesis.student_name && thesis.student_lastname) {
            gradingFormHtml += `
                <div class="grading-form-info">
                    <div><span class="info-label">Φοιτητής:</span> ${escapeHtml(thesis.student_name)} ${escapeHtml(thesis.student_lastname)}</div>
                    ${thesis.AM ? `<div><span class="info-label">Αριθμός Μητρώου:</span> ${escapeHtml(thesis.AM)}</div>` : ''}
                </div>
            `;
        }
        
        // Ενσωμάτωση του PDF και σύνδεσμος για κατέβασμα
        gradingFormHtml += `
            <embed src="${escapeHtml(thesis.grading_form)}" type="application/pdf" class="pdf-embed">
            <a href="${escapeHtml(thesis.grading_form)}" download class="btn">Κατέβασμα PDF</a>
        `;
        
        // Ενημέρωση του container με το HTML
        gradingFormContainer.innerHTML = gradingFormHtml;
    } else {
        // Αν δεν υπάρχει πρακτικό, εμφάνιση σχετικού μηνύματος
        gradingFormContainer.innerHTML = '<div class="no-info">Δεν υπάρχει διαθέσιμο πρακτικό βαθμολόγησης</div>';
    }
}

// Ενημέρωση τελικής βαθμολογίας
function updateFinalGrade(thesis) {
    const gradeContainer = document.getElementById('thesisGrade');
    if (thesis.final_grade) {
        gradeContainer.innerHTML = `
            <div class="grade-display">
                <span class="info-label">Τελικός Βαθμός:</span>
                <span class="grade-value">${escapeHtml(thesis.final_grade)}</span>
            </div>
        `;
    } else {
        gradeContainer.innerHTML = '<div class="no-info">Δεν υπάρχει διαθέσιμος τελικός βαθμός</div>';
    }
}

// Φόρτωση και εμφάνιση επιτροπής
function loadCommittee(thesisId) {
    // Βρίσκουμε το στοιχείο της λίστας επιτροπής στο DOM
    const committeeList = document.getElementById('committeeList');
    if (!committeeList) return;

    // Κάνουμε αίτημα στο backend για να πάρουμε τα στοιχεία της διπλωματικής (και της επιτροπής)
    fetch(`get_thesis_details.php?thesis_id=${thesisId}`)
        .then(response => {
            // Έλεγχος αν η απάντηση είναι επιτυχής
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            // Μετατροπή της απάντησης σε JSON
            return response.json();
        })
        .then(thesis => {
            // Έλεγχος αν υπάρχει επιτροπή και αν έχει μέλη
            if (thesis.committee && thesis.committee.length > 0) {
                // Δημιουργία HTML για κάθε μέλος της επιτροπής
                committeeList.innerHTML = thesis.committee.map(member => `
                    <div class="committee-member">
                        <span class="role">${escapeHtml(member.prof_role)}:</span>
                        <span class="name">${escapeHtml(member.first_name)} ${escapeHtml(member.last_name)}</span>
                        ${member.department ? `<span class="department">(${escapeHtml(member.department)})</span>` : ''}
                        ${member.grade !== null ? `<span class="grade">Βαθμός: ${escapeHtml(member.grade)}</span>` : ''}
                    </div>
                `).join('');
            } else {
                // Αν δεν υπάρχει επιτροπή, εμφάνιση σχετικού μηνύματος
                committeeList.innerHTML = '<div class="no-info">Δεν έχει οριστεί επιτροπή</div>';
            }
        })
        .catch(error => {
            // Σε περίπτωση σφάλματος, εμφάνιση μηνύματος σφάλματος
            console.error('Σφάλμα φόρτωσης επιτροπής:', error);
            committeeList.innerHTML = `
                <div class="error">
                    Σφάλμα φόρτωσης επιτροπής: ${escapeHtml(error.message)}
                </div>
            `;
        });
}

// Εμφάνιση μηνύματος σφάλματος στην οθόνη
 function showMessage(type, text) {
        responseMessage.innerHTML = `
            <div class="alert alert-${type}">
                ${escapeHtml(text)}
                <span class="close-btn">&times;</span>
            </div>
        `;
        
        responseMessage.querySelector('.close-btn').addEventListener('click', () => {
            responseMessage.innerHTML = '';
        });
        
        setTimeout(() => {
            if (responseMessage.innerHTML) {
                responseMessage.innerHTML = '';
            }
        }, 5000);
    }

// Βοηθητική συνάρτηση για αποφυγή XSS
function escapeHtml(unsafe) {
    return unsafe?.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;") || '';
}

// Μορφοποίηση ημερομηνίας σε ελληνική μορφή
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('el-GR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}