document.addEventListener('DOMContentLoaded', function() {
    const studentForm = document.getElementById('studentForm');
    const tableBody = document.querySelector('.student-table tbody');

    // Form submission handler
    studentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(studentForm);
        const data = {};
        formData.forEach((value, key) => data[key] = value);
        
        // Create new table row
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${data.jeja_no}</td>
            <td>${new Date().toISOString().split('T')[0]}</td>
            <td>${data.full_name}</td>
            <td>${data.address}</td>
            <td>${data.phone}</td>
            <td>${data.email}</td>
            <td>${data.school}</td>
            <td>${data.parent_name}</td>
            <td>${data.parent_phone}</td>
            <td>${data.parent_email}</td>
            <td>${data.belt_rank}</td>
            <td>${data.discount}</td>
            <td>${data.class}</td>
            <td>${data.schedule}</td>
            <td>Active</td>
        `;
        
        // Add row to table
        tableBody.appendChild(row);
        
        // Clear form
        studentForm.reset();
    });

    // Export button handler
    document.querySelector('.btn-export').addEventListener('click', function() {
        // Get table data
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        const headers = Array.from(document.querySelectorAll('.student-table th'))
            .map(header => header.textContent);
        
        // Convert to CSV
        const csv = [
            headers.join(','),
            ...rows.map(row => 
                Array.from(row.cells)
                    .map(cell => `"${cell.textContent}"`)
                    .join(',')
            )
        ].join('\n');
        
        // Create download link
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('hidden', '');
        a.setAttribute('href', url);
        a.setAttribute('download', 'students.csv');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // Update button handler
    document.querySelector('.btn-update').addEventListener('click', function() {
        const selectedRow = tableBody.querySelector('tr.selected');
        if (!selectedRow) {
            alert('Please select a student to update');
            return;
        }
        
        // Get form data
        const formData = new FormData(studentForm);
        const data = {};
        formData.forEach((value, key) => data[key] = value);
        
        // Update row
        const cells = selectedRow.cells;
        cells[0].textContent = data.jeja_no;
        cells[1].textContent = new Date().toISOString().split('T')[0];
        cells[2].textContent = data.full_name;
        cells[3].textContent = data.address;
        cells[4].textContent = data.phone;
        cells[5].textContent = data.email;
        cells[6].textContent = data.school;
        cells[7].textContent = data.parent_name;
        cells[8].textContent = data.parent_phone;
        cells[9].textContent = data.parent_email;
        cells[10].textContent = data.belt_rank;
        cells[11].textContent = data.discount;
        cells[12].textContent = data.class;
        cells[13].textContent = data.schedule;
        cells[14].textContent = 'Active';
        
        // Clear form and selection
        studentForm.reset();
        selectedRow.classList.remove('selected');
    });

    // Make table rows selectable
    tableBody.addEventListener('click', function(e) {
        const row = e.target.closest('tr');
        if (!row) return;
        
        // Toggle selection
        document.querySelectorAll('.student-table tbody tr').forEach(r => 
            r.classList.remove('selected'));
        row.classList.add('selected');
        
        // Fill form with row data
        const cells = row.cells;
        document.querySelector('[name="jeja_no"]').value = cells[0].textContent;
        document.querySelector('[name="full_name"]').value = cells[2].textContent;
        document.querySelector('[name="address"]').value = cells[3].textContent;
        document.querySelector('[name="phone"]').value = cells[4].textContent;
        document.querySelector('[name="email"]').value = cells[5].textContent;
        document.querySelector('[name="school"]').value = cells[6].textContent;
        document.querySelector('[name="parent_name"]').value = cells[7].textContent;
        document.querySelector('[name="parent_phone"]').value = cells[8].textContent;
        document.querySelector('[name="parent_email"]').value = cells[9].textContent;
        document.querySelector('[name="belt_rank"]').value = cells[10].textContent;
        document.querySelector('[name="discount"]').value = cells[11].textContent;
        document.querySelector('[name="class"]').value = cells[12].textContent;
        document.querySelector('[name="schedule"]').value = cells[13].textContent;
    });
}); 