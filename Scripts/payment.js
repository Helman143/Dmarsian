// Fetch and populate payment records from the server
function fetchPayments(searchTerm = '') {
    const paymentsUrl = 'get_payments.php' + (searchTerm ? ('?search=' + encodeURIComponent(searchTerm)) : '');
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const month = `${yyyy}-${mm}`;
    Promise.all([
        fetch(paymentsUrl).then(r => r.json()),
        fetch('api/balances.php?month=' + encodeURIComponent(month)).then(r => r.json()).catch(() => null),
        fetch('get_students.php').then(r => r.json()).catch(() => null)
    ])
    .then(([payments, balancesResp, studentsResp]) => {
        const balancesMap = {};
        if (balancesResp && balancesResp.status === 'success' && Array.isArray(balancesResp.balances)) {
            balancesResp.balances.forEach(b => {
                const raw = (b.jeja_no || '');
                const num = raw.replace(/\D/g, '');
                const key = num ? ('STD-' + num.padStart(5, '0')) : raw;
                balancesMap[key] = Number(b.balance || 0);
            });
        }

        // Build a map of current student status (Active/Inactive/Freeze) keyed by canonical STD-#####
        const statusesMap = {};
        if (studentsResp && studentsResp.status === 'success' && Array.isArray(studentsResp.data)) {
            studentsResp.data.forEach(s => {
                const raw = (s.jeja_no || '');
                const num = String(raw).replace(/\D/g, '');
                const key = num ? ('STD-' + num.padStart(5, '0')) : raw;
                let status = (s.status || '').trim();
                // normalize to expected values only
                const lower = status.toLowerCase();
                if (lower === 'active' || lower === 'inactive' || lower === 'freeze') {
                    statusesMap[key] = status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
                }
            });
        }

        populatePaymentTable(payments || [], balancesMap, statusesMap);
    })
    .catch(() => {
        const tableBody = document.getElementById('paymentTableBody');
        tableBody.innerHTML = '<tr><td colspan="10">Error fetching payment records.</td></tr>';
    });
}

// Populate payment records table
function populatePaymentTable(records, balancesMap = {}, statusesMap = {}) {
    const tableBody = document.getElementById('paymentTableBody');
    tableBody.innerHTML = '';
    if (!records.length) {
        tableBody.innerHTML = '<tr><td colspan="10">No payment records found.</td></tr>';
        return;
    }
    // Pre-compute per-student stats for fallback balance (current month totals, prior payments, discount)
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const monthStr = `${yyyy}-${mm}`;
    const statsMap = new Map(); // key -> { sumThisMonth, hasPrev, discount }
    records.forEach(r => {
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
    records.forEach(record => {
        const row = document.createElement('tr');
        const raw = record.jeja_no || '';
        const num = String(raw).replace(/\D/g, '');
        const canon = num ? ('STD-' + num.padStart(5, '0')) : raw;
        let bal = balancesMap[canon] != null ? Number(balancesMap[canon]) : null;
        if (bal == null || isNaN(bal)) {
            const s = statsMap.get(canon) || { sumThisMonth: 0, hasPrev: false, discount: 0 };
            const dueBase = s.hasPrev ? 1500 : 1800;
            const estimated = Math.max(0, (dueBase - (parseFloat(s.discount) || 0)) - (parseFloat(s.sumThisMonth) || 0));
            bal = estimated;
        }
        // Prefer the student's current status; fallback to record.status
        let statusText = statusesMap[canon] || (record.status ? String(record.status).trim() : '');
        const statusLower = statusText.toLowerCase();
        if (!(statusLower === 'active' || statusLower === 'inactive' || statusLower === 'freeze')) {
            // If value is not one of the three expected, clear it to avoid confusion
            statusText = '';
        } else {
            // Normalize capitalization
            statusText = statusText.charAt(0).toUpperCase() + statusText.slice(1).toLowerCase();
        }
        row.innerHTML = `
            <td>${record.id}</td>
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

// Handle form submission via AJAX
function handlePaymentSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    // Simple validation
    for (let [key, value] of formData.entries()) {
        if (!value) {
            const msgDiv = document.getElementById('paymentMessage');
            msgDiv.style.display = 'block';
            msgDiv.textContent = 'All fields are required.';
            msgDiv.style.color = 'red';
            setTimeout(() => { msgDiv.style.display = 'none'; }, 3000);
            return;
        }
    }
    fetch('api/payments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        const msgDiv = document.getElementById('paymentMessage');
        msgDiv.style.display = 'block';
        msgDiv.textContent = result.message;
        msgDiv.style.color = result.success ? 'green' : 'red';
        if (result.success) {
            event.target.reset();
            document.getElementById('amount_paid').value = '0.00';
            fetchPayments();
        }
        setTimeout(() => { msgDiv.style.display = 'none'; }, 3000);
    })
    .catch(() => {
        const msgDiv = document.getElementById('paymentMessage');
        msgDiv.style.display = 'block';
        msgDiv.textContent = 'Error saving payment.';
        msgDiv.style.color = 'red';
        setTimeout(() => { msgDiv.style.display = 'none'; }, 3000);
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
    const nonDiscountTbody = document.getElementById('nonDiscountTableBody');
    const discountTbody = document.getElementById('discountTableBody');
    nonDiscountTbody.innerHTML = '';
    discountTbody.innerHTML = '';

    // Filter and populate Non Discount Students
    students.filter(student => parseFloat(student.discount) === 0 &&
        (!searchNonDiscount || student.full_name.toLowerCase().includes(searchNonDiscount.toLowerCase()) || student.jeja_no.toLowerCase().includes(searchNonDiscount.toLowerCase()))
    ).forEach(student => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : ''}</td>
            <td>${student.full_name}</td>
            <td>₱${parseFloat(student.discount).toFixed(2)}</td>
            <td>${student.date_enrolled || ''}</td>
            <td class="status-${student.status ? student.status.toLowerCase() : ''}">${student.status || ''}</td>
        `;
        nonDiscountTbody.appendChild(row);
    });

    // Filter and populate Discount Students
    students.filter(student => parseFloat(student.discount) > 0 &&
        (!searchDiscount || student.full_name.toLowerCase().includes(searchDiscount.toLowerCase()) || student.jeja_no.toLowerCase().includes(searchDiscount.toLowerCase()))
    ).forEach(student => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : ''}</td>
            <td>${student.full_name}</td>
            <td>₱${parseFloat(student.discount).toFixed(2)}</td>
            <td>${student.date_enrolled || ''}</td>
            <td class="status-${student.status ? student.status.toLowerCase() : ''}">${student.status || ''}</td>
        `;
        discountTbody.appendChild(row);
    });

    // If no records
    if (nonDiscountTbody.children.length === 0) {
        nonDiscountTbody.innerHTML = '<tr><td colspan="9">No non-discount students found.</td></tr>';
    }
    if (discountTbody.children.length === 0) {
        discountTbody.innerHTML = '<tr><td colspan="9">No discount students found.</td></tr>';
    }
}

// Auto-fill discount when STD No. or Full Name is entered
function fetchStudentDiscount() {
    let jejaNo = document.getElementById('jeja_no').value.trim();
    if (!jejaNo) {
        document.getElementById('discount').value = '0.00';
        document.getElementById('full_name').value = '';
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
                } else {
                    document.getElementById('discount').value = '0.00';
                    if (document.getElementById('full_name')) {
                        document.getElementById('full_name').value = '';
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