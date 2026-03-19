// Pagination global variables
let currentPaymentPage = 1;
let currentNonDiscountPage = 1;
let currentDiscountPage = 1;
const paymentItemsPerPage = 10;

let globalPaymentRecords = [];
let globalBalancesMap = {};
let globalStatusesMap = {};
let globalNonDiscountStudents = [];
let globalDiscountStudents = [];

// Fetch and populate payment records from the server
function fetchPayments(searchTerm = '') {

    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const month = `${yyyy}-${mm}`;
    // Add cache-busting parameter to prevent stale data
    const cacheBuster = '&_t=' + Date.now();
    const paymentsUrl = 'get_payments.php' + (searchTerm ? ('?search=' + encodeURIComponent(searchTerm) + cacheBuster) : ('?' + cacheBuster.substring(1)));
    
    Promise.all([
        fetch(paymentsUrl, {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        })
            .then(r => {
                if (!r.ok) {
                    throw new Error(`HTTP error! status: ${r.status}`);
                }
                return r.text().then(text => {
                    try {
                        const parsed = JSON.parse(text);
                        return parsed;
                    } catch (e) {
                        console.error('Error parsing payments JSON:', text);
                        return [];
                    }
                });
            })
            .catch(err => {
                console.error('Error fetching payments:', err);
                return [];
            }),
        fetch('api/balances.php?month=' + encodeURIComponent(month))
            .then(r => r.json())
            .catch(() => null),
        fetch('get_students.php')
            .then(r => r.json())
            .catch(() => null)
    ])
    .then(([payments, balancesResp, studentsResp]) => {
        // Ensure payments is an array
        if (!Array.isArray(payments)) {
            console.error('Payments data is not an array:', payments);
            payments = [];
        }
        
        const balancesMap = {};
        if (balancesResp && balancesResp.status === 'success' && Array.isArray(balancesResp.balances)) {
            balancesResp.balances.forEach(b => {
                const raw = (b.jeja_no || '');
                const num = raw.replace(/\D/g, '');
                const key = num ? ('STD-' + num.padStart(5, '0')) : raw;
                balancesMap[key] = Number(b.balance || 0);
            });
        }

        const statusesMap = {};
        if (studentsResp && studentsResp.status === 'success' && Array.isArray(studentsResp.data)) {
            studentsResp.data.forEach(s => {
                const raw = (s.jeja_no || '');
                const num = String(raw).replace(/\D/g, '');
                const key = num ? ('STD-' + num.padStart(5, '0')) : raw;
                let status = (s.status || '').trim();
                const lower = status.toLowerCase();
                if (lower === 'active' || lower === 'inactive' || lower === 'freeze') {
                    statusesMap[key] = status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
                }
            });
        }

        globalPaymentRecords = payments;
        globalBalancesMap = balancesMap;
        globalStatusesMap = statusesMap;
        populatePaymentTable();
    })
    .catch(err => {
        console.error('Error in fetchPayments:', err);
        const tableBody = document.getElementById('paymentTableBody');
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="9">Error fetching payment records. Please refresh the page.</td></tr>';
        }
    });
}

