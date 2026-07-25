document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const responseMessage = document.getElementById('responseMessage');
    const resultsContainer = document.getElementById('resultsContainer'); 
    // Αρχική φόρτωση δεδομένων
    loadStudents();

    // Αναζήτηση κατά το submit
    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        loadStudents(searchInput.value);
    });

    function loadStudents(search = '') {
        fetch(`get_students.php?search=${encodeURIComponent(search)}`)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(error => {
                        throw new Error(error.message || 'Σφάλμα δικτύου');
                    });
                }
                return response.json();
            })
            .then(students => {
                showStudents(students);
            })
            .catch(error => {
                showMessage('error', error.message);
                console.error('Σφάλμα:', error);
            });
    }

    function showStudents(students) {
        if (students.length === 0) {
            resultsContainer.innerHTML = '<p>Δεν βρέθηκαν φοιτητές</p>';
            return;
        }

        resultsContainer.innerHTML = `
            <table>
                <thead>
                    <tr>
                        <th>ΑΜ</th>
                        <th>Όνομα</th>
                        <th>Επώνυμο</th>
                        <th>Κατάσταση</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    ${students.map(student => `
                    <tr class="student-row" data-am="${student.AM}">
                        <td>${escapeHtml(student.AM)}</td>
                        <td>${escapeHtml(student.name)}</td>
                        <td>${escapeHtml(student.lastname)}</td>
                        <td>${student.assigned_thesis_id ? 'Υπάρχει ανάθεση' : 'Χωρίς ανάθεση'}</td>
                        <td>
                            ${student.assigned_thesis_id ? 
                                `<button class="cancel-btn" data-am="${student.AM}" data-thesis-id="${student.assigned_thesis_id}">Ακύρωση</button>` : 
                                ''}
                        </td>
                    </tr>
                    `).join('')}
                </tbody>
            </table>
        `;

        // Προσθήκη event listener για κλικ σε γραμμή φοιτητή
        document.querySelectorAll('.student-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if (!e.target.classList.contains('cancel-btn')) {
                    const am = this.getAttribute('data-am');
                    window.location.href = `anathesis.php?am=${am}`;
                }
            });
        });

        // Προσθήκη event listener για κουμπιά ακύρωσης
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (confirm('Είστε σίγουρος ότι θέλετε να ακυρώσετε αυτή την ανάθεση;')) {
                    cancelAssignment(
                        this.getAttribute('data-am'),
                        this.getAttribute('data-thesis-id')
                    );
                }
            });
        });
    }

function cancelAssignment(am, thesisId) {
    if (!thesisId) {
        showMessage ('error','Η διπλωματική δεν έχει ID για ακύρωση');
        return;
    }

    const btn = document.querySelector(`.cancel-btn[data-am="${am}"]`);
    btn.textContent = 'Περιμένετε...';
    btn.disabled = true;

    fetch('cancel_anathesis.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ student_am: am, thesis_id: thesisId })
    })
    .then(response => {
        if (!response.ok) 
            return response.json().then(error => {
                throw new Error(error.message || 'Σφάλμα δικτύου');
            }); 
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage('success','Η ανάθεση ακυρώθηκε επιτυχώς');
            // Ενημέρωση μόνο της γραμμής
            const row = document.querySelector(`tr[data-am="${am}"]`);
            row.querySelector('td:nth-child(4)').textContent = 'Χωρίς ανάθεση';
            row.querySelector('td:nth-child(5)').innerHTML = '';
        } else {
            throw new Error(data.error || 'Unknown error');
        }
    })
    .catch(error => {
        showMessage('error', error.message);
        btn.textContent = 'Ακύρωση';
        btn.disabled = false;
    });
}
    // Βοηθητικές συναρτήσεις
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

       // Εμφάνιση μηνυμάτων
    function showMessage(type, text) {
        responseMessage.innerHTML = `<div class="${type}">${text}</div>`;
        setTimeout(() => responseMessage.innerHTML = '', 3000);
    }


});