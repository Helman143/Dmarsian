// Fetch payment records and update table, stats, and chart
async function fetchAndDisplayPayments() {
    const response = await fetch('get_payments.php');
    const payments = await response.json();

    // 1. Populate Transaction Table
    const tbody = document.getElementById('transactionTableBody');
    tbody.innerHTML = '';
    payments.forEach(payment => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${payment.id}</td>
            <td>${payment.date_paid}</td>
            <td>${payment.jeja_no}</td>
            <td>₱${parseFloat(payment.amount_paid).toLocaleString()}</td>
            <td>₱${parseFloat(payment.amount_paid).toLocaleString()}</td>
            <td>${payment.payment_type}</td>
            <td>${payment.discount}</td>
            <td>₱${(parseFloat(payment.amount_paid) - parseFloat(payment.discount || 0)).toLocaleString()}</td>
            <td>${payment.status}</td>
        `;
        tbody.appendChild(row);
    });

    // 2. Update Monthly and Yearly Stats
    const now = new Date();
    let monthlyTotal = 0, yearlyTotal = 0;
    payments.forEach(payment => {
        const paidDate = new Date(payment.date_paid);
        if (paidDate.getFullYear() === now.getFullYear()) {
            yearlyTotal += parseFloat(payment.amount_paid);
            if (paidDate.getMonth() === now.getMonth()) {
                monthlyTotal += parseFloat(payment.amount_paid);
            }
        }
    });
    document.querySelector('.stat-box.monthly .amount').textContent = `₱${monthlyTotal.toLocaleString()}`;
    document.querySelector('.stat-box.yearly .amount').textContent = `₱${yearlyTotal.toLocaleString()}`;

    // 3. Update Chart
    updateCollectionChart(payments);
}

// Chart.js logic
let collectionChart;
function updateCollectionChart(payments) {
    const period = document.getElementById('trendPeriod').value;
    let labels = [], data = [];

    if (period === 'yearly') {
        // Group by year
        const yearly = {};
        payments.forEach(p => {
            const year = new Date(p.date_paid).getFullYear();
            yearly[year] = (yearly[year] || 0) + parseFloat(p.amount_paid);
        });
        labels = Object.keys(yearly);
        data = Object.values(yearly);
    } else if (period === 'monthly') {
        // Group by month (current year)
        const now = new Date();
        const monthly = Array(12).fill(0);
        payments.forEach(p => {
            const d = new Date(p.date_paid);
            if (d.getFullYear() === now.getFullYear()) {
                monthly[d.getMonth()] += parseFloat(p.amount_paid);
            }
        });
        labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        data = monthly;
    } else if (period === 'weekly') {
        // Group by week (current month)
        const now = new Date();
        const weeks = [0,0,0,0,0,0];
        payments.forEach(p => {
            const d = new Date(p.date_paid);
            if (d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth()) {
                const week = Math.floor((d.getDate() - 1) / 7);
                weeks[week] += parseFloat(p.amount_paid);
            }
        });
        labels = ['Week 1','Week 2','Week 3','Week 4','Week 5','Week 6'];
        data = weeks;
    }

    if (collectionChart) collectionChart.destroy();
    const ctx = document.getElementById('collectionTrendChart').getContext('2d');
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
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    fetchAndDisplayPayments();
    document.getElementById('trendPeriod').addEventListener('change', fetchAndDisplayPayments);
}); 