document.addEventListener('DOMContentLoaded', function() {
    const thesesBody = document.getElementById('thesesBody');
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const responseMessage = document.getElementById('responseMessage');

    // Αρχική φόρτωση δεδομένων
    loadTheses();

    // Χειρισμός αναζήτησης
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadTheses(searchInput.value);
    });

    // Φόρτωση δεδομένων
    function loadTheses(search = '') {
        fetch(`get_energes_theses.php?search=${encodeURIComponent(search)}`)
            .then(response => {
                return response.json().then(data => ({ ok: response.ok, data }));
            })
            .then(({ ok, data }) => {
                if (!ok) {
                    showMessage('error', data.error || 'Σφάλμα φόρτωσης δεδομένων');
                    console.error('Σφάλμα:', data.error);
                    return;
                }
                showTheses(data.theses || []);
            })
            .catch(error => {
                showMessage('error', 'Σφάλμα φόρτωσης δεδομένων');
                console.error('Σφάλμα:', error);
            });
    }

    // Εμφάνιση δεδομένων στον πίνακα
    function showTheses(theses) {
        thesesBody.innerHTML = '';
        
        if (!theses || theses.length === 0) {
            thesesBody.innerHTML = '<tr><td colspan="8">Δεν βρέθηκαν ενεργές πτυχιακές εργασίες</td></tr>';
            return;
        }

        theses.forEach(thesis => {
            const row = document.createElement('tr');
            
            // Μορφοποίηση ημερομηνιών
            const createdAt = thesis.created_at ? new Date(thesis.created_at).toLocaleString('el-GR') : '-';
            const updatedAt = thesis.updated_at ? new Date(thesis.updated_at).toLocaleString('el-GR') : '-';
            
            // Περιορισμός περιγραφής
            const shortDesc = thesis.description ? 
                thesis.description.substring(0, 100) + (thesis.description.length > 100 ? '...' : '') : 
                '-';
            
            // Κουμπί PDF
            const pdfBtn = thesis.pdf_file ? 
                `<a href="uploads/${escapeHtml(thesis.pdf_file)}" class="btn btn-view" target="_blank">Προβολή</a>` : 
                '-';
            
            // Κουμπί σημειώσεων
            const notesBtn = `<a href="notes.php?id=${thesis.thesis_id}" class="btn btn-notes">
                ${thesis.professor_notes ? 'Επεξεργασία' : 'Προσθήκη'}
            </a>`;
            
            // Κουμπιά ενεργειών (ανάλογα με τον ρόλο)
            let actionBtns = '';
            if (thesis.status !== 'Ακυρωμένη' && thesis.status !== 'Περατωμένη') {
                actionBtns = `<a href="edit_thesis.php?id=${thesis.thesis_id}" class="btn btn-edit">Επεξεργασία</a>`;
                
                if (thesis.prof_role === 'Επιβλέπων') {
                    actionBtns += `
                        <button class="btn btn-cancel" data-id="${thesis.thesis_id}">Ακύρωση</button>
                        <button class="btn btn-examine" data-id="${thesis.thesis_id}">Υπό Εξέταση</button>
                    `;
                }
            } else {
                actionBtns = '<span>Δεν υπάρχουν ενέργειες</span>';
            }
            
            // Συντακτικό HTML γραμμής
            row.innerHTML = `
                <td>${escapeHtml(thesis.title)}</td>
                <td>${escapeHtml(shortDesc)}</td>
                <td>${escapeHtml(thesis.status)}</td>
                <td>${notesBtn}</td>
                <td>${pdfBtn}</td>
                <td>${createdAt}</td>
                <td>${updatedAt}</td>
                <td class="actions">${actionBtns}</td>
            `;
            
            thesesBody.appendChild(row);
        });

        // Προσθήκη event listeners
        addEventListeners();
    }

    // Χειρισμός ακύρωσης
    function cancelThesis(thesisId) {
        if (!confirm('Είστε σίγουρος ότι θέλετε να ακυρώσετε αυτή την ανάθεση;')) return;
        fetch('cancel_anathesis.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded' 
            },
            body: `thesis_id=${thesisId}`
        })
        .then(response => response.json().then(result => ({ ok: response.ok, result })))
        .then(({ ok, result }) => {
            if (!ok) {
                showMessage('error', result.message || 'Σφάλμα κατά την ακύρωση');
                console.error('Σφάλμα:', result.message);
                return;
            }
            if (result.success) {
                showMessage('success', result.message);
                loadTheses(searchInput.value);
            } else {
                showMessage('error', result.message || 'Σφάλμα κατά την ακύρωση');
            }
        })
        .catch(error => {
            showMessage('error', 'Σφάλμα κατά την ακύρωση');
            console.error('Σφάλμα:', error);
        });
    }

    // Χειρισμός "Υπό Εξέταση"
    function examineThesis(thesisId) {
        if (!confirm('Θέλετε να μεταφέρετε αυτή τη διπλωματική σε "Υπό Εξέταση";')) return;
        fetch('change_status.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded' 
            },
            body: `mark_review=${thesisId}`
        })
        .then(response => response.json().then(result => ({ ok: response.ok, result })))
        .then(({ ok, result }) => {
            if (!ok) {
                showMessage('error', result.error || result.message || 'Σφάλμα κατά την ενημέρωση');
                console.error('Σφάλμα:', result.error || result.message);
                return;
            }
            showMessage(result.success ? 'success' : 'error', result.message);
            loadTheses(searchInput.value);
        })
        .catch(error => {
            showMessage('error', 'Σφάλμα κατά την ενημέρωση');
            console.error('Σφάλμα:', error);
        });
    }

    // Προσθήκη όλων των event listeners
    function addEventListeners() {
        // Ακύρωση
        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', () => cancelThesis(btn.dataset.id));
        });
        
        // Υπό Εξέταση
        document.querySelectorAll('.btn-examine').forEach(btn => {
            btn.addEventListener('click', () => examineThesis(btn.dataset.id));
        });
    }

    // Εμφάνιση μηνύματος
    function showMessage(type, text) {
        responseMessage.innerHTML = `
            <div class="alert alert-${type}">
                ${escapeHtml(text)}
                <span class="close-btn">&times;</span>
            </div>
        `;
        
        // Κλείσιμο μηνύματος
        responseMessage.querySelector('.close-btn').addEventListener('click', () => {
            responseMessage.innerHTML = '';
        });
        
        // Αυτόματο κλείσιμο μετά από 5 δευτερόλεπτα
        setTimeout(() => {
            if (responseMessage.innerHTML) {
                responseMessage.innerHTML = '';
            }
        }, 5000);
    }

    // Προστασία XSS
    function escapeHtml(unsafe) {
        return unsafe?.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
    }
});