// Populate payment records table
function populatePaymentTable() {
    const tableBody = document.getElementById('paymentTableBody');
    tableBody.innerHTML = '';
    
    if (!globalPaymentRecords || !globalPaymentRecords.length) {
        tableBody.innerHTML = '<tr><td colspan="9">No payment records found.</td></tr>';
        renderPaginationControls('paymentPagination', 0, 1, 'changePaymentPage');
        return;
    }

    const totalPages = Math.ceil(globalPaymentRecords.length / paymentItemsPerPage);
    if (currentPaymentPage > totalPages) currentPaymentPage = totalPages || 1;

    const startIdx = (currentPaymentPage - 1) * paymentItemsPerPage;
    const paginatedRecords = globalPaymentRecords.slice(startIdx, startIdx + paymentItemsPerPage);

    // Pre-compute per-student stats for fallback balance (current month totals, prior payments, discount)
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const monthStr = `${yyyy}-${mm}`;
    const statsMap = new Map(); // key -> { sumThisMonth, hasPrev, discount }
    globalPaymentRecords.forEach(r => {
        const raw = r.jeja_no || '';
        const num = String(raw).replace(/\D/g, '');
        const key = num ? ('STD-' + num.padStart(5, '0')) : raw;
        const recDate = r.date_paid ? new Date(r.date_paid) : null;
        const recMonth = recDate ? `${recDate.getFullYear()}-${String(recDate.getMonth() + 1).padStart(2, '0')}` : '';
        const stat = statsMap.get(key) || { sumThisMonth: 0, hasPrev: false, discount: 0 };
        const amt = parseFloat(r.amount_paid) || 0;
        if (recMonth === monthStr) {
            stat.sumThisMonth += amt;
        } else if (recDate && recDate < new Date(`${monthStr}-01T00:00:00`)) {
            stat.hasPrev = true;
        }
        const disc = parseFloat(r.discount);
        if (!isNaN(disc)) {
            stat.discount = disc; // assume per-month discount constant per student
        }
        statsMap.set(key, stat);
    });

    if (paginatedRecords.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="9">No payment records found on this page.</td></tr>';
    } else {
        paginatedRecords.forEach(record => {
            const row = document.createElement('tr');
            const raw = record.jeja_no || '';
            const num = String(raw).replace(/\D/g, '');
            const canon = num ? ('STD-' + num.padStart(5, '0')) : raw;
            let bal = globalBalancesMap[canon] != null ? Number(globalBalancesMap[canon]) : null;
            if (bal == null || isNaN(bal)) {
                const s = statsMap.get(canon) || { sumThisMonth: 0, hasPrev: false, discount: 0 };
                const dueBase = s.hasPrev ? 1500 : 1800;
                const estimated = Math.max(0, (dueBase - (parseFloat(s.discount) || 0)) - (parseFloat(s.sumThisMonth) || 0));
                bal = estimated;
            }
            // Prefer the student's current status; fallback to record.status
            let statusText = globalStatusesMap[canon] || (record.status ? String(record.status).trim() : '');
            const statusLower = statusText.toLowerCase();
            if (!(statusLower === 'active' || statusLower === 'inactive' || statusLower === 'freeze')) {
                statusText = '';
            } else {
                statusText = statusText.charAt(0).toUpperCase() + statusText.slice(1).toLowerCase();
            }
            row.innerHTML = `
                <td>${record.jeja_no ? record.jeja_no.replace(/^STD-/, '') : ''}</td>
                <td>${record.fullname}</td>
                <td>${record.date_paid}</td>
                <td>₱${parseFloat(record.amount_paid).toFixed(2)}</td>
                <td>${record.payment_type}</td>
                <td>₱${(parseFloat(record.discount) || 0).toFixed(2)}</td>
                <td>${record.date_enrolled}</td>
                <td>₱${Number(bal).toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                <td class="status-${statusText ? statusText.toLowerCase() : ''}">${statusText}</td>
            `;
            tableBody.appendChild(row);
        });
    }

    renderPaginationControls('paymentPagination', totalPages, currentPaymentPage, 'changePaymentPage');
}

function changePaymentPage(page) {
    currentPaymentPage = page;
    populatePaymentTable();
}
// Handle form submission via AJAX
function handlePaymentSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    // Validation - exclude readonly fields (discount is readonly)
    const requiredFields = ['jeja_no', 'payment_type', 'full_name', 'date_paid', 'amount_paid', 'status'];
    for (const field of requiredFields) {
        const value = formData.get(field);
        if (!value || value.trim() === '') {
            const msgDiv = document.getElementById('paymentMessage');
            msgDiv.style.display = 'block';
            msgDiv.className = 'payment-message error';
            msgDiv.textContent = `Please fill in all required fields. Missing: ${field}`;
            msgDiv.style.color = 'red';
            setTimeout(() => { msgDiv.style.display = 'none'; }, 3000);
            return;
        }
    }
    
    // Ensure discount has a value (default to 0.00 if empty)
    if (!formData.get('discount') || formData.get('discount').trim() === '') {
        formData.set('discount', '0.00');
    }
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> SAVING...';
    
    fetch('api/payments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response parsing error:', text);
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(result => {
        const msgDiv = document.getElementById('paymentMessage');
        msgDiv.style.display = 'block';
        msgDiv.className = result.success ? 'payment-message success' : 'payment-message error';
        msgDiv.textContent = result.message || (result.success ? 'Payment saved successfully!' : 'Failed to save payment.');
        msgDiv.style.color = result.success ? 'green' : 'red';
        
        if (result.success) {
            event.target.reset();
            document.getElementById('amount_paid').value = '0.00';
            document.getElementById('discount').value = '0.00';
            document.getElementById('status').value = '';
            
            // Broadcast payment update event for cross-page communication
            try {
                // Method 1: BroadcastChannel (works across tabs/windows)
                if (typeof BroadcastChannel !== 'undefined') {
                    const channel = new BroadcastChannel('payment-updates');
                    channel.postMessage({ type: 'payment-saved', timestamp: Date.now() });
                    // Don't close immediately - let it stay open for other tabs
                    setTimeout(() => channel.close(), 100);
                }
                // Method 2: localStorage event (works across tabs/windows)
                const updateTrigger = Date.now().toString();
                localStorage.setItem('payment-update-trigger', updateTrigger);
                // For same-tab updates, dispatch a custom event
                window.dispatchEvent(new CustomEvent('payment-updated', { detail: { timestamp: Date.now() } }));
                // Remove the trigger after a short delay to allow other tabs to detect it
                setTimeout(() => localStorage.removeItem('payment-update-trigger'), 100);
            } catch (e) {
                console.error('Error broadcasting payment update:', e);
            }
            
            // Refresh payment table immediately (removed setTimeout delay)
            fetchPayments();
        }
        setTimeout(() => { msgDiv.style.display = 'none'; }, 5000);
    })
    .catch(error => {
        console.error('Payment submission error:', error);
        const msgDiv = document.getElementById('paymentMessage');
        msgDiv.style.display = 'block';
        msgDiv.className = 'payment-message error';
        msgDiv.textContent = 'Error saving payment: ' + error.message;
        msgDiv.style.color = 'red';
        setTimeout(() => { msgDiv.style.display = 'none'; }, 5000);
    })
    .finally(() => {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
}

// Format amount input
function formatAmount(input) {
    let value = input.value.replace(/[^\d.]/g, '');
    if (value) {
        value = parseFloat(value).toFixed(2);
        input.value = value;
    }
}

// Fetch and populate students with and without discounts
function fetchStudentsForDiscountTables(searchNonDiscount = '', searchDiscount = '') {
    fetch('get_students.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                populateDiscountTables(result.data, searchNonDiscount, searchDiscount);
            } else {
                document.getElementById('nonDiscountTableBody').innerHTML = '<tr><td colspan="9">Error fetching students.</td></tr>';
                document.getElementById('discountTableBody').innerHTML = '<tr><td colspan="9">Error fetching students.</td></tr>';
            }
        })
        .catch(() => {
            document.getElementById('nonDiscountTableBody').innerHTML = '<tr><td colspan="9">Error fetching students.</td></tr>';
            document.getElementById('discountTableBody').innerHTML = '<tr><td colspan="9">Error fetching students.</td></tr>';
        });
}

