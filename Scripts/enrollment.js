// Sample data for pending enrollments
// let pendingEnrollments = [ ... ];

// Sample data for approved enrollments
// let approvedEnrollments = [ ... ];

let pendingEnrollments = [];
let approvedEnrollments = [];

// Initialize BroadcastChannel for cross-page communication
const enrollmentChannel = typeof BroadcastChannel !== 'undefined' 
    ? new BroadcastChannel('enrollment-updates') 
    : null;

// Function to generate EDP number
function generateEdpNo() {
    const date = new Date();
    const year = date.getFullYear().toString().substr(-2);
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    return `EDP-${year}${month}-${random}`;
}

// Function to generate transaction ID
function generateTransactionId() {
    const prefix = 'TRX';
    const timestamp = Date.now().toString().substr(-6);
    const random = Math.floor(Math.random() * 100).toString().padStart(2, '0');
    return `${prefix}${timestamp}${random}`;
}

// Function to populate pending enrollments table
function populatePendingTable(data = pendingEnrollments) {
    const tableBody = document.getElementById('pendingTableBody');
    tableBody.innerHTML = '';

    data.forEach(enrollment => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${enrollment.name}</td>
            <td>${enrollment.date}</td>
            <td>${enrollment.age}</td>
            <td>${enrollment.address}</td>
            <td>${enrollment.phone}</td>
            <td>${enrollment.belt}</td>
            <td class="status-${enrollment.status.toLowerCase()}">${enrollment.status}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-approve" onclick="approveEnrollment(${enrollment.id})">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Function to populate approved enrollments table
function populateApprovedTable(data = approvedEnrollments) {
    const tableBody = document.getElementById('approvedTableBody');
    tableBody.innerHTML = '';

    data.forEach(enrollment => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${enrollment.transactionId}</td>
            <td>${enrollment.edpNo}</td>
            <td>${enrollment.dateEnrolled}</td>
            <td>${enrollment.name}</td>
            <td>${enrollment.phone}</td>
            <td>₱${enrollment.amountPaid}</td>
            <td>${enrollment.paymentType}</td>
        `;
        tableBody.appendChild(row);
    });
}

// Function to approve enrollment
function approveEnrollment(id) {
    if (confirm('Approve this enrollment?')) {
        fetch('approve_enrollment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(response => response.json())
        .then(result => {
            let icon = 'info';
            let title = 'Info';
            if (result.status === 'success') {
                icon = 'success';
                title = 'Success!';
            } else if (result.status === 'error') {
                icon = 'error';
                title = 'Error';
            } else if (result.status === 'info') {
                icon = 'info';
                title = 'Information';
            }
            
            Swal.fire({
                icon: icon,
                title: title,
                text: result.message,
                timer: result.status === 'success' ? 2000 : 3000,
                showConfirmButton: result.status === 'error' || result.status === 'info'
            });
            
            // If student data is returned, add it immediately to the table
            if (result.status === 'success' && result.student) {
                // Add the new student to the approved enrollments table immediately
                const student = result.student;
                const tbody = document.getElementById('approvedTableBody');
                if (tbody) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${student.id || ''}</td>
                        <td>${student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : ''}</td>
                        <td>${student.date_enrolled || ''}</td>
                        <td>${student.full_name || ''}</td>
                        <td>${student.phone || ''}</td>
                    `;
                    // Insert at the top of the table
                    tbody.insertBefore(row, tbody.firstChild);
                    // Also add to the approvedEnrollments array
                    approvedEnrollments.unshift({
                        id: student.id,
                        jeja_no: student.jeja_no,
                        date_enrolled: student.date_enrolled,
                        full_name: student.full_name,
                        phone: student.phone
                    });
                }
                
                // Remove the approved enrollment from pending table immediately
                pendingEnrollments = pendingEnrollments.filter(req => req.id !== id);
                renderPendingTable(pendingEnrollments);
                
                // Broadcast approval event to other tabs/pages
                if (enrollmentChannel) {
                    enrollmentChannel.postMessage({
                        type: 'enrollment_approved',
                        student: student,
                        enrollmentId: id
                    });
                }
            }
            
            // Refresh pending enrollments to remove the approved one
            loadPendingEnrollments();
            // Refresh approved enrollments immediately (no delay needed with transaction)
            loadApprovedEnrollments();
            
            // Also update discount tables in payment.php if present
            if (typeof fetchStudentsForDiscountTables === 'function') {
                fetchStudentsForDiscountTables();
            }
        });
    }
}

