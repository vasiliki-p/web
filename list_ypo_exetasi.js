document.addEventListener('DOMContentLoaded', function() {
    const thesesBody = document.getElementById('thesesBody');
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const responseMessage = document.getElementById('responseMessage');

    // Αρχική φόρτωση
    loadTheses();

    // Αναζήτηση
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadTheses(searchInput.value.trim());
    });

    // Φόρτωση δεδομένων
    function loadTheses(search = '') {
        fetch(`get_ypo_exetasi_theses.php?search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                showTheses(data.theses || []);
            })
            .catch(error => {
                console.error('Error loading theses:', error);
                thesesBody.innerHTML = '<tr><td colspan="11">Σφάλμα φόρτωσης δεδομένων</td></tr>';
            });
    }

    // Εμφάνιση δεδομένων
    function showTheses(theses) {
        thesesBody.innerHTML = '';
        
        if (theses.length === 0) {
            thesesBody.innerHTML = '<tr><td colspan="11">Δεν βρέθηκαν πτυχιακές</td></tr>';
            return;
        }

        theses.forEach(thesis => {
            const row = document.createElement('tr');
            
            // Μορφοποίηση δεδομένων
            const shortDesc = thesis.description ? 
                `${thesis.description.substring(0, 100)}${thesis.description.length > 100 ? '...' : ''}` : 
                '-';
                
            const createdAt = formatDate(thesis.created_at);
            const updatedAt = formatDate(thesis.updated_at);
            
            // Επιτροπή
            let committeeHtml = '-';
            if (thesis.committee?.length > 0) {
                committeeHtml = thesis.committee.map(member => `
                    <div class="committee-member">
                        <span>${escapeHtml(member.first_name)} ${escapeHtml(member.last_name)}</span>
                        <small>(${member.prof_role})</small>
                        ${member.grade !== null ? `<strong>${member.grade}</strong>` : '<em>-</em>'}
                    </div>
                `).join('');
            }
            
            // Σημειώσεις
            const notesHtml = thesis.professor_notes ? 
                `<div class="notes-preview">${escapeHtml(thesis.professor_notes)}</div>` : 
                '<em>Δεν υπάρχουν</em>';
            
            // Δημιουργία γραμμής
            row.innerHTML = `
                <td>${escapeHtml(thesis.title)}</td>
                <td>${escapeHtml(shortDesc)}</td>
                <td>${escapeHtml(thesis.status)}</td>
                <td>${committeeHtml}</td>
                <td>${notesHtml}</td>
                <td>${thesis.pdf_file ? `<a href="uploads/${thesis.pdf_file}" target="_blank">Προβολή</a>` : '-'}</td>
                <td>${thesis.draft_file ? `<a href="uploads/${thesis.draft_file}" target="_blank">Πρόχειρο</a>` : '-'}</td>
                <td>${thesis.final_grade || '-'}</td>
                <td>${createdAt}</td>
                <td>${updatedAt}</td>
                <td class="actions">
                    ${showActionButtons(thesis)}
                </td>
            `;
            
            thesesBody.appendChild(row);
        });

        addEventListeners();
    }

    // Κουμπιά ενεργειών
    function showActionButtons(thesis) {
        let buttons = '';
        
        if (thesis.status !== 'Ακυρωμένη' && thesis.status !== 'Περατωμένη') {
            buttons += `<a href="edit_thesis.php?id=${thesis.thesis_id}" class="btn btn-edit">Επεξεργασία</a>`;
            
            if (thesis.prof_role === 'Επιβλέπων') {
                buttons += `
                    <button class="btn btn-cancel" data-id="${thesis.thesis_id}">Ακύρωση</button>
                    <button class="btn btn-examine" data-id="${thesis.thesis_id}">Υπό Εξέταση</button>
                `;
            }
            
            if (thesis.prof_role) {
                buttons += `<button class="btn btn-grade" data-id="${thesis.thesis_id}">
                    ${thesis.prof_role === 'Επιβλέπων' ? 'Τελικός Βαθμός' : 'Βαθμός'}
                </button>`;
            }
        }
        
        return buttons;
    }

    // Event listeners
    function addEventListeners() {
        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', () => cancelThesis(btn.dataset.id));
        });
        
        document.querySelectorAll('.btn-examine').forEach(btn => {
            btn.addEventListener('click', () => examineThesis(btn.dataset.id));
        });
        
        document.querySelectorAll('.btn-grade').forEach(btn => {
            btn.addEventListener('click', () => {
                submitGrade(btn.dataset.id, btn.textContent.includes('Τελικός'));
            });
        });
    }


  function cancelThesis(thesisId) {
        if (!confirm('Είστε σίγουρος για την ακύρωση;')) return;
        
        fetch('cancel_anathesis.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ thesis_id: thesisId })
        })
        .then(response => response.json())
        .then(result => {
            showMessage(result.success ? 'success' : 'error', result.message);
            loadTheses(searchInput.value.trim());
        })
        .catch(error => {
            console.error('Error canceling thesis:', error);
            showMessage('error', 'Σφάλμα κατά την ακύρωση');
        });
    }
    
    function examineThesis(thesisId) {
        if (!confirm('Θέλετε να στείλετε την εργασία για εξέταση;')) return;
        
        fetch('change_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ thesis_id: thesisId, new_status: 'Υπό Εξέταση' })
        })
        .then(response => response.json())
        .then(result => {
            showMessage(result.success ? 'success' : 'error', result.message);
            loadTheses(searchInput.value.trim());
        })
        .catch(error => {
            console.error('Error examining thesis:', error);
            showMessage('error', 'Σφάλμα κατά την αλλαγή κατάστασης');
        });
    }
    
    function submitGrade(thesisId, isFinal) {
        const grade = prompt(`Εισάγετε βαθμό (0-10)${isFinal ? ' (Τελικός)' : ''}:`);
        if (grade === null || isNaN(grade)) return;
        
        fetch('submit_grade.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                thesis_id: thesisId, 
                grade: parseFloat(grade),
                is_final: isFinal 
            })
        })
        .then(response => response.json())
        .then(result => {
            showMessage(result.success ? 'success' : 'error', result.message);
            loadTheses(searchInput.value.trim());
        })
        .catch(error => {
            console.error('Error submitting grade:', error);
            showMessage('error', 'Σφάλμα κατά την υποβολή βαθμού');
        });
    }

// Βοηθητικές συναρτήσεις
    function formatDate(dateString) {
        return dateString ? new Date(dateString).toLocaleString('el-GR') : '-';
    }
    
    function escapeHtml(text) {
        return text?.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
    }
    
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
});
