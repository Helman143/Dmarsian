// admin_trial_session.js - Optimized with SweetAlert2 and Real-time Updates

// Initialize BroadcastChannel for cross-page communication
const trialChannel = typeof BroadcastChannel !== 'undefined' ? new BroadcastChannel('trial-updates') : null;

document.addEventListener('DOMContentLoaded', function () {
    initHandlers();
});

// Re-initialize handlers
function initHandlers() {
    // Complete Trial Session handler
    document.querySelectorAll('.btn-complete').forEach(function(btn) {
        // Clone to clear listeners
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        newBtn.addEventListener('click', function() {
            var idx = this.getAttribute('data-index');
            handleComplete(idx, this.closest('tr'));
        });
    });

    // Convert to Enrollment handler (Pending Table)
    document.querySelectorAll('#pendingTableBody .btn-approve:not(.btn-complete)').forEach(function(btn) {
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        newBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Note',
                text: 'Please mark this trial as "Complete" first before converting to enrollment.',
                icon: 'info',
                background: '#1a1a1a',
                color: '#fff'
            });
        });
    });

    // Convert to Enrollment handler (Complete Table)
    document.querySelectorAll('#completeTableBody .btn-approve').forEach(function(btn) {
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        newBtn.addEventListener('click', function() {
            var row = this.closest('tr');
            var regId = row.querySelector('td').textContent.trim();
            handleConvert(regId, row);
        });
    });
}

async function handleComplete(idx, row) {
    const result = await Swal.fire({
        title: 'Complete Trial?',
        text: 'Mark this trial session as complete? This will move it to the Complete table.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#00ff6a',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, complete it!',
        background: '#1a1a1a',
        color: '#fff'
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch('complete_trial_session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'index=' + encodeURIComponent(idx) + '&t=' + Date.now()
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            Swal.fire({
                title: 'Completed!',
                text: 'Trial session marked as complete.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
                background: '#1a1a1a',
                color: '#fff'
            });
            row.remove();
            const tbody = document.getElementById('pendingTableBody');
            if (tbody && tbody.children.length === 0) {
                tbody.innerHTML = '<tr><td colspan="14">No trial session registrations found.</td></tr>';
            }
            if (trialChannel) trialChannel.postMessage({ type: 'trial-completed' });
            setTimeout(() => { location.reload(); }, 1500); 
        } else {
            Swal.fire({ title: 'Error', text: data.message, icon: 'error', background: '#1a1a1a', color: '#fff' });
        }
    } catch (err) {
        Swal.fire({ title: 'Request Failed', text: err.message, icon: 'error', background: '#1a1a1a', color: '#fff' });
    }
}

async function handleConvert(regId, row) {
    const result = await Swal.fire({
        title: 'Convert to Enrollment?',
        text: 'Are you sure you want to convert this trial session to a full student enrollment?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#00ff6a',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, convert!',
        background: '#1a1a1a',
        color: '#fff'
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch('convert_trial_to_student.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'registration_id=' + encodeURIComponent(regId) + '&t=' + Date.now()
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            Swal.fire({
                title: 'Enrolled!',
                text: 'Student enrolled successfully.',
                icon: 'success',
                background: '#1a1a1a',
                color: '#fff'
            });
            row.remove();
            const tbody = document.getElementById('completeTableBody');
            if (tbody && tbody.children.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9">No complete trial session registrations found.</td></tr>';
            }
            if (trialChannel) trialChannel.postMessage({ type: 'trial-converted' });
        } else {
            let errorMsg = data.message || 'Unknown error occurred';
            if (data.mysql_error) errorMsg += '\n\nDB Error: ' + data.mysql_error;
            Swal.fire({ title: 'Error', text: errorMsg, icon: 'error', background: '#1a1a1a', color: '#fff' });
        }
    } catch (err) {
        Swal.fire({ title: 'Request Failed', text: err.message, icon: 'error', background: '#1a1a1a', color: '#fff' });
    }
}

// Listen for updates from other tabs
if (trialChannel) {
    trialChannel.onmessage = function(event) {
        if (event.data.type === 'trial-completed' || event.data.type === 'trial-converted') {
            console.log('Trial update received via BroadcastChannel, refreshing...');
            location.reload(); 
        }
    };
}

// Search functionality
document.getElementById('searchPending').addEventListener('input', function(e) {
    searchTable('pendingTableBody', e.target.value);
});
document.getElementById('searchComplete').addEventListener('input', function(e) {
    searchTable('completeTableBody', e.target.value);
});

function searchTable(tbodyId, value) {
    var tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    var filter = value.toUpperCase();
    var trs = tbody.getElementsByTagName('tr');
    for (var i = 0; i < trs.length; i++) {
        var tds = trs[i].getElementsByTagName('td');
        var show = false;
        for (var j = 0; j < tds.length; j++) {
            if (tds[j].innerText.toUpperCase().indexOf(filter) > -1) {
                show = true;
                break;
            }
        }
        trs[i].style.display = show ? '' : 'none';
    }
}