document.addEventListener('DOMContentLoaded', function () {
    const thesesBody = document.getElementById('thesesBody');
    const responseMessage = document.getElementById('responseMessage');

    // Φόρτωση αρχικών δεδομένων
    loadTheses();

function loadTheses() {

    // Κάνει AJAX αίτημα GET χωρίς παραμέτρους φίλτρου
    fetch('get_energes_ypo_eksetasi2.php')
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
       
       
    function showTheses(theses) {
        thesesBody.innerHTML = '';

        if (theses.length === 0) {
            thesesBody.innerHTML = `
                <tr>
                    <td colspan="3">Δεν βρέθηκαν πτυχιακές εργασίες</td>
                </tr>
            `;
            return;
        }

        theses.forEach(thesis => {
            const row = document.createElement('tr');
            row.classList.add('clickable-row');
            row.dataset.thesisId = thesis.thesis_id;

            let actionsHtml = '';

            if (thesis.status === 'Ενεργή') {
                actionsHtml += `<button class="btn-cancel" data-thesis-id="${thesis.thesis_id}">Ακύρωση</button>`;
            }

            // Εάν υπάρχουν και final_grade και repository_link → εμφάνιση κουμπιού "Περατωμένη"
                if (thesis.final_grade && thesis.repository_link && thesis.status == 'Υπό Εξέταση') {
                actionsHtml += `<button class="btn-complete" data-thesis-id="${thesis.thesis_id}">Σήμανση ως Περατωμένη</button>`;
            }

            row.innerHTML = `
                <td>
                    <a href="provoli_dipl_erg2.php?id=${encodeURIComponent(thesis.thesis_id)}" class="thesis-link">
                        ${escapeHtml(thesis.title || '')}
                    </a>
                </td>
                <td>${escapeHtml(thesis.status || '')}</td>
                <td>${actionsHtml || '-'}</td>
            `;

            thesesBody.appendChild(row);
        });

        // Event listeners
        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const thesisId = this.dataset.thesisId;
                const reason = prompt("Εισάγετε λόγο ακύρωσης:");//prompt εμφανίζει παράθυρο για εισαγωγή λόγου ακύρωσης
                if (reason) {
                    cancelAssignment(thesisId, reason);
                }
            });
        });

        document.querySelectorAll('.btn-complete').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const thesisId = this.dataset.thesisId;
                if (confirm("Είστε σίγουροι ότι θέλετε να ορίσετε την εργασία ως Περατωμένη;")) {
                    markAsCompleted(thesisId);
                }
            });
        });
    }

    function cancelAssignment(thesisId, reason) {

        fetch('cancel_thesis.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                thesis_id: thesisId,
                cancellation_reason: reason
            })
        })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Σφάλμα κατά την ακύρωση');
                showMessage('success', 'Η πτυχιακή ακυρώθηκε επιτυχώς');
                loadTheses();
            })
            .catch(error => {
                showMessage('error', `Σφάλμα: ${error.message}`);
            })
    }

    function markAsCompleted(thesisId) {

        fetch('complete_thesis.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ thesis_id: thesisId })
        })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Σφάλμα κατά την ενημέρωση');
                showMessage('success', data.message);
                loadTheses();
            })
            .catch(error => {
                showMessage('error', `Σφάλμα: ${error.message}`);
            })
             }

    function escapeHtml(unsafe) {
        if (!unsafe || typeof unsafe !== 'string') return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
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
            responseMessage.innerHTML = '';
        }, 5000);
    }
});