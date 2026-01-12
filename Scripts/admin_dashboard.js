// Function to fetch and update dashboard stats
function fetchDashboardStats() {
    // #region agent log
    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:2',message:'fetchDashboardStats called',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
    // #endregion
    
    console.log('Admin Dashboard: Fetching dashboard stats...');
    const cacheBuster = '?_t=' + Date.now();
    const statsUrl = 'get_dashboard_stats.php' + cacheBuster;
    console.log('Admin Dashboard: Stats URL:', statsUrl, 'Current path:', window.location.pathname);
    fetch(statsUrl, {
        cache: 'no-store',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache'
        }
    })
    .then(response => {
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:15',message:'Dashboard stats response received',data:{status:response.status,ok:response.ok},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
        // #endregion
        console.log('Admin Dashboard: Stats response status:', response.status, response.statusText);
        if (!response.ok) {
            console.error('Admin Dashboard: Stats fetch failed with status:', response.status);
            throw new Error(`Dashboard stats fetch failed: ${response.status} ${response.statusText}`);
        }
        return response.text().then(text => {
            console.log('Admin Dashboard: Stats response text preview:', text.substring(0, 100));
            try {
                const parsed = JSON.parse(text);
                console.log('Admin Dashboard: Stats JSON parsed successfully');
                return parsed;
            } catch (e) {
                console.error('Admin Dashboard: Failed to parse dashboard stats JSON. Full response:', text);
                throw new Error('Invalid JSON response from dashboard stats: ' + e.message);
            }
        });
    })
    .then(data => {
        console.log('Admin Dashboard: Stats data received:', data);
        if (data.status === 'success') {
            // Update stat cards with specific IDs
            const todayEnrollees = document.getElementById('today-enrollees');
            const weeklyEnrollees = document.getElementById('weekly-enrollees');
            const todayCollected = document.getElementById('today-collected');
            const weeklyCollected = document.getElementById('weekly-collected');
            
            if (todayEnrollees) {
                todayEnrollees.textContent = data.todayEnrollees;
                todayEnrollees.style.color = '';
            }
            if (weeklyEnrollees) {
                weeklyEnrollees.textContent = data.weeklyEnrollees;
                weeklyEnrollees.style.color = '';
            }
            if (todayCollected) {
                todayCollected.textContent = '₱' + data.todayCollected.toLocaleString();
                todayCollected.style.color = '';
            }
            if (weeklyCollected) {
                weeklyCollected.textContent = '₱' + data.weeklyCollected.toLocaleString();
                weeklyCollected.style.color = '';
            }
            console.log('Admin Dashboard: Stats updated successfully');
            // Update Student Overview chart if present
            const studentChartCanvas = document.getElementById('studentChart');
            if (studentChartCanvas && window.Chart) {
                const ctx = studentChartCanvas.getContext('2d');
                if (
                    window.studentOverviewChart &&
                    window.studentOverviewChart.data &&
                    window.studentOverviewChart.data.datasets &&
                    window.studentOverviewChart.data.datasets[0]
                ) {
                    window.studentOverviewChart.data.datasets[0].data = [data.todayEnrollees, data.weeklyEnrollees];
                    window.studentOverviewChart.update();
                } else {
                    window.studentOverviewChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ["Today's Enrollees", "Weekly Enrollees"],
                            datasets: [{
                                data: [data.todayEnrollees, data.weeklyEnrollees],
                                backgroundColor: [
                                    '#00ff6a',
                                    'rgba(0, 255, 106, 0.25)'
                                ],
                                borderColor: [
                                    '#00ff6a',
                                    '#00ff6a'
                                ],
                                borderWidth: 2,
                                hoverOffset: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '60%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#00ff6a',
                                        font: { size: 12 },
                                        padding: 20
                                    }
                                }
                            }
                        }
                    });
                }
            }
            // Update Active vs. Inactive Students chart
            const active = Number(data.activePayments) || 0;
            const inactive = Number(data.inactivePayments) || 0;
            const chartActive = (active === 0 && inactive === 0) ? 0.0001 : active;
            const chartInactive = (active === 0 && inactive === 0) ? 0.0001 : inactive;
            const activeInactiveCanvas = document.getElementById('activeInactiveChart');
            if (activeInactiveCanvas && window.Chart) {
                const ctx = activeInactiveCanvas.getContext('2d');
                if (
                    window.activeInactiveChart &&
                    window.activeInactiveChart.data &&
                    window.activeInactiveChart.data.datasets &&
                    window.activeInactiveChart.data.datasets[0]
                ) {
                    window.activeInactiveChart.data.datasets[0].data = [chartActive, chartInactive];
                    window.activeInactiveChart.update();
                } else {
                    window.activeInactiveChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Active', 'Inactive'],
                            datasets: [{
                                data: [chartActive, chartInactive],
                                backgroundColor: ['#00ff6a', 'rgba(255,255,255,0.2)'],
                                borderColor: ['#00ff6a', 'rgba(255,255,255,0.4)'],
                                borderWidth: 2,
                                hoverOffset: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '60%',
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        color: '#fff',
                                        padding: 10,
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.label + ': ' + context.raw + ' students';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }
    })
    .catch(error => {
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:133',message:'Dashboard stats fetch error',data:{error:error.message},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
        // #endregion
        console.error('Error fetching dashboard stats:', error);
        // Show error state in stat cards only if DOM is ready
        try {
            const statElements = ['today-enrollees', 'weekly-enrollees', 'today-collected', 'weekly-collected'];
            statElements.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error: ' + (error.message || 'Failed to load');
                    element.style.color = '#ff4d4d';
                }
            });
        } catch (domError) {
            console.error('DOM not ready for error display:', domError);
        }
    });
}