// Search functionality for pending enrollments
function searchPending(term) {
    const search = term.trim().toLowerCase();
    const filtered = pendingEnrollments.filter(req =>
        (req.id && req.id.toString().includes(search)) ||
        (req.full_name && req.full_name.toLowerCase().includes(search))
    );
    renderPendingTable(filtered);
}

// Search functionality for approved enrollments
function searchApproved(term) {
    const search = term.trim().toLowerCase();
    const filtered = approvedEnrollments.filter(student =>
        (student.id && student.id.toString().includes(search)) ||
        (student.full_name && student.full_name.toLowerCase().includes(search))
    );
    renderApprovedTable(filtered);
}

// Listen for enrollment updates from other tabs/pages
if (enrollmentChannel) {
    enrollmentChannel.addEventListener('message', (event) => {
        if (event.data && event.data.type === 'enrollment_approved') {
            // Refresh both tables when enrollment is approved in another tab
            loadPendingEnrollments();
            loadApprovedEnrollments();
        }
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    loadPendingEnrollments();
    loadApprovedEnrollments();

    // Search input events
    const searchPendingInput = document.getElementById('searchPending');
    const searchApprovedInput = document.getElementById('searchApproved');

    let pendingTimeout;
    let approvedTimeout;

    searchPendingInput.addEventListener('input', (e) => {
        clearTimeout(pendingTimeout);
        pendingTimeout = setTimeout(() => {
            searchPending(e.target.value);
        }, 300);
    });

    searchApprovedInput.addEventListener('input', (e) => {
        clearTimeout(approvedTimeout);
        approvedTimeout = setTimeout(() => {
            searchApproved(e.target.value);
        }, 300);
    });
});

function loadPendingEnrollments() {
    // Add cache-busting to ensure fresh data on DigitalOcean
    fetch('get_pending_enrollments.php?t=' + Date.now(), {
        cache: 'no-store',
        headers: {
            'Cache-Control': 'no-cache'
        }
    })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                pendingEnrollments = result.data;
                renderPendingTable(pendingEnrollments);
            }
        })
        .catch(error => {
            console.error('Error loading pending enrollments:', error);
        });
}

function renderPendingTable(data) {
    const tbody = document.getElementById('pendingTableBody');
    tbody.innerHTML = '';
    data.forEach(req => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${req.id || ''}</td>
            <td>${req.date_registered || ''}</td>
            <td>${req.full_name || ''}</td>
            <td>${req.address || ''}</td>
            <td>${req.phone || ''}</td>
            <td>${req.email || ''}</td>
            <td>${req.school || ''}</td>
            <td>${req.parent_name || ''}</td>
            <td>${req.parent_phone || ''}</td>
            <td>${req.parent_email || ''}</td>
            <td>${req.rank || ''}</td>
            <td>${req.belt_rank || ''}</td>
            <td>${req.class || ''}</td>
            <td><button class="btn-approve" onclick="approveEnrollment(${req.id})">Approve</button></td>
        `;
        tbody.appendChild(row);
    });
}

function loadApprovedEnrollments() {
    // Add cache-busting to ensure fresh data on DigitalOcean
    fetch('get_approved_enrollments.php?t=' + Date.now(), {
        cache: 'no-store',
        headers: {
            'Cache-Control': 'no-cache'
        }
    })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                approvedEnrollments = result.data;
                renderApprovedTable(approvedEnrollments);
            }
        })
        .catch(error => {
            console.error('Error loading approved enrollments:', error);
        });
}

function renderApprovedTable(data) {
    const tbody = document.getElementById('approvedTableBody');
    tbody.innerHTML = '';
    data.forEach(student => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${student.id || ''}</td>
            <td>${student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : ''}</td>
            <td>${student.date_enrolled || ''}</td>
            <td>${student.full_name || ''}</td>
            <td>${student.phone || ''}</td>
        `;
        tbody.appendChild(row);
    });
} 