// Fetch and populate payment records from the server
function fetchPayments(searchTerm = '') {
    // #region agent log
    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_payment.js:2',message:'fetchPayments called',data:{searchTerm,hasCacheBuster:true},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
    // #endregion
    
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
                // #region agent log
                fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_payment.js:12',message:'Payments fetch response received',data:{status:r.status,statusText:r.statusText},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
                // #endregion
                if (!r.ok) {
                    throw new Error(`HTTP error! status: ${r.status}`);
                }
                return r.text().then(text => {
                    try {
                        const parsed = JSON.parse(text);
                        // #region agent log
                        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_payment.js:20',message:'Payments data parsed',data:{recordCount:Array.isArray(parsed)?parsed.length:'not-array'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
                        // #endregion
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

        populatePaymentTable(payments, balancesMap, statusesMap);
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
function populatePaymentTable(records, balancesMap = {}, statusesMap = {}) {
    const tableBody = document.getElementById('paymentTableBody');
    tableBody.innerHTML = '';
    if (!records.length) {
        tableBody.innerHTML = '<tr><td colspan="9">No payment records found.</td></tr>';
        return;
    }
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const monthStr = `${yyyy}-${mm}`;
    const statsMap = new Map();
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
            stat.discount = disc;
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
        let statusText = statusesMap[canon] || (record.status ? String(record.status).trim() : '');
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
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_payment.js:191',message:'Payment submission result received',data:{success:result.success,message:result.message},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
        // #endregion
        
        const msgDiv = document.getElementById('paymentMessage');
        msgDiv.style.display = 'block';
        msgDiv.className = result.success ? 'payment-message success' : 'payment-message error';
        msgDiv.textContent = result.message || (result.success ? 'Payment saved successfully!' : 'Failed to save payment.');
        msgDiv.style.color = result.success ? 'green' : 'red';
        
        if (result.success) {
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_payment.js:200',message:'Payment success - before refresh',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
            // #endregion
            
            event.target.reset();
            document.getElementById('amount_paid').value = '0.00';
            document.getElementById('discount').value = '0.00';
            
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
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_payment.js:220',message:'Calling fetchPayments immediately',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
            // #endregion
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