// --- Collected vs. Uncollected Payments Chart Logic ---
let paymentsChart;

function fetchAndRenderPaymentsChart() {
    // #region agent log
    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:152',message:'fetchAndRenderPaymentsChart called',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'I'})}).catch(()=>{});
    // #endregion
    
    // Get date range from inputs, but default to current month if not set
    const fromDateInput = document.getElementById('from-date');
    const toDateInput = document.getElementById('to-date');
    const fromDate = fromDateInput ? fromDateInput.value : null;
    const toDate = toDateInput ? toDateInput.value : null;
    
    // Use current month as default (same as admin_collection.js)
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth();
    const startOfMonth = fromDate ? new Date(fromDate) : new Date(currentYear, currentMonth, 1);
    const endOfMonth = toDate ? new Date(toDate + 'T23:59:59') : new Date(currentYear, currentMonth + 1, 0, 23, 59, 59);
    
    // Extract month parameter for dues API (YYYY-MM format)
    const monthParam = `${startOfMonth.getFullYear()}-${String(startOfMonth.getMonth() + 1).padStart(2, '0')}`;
    
    // Add cache-busting parameters
    const cacheBuster = '?_t=' + Date.now();
    const paymentsUrl = 'get_payments.php' + cacheBuster;
    const duesUrl = `api/dues.php?month=${monthParam}` + '&_t=' + Date.now();
    console.log('Admin Dashboard: Payments URL:', paymentsUrl);
    console.log('Admin Dashboard: Dues URL:', duesUrl);
    
    Promise.all([
        fetch(paymentsUrl, {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        }).then(response => {
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:188',message:'Payments fetch response',data:{status:response.status,ok:response.ok,url:paymentsUrl},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'H'})}).catch(()=>{});
            // #endregion
            if (!response.ok) {
                throw new Error(`Payments fetch failed: ${response.status} ${response.statusText}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    // #region agent log
                    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:197',message:'Payments JSON parse error',data:{textPreview:text.substring(0,100)},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'H'})}).catch(()=>{});
                    // #endregion
                    console.error('Failed to parse payments JSON:', text.substring(0, 200));
                    throw new Error('Invalid JSON response from payments endpoint');
                }
            });
        }),
        fetch(duesUrl, {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        }).then(response => {
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:207',message:'Dues fetch response',data:{status:response.status,ok:response.ok,url:duesUrlWithCache},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'H'})}).catch(()=>{});
            // #endregion
            if (!response.ok) {
                throw new Error(`Dues fetch failed: ${response.status} ${response.statusText}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    // #region agent log
                    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:216',message:'Dues JSON parse error',data:{textPreview:text.substring(0,100)},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'H'})}).catch(()=>{});
                    // #endregion
                    console.error('Failed to parse dues JSON:', text.substring(0, 200));
                    throw new Error('Invalid JSON response from dues endpoint');
                }
            });
        })
    ])
    .then(([payments, duesData]) => {
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:225',message:'Both fetches completed',data:{paymentsIsArray:Array.isArray(payments),duesStatus:duesData?.status},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'H'})}).catch(()=>{});
        // #endregion
        
        // Calculate Collected: Same logic as admin_collection.js - sum amount_paid for current month
        let collected = 0;
        if (Array.isArray(payments)) {
            payments.forEach(p => {
                if (p.date_paid) {
                    const paidDate = new Date(p.date_paid);
                    // Check if payment is within the selected date range
                    if (!isNaN(paidDate.getTime()) && paidDate >= startOfMonth && paidDate <= endOfMonth) {
                        collected += parseFloat(p.amount_paid || 0);
                    }
                }
            });
        }
        
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:240',message:'Collected calculated',data:{collected:collected,monthParam:monthParam,paymentsCount:Array.isArray(payments)?payments.length:0},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'I'})}).catch(()=>{});
        // #endregion
        
        // Calculate Uncollected: Sum all balance from dues (same as dues table in admin_dashboard.php)
        let uncollected = 0;
        if (duesData && duesData.status === 'success' && Array.isArray(duesData.dues)) {
            duesData.dues.forEach(due => {
                // Sum all balances - API already filtered by month
                const balance = parseFloat(due.balance || 0);
                if (!isNaN(balance) && balance > 0) {
                    uncollected += balance;
                }
            });
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:252',message:'Uncollected calculated from dues',data:{uncollected:uncollected,duesCount:duesData.dues.length},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'I'})}).catch(()=>{});
            // #endregion
        } else {
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:260',message:'Dues data not available',data:{duesStatus:duesData?.status,duesMessage:duesData?.message},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'I'})}).catch(()=>{});
            // #endregion
            console.warn('Dues data not available, uncollected will be 0');
            uncollected = 0;
        }
        
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:275',message:'Chart data ready',data:{collected:collected,uncollected:uncollected,monthParam:monthParam},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'I'})}).catch(()=>{});
        // #endregion
        
        // Always show at least a small value so chart renders
        const chartCollected = (collected === 0 && uncollected === 0) ? 0.0001 : collected;
        const chartUncollected = (collected === 0 && uncollected === 0) ? 0.0001 : uncollected;
        
        const paymentsCanvas = document.getElementById('paymentsChart');
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:246',message:'Attempting to render chart',data:{hasCanvas:!!paymentsCanvas,hasChart:typeof window.Chart !== 'undefined',collected:collected,uncollected:uncollected},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
        // #endregion
        
        if (paymentsCanvas && window.Chart) {
            const ctx = paymentsCanvas.getContext('2d');
            if (paymentsChart) paymentsChart.destroy();
            paymentsChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Collected', 'Uncollected'],
                    datasets: [{
                        data: [chartCollected, chartUncollected],
                        backgroundColor: ['#00ff6a', '#ff4d4d'],
                        borderColor: ['#00ff6a', '#ff4d4d'],
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#e9ffee',
                                padding: 10,
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw === 0.0001 ? 0 : context.raw;
                                    return context.label + ': ₱' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:281',message:'Chart created successfully',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
            // #endregion
        } else {
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:285',message:'Chart not rendered - missing requirements',data:{hasCanvas:!!paymentsCanvas,hasChart:typeof window.Chart !== 'undefined'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
            // #endregion
            console.error('Cannot render chart: canvas or Chart.js not available', {
                hasCanvas: !!paymentsCanvas,
                hasChart: typeof window.Chart !== 'undefined'
            });
        }
    })
    .catch(error => {
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:310',message:'Chart fetch error',data:{error:error.message,errorName:error.name,url:error.url || 'unknown'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'H'})}).catch(()=>{});
        // #endregion
        console.error('Error fetching chart data:', error);
        // Show error in chart area
        const paymentsCanvas = document.getElementById('paymentsChart');
        if (paymentsCanvas && window.Chart) {
            const ctx = paymentsCanvas.getContext('2d');
            if (paymentsChart) paymentsChart.destroy();
            paymentsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Error'],
                    datasets: [{
                        label: 'Error Loading Data',
                        data: [0],
                        backgroundColor: ['#ff4d4d']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Error: ' + (error.message || 'Failed to load chart data'),
                            color: '#ff4d4d'
                        }
                    }
                }
            });
        }
        
        // Try to fetch just payments data as fallback
        const cacheBuster = '?_t=' + Date.now();
        const paymentsUrl = 'get_payments.php' + cacheBuster;
        fetch(paymentsUrl, {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error(`Payments fetch failed: ${response.status}`);
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid JSON from payments');
                }
            });
        })
        .then(payments => {
            // Calculate collected from payments only
            let collected = 0;
            if (Array.isArray(payments)) {
                payments.forEach(p => {
                    if (p.date_paid) {
                        const paidDate = new Date(p.date_paid);
                        const from = fromDate ? new Date(fromDate) : null;
                        const to = toDate ? new Date(toDate + 'T23:59:59') : null;
                        let inRange = true;
                        if (from && paidDate < from) inRange = false;
                        if (to && paidDate > to) inRange = false;
                        if (inRange) {
                            collected += parseFloat(p.amount_paid || 0);
                        }
                    }
                });
            }
            
            // Render chart with collected only (uncollected = 0)
            const paymentsCanvas = document.getElementById('paymentsChart');
            if (paymentsCanvas && window.Chart) {
                const ctx = paymentsCanvas.getContext('2d');
                if (paymentsChart) paymentsChart.destroy();
                const uncollected = 0;
                const chartCollected = (collected === 0 && uncollected === 0) ? 0.0001 : collected;
                const chartUncollected = (collected === 0 && uncollected === 0) ? 0.0001 : uncollected;
                
                paymentsChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Collected', 'Uncollected (N/A)'],
                        datasets: [{
                            data: [chartCollected, chartUncollected],
                            backgroundColor: ['#00ff6a', '#ff4d4d'],
                            borderColor: ['#00ff6a', '#ff4d4d'],
                            borderWidth: 2,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    color: '#fff',
                                    padding: 10,
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.label.includes('N/A')) {
                                            return 'Uncollected data unavailable';
                                        }
                                        return context.label + ': ₱' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                return; // Success, exit
            }
            throw new Error('Canvas or Chart.js not available');
        })
        .catch(fallbackError => {
            console.error('Fallback fetch also failed:', fallbackError);
        
        // Try to render an empty chart or show error message
        const paymentsCanvas = document.getElementById('paymentsChart');
        if (paymentsCanvas && window.Chart) {
            const ctx = paymentsCanvas.getContext('2d');
            if (paymentsChart) paymentsChart.destroy();
            // Render empty chart with error state
            paymentsChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Error Loading Data'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#ff4d4d'],
                        borderColor: ['#ff4d4d'],
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#fff',
                                padding: 10,
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Error: ' + error.message;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
}

// Format date to show month as a word (e.g., "November 10, 2025")
function formatDateWithWordMonth(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString(undefined, {
        month: 'long',
        day: '2-digit',
        year: 'numeric'
    });
}

// --- Dues Table Population ---
function fetchAndPopulateDues() {
    // #region agent log
    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:253',message:'fetchAndPopulateDues called',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
    // #endregion
    
    console.log('Admin Dashboard: Fetching dues data...');
    const cacheBuster = '?_t=' + Date.now();
    const duesUrl = 'api/dues.php' + cacheBuster;
    console.log('Admin Dashboard: Dues URL:', duesUrl);
    fetch(duesUrl, {
        cache: 'no-store',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache'
        }
    })
        .then(response => {
            console.log('Admin Dashboard: Dues response status:', response.status, response.statusText);
            if (!response.ok) {
                console.error('Admin Dashboard: Dues fetch failed with status:', response.status);
                throw new Error(`Dues fetch failed: ${response.status} ${response.statusText}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Admin Dashboard: Dues response text preview:', text.substring(0, 100));
            let data;
            try {
                data = JSON.parse(text);
                console.log('Admin Dashboard: Dues JSON parsed successfully');
            } catch (e) {
                console.error('Admin Dashboard: Failed to parse dues JSON. Full response:', text);
                const duesTableBody = document.querySelector('.dues-table tbody');
                if (duesTableBody) {
                    const errRow = document.createElement('tr');
                    errRow.innerHTML = '<td colspan="10" style="text-align:center;color:#fff;">Error parsing dues</td>';
                    duesTableBody.innerHTML = '';
                    duesTableBody.appendChild(errRow);
                }
                console.error('Dues response (not JSON):', text);
                return;
            }
            if (data.status === 'success') {
                console.log('Admin Dashboard: Dues data received:', data.dues.length, 'items');
                const duesTableBody = document.querySelector('.dues-table tbody');
                if (duesTableBody) {
                    duesTableBody.innerHTML = '';
                    if (data.dues.length === 0) {
                        const noDataRow = document.createElement('tr');
                        noDataRow.innerHTML = '<td colspan="10" style="text-align: center; color: #fff;">No dues found for this month</td>';
                        duesTableBody.appendChild(noDataRow);
                    } else {
                        data.dues.forEach(due => {
                            const row = document.createElement('tr');
                            const lastSent = due.last_reminder_at ? new Date(due.last_reminder_at).toLocaleString() : '-';
                            const amount = Number(due.amount || 0).toFixed(2);
                            const discount = Number(due.discount || 0).toFixed(2);
                            const total = Number(due.total_payment || 0).toFixed(2);
                            const paid = Number(due.amount_paid || 0).toFixed(2);
                            const balance = Number((Number(due.balance) ?? (Number(total) - Number(paid))) || 0).toFixed(2);
                            row.innerHTML = `
                                <td>${formatDateWithWordMonth(due.due_date)}</td>
                                <td>${due.id_name}</td>
                                <td>₱${Number(amount).toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                                <td>₱${Number(discount).toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                                <td>₱${Number(total).toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                                <td>₱${Number(paid).toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                                <td>₱${Number(balance).toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                                <td>${due.contact}</td>
                                <td><span title="Count: ${due.reminder_count || 0}">${lastSent}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-success send-reminder-btn" data-jeja="${due.jeja_no}">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </td>
                            `;
                            duesTableBody.appendChild(row);
                        });
                    }
                }
            } else {
                console.error('Error fetching dues:', data.message);
                const duesTableBody = document.querySelector('.dues-table tbody');
                if (duesTableBody) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.innerHTML = '<td colspan="10" style="text-align: center; color: #fff;">' + (data.message || 'No dues found') + '</td>';
                    duesTableBody.innerHTML = '';
                    duesTableBody.appendChild(noDataRow);
                }
            }
        })
        .catch(error => {
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:550',message:'Dues fetch error',data:{error:error.message},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
            // #endregion
            console.error('Error fetching dues:', error);
            try {
                const duesTableBody = document.querySelector('.dues-table tbody');
                if (duesTableBody) {
                    const errRow = document.createElement('tr');
                    errRow.innerHTML = '<td colspan="10" style="text-align:center;color:#ff4d4d;padding:20px;"><i class="fas fa-exclamation-triangle"></i> Error loading dues: ' + (error.message || 'Network error') + '<br><small>Check browser console for details</small></td>';
                    duesTableBody.innerHTML = '';
                    duesTableBody.appendChild(errRow);
                }
            } catch (domError) {
                console.error('DOM not ready for error display:', domError);
            }
        });
}

function fetchAndRenderActiveInactiveChart() {
    // #region agent log
    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:580',message:'fetchAndRenderActiveInactiveChart called',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
    // #endregion
    
    console.log('Admin Dashboard: Fetching active/inactive counts...');
    const cacheBuster = '?_t=' + Date.now();
    const activeUrl = 'get_active_inactive_counts.php' + cacheBuster;
    console.log('Admin Dashboard: Active/Inactive URL:', activeUrl);
    fetch(activeUrl, {
        cache: 'no-store',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Active/inactive fetch failed: ${response.status} ${response.statusText}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse active/inactive JSON:', text.substring(0, 200));
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(counts => {
            const active = counts.active || 0;
            const inactive = counts.inactive || 0;
            const chartActive = (active === 0 && inactive === 0) ? 0.0001 : active;
            const chartInactive = (active === 0 && inactive === 0) ? 0.0001 : inactive;
            const activeInactiveCanvas = document.getElementById('activeInactiveChart');
            if (activeInactiveCanvas && window.Chart) {
                const ctx = activeInactiveCanvas.getContext('2d');
                // Only destroy if it's a Chart instance
                if (window.activeInactiveChart && typeof window.activeInactiveChart.destroy === 'function') {
                    window.activeInactiveChart.destroy();
                }
                window.activeInactiveChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Inactive'],
                        datasets: [{
                            data: [chartActive, chartInactive],
                            backgroundColor: ['#00ff6a', 'rgba(255,255,255,0.2)'],
                            borderColor: ['#00ff6a', 'rgba(255,255,255,0.4)'],
                            borderWidth: 2,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    color: '#e9ffee',
                                    padding: 10,
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.raw + ' students';
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                console.error('activeInactiveChart canvas or Chart.js not found!');
            }
        })
        .catch(err => {
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:625',message:'Active/inactive fetch error',data:{error:err.message},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
            // #endregion
            console.error('Error fetching active/inactive counts:', err);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    // #region agent log
    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:631',message:'DOMContentLoaded fired',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
    // #endregion
    
    console.log('Admin Dashboard: DOMContentLoaded - Starting data fetch...');
    
    // Initial fetch on page load - must be inside DOMContentLoaded
    fetchDashboardStats();
    fetchAndPopulateDues();
    fetchAndRenderActiveInactiveChart();
    
    // Event listeners for date filters - must be inside DOMContentLoaded
    const fromDateInput = document.getElementById('from-date');
    const toDateInput = document.getElementById('to-date');
    const paymentsCanvas = document.getElementById('paymentsChart');
    
    // #region agent log
    fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:397',message:'Checking DOM elements',data:{hasFromDate:!!fromDateInput,hasToDate:!!toDateInput,hasCanvas:!!paymentsCanvas,hasChart:typeof window.Chart !== 'undefined'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
    // #endregion
    
    if (fromDateInput && toDateInput) {
        fromDateInput.addEventListener('change', fetchAndRenderPaymentsChart);
        toDateInput.addEventListener('change', fetchAndRenderPaymentsChart);
        // Initial render
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:405',message:'Calling fetchAndRenderPaymentsChart on DOMContentLoaded',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
        // #endregion
        fetchAndRenderPaymentsChart();
    } else {
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:410',message:'Date inputs not found',data:{hasFromDate:!!fromDateInput,hasToDate:!!toDateInput},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
        // #endregion
        console.error('Date input elements not found!');
    }
    
    // Listen for payment updates from other pages/tabs
    // Method 1: BroadcastChannel (modern browsers)
    if (typeof BroadcastChannel !== 'undefined') {
        const channel = new BroadcastChannel('payment-updates');
        channel.addEventListener('message', (event) => {
            // #region agent log
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:390',message:'BroadcastChannel payment update received',data:{type:event.data.type},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'F'})}).catch(()=>{});
            // #endregion
            if (event.data && event.data.type === 'payment-saved') {
                console.log('Payment update received via BroadcastChannel, refreshing dashboard...');
                fetchDashboardStats();
                fetchAndRenderPaymentsChart();
                fetchAndPopulateDues();
                fetchAndRenderActiveInactiveChart();
            }
        });
    }
    
    // Method 2: localStorage event (works across tabs/windows)
    window.addEventListener('storage', (event) => {
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:405',message:'Storage event received',data:{key:event.key},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'F'})}).catch(()=>{});
        // #endregion
        if (event.key === 'payment-update-trigger') {
            console.log('Payment update received via localStorage, refreshing dashboard...');
            fetchDashboardStats();
            fetchAndRenderPaymentsChart();
            fetchAndPopulateDues();
            fetchAndRenderActiveInactiveChart();
        }
    });
    
    // Method 3: Custom event (for same-tab updates)
    window.addEventListener('payment-updated', (event) => {
        // #region agent log
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:418',message:'Custom payment-updated event received',data:{timestamp:event.detail?.timestamp},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'F'})}).catch(()=>{});
        // #endregion
        console.log('Payment update received via custom event, refreshing dashboard...');
        fetchDashboardStats();
        fetchAndRenderPaymentsChart();
        fetchAndPopulateDues();
        fetchAndRenderActiveInactiveChart();
    });
    
    setInterval(function() {
        const cacheBuster = '?_t=' + Date.now();
        fetch('get_dashboard_stats.php' + cacheBuster, {
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Polling fetch failed: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse polling JSON:', text.substring(0, 200));
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(data => {
                if (data.status === 'success') {
                    const todayEnrollees = document.getElementById('today-enrollees');
                    const weeklyEnrollees = document.getElementById('weekly-enrollees');
                    const todayCollected = document.getElementById('today-collected');
                    const weeklyCollected = document.getElementById('weekly-collected');
                    if (todayEnrollees) todayEnrollees.textContent = data.todayEnrollees;
                    if (weeklyEnrollees) weeklyEnrollees.textContent = data.weeklyEnrollees;
                    if (todayCollected) todayCollected.textContent = '₱' + data.todayCollected.toLocaleString();
                    if (weeklyCollected) weeklyCollected.textContent = '₱' + data.weeklyCollected.toLocaleString();
                    if (window.studentOverviewChart) {
                        window.studentOverviewChart.data.datasets[0].data = [data.todayEnrollees, data.weeklyEnrollees];
                        window.studentOverviewChart.update();
                    }
                    if (window.activeInactiveChart) {
                        const active = Number(data.activePayments) || 0;
                        const inactive = Number(data.inactivePayments) || 0;
                        const chartActive = (active === 0 && inactive === 0) ? 0.0001 : active;
                        const chartInactive = (active === 0 && inactive === 0) ? 0.0001 : inactive;
                        window.activeInactiveChart.data.datasets[0].data = [chartActive, chartInactive];
                        window.activeInactiveChart.update();
                    }
                }
            })
            .catch(error => {
                // #region agent log
                fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'admin_dashboard.js:790',message:'Polling error',data:{error:error.message},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
                // #endregion
                console.error('Error refreshing dashboard stats:', error);
            });
        fetchAndPopulateDues();
        fetchAndRenderActiveInactiveChart();
        fetchAndRenderPaymentsChart();
    }, 300000); // Polling fallback every 5 minutes

    // Single send via table action
    const duesTable = document.querySelector('.dues-table');
    if (duesTable) {
        duesTable.addEventListener('click', async function(e) {
            const btn = e.target.closest('.send-reminder-btn');
            if (!btn) return;
            const jeja = btn.getAttribute('data-jeja');
            if (!jeja) return;
            const ok = confirm('Send reminder email to student and parent for ' + jeja + '?');
            if (!ok) return;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            try {
                const resp = await fetch('send_due_reminder.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mode: 'single', jeja_no: jeja })
                });
                const result = await resp.json();
                if (result.status === 'success') {
                    alert('Reminder sent successfully!\n\nIf you don\'t receive the email, please check:\n1. Spam/Junk folder\n2. Email configuration (SMTP2GO settings)\n3. Server error logs');
                    // Refresh dues only on success
                    fetchAndPopulateDues();
                } else {
                    let msg = result.message || result.error || 'Unknown error';
                    let errorMsg = 'Send failed: ' + msg;
                    
                    // Add HTTP code if available
                    if (result.http_code) {
                        errorMsg += '\nHTTP Status: ' + result.http_code;
                    }
                    
                    // Add debug info if available
                    if (result.debug_info) {
                        console.error('Email Debug Info:', result.debug_info);
                        errorMsg += '\n\nDebug Info:\n';
                        errorMsg += '- API Key Set: ' + (result.debug_info.api_key_set ? 'Yes' : 'No') + '\n';
                        errorMsg += '- Sender Email Set: ' + (result.debug_info.sender_email_set ? 'Yes' : 'No');
                    }
                    
                    if (msg.includes('SMTP2GO') || msg.includes('not configured')) {
                        errorMsg += '\n\nPlease check your email configuration:\n';
                        errorMsg += '1. Go to Digital Ocean App Platform Dashboard\n';
                        errorMsg += '2. Settings → App-Level Environment Variables\n';
                        errorMsg += '3. Ensure SMTP2GO_API_KEY is set\n';
                        errorMsg += '4. Ensure SMTP2GO_SENDER_EMAIL is set\n';
                        errorMsg += '5. Click Save and redeploy if needed';
                    } else if (msg.includes('HTTP 401') || msg.includes('HTTP 403')) {
                        errorMsg += '\n\nThis usually means:\n';
                        errorMsg += '1. SMTP2GO API key is invalid or expired\n';
                        errorMsg += '2. Check your SMTP2GO account dashboard\n';
                        errorMsg += '3. Verify the API key is active';
                    } else if (msg.includes('HTTP 400')) {
                        errorMsg += '\n\nThis usually means:\n';
                        errorMsg += '1. Sender email is not verified in SMTP2GO\n';
                        errorMsg += '2. Invalid email format\n';
                        errorMsg += '3. Check SMTP2GO dashboard for sender verification';
                    }
                    
                    alert(errorMsg);
                    console.error('Email send error:', result);
                }
            } catch (err) {
                alert('Error sending reminder: ' + err.message + '\n\nPlease check:\n1. Server connection\n2. Email configuration\n3. Browser console for details');
                console.error('Reminder send error:', err);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            }
        });
    }

    // Bulk send
    const bulkBtn = document.getElementById('sendAllRemindersBtn');
    if (bulkBtn) {
        bulkBtn.addEventListener('click', async function() {
            const ok = confirm('Send reminders to ALL due students listed?');
            if (!ok) return;
            bulkBtn.disabled = true;
            bulkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            try {
                const resp = await fetch('send_due_reminder.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mode: 'bulk' })
                });
                const result = await resp.json();
                if (result.status === 'success') {
                    let msg = 'Bulk reminders processed:\n';
                    msg += '- Total: ' + (result.count || 0) + '\n';
                    msg += '- Success: ' + (result.success_count || 0) + '\n';
                    msg += '- Failed: ' + (result.failed_count || 0) + '\n';
                    msg += '- Skipped: ' + (result.skipped_count || 0);
                    if (result.failed_count > 0) {
                        msg += '\n\nSome emails failed. Check server logs for details.';
                    }
                    if (result.success_count > 0) {
                        msg += '\n\nIf emails are not received, check:\n1. Spam/Junk folder\n2. Email configuration\n3. SMTP2GO account status';
                    }
                    alert(msg);
                    // Refresh only when successful
                    fetchAndPopulateDues();
                } else {
                    let msg = result.message || result.error || 'Unknown error';
                    let errorMsg = 'Bulk send failed: ' + msg;
                    
                    if (msg.includes('SMTP2GO') || msg.includes('not configured')) {
                        errorMsg += '\n\nPlease check your email configuration:\n';
                        errorMsg += '1. Go to Digital Ocean App Platform Dashboard\n';
                        errorMsg += '2. Settings → App-Level Environment Variables\n';
                        errorMsg += '3. Ensure SMTP2GO_API_KEY is set\n';
                        errorMsg += '4. Ensure SMTP2GO_SENDER_EMAIL is set\n';
                        errorMsg += '5. Click Save and redeploy if needed';
                    }
                    
                    alert(errorMsg);
                    console.error('Bulk email send error:', result);
                }
            } catch (err) {
                alert('Error sending bulk reminders: ' + err.message + '\n\nPlease check:\n1. Server connection\n2. Email configuration\n3. Browser console for details');
                console.error('Bulk reminder send error:', err);
            } finally {
                bulkBtn.disabled = false;
                bulkBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send All Reminders';
            }
        });
    }
}); 