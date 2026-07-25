document.addEventListener('DOMContentLoaded', function() {
    // Στοιχεία DOM
    const thesisData = document.getElementById('thesis-data');
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    const responseMessage = document.getElementById('responseMessage');
    
    // Παράμετροι αναζήτησης
    const urlParams = new URLSearchParams(window.location.search);
    const initialSearch = urlParams.get('search') || '';

    // Αρχική φόρτωση δεδομένων
    searchInput.value = initialSearch;
    loadThesisData(initialSearch);

    // Υποβολή φόρμας αναζήτησης
    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        loadThesisData(searchInput.value);
    });

    // Φόρτωση δεδομένων πτυχιακών
    function loadThesisData(search = '') {
        fetch(`get_thesis.php?search=${encodeURIComponent(search)}&prof_id=${encodeURIComponent($prof_id)}`)
            .then(handleResponse)
            .then(data => showThesisData(data))
            .catch(error => showError('Σφάλμα φόρτωσης δεδομένων', error));
    }

    // Εμφάνιση δεδομένων στον πίνακα
    function showThesisData(theses) {
        if (!Array.isArray(theses)) {
            showMessage('error', 'Λάθος μορφή δεδομένων');
            return;
        }

        thesisData.innerHTML = theses.length > 0 
            ? theses.map(thesis => createThesisRow(thesis)).join('')
            : '<tr><td colspan="7">Δεν βρέθηκαν πτυχιακές εργασίες</td></tr>';

        // Προσθήκη event listeners για κουμπιά
        addEventListeners();
    }

    // Δημιουργία γραμμής πίνακα για μια πτυχιακή
    function createThesisRow(thesis) {
        return `
            <tr>
                <td>${escapeHtml(thesis.title)}</td>
                <td>${escapeHtml(truncateText(thesis.description, 100))}</td>
                <td>${escapeHtml(thesis.status)}</td>
                <td>
                    ${thesis.pdf_file 
                        ? `<a href="${escapeHtml(thesis.pdf_file)}" target="_blank" class="btn btn-view">Προβολή</a>` 
                        : '-'}
                </td>
                <td>${formatDate(thesis.created_at)}</td>
                <td>${formatDate(thesis.updated_at)}</td>
                <td class="actions-cell">
                    <a href="edit_thesis.php?id=${thesis.thesis_id}" class="btn btn-edit">Επεξεργασία</a>
                    <button class="btn-review" data-thesis-id="${thesis.thesis_id}">Αλλαγή Κατάστασης</button>
                </td>
            </tr>
        `;
    }

    // Προσθήκη event listeners για αλληλεπίδραση
    function addEventListeners() {
        document.querySelectorAll('.btn-review').forEach(btn => {
            btn.addEventListener('click', handleStatusChange);
        });
    }

    // Χειρισμός αλλαγής κατάστασης
    function handleStatusChange(e) {
        e.preventDefault();
        const thesisId = e.target.dataset.thesisId;
        
        if (confirm("Είστε σίγουρος ότι θέλετε να αλλάξετε την κατάσταση σε Υπό Εξέταση;")) {
            changeStatus(thesisId);
        }
    }

    // Αλλαγή κατάστασης μέσω API
    function changeStatus(thesisId) {
        fetch('change_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                thesis_id: thesisId,
                status: 'review'
            })
        })
        .then(handleResponse)
        .then(data => {
            showMessage('success', data.message);
            loadThesisData(searchInput.value);
        })
        .catch(error => showError('Σφάλμα αλλαγής κατάστασης', error));
    }

    // Βοηθητικές συναρτήσεις
    function handleResponse(response) {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.message || 'Σφάλμα απάντησης') });
        }
        return response.json();
    }

    function showError(context, error) {
        console.error(`${context}:`, error);
        showMessage('error', `${context}: ${error.message}`);
    }

    function escapeHtml(text) {
        return text?.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
    }

    function truncateText(text, maxLength) {
        return text?.length > maxLength ? text.substring(0, maxLength) + '...' : text || '';
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('el-GR', options);
    }

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