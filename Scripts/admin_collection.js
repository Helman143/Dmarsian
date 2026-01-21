// Store payments data globally to avoid re-fetching on period change
let paymentsData = [];

// Fetch payment records and update table, stats, and chart
async function fetchAndDisplayPayments() {
    // #region agent log
    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_collection.js:5',message:'fetchAndDisplayPayments called',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
    // #endregion
    
    try {
        // Add cache-busting parameter
        const cacheBuster = '?_t=' + Date.now();
        const response = await fetch('get_payments.php' + cacheBuster, {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        });
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_collection.js:12',message:'Collection fetch response received',data:{status:response.status},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const text = await response.text();
        let payments;
        try {
            payments = JSON.parse(text);
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_collection.js:22',message:'Collection payments parsed',data:{recordCount:Array.isArray(payments)?payments.length:'not-array'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
            // #endregion
        } catch (e) {
            console.error('Error parsing payments JSON:', text);
            payments = [];
        }
        
        // Ensure payments is an array
        if (!Array.isArray(payments)) {
            console.error('Invalid payments data format:', payments);
            payments = [];
        }

        // 1. Populate Transaction Table
        const tbody = document.getElementById('transactionTableBody');
        if (tbody) {
            tbody.innerHTML = '';
            if (payments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px;">No payment transactions found.</td></tr>';
            } else {
                payments.forEach(payment => {
                    const row = document.createElement('tr');
                    const amountPaid = parseFloat(payment.amount_paid || 0);
                    const discount = parseFloat(payment.discount || 0);
                    const totalAmount = amountPaid - discount;
                    row.innerHTML = `
                        <td>${payment.id || ''}</td>
                        <td>${payment.date_paid || ''}</td>
                        <td>${payment.jeja_no || ''}</td>
                        <td>₱${amountPaid.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>₱${amountPaid.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>${payment.payment_type || ''}</td>
                        <td>₱${discount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>${payment.status || ''}</td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }

        // 2. Update Monthly and Yearly Stats
        const now = new Date();
        let monthlyTotal = 0, yearlyTotal = 0;
        payments.forEach(payment => {
            if (payment.date_paid) {
                const paidDate = new Date(payment.date_paid);
                if (!isNaN(paidDate.getTime()) && paidDate.getFullYear() === now.getFullYear()) {
                    yearlyTotal += parseFloat(payment.amount_paid || 0);
                    if (paidDate.getMonth() === now.getMonth()) {
                        monthlyTotal += parseFloat(payment.amount_paid || 0);
                    }
                }
            }
        });
        const monthlyAmount = document.querySelector('.stat-box.monthly .amount');
        const yearlyAmount = document.querySelector('.stat-box.yearly .amount');
        if (monthlyAmount) monthlyAmount.textContent = `₱${monthlyTotal.toLocaleString()}`;
        if (yearlyAmount) yearlyAmount.textContent = `₱${yearlyTotal.toLocaleString()}`;

        // Store payments data for period change updates
        paymentsData = payments;

        // 3. Update Chart
        updateCollectionChart(payments);
    } catch (error) {
        console.error('Error fetching payments:', error);
        paymentsData = [];
        // Initialize chart with empty data on error
        updateCollectionChart([]);
    }
}

// Chart.js logic
let collectionChart;
function updateCollectionChart(payments) {
    const canvas = document.getElementById('collectionTrendChart');
    if (!canvas || !window.Chart) {
        console.error('Chart canvas or Chart.js library not found!');
        return;
    }

    const period = document.getElementById('trendPeriod')?.value || 'weekly';
    let labels = [], data = [];

    if (period === 'yearly') {
        // Group by year
        const yearly = {};
        payments.forEach(p => {
            if (p.date_paid) {
                const year = new Date(p.date_paid).getFullYear();
                if (!isNaN(year)) {
                    yearly[year] = (yearly[year] || 0) + parseFloat(p.amount_paid || 0);
                }
            }
        });
        labels = Object.keys(yearly).sort();
        data = labels.map(year => yearly[year]);
        // Ensure we have at least one data point
        if (labels.length === 0) {
            labels = [new Date().getFullYear().toString()];
            data = [0];
        }
    } else if (period === 'monthly') {
        // Group by month (current year)
        const now = new Date();
        const monthly = Array(12).fill(0);
        payments.forEach(p => {
            if (p.date_paid) {
                const d = new Date(p.date_paid);
                if (!isNaN(d.getTime()) && d.getFullYear() === now.getFullYear()) {
                    monthly[d.getMonth()] += parseFloat(p.amount_paid || 0);
                }
            }
        });
        labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        data = monthly;
    } else if (period === 'weekly') {
        // Group by week (current month)
        const now = new Date();
        const weeks = [0,0,0,0,0,0];
        payments.forEach(p => {
            if (p.date_paid) {
                const d = new Date(p.date_paid);
                if (!isNaN(d.getTime()) && d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth()) {
                    const week = Math.min(Math.floor((d.getDate() - 1) / 7), 5);
                    weeks[week] += parseFloat(p.amount_paid || 0);
                }
            }
        });
        labels = ['Week 1','Week 2','Week 3','Week 4','Week 5','Week 6'];
        data = weeks;
    }

    // Destroy existing chart if it exists
    if (collectionChart) {
        collectionChart.destroy();
        collectionChart = null;
    }

    const ctx = canvas.getContext('2d');
    collectionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Collection',
                data: data,
                borderColor: '#0f0',
                backgroundColor: 'rgba(0,255,0,0.1)',
                fill: true,
                tension: 0.3,
                borderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#0f0',
                pointBorderColor: '#0f0'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#0f0',
                    bodyColor: '#fff',
                    borderColor: '#0f0',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#fff',
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0, 255, 0, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#fff'
                    },
                    grid: {
                        color: 'rgba(0, 255, 0, 0.1)'
                    }
                }
            }
        }
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    fetchAndDisplayPayments();
    
    // Listen for payment updates from other pages/tabs
    // Method 1: BroadcastChannel (modern browsers)
    if (typeof BroadcastChannel !== 'undefined') {
        const channel = new BroadcastChannel('payment-updates');
        channel.addEventListener('message', (event) => {
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_collection.js:219',message:'BroadcastChannel payment update received',data:{type:event.data.type},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
            // #endregion
            if (event.data && event.data.type === 'payment-saved') {
                console.log('Payment update received via BroadcastChannel, refreshing...');
                fetchAndDisplayPayments();
            }
        });
    }
    
    // Method 2: localStorage event (works across tabs/windows)
    window.addEventListener('storage', (event) => {
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_collection.js:230',message:'Storage event received',data:{key:event.key},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
        // #endregion
        if (event.key === 'payment-update-trigger') {
            console.log('Payment update received via localStorage, refreshing...');
            fetchAndDisplayPayments();
        }
    });
    
    // Method 3: Custom event (for same-tab updates)
    window.addEventListener('payment-updated', (event) => {
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_collection.js:240',message:'Custom payment-updated event received',data:{timestamp:event.detail?.timestamp},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
        // #endregion
        console.log('Payment update received via custom event, refreshing...');
        fetchAndDisplayPayments();
    });
    
    // Polling fallback: refresh every 30 seconds as a safety net
    setInterval(() => {
        fetchAndDisplayPayments();
    }, 30000);
    
    const trendPeriod = document.getElementById('trendPeriod');
    if (trendPeriod) {
        trendPeriod.addEventListener('change', () => {
            // Update chart with existing data when period changes
            if (paymentsData.length > 0) {
                updateCollectionChart(paymentsData);
            } else {
                // If no data, fetch it
                fetchAndDisplayPayments();
            }
        });
    }
}); 