document.addEventListener('DOMContentLoaded', function() {
    const thesesBody = document.getElementById('thesesBody');
    const responseMessage = document.getElementById('responseMessage');

    // Load initial data
    loadTheses();


    // Load theses via AJAX
   function loadTheses() {
     // Κάνει AJAX αίτημα GET 
    fetch('get_energes_ypo_eksetasi.php')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.error || 'Άγνωστο σφάλμα');
            showTheses(data.theses); // Εμφανίζει όλες τις πτυχιακές
        })
        .catch(error => {
            console.error('Σφάλμα φόρτωσης πτυχιακών:', error);
            showMessage('error', `Σφάλμα φόρτωσης: ${error.message}`);
        })
       }
       

    // Render theses in the table
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
    <td>
        <a href="provoli_dipl_erg2.php?id=${encodeURIComponent(thesis.thesis_id)}" class="thesis-link">
            ${escapeHtml(thesis.title || '')}
        </a>
    </td>

    <td>${escapeHtml(thesis.status || '')}</td>

`;
            
            thesesBody.appendChild(row);
        });

    }

    // Helper functions
    function escapeHtml(unsafe) {
        if (!unsafe || typeof unsafe !== 'string') return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
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