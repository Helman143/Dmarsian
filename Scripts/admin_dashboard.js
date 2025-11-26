// Fetch and update dashboard stat cards
fetch('get_dashboard_stats.php')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update stat cards with specific IDs
            const todayEnrollees = document.getElementById('today-enrollees');
            const weeklyEnrollees = document.getElementById('weekly-enrollees');
            const todayCollected = document.getElementById('today-collected');
            const weeklyCollected = document.getElementById('weekly-collected');
            
            if (todayEnrollees) todayEnrollees.textContent = data.todayEnrollees;
            if (weeklyEnrollees) weeklyEnrollees.textContent = data.weeklyEnrollees;
            if (todayCollected) todayCollected.textContent = '₱' + data.todayCollected.toLocaleString();
            if (weeklyCollected) weeklyCollected.textContent = '₱' + data.weeklyCollected.toLocaleString();
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
                        type: 'pie',
                        data: {
                            labels: ["Today's Enrollees", "Weekly Enrollees"],
                            datasets: [{
                                data: [data.todayEnrollees, data.weeklyEnrollees],
                                backgroundColor: [
                                    '#5DD62C',
                                    'rgba(93, 214, 44, 0.3)'
                                ],
                                borderColor: [
                                    '#5DD62C',
                                    '#5DD62C'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#5DD62C',
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
                        type: 'pie',
                        data: {
                            labels: ['Active', 'Inactive'],
                            datasets: [{
                                data: [chartActive, chartInactive],
                                backgroundColor: ['#5DD62C', '#f00'],
                                borderColor: ['#5DD62C', '#f00'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
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
        console.error('Error fetching dashboard stats:', error);
        // Show error state in stat cards
        const statElements = ['today-enrollees', 'weekly-enrollees', 'today-collected', 'weekly-collected'];
        statElements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
            }
        });
    });

// --- Collected vs. Uncollected Payments Chart Logic ---
let paymentsChart;

function fetchAndRenderPaymentsChart() {
    const fromDate = document.getElementById('from-date').value;
    const toDate = document.getElementById('to-date').value;
    fetch('get_payments.php')
        .then(response => response.json())
        .then(payments => {
            // Filter by date range
            const filtered = payments.filter(p => {
                const paidDate = new Date(p.date_paid);
                const from = fromDate ? new Date(fromDate) : null;
                const to = toDate ? new Date(toDate) : null;
                if (from && paidDate < from) return false;
                if (to && paidDate > to) return false;
                return true;
            });
            let collected = 0, uncollected = 0;
            filtered.forEach(p => {
                const amt = parseFloat(p.amount_paid) || 0;
                if (String(p.status).toLowerCase() === 'paid' || String(p.status).toLowerCase() === 'active') {
                    collected += amt;
                } else {
                    uncollected += amt;
                }
            });
            // Always show at least a small value so chart renders
            if (collected === 0 && uncollected === 0) {
                collected = 0.0001;
                uncollected = 0.0001;
            }
            const paymentsCanvas = document.getElementById('paymentsChart');
            if (paymentsCanvas && window.Chart) {
                const ctx = paymentsCanvas.getContext('2d');
                if (paymentsChart) paymentsChart.destroy();
                paymentsChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Collected', 'Uncollected'],
                        datasets: [{
                            data: [collected, uncollected],
                            backgroundColor: ['#5DD62C', 'rgba(93, 214, 44, 0.3)'],
                            borderColor: ['#5DD62C', '#5DD62C'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
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
                                        return context.label + ': ₱' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
}

// Event listeners for date filters
const fromDateInput = document.getElementById('from-date');
const toDateInput = document.getElementById('to-date');
if (fromDateInput && toDateInput) {
    fromDateInput.addEventListener('change', fetchAndRenderPaymentsChart);
    toDateInput.addEventListener('change', fetchAndRenderPaymentsChart);
    // Initial render
    fetchAndRenderPaymentsChart();
}

// --- Dues Table Population ---
function fetchAndPopulateDues() {
    fetch('api/dues.php')
        .then(response => response.text())
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
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
                                <td>${due.due_date}</td>
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
            console.error('Error fetching dues:', error);
            const duesTableBody = document.querySelector('.dues-table tbody');
            if (duesTableBody) {
                const errRow = document.createElement('tr');
                errRow.innerHTML = '<td colspan="10" style="text-align:center;color:#fff;">Error loading dues</td>';
                duesTableBody.innerHTML = '';
                duesTableBody.appendChild(errRow);
            }
        });
}

function fetchAndRenderActiveInactiveChart() {
    fetch('get_active_inactive_counts.php')
        .then(response => response.json())
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
                    type: 'pie',
                    data: {
                        labels: ['Active', 'Inactive'],
                        datasets: [{
                            data: [chartActive, chartInactive],
                            backgroundColor: ['#5DD62C', '#f00'],
                            borderColor: ['#5DD62C', '#f00'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
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
            } else {
                console.error('activeInactiveChart canvas or Chart.js not found!');
            }
        })
        .catch(err => {
            console.error('Error fetching active/inactive counts:', err);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    fetchAndPopulateDues();
    fetchAndRenderActiveInactiveChart();
    setInterval(function() {
        fetch('get_dashboard_stats.php')
            .then(response => response.json())
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
            .catch(error => console.error('Error refreshing dashboard stats:', error));
        fetchAndPopulateDues();
        fetchAndRenderActiveInactiveChart();
    }, 300000);

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
                    alert('Reminder sent successfully.');
                    // Refresh dues only on success
                    fetchAndPopulateDues();
                } else {
                    const msg = result.message || result.error || 'Unknown error';
                    alert('Send failed: ' + msg);
                }
            } catch (err) {
                alert('Error sending reminder: ' + err.message);
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
                    alert('Bulk reminders processed: ' + (result.count || 0));
                    // Refresh only when successful
                    fetchAndPopulateDues();
                } else {
                    const msg = result.message || result.error || 'Unknown error';
                    alert('Bulk send failed: ' + msg);
                }
            } catch (err) {
                alert('Error sending bulk reminders: ' + err.message);
            } finally {
                bulkBtn.disabled = false;
                bulkBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send All Reminders';
            }
        });
    }
}); 