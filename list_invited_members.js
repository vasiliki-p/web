document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('invitedMembersTableBody');
    const responseMessage = document.getElementById('responseMessage');

    // Load initial data
    loadInvitedMembers();

    // Load invited members via AJAX
    function loadInvitedMembers() {
        
        fetch(`get_invited_members.php?thesis_id=${thesisId}`)
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
            showInvitedMembers(data.invitations);
        })
        .catch(error => {
            console.error('Error loading invited members:', error);
            showMessage('error', `Σφάλμα φόρτωσης: ${error.message}`);
        })
    }

    // Render invited members in the table
   function showInvitedMembers(invitations) {
    tableBody.innerHTML = '';
    
    if (invitations.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5">Δεν υπάρχουν προσκλήσεις για αυτή την πτυχιακή</td>
            </tr>
        `;
        return;
    }
    
    invitations.forEach(invitation => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${escapeHtml(invitation.username)}</td>
            <td>${escapeHtml(invitation.role)}</td>
            <td>${formatDate(invitation.created_at)}</td>  <!-- Νέα στήλη -->
            <td class="status-${invitation.status.toLowerCase()}">
                ${getStatusText(invitation.status)}
            </td>
            <td>${formatDate(invitation.responded_at)}</td>
        `;
        
        tableBody.appendChild(row);
    });
}

    // Helper functions
    function escapeHtml(unsafe) {
        return unsafe?.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
    }

    function getStatusText(status) {
        switch(status) {
            case 'pending': return 'Εκκρεμεί';
            case 'accepted': return 'Αποδεκτή';
            case 'rejected': return 'Απορριφθείσα';
            default: return status;
        }
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
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