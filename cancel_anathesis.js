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
        loadTheses(searchInput.value.trim());
    });

    // Φόρτωση δεδομένων μέσω AJAX
    function loadTheses(search = '') {        
        fetch(`get_theses.php?search=${encodeURIComponent(search)}&status=Υπό Ανάθεση,Ενεργή`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                showTheses(data);
            })
            .catch(error => {
                showMessage('error', `Σφάλμα φόρτωσης δεδομένων: ${error.message}`);
                console.error('Σφάλμα:', error);
            });
    }

    // Εμφάνιση δεδομένων στον πίνακα
    function showTheses(theses) {
        thesesBody.innerHTML = '';
        
        if (!theses || theses.length === 0) {
            thesesBody.innerHTML = '<tr><td colspan="8">Δεν βρέθηκαν πτυχιακές εργασίες υπό ανάθεση ή ενεργές</td></tr>';
            return;
        }

        theses.forEach(thesis => {
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td>${escapeHtml(thesis.title)}</td>
                <td>${escapeHtml(truncate(thesis.description, 100))}</td>
                <td>${escapeHtml(thesis.status)}</td>
                <td>${thesis.student_name ? escapeHtml(thesis.student_name) : '-'}</td>
                <td>
                    ${thesis.pdf_file ? `<a href="${escapeHtml(thesis.pdf_file)}" target="_blank" class="btn btn-view">Προβολή</a>` : '-'}
                </td>
                <td>${formatDate(thesis.created_at)}</td>
                <td>${formatDate(thesis.updated_at)}</td>
                <td class="actions">
                    ${thesis.prof_role === 'Επιβλέπων' ? 
                        `<a href="edit_thesis.php?id=${thesis.thesis_id}" class="btn btn-edit">Επεξεργασία</a>
                         <button class="btn btn-cancel" data-id="${thesis.thesis_id}" data-status="${thesis.status}">Ακύρωση Ανάθεσης</button>` : 
                        '<span class="no-actions">-</span>'}
                </td>
            `;
            
            thesesBody.appendChild(row);
        });

        // Προσθήκη event listeners για ακύρωση ανάθεσης
        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', function() {
                const thesisId = this.getAttribute('data-id');
                const status = this.getAttribute('data-status');
                cancelAction(thesisId, status);
            });
        });
    }

    // Επεξεργασία ακύρωσης ανάλογα με το status
    function cancelAction(thesisId, status) {
        if (status === 'Υπό Ανάθεση') {
            cancelPendingAssignment(thesisId);
        } else if (status === 'Ενεργή') {
            cancelEnergiThesis(thesisId);
        }
    }

    // Ακύρωση ανάθεσης (για "Υπό Ανάθεση")
    function cancelAction(thesisId, status, studentAM) {
        if (!confirm(`Είστε σίγουρος ότι θέλετε να ακυρώσετε αυτή την ανάθεση;`)) {
            return;
        }

        const cancelBtn = document.querySelector(`.btn-cancel[data-id="${thesisId}"]`);
        const originalText = cancelBtn.textContent;
        cancelBtn.textContent = 'Γίνεται ακύρωση...';
        cancelBtn.disabled = true;

        fetch('cancel_anathesis.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                thesis_id: thesisId,
                am: studentAM || null
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Σφάλμα δικτύου');
                });
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message);
            }
            showMessage('success', data.message);
            setTimeout(() => {
                loadTheses(searchInput.value.trim());
            }, 1000);
        })
        .catch(error => {
            showMessage('error', error.message);
            cancelBtn.textContent = originalText;
            cancelBtn.disabled = false;
            console.error('Σφάλμα:', error);
        });
    }

    // Ακύρωση ανάθεσης για εργασία "Ενεργή"
    function cancelEnergiThesis(thesisId) {
        if (!confirm('Είστε σίγουρος ότι θέλετε να ακυρώσετε αυτή την ενεργή διπλωματική; Θα καταγραφεί ως ακυρωμένη από Διδάσκοντα.')) {
            return;
        }

        showMessage('info', 'Γίνεται ακύρωση διπλωματικής...', 2000);

        fetch('cancel_anathesis.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                thesis_id: thesisId,
                action: 'cancel_active'
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
            loadTheses(searchInput.value.trim());
        })
        .catch(error => {
            showMessage('error', error.message);
            console.error('Σφάλμα:', error);
        });
    }

    // Βοηθητικές συναρτήσεις (μορφοποίηση ημερομηνίας, περικοπή κειμένου, εμφάνιση μηνυμάτων, escapeHtml)
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return isNaN(date) ? '-' : date.toLocaleDateString('el-GR');
    }

    function truncate(text, length) {
        return text && text.length > length ? text.substring(0, length) + '...' : text;
    }

    function showMessage(type, text, timeout = 5000) {
        responseMessage.innerHTML = `<div class="alert alert-${type}">${escapeHtml(text)}</div>`;
        if (timeout) {
            setTimeout(() => {
                responseMessage.innerHTML = '';
            }, timeout);
        }
    }

    // Βοηθητική συνάρτηση για αποφυγή XSS
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';

    }
});