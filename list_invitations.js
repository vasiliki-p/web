document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('invitationsTableBody');
    const responseMessage = document.getElementById('responseMessage');

    // Load initial data
    loadInvitations();

    // Function to handle accepting an invitation
    function acceptInvitation(invitationId, thesisId, profId) {
        fetch(`accept.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                invitation_id: invitationId,
                thesis_id: thesisId,
                prof_id: profId
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
                throw new Error(data.error || 'Unknown error');
            }
            showMessage('success', 'Η πρόσκληση αποδέχθηκε επιτυχώς');
            loadInvitations();
        })
        .catch(error => {
            console.error('Error accepting invitation:', error);
            showMessage('error', `Σφάλμα: ${error.message}`);
        })
    }

    // Function to handle rejecting an invitation
    function rejectInvitation(invitationId, profId) {
        
        fetch(`reject.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                invitation_id: invitationId,
                prof_id: profId
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
                throw new Error(data.error || 'Unknown error');
            }
            showMessage('success', 'Η πρόσκληση απορρίφθηκε επιτυχώς');
            loadInvitations();
        })
        .catch(error => {
            console.error('Error rejecting invitation:', error);
            showMessage('error', `Σφάλμα: ${error.message}`);
        })
        
    }

    // Load invitations via AJAX
    function loadInvitations() {
        
        fetch('get_invitations.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Unknown error');
            }
            showInvitations(data.invitations);
        })
        .catch(error => {
            console.error('Error loading invitations:', error);
            showMessage('error', `Σφάλμα φόρτωσης: ${error.message}`);
        })
    }

    // Render invitations in the table
    function showInvitations(invitations) {
        tableBody.innerHTML = '';
        
        if (invitations.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8">Δεν έχετε προσκλήσεις για συμμετοχή σε τριμελείς επιτροπές</td>
                </tr>
            `;
            return;
        }
        
        invitations.forEach(invitation => {
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td>${escapeHtml(invitation.title)}</td>
                <td>${escapeHtml(truncateText(invitation.description, 100))}</td>
                <td>${escapeHtml(invitation.student_name)} ${escapeHtml(invitation.student_lastname)}</td>
                <td>${escapeHtml(invitation.AM)}</td>
                <td class="status-${(invitation.status || 'pending').toLowerCase()}">
                ${escapeHtml(
                    invitation.status === 'accepted' ? 'Αποδεκτή' : 
                    invitation.status === 'rejected' ? 'Απορριφθείσα' :  
                    'Εκκρεμής'
                )}
                </td>
                <td>${escapeHtml(invitation.supervisor_name)}</td>
                <td>${formatDate(invitation.created_at)}</td>
                <td class="actions">
                    ${invitation.status === 'pending' ? `
                        <button class="btn btn-accept" 
                            data-invitation-id="${invitation.invitation_id}"
                            data-thesis-id="${invitation.thesis_id}"
                            data-prof-id="${invitation.prof_id}">
                        Αποδοχή
                    </button>
                    <button class="btn btn-reject" 
                            data-invitation-id="${invitation.invitation_id}"
                            data-prof-id="${invitation.prof_id}">
                        Απόρριψη
                    </button>
                    ` : ''}
                  <a href="thesis_details.php?thesis_id=${invitation.thesis_id}" class="btn btn-view">Λεπτομέρειες</a>
                </td>
                
            `;
            
            tableBody.appendChild(row);
        });
    }


    // Προσθήκη στο τέλος του DOMContentLoaded listener:
tableBody.addEventListener('click', (e) => {
    const target = e.target;
    
    // Αν το click ήταν σε κουμπί "Αποδοχή"
    if (target.classList.contains('btn-accept')) {
        const invitationId = target.dataset.invitationId;
        const thesisId = target.dataset.thesisId;
        const profId = target.dataset.profId;
        acceptInvitation(invitationId, thesisId, profId);
    }
    
    // Αν το click ήταν σε κουμπί "Απόρριψη"
    if (target.classList.contains('btn-reject')) {
        const invitationId = target.dataset.invitationId;
        const profId = target.dataset.profId;
        rejectInvitation(invitationId, profId);
    }
});


    // Helper functions
    function escapeHtml(unsafe) {
        return unsafe?.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
    }

    function truncateText(text, maxLength) {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    function formatDate(dateString) {
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