document.addEventListener('DOMContentLoaded', function() {
    const thesisId = document.getElementById('thesis_id').value;
    const form = document.getElementById('thesisForm');
    const responseMessage = document.getElementById('responseMessage');
    const fileInfo = document.getElementById('fileInfo');
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    // Φόρτωση αρχικών δεδομένων πτυχιακής
    loadThesisData();

    // Φόρτωση δεδομένων πτυχιακής και επιλογών κατάστασης
    function loadThesisData() {
        // Φόρτωση δεδομένων πτυχιακής
        fetch(`get_thesis.php?id=${thesisId}`)
            .then(response => {
                console.log('Response status:', response.status); // Προσθήκη αυτής της γραμμής
                if (!response.ok) throw new Error('Αποτυχία φόρτωσης πτυχιακής');
                return response.json();
            })
            .then(thesis => {
                 console.log('Loaded thesis:', thesis);
                // Φόρτωση επιλογών κατάστασης
                return fetch('status_options.php')
                    .then(statusRes => {
                        if (!statusRes.ok) throw new Error('Αποτυχία φόρτωσης επιλογών κατάστασης');
                        return statusRes.json();
                    })
                    .then(statusData => {
                        const statusOptions = statusData.options || [];
                        
                        // Σύνοψη δεδομένων
                        document.getElementById('title').value = thesis.title || '';
                        document.getElementById('description').value = thesis.description || '';
                        
                        const statusSelect = document.getElementById('status');
                        statusSelect.innerHTML = '<option value="">-- Επιλέξτε Κατάσταση --</option>';
                        
                        statusOptions.forEach(option => {
                            const opt = document.createElement('option');
                            opt.value = option;
                            opt.textContent = option;
                            if (option === thesis.status) opt.selected = true;
                            statusSelect.appendChild(opt);
                        });

                        updateFileInfo(thesis.pdf_file);
                    });
            })
            .catch(error => {
                console.error('Σφάλμα φόρτωσης:', error);
                showMessage('error', `Σφάλμα φόρτωσης: ${error.message}`);
                
                // Fallback επιλογές
                const fallbackOptions = ['Υπό Ανάθεση', 'Ενεργή', 'Περατωμένη', 'Ακυρωμένη', 'Υπό Εξέταση'];
                const statusSelect = document.getElementById('status');
                statusSelect.innerHTML = fallbackOptions.map(opt => 
                    `<option value="${opt}">${opt}</option>`
                ).join('');
            });
    }

    // Ενημέρωση πληροφοριών αρχείου PDF στην οθόνη
    function updateFileInfo(pdfFile) {
        fileInfo.innerHTML = pdfFile ? `
            <div class="current-file">
                <strong>Υπάρχον Αρχείο:</strong> 
                <a href="uploads/${pdfFile}" target="_blank" class="file-link">Προβολή PDF</a>
                <button type="button" class="btn btn-danger" id="deleteFileBtn">Διαγραφή Αρχείου</button>
            </div>
        ` : '<div class="no-file">Δεν υπάρχει ανεβασμένο αρχείο</div>';

        if (pdfFile) {
            document.getElementById('deleteFileBtn').addEventListener('click', confirmDelete);
        }
    }

    // Επιβεβαίωση διαγραφής αρχείου PDF
    function confirmDelete() {
        if (confirm('Είστε σίγουρος ότι θέλετε να διαγράψετε αυτό το αρχείο PDF;')) {
            deletePdfFile();
        }
    }

    // Διαγραφή αρχείου PDF από τον server
    function deletePdfFile() {
        fetch(`delete_pdf.php?id=${thesisId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Αποτυχία διαγραφής');
            showMessage('success', data.message || 'Το αρχείο διαγράφηκε επιτυχώς');
            updateFileInfo(null);
        })
        .catch(error => {
            showMessage('error', `Σφάλμα διαγραφής: ${error.message}`);
            console.error(error);
        });
    }

    // Υποβολή της φόρμας επεξεργασίας πτυχιακής
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const fileInput = document.getElementById('pdf_file');
        
        // Έλεγχος αν το αρχείο PDF υπερβαίνει το μέγιστο μέγεθος (5MB)
        if (fileInput.files[0] && fileInput.files[0].size > MAX_FILE_SIZE) {
            showMessage('error', 'Το αρχείο υπερβαίνει το μέγιστο επιτρεπόμενο μέγεθος (5MB)');
            return;
        }

        // Αποστολή των δεδομένων της φόρμας στο backend για ενημέρωση
        fetch(`update_thesis.php?id=${thesisId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.error || 'Σφάλμα κατά την ενημέρωση'); });
            }
            return response.json();
        })
        .then(data => {
            showMessage('success', data.message || 'Οι αλλαγές αποθηκεύτηκαν επιτυχώς');
            if (data.thesis && data.thesis.pdf_file) {
                updateFileInfo(data.thesis.pdf_file.replace('uploads/', ''));
            }
        })
        .catch(error => {
            console.error('Submission error:', error);
            showMessage('error', error.message || 'Προέκυψε σφάλμα κατά την αποθήκευση');
        });
    });

    // Εμφάνιση μηνυμάτων επιτυχίας ή σφάλματος
    function showMessage(type, text) {
        responseMessage.innerHTML = `
            <div class="alert alert-${type}">
                ${text}
                <span class="close-btn">&times;</span>
            </div>
        `;
        
        // Κλείσιμο μηνύματος όταν πατηθεί το ×
        responseMessage.querySelector('.close-btn').addEventListener('click', () => {
            responseMessage.innerHTML = '';
        });
        
        // Αυτόματο κλείσιμο μετά από 5 δευτερόλεπτα
        setTimeout(() => {
            if (responseMessage.innerHTML) responseMessage.innerHTML = '';
        }, 5000);
    }
});