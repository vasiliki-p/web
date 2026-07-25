document.addEventListener('DOMContentLoaded', function() {
    // Επιλογή στοιχείων DOM
    const notesTextarea = document.getElementById('notesTextarea');
    const charCount = document.getElementById('charCount');
    const notesContainer = document.getElementById('notesContainer');

    
    // Αρχική φόρτωση σημειώσεων
    loadNotes();

    // Μετρητής χαρακτήρων για το textarea
    function updateCharCount() {
        const currentLength = notesTextarea.value.length;
        charCount.textContent = currentLength;
        
        if (currentLength >= 300) {
            charCount.style.color = 'red';
        } else {
            charCount.style.color = '';
        }
    }

    // Αρχικοποίηση μετρητή χαρακτήρων
    updateCharCount();
    
    // Event listener για ενημέρωση μετρητή κατά την πληκτρολόγηση
    notesTextarea.addEventListener('input', updateCharCount);

    // Φόρτωση σημειώσεων μέσω AJAX
    function loadNotes() {
        fetch(`get_notes.php?thesis_id=${thesisId}&prof_id=${professorId}`)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.error || 'Network error');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Invalid response');
                }
                showNotes(data.data);
            })
            .catch(error => {
                // Εμφάνιση μηνύματος σφάλματος και δυνατότητα επαναφόρτωσης
                console.error('Error loading notes:', error);
                notesContainer.innerHTML = `
                    <p class="error">
                        Error loading notes: ${error.message}
                        <button onclick="loadNotes()">Retry</button>
                    </p>
                `;
            });
    }

    // Εμφάνιση σημειώσεων στην οθόνη
    function showNotes(notes) {
        if (!notes || notes.length === 0) {
            notesContainer.innerHTML = '<p>Δεν υπάρχουν σημειώσεις ακόμα.</p>';
            return;
        }

        let html = '';
        notes.forEach(note => {
            const date = new Date(note.created_at).toLocaleString('el-GR');
            
            html += `
                <div class="note">
                    <div class="note-meta">
                        <span class="date">${date}</span>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_note" value="${note.note_id}">
                            <button type="submit" onclick="return confirm('Είστε σίγουρος ότι θέλετε να διαγράψετε αυτή τη σημείωση;')" 
                                    class="delete-note-btn">
                                [Διαγραφή]
                            </button>
                        </form>
                    </div>
                    <div class="note-content">${escapeHtml(note.notes)}</div>
                </div>
            `;
        });

        notesContainer.innerHTML = html;
    }

    // Προστασία από XSS
    function escapeHtml(unsafe) {
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/>/g, "&gt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
    }

});