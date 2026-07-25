document.addEventListener('DOMContentLoaded', function() {
    const thesesBody = document.getElementById('thesesBody');
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const roleFilter = document.getElementById('roleFilter');
    const clearFilters = document.getElementById('clearFilters');
    const exportBtn = document.getElementById('exportBtn');
    const exportFormat = document.getElementById('exportFormat');
    const responseMessage = document.getElementById('responseMessage');

    // Φόρτωση αρχικών δεδομένων
    loadTheses();
    loadStatusOptions();

    // Αναζήτηση και φιλτράρισμα
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadTheses();
    });

    // Καθαρισμός φίλτρων
    clearFilters.addEventListener('click', function() {
        searchInput.value = '';
        statusFilter.value = '';
        roleFilter.value = '';
        loadTheses();
    });

    // Εξαγωγή δεδομένων
    exportBtn.addEventListener('click', function() {
        if (!exportFormat.value) return;
        
        const params = new URLSearchParams();
        params.append('search', searchInput.value);
        params.append('status', statusFilter.value);
        params.append('prof_role', roleFilter.value);
        params.append('format', exportFormat.value);
        
        window.location.href = `export_thesis.php?${params.toString()}`;
    });

    function loadTheses() {
    const params = new URLSearchParams();
    params.append('search', searchInput.value);
    params.append('status', statusFilter.value);
    params.append('prof_role', roleFilter.value);
    
    // Ειδικές περιπτώσεις ανακατεύθυνσης (μόνο για συγκεκριμένες καταστάσεις)
    if (statusFilter.value === 'Υπό Ανάθεση' &&!roleFilter.value) {
        window.location.href = 'list_ypo_anathesis.php';
        return;
    }
    else if (statusFilter.value === 'Ενεργή' && !roleFilter.value) {
        window.location.href = 'list_energes.php';
        return;
    }
    else if (statusFilter.value === 'Υπό Εξέταση') {
        // Ομαδοποίηση όλων των περιπτώσεων Υπό Εξέταση
        let redirectPage = 'list_ypo_exetasi.php'; // Βασική σελίδα
        window.location.href = redirectPage;
        return;
    }

    // Για όλες τις άλλες περιπτώσεις (συμπεριλαμβανομένης της "Διαθέσιμη"), φόρτωση μέσω AJAX
    fetch(`get_theses.php?${params.toString()}`)
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            showTheses(data);
        })
        .catch(error => {
            showMessage('error', 'Σφάλμα φόρτωσης δεδομένων: ' + error.message);
        });
}


    // Εμφάνιση δεδομένων στον πίνακα
    function showTheses(theses) {
        thesesBody.innerHTML = '';
        
        if (theses.length === 0) {
            thesesBody.innerHTML = '<tr><td colspan="8">Δεν βρέθηκαν πτυχιακές εργασίες</td></tr>';
            return;
        }

        theses.forEach(thesis => {
            const row = document.createElement('tr');
            row.className = 'clickable-row';
            row.addEventListener('click', () => {
                window.location.href = `thesis_details.php?thesis_id=${thesis.thesis_id}`;
            });
            
            row.innerHTML = `
                <td>${escapeHtml(thesis.title)}</td>
                <td>${escapeHtml(thesis.description.substring(0, 100))}${thesis.description.length > 100 ? '...' : ''}</td>
                <td>${escapeHtml(thesis.status)}</td>
                <td>
                    ${thesis.pdf_file ? `<a href="${escapeHtml(thesis.pdf_file)}" target="_blank" class="btn btn-view" onclick="event.stopPropagation()">Προβολή</a>` : '-'}
                </td>
                <td>${escapeHtml(thesis.prof_role)}</td>
                <td>${escapeHtml(thesis.created_at)}</td>
                <td>${escapeHtml(thesis.updated_at)}</td>
                <td class="actions">
                    <a href="edit_thesis.php?id=${thesis.thesis_id}" class="btn btn-edit_thesis" onclick="event.stopPropagation()">Επεξεργασία</a>
                    <button class="btn btn-delete" data-id="${thesis.thesis_id}" onclick="event.stopPropagation()">Διαγραφή</button>
                </td>
            `;
            
            thesesBody.appendChild(row);
        });

        // Προσθήκη event listeners για διαγραφή
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const thesisId = this.getAttribute('data-id');
                deleteThesis(thesisId);
            });
        });
    }


    function loadStatusOptions() {
    fetch('status_options.php')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const select = document.getElementById('statusFilter'); // Αλλαγή εδώ
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

    // Διαγραφή πτυχιακής μέσω AJAX
   function deleteThesis(thesisId) {
    if (!confirm('Είστε σίγουρος ότι θέλετε να διαγράψετε αυτή την πτυχιακή;')) return;

    fetch('delete_thesis.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: thesisId })
    })
    .then(response => {
        if (!response.ok) {
            // Αν η απάντηση δεν είναι OK, διαβάζουμε το JSON για το μήνυμα λάθους
            return response.json().then(errorData => {
                throw new Error(errorData.message || 'Σφάλμα διαγραφής');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage('success', data.message);
            loadTheses();
        } else {
            showMessage('error', data.message);
        }
    })
    .catch(error => {
        showMessage('error', error.message);
        console.error('Σφάλμα:', error);
    });
}
    // Εμφάνιση μηνυμάτων
    function showMessage(type, text) {
        responseMessage.innerHTML = `<div class="${type}">${text}</div>`;
        setTimeout(() => responseMessage.innerHTML = '', 3000);
    }

    // Προστασία XSS
    function escapeHtml(unsafe) {
        return unsafe
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';

    }
});