function populateDiscountTables(students, searchNonDiscount, searchDiscount) {
    // Filter and populate Non Discount Students
    globalNonDiscountStudents = students.filter(student => parseFloat(student.discount) === 0 &&
        (!searchNonDiscount || student.full_name.toLowerCase().includes(searchNonDiscount.toLowerCase()) || student.jeja_no.toLowerCase().includes(searchNonDiscount.toLowerCase()))
    );

    // Filter and populate Discount Students
    globalDiscountStudents = students.filter(student => parseFloat(student.discount) > 0 &&
        (!searchDiscount || student.full_name.toLowerCase().includes(searchDiscount.toLowerCase()) || student.jeja_no.toLowerCase().includes(searchDiscount.toLowerCase()))
    );

    // Reset pages to 1 on new data/search
    currentNonDiscountPage = 1;
    currentDiscountPage = 1;

    renderNonDiscountTable();
    renderDiscountTable();
}

function renderNonDiscountTable() {
    const tbody = document.getElementById('nonDiscountTableBody');
    tbody.innerHTML = '';

    const totalPages = Math.ceil(globalNonDiscountStudents.length / paymentItemsPerPage);
    if (currentNonDiscountPage > totalPages) currentNonDiscountPage = totalPages || 1;

    const startIdx = (currentNonDiscountPage - 1) * paymentItemsPerPage;
    const paginatedRecords = globalNonDiscountStudents.slice(startIdx, startIdx + paymentItemsPerPage);

    if (paginatedRecords.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5">No non-discount students found.</td></tr>';
    } else {
        paginatedRecords.forEach(student => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : ''}</td>
                <td>${student.full_name}</td>
                <td>₱${parseFloat(student.discount).toFixed(2)}</td>
                <td>${student.date_enrolled || ''}</td>
                <td class="status-${student.status ? student.status.toLowerCase() : ''}">${student.status || ''}</td>
            `;
            tbody.appendChild(row);
        });
    }

    renderPaginationControls('nonDiscountPagination', totalPages, currentNonDiscountPage, 'changeNonDiscountPage');
}

function changeNonDiscountPage(page) {
    currentNonDiscountPage = page;
    renderNonDiscountTable();
}

function renderDiscountTable() {
    const tbody = document.getElementById('discountTableBody');
    tbody.innerHTML = '';

    const totalPages = Math.ceil(globalDiscountStudents.length / paymentItemsPerPage);
    if (currentDiscountPage > totalPages) currentDiscountPage = totalPages || 1;

    const startIdx = (currentDiscountPage - 1) * paymentItemsPerPage;
    const paginatedRecords = globalDiscountStudents.slice(startIdx, startIdx + paymentItemsPerPage);

    if (paginatedRecords.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5">No discount students found.</td></tr>';
    } else {
        paginatedRecords.forEach(student => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : ''}</td>
                <td>${student.full_name}</td>
                <td>₱${parseFloat(student.discount).toFixed(2)}</td>
                <td>${student.date_enrolled || ''}</td>
                <td class="status-${student.status ? student.status.toLowerCase() : ''}">${student.status || ''}</td>
            `;
            tbody.appendChild(row);
        });
    }

    renderPaginationControls('discountPagination', totalPages, currentDiscountPage, 'changeDiscountPage');
}

