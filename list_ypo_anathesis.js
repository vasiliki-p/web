document.addEventListener('DOMContentLoaded', function() {
    const thesesBody = document.getElementById('thesesBody');
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const responseMessage = document.getElementById('responseMessage');

    // Φόρτωση αρχικών δεδομένων πτυχιακών εργασιών
    loadTheses();

    // Αναζήτηση πτυχιακών εργασιών
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadTheses(searchInput.value);
    });

    // Φόρτωση πτυχιακών εργασιών μέσω AJAX
    function loadTheses(search = '') {
        
        fetch(`get_anathesis_theses.php?search=${encodeURIComponent(search)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Unknown error');
            }
            showTheses(data.theses);
        })
        .catch(error => {
            console.error('Error loading theses:', error);
            showMessage('error', `Σφάλμα φόρτωσης: ${error.message}`);
        })
      
    }

    // Εμφάνιση πτυχιακών εργασιών στον πίνακα
    function showTheses(theses) {
        thesesBody.innerHTML = '';
        
        if (theses.length === 0) {
            thesesBody.innerHTML = `
                <tr>
                    <td colspan="7">Δεν βρέθηκαν πτυχιακές εργασίες υπό ανάθεση</td>
                </tr>
            `;
            return;
        }
        
        theses.forEach(thesis => {
            const row = document.createElement('tr');
            row.classList.add('clickable-row');
            row.dataset.thesisId = thesis.thesis_id;
            
            row.innerHTML = `
                <td>${escapeHtml(thesis.title)}</td>
                <td>${escapeHtml(truncateText(thesis.description, 100))}</td>
                <td>${thesis.student_name ? escapeHtml(thesis.student_name + ' ' + thesis.student_lastname) : '-'}</td>
                <td>${thesis.AM ? escapeHtml(thesis.AM) : '-'}</td>
                <td>
                    ${thesis.pdf_file ? `<a href="${escapeHtml(thesis.pdf_file)}" target="_blank" class="btn btn-view">Προβολή</a>` : '-'}
                </td>
                <td>${formatDate(thesis.created_at)}</td>
                <td class="actions-cell">
                    ${thesis.AM ? 
                        `<button class="btn-cancel" data-thesis-id="${thesis.thesis_id}" data-am="${thesis.AM}">
                            Ακύρωση
                        </button>` : 
                        '-'}
                </td>
            `;
            
            thesesBody.appendChild(row);
        });

        // Προσθήκη event listener για κλικ σε γραμμή (εκτός από κουμπί ακύρωσης ή προβολής PDF)
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Μην πλοηγηθεί αν πατηθεί το κουμπί ακύρωσης ή το PDF
                if (e.target.classList.contains('btn-cancel') || e.target.classList.contains('btn-view')) {
                    return;
                }
                const thesisId = this.dataset.thesisId;
                window.location.href = `list_invited_members.php?thesis_id=${thesisId}`;
            });
        });

        // Προσθήκη event listener για ακύρωση ανάθεσης
        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const thesisId = this.dataset.thesisId;
                const am = this.dataset.am;
                
                if (confirm('Είστε σίγουρος ότι θέλετε να ακυρώσετε την ανάθεση αυτής της διπλωματικής; Όλες οι προσκλήσεις και οι αναθέσεις σε τριμελείς θα διαγραφούν αυτόματα.')) {
                    cancelAssignment(thesisId, am);
                }
            });
        });
    }

    // Ακύρωση ανάθεσης πτυχιακής εργασίας
    function cancelAssignment(thesisId, am) {
        
        fetch('cancel_assignment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                thesis_id: thesisId,
                am: am
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Σφάλμα κατά την ακύρωση');
            }
            showMessage('success', data.message);
            loadTheses(searchInput.value);
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', `Σφάλμα: ${error.message}`);
        })
    }

    // Βοηθητικές συναρτήσεις
    // Ασφαλής εμφάνιση HTML (αποφυγή XSS)
    function escapeHtml(unsafe) {
        return unsafe?.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
    }

    // Περικοπή κειμένου για εμφάνιση
    function truncateText(text, maxLength) {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    // Μορφοποίηση ημερομηνίας
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('el-GR', options);
    }

    // Εμφάνιση μηνυμάτων επιτυχίας ή σφάλματος
    function showMessage(type, text) {
        responseMessage.innerHTML = `
            <div class="alert alert-${type}">
                ${text}
                <span class="close-btn">&times;</span>
            </div>
        `;
        
        responseMessage.querySelector('.close-btn').addEventListener('click', () => {
            responseMessage.innerHTML = '';
        });
        
        setTimeout(() => {
            if (responseMessage.innerHTML) responseMessage.innerHTML = '';
        }, 5000);
    }
});