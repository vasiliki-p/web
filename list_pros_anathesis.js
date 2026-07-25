document.addEventListener('DOMContentLoaded', function() {
    const thesesBody = document.getElementById('thesesBody');
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const responseMessage = document.getElementById('responseMessage');

    // Φόρτωση αρχικών δεδομένων
    loadTheses();

    // Αναζήτηση
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadTheses(searchInput.value);
    });

    // Φόρτωση δεδομένων μέσω AJAX
    function loadTheses(search = '') {
       fetch(`get_diathesimes_theses.php?search=${encodeURIComponent(search)}`)
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { throw new Error(text) });
        }
        return response.json();
    })
    .then(data => {
        showTheses(data);
    })
    .catch(error => {
        showMessage('error', 'Σφάλμα: ' + error.message);
        console.error(error); // Δες το λεπτομερές σφάλμα στο console
    });
    }

    // Εμφάνιση δεδομένων στον πίνακα
    function showTheses(theses) {
        thesesBody.innerHTML = '';
        
        if (theses.length === 0) {
            thesesBody.innerHTML = '<tr><td colspan="7">Δεν βρέθηκαν πτυχιακές εργασίες</td></tr>';
            return;
        }

        theses.forEach(thesis => {
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td>${escapeHtml(thesis.title)}</td>
                <td>${escapeHtml(thesis.description.substring(0, 100))}${thesis.description.length > 100 ? '...' : ''}</td>
                <td>${escapeHtml(thesis.status)}</td>
                <td>
                    ${thesis.pdf_file ? `<a href="${escapeHtml(thesis.pdf_file)}" target="_blank" class="btn btn-view">Προβολή</a>` : '-'}
                </td>
                <td>${escapeHtml(thesis.created_at)}</td>
                <td>${escapeHtml(thesis.updated_at)}</td>
                <td class="actions">
                    <a href="edit_thesis.php?id=${thesis.thesis_id}" class="btn btn-edit_thesis">Επεξεργασία</a>
                    <button class="btn btn-delete" data-id="${thesis.thesis_id}">Διαγραφή</button>
                </td>
            `;
            
            thesesBody.appendChild(row);
        });

        // Προσθήκη event listeners για διαγραφή
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const thesisId = this.getAttribute('data-id');
                deleteThesis(thesisId);
            });
        });
    }

    // Διαγραφή πτυχιακής
    function deleteThesis(thesisId) {
    if (!confirm('Είστε σίγουρος ότι θέλετε να διαγράψετε αυτή την πτυχιακή;')) {
        return;
    }

    fetch(`delete_thesis.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${thesisId}`
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { throw new Error(text) });
        }
        return response.json();
    })
    .then(data => {
        showMessage('success', data.message);
        loadTheses(searchInput.value);
    })
    .catch(error => {
        showMessage('error', `Σφάλμα διαγραφής: ${error.message}`);
        console.error('Error:', error);
    });
}

    // Εμφάνιση μηνυμάτων
    function showMessage(type, text) {
        responseMessage.innerHTML = `<div class="${type}">${text}</div>`;
        setTimeout(() => responseMessage.innerHTML = '', 3000);
    }

    // Προστασία XSS
    function escapeHtml(unsafe) {
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';

    }
});