function changeDiscountPage(page) {
    currentDiscountPage = page;
    renderDiscountTable();
}

function renderPaginationControls(containerId, totalPages, currentPage, changeFunc) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<div class="pagination">';

    // Previous Button
    html += `<button class="page-btn prev" ${currentPage === 1 ? 'disabled' : ''} onclick="${changeFunc}(${currentPage - 1})">
        <i class="fas fa-chevron-left"></i> Previous
    </button>`;

    // Page Numbers
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);

    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    if (startPage > 1) {
        html += `<button class="page-link" onclick="${changeFunc}(1)">1</button>`;
        if (startPage > 2) html += '<span class="pager-dots">...</span>';
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="page-link ${i === currentPage ? 'active' : ''}" onclick="${changeFunc}(${i})">${i}</button>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span class="pager-dots">...</span>';
        html += `<button class="page-link" onclick="${changeFunc}(${totalPages})">${totalPages}</button>`;
    }

    // Next Button
    html += `<button class="page-btn next" ${currentPage === totalPages ? 'disabled' : ''} onclick="${changeFunc}(${currentPage + 1})">
        Next <i class="fas fa-chevron-right"></i>
    </button>`;

    html += '</div>';
    container.innerHTML = html;
}


// Auto-fill discount and status when STD No. or Full Name is entered
function fetchStudentDiscount() {
    let jejaNo = document.getElementById('jeja_no').value.trim();
    if (!jejaNo) {
        document.getElementById('discount').value = '0.00';
        document.getElementById('full_name').value = '';
        document.getElementById('status').value = '';
        return;
    }
    // Prepend STD- and pad to 5 digits if not present
    if (!jejaNo.startsWith('STD-')) {
        jejaNo = 'STD-' + jejaNo.padStart(5, '0');
    }
    fetch('get_students.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                const student = result.data.find(s => s.jeja_no === jejaNo);
                if (student) {
                    document.getElementById('discount').value = parseFloat(student.discount).toFixed(2);
                    if (document.getElementById('full_name')) {
                        document.getElementById('full_name').value = student.full_name;
                    }
                    // Auto-populate status from student's current status
                    if (document.getElementById('status')) {
                        let status = (student.status || '').trim();
                        const statusLower = status.toLowerCase();
                        if (statusLower === 'active' || statusLower === 'inactive' || statusLower === 'freeze') {
                            status = status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
                            document.getElementById('status').value = status;
                        } else {
                            document.getElementById('status').value = '';
                        }
                    }
                } else {
                    document.getElementById('discount').value = '0.00';
                    if (document.getElementById('full_name')) {
                        document.getElementById('full_name').value = '';
                    }
                    if (document.getElementById('status')) {
                        document.getElementById('status').value = '';
                    }
                }
            }
        });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    fetchPayments();
    // Payment form submission
    const paymentForm = document.getElementById('paymentForm');
    paymentForm.addEventListener('submit', handlePaymentSubmit);
    // Amount input formatting
    const amountInput = document.getElementById('amount_paid');
    amountInput.addEventListener('blur', () => formatAmount(amountInput));
    // Search input event for payment records
    const searchInput = document.getElementById('searchPayment');
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchPayments(e.target.value);
        }, 300);
    });

    // Fetch and search for discount tables
    let searchNonDiscountTimeout, searchDiscountTimeout;
    const searchNonDiscountInput = document.getElementById('searchNonDiscount');
    const searchDiscountInput = document.getElementById('searchDiscount');
    searchNonDiscountInput.addEventListener('input', (e) => {
        clearTimeout(searchNonDiscountTimeout);
        searchNonDiscountTimeout = setTimeout(() => {
            fetchStudentsForDiscountTables(e.target.value, searchDiscountInput.value);
        }, 300);
    });
    searchDiscountInput.addEventListener('input', (e) => {
        clearTimeout(searchDiscountTimeout);
        searchDiscountTimeout = setTimeout(() => {
            fetchStudentsForDiscountTables(searchNonDiscountInput.value, e.target.value);
        }, 300);
    });
    // Initial load for discount tables
    fetchStudentsForDiscountTables();

    // Auto-fetch discount on STD No. or Full Name blur
    document.getElementById('jeja_no').addEventListener('blur', fetchStudentDiscount);
    document.getElementById('full_name').addEventListener('blur', fetchStudentDiscount);

}); 