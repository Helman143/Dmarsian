<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - D'MARSIANS Taekwondo System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" media="(max-width: 767.98px)">
    <link rel="stylesheet" href="Styles/dashboard.css">
    <link rel="stylesheet" href="Styles/student_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
    <link rel="stylesheet" href="Styles/sidebar.css">
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <?php $active = 'student'; include 'partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Mobile menu button (visible on small screens only) -->
            <button class="btn btn-outline-primary d-md-none mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                <i class="fas fa-bars"></i> Menu
            </button>
            <h1 class="page-title">STUDENT MANAGEMENT</h1>
            
            <div class="student-form-container">
                <form class="student-form" id="studentForm" onsubmit="return handleFormSubmit(event)">
                    <div class="form-grid">
                        <!-- Left Column -->
                        <div class="form-column">
                            <div class="form-group">
                                <label>STD No.</label>
                                <input type="text" name="jeja_no" readonly placeholder="Auto-generated" style="background:#eee;cursor:not-allowed;">
                            </div>
                            
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Phone No.</label>
                                <input type="tel" name="phone" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" inputmode="email" autocomplete="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" required>
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>School</label>
                                <select name="school" required>
                                    <option value="">Select</option>
                                    <option value="SCC">SCC</option>
                                    <option value="ZSSAT">ZSSAT</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Class</label>
                                <select name="class" required>
                                    <option value="">Select</option>
                                    <option value="Poomsae">Poomsae</option>
                                    <option value="Kyorugi">Kyorugi</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="form-column">
                            <div class="form-group">
                                <label>Parent's Name</label>
                                <input type="text" name="parent_name">
                            </div>
                            
                            <div class="form-group">
                                <label>Parent's Phone</label>
                                <input type="tel" name="parent_phone">
                            </div>
                            
                            <div class="form-group">
                                <label>Parent's Email</label>
                                <input type="email" inputmode="email" autocomplete="email" name="parent_email">
                            </div>
                            
                            <div class="form-group">
                                <label>Belt Rank</label>
                                <div class="belt-rank-container">
                                    <select name="belt_rank" required>
                                        <option value="">Select</option>
                                        <option value="White">White</option>
                                        <option value="Yellow">Yellow</option>
                                        <option value="Green">Green</option>
                                        <option value="Blue">Blue</option>
                                        <option value="Red">Red</option>
                                        <option value="Black">Black</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Discount</label>
                                <div class="discount-container">
                                    <input type="number" name="discount" value="0.00" step="0.01">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Schedule</label>
                                <select name="schedule" required>
                                    <option value="">Select</option>
                                    <option value="MWF-PM">MWF Afternoon</option>
                                    <option value="TTS-PM">TTS Afternoon</option>
                                </select>
                            </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" required>
                                <option value="">Select</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Freeze">Freeze</option>
                            </select>
                        </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-save">
                            <span class="btn-content"><i class="fas fa-save"></i><span>SAVE</span></span>
                        </button>       
                        <button type="submit" class="btn btn-update">
                            <span class="btn-content"><i class="fas fa-sync-alt"></i><span>UPDATE</span></span>
                        </button>
                        <button type="reset" class="btn btn-clear">
                            <span class="btn-content"><i class="fas fa-eraser"></i><span>CLEAR</span></span>
                        </button>
                        <button type="button" class="btn btn-export">
                            <span class="btn-content"><i class="fas fa-file-export"></i><span>EXPORT</span></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Student Table -->
            <!-- Mobile toolbar (hidden on md and up) -->
			<div class="d-flex gap-2 align-items-center mb-2 d-md-none" id="enrolleesToolbar">
				<input class="form-control form-control-sm" id="enrolleesSearch" placeholder="Search...">
				<select class="form-select form-select-sm" id="enrolleesFilter">
					<option value="">All</option>
					<option value="Active">Active</option>
					<option value="Inactive">Inactive</option>
                    <option value="Freeze">Freeze</option>
				</select>
			</div>

            <div class="table-container table-responsive enrollees-card">
                <div id="studentsDesktopToolbar" class="d-none d-md-flex justify-content-end align-items-center gap-2 table-toolbar">
                    <div class="search-container">
                        <input type="text" id="studentSearchBox" placeholder="Search students...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
                <table class="student-table table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>STD No.</th>
                            <th class="d-none d-md-table-cell">Date Enrolled</th>
                            <th>Fullname</th>
                            <th class="d-none d-md-table-cell">Address</th>
                            <th>Phone No.</th>
                            <th class="d-none d-md-table-cell">Email</th>
                            <th class="d-none d-md-table-cell">Gender</th>
                            <th class="d-none d-md-table-cell">School</th>
                            <th class="d-none d-md-table-cell">Parent's Name</th>
                            <th class="d-none d-md-table-cell">Parent's Phone</th>
                            <th class="d-none d-md-table-cell">Parent's Email</th>
                            <th class="d-none d-md-table-cell">Belt Rank</th>
                            <th class="d-none d-md-table-cell">Discount</th>
                            <th class="d-none d-md-table-cell">Schedule</th>
                            <th class="d-none d-md-table-cell">Class</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <!-- Data will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <script src="Scripts/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Map numeric belt ranks to labels and pass-through existing labels
    function mapBeltRankToLabel(value) {
        const mapping = {
            '0': 'White',
            '1': 'Yellow',
            '2': 'Green',
            '3': 'Blue',
            '4': 'Red',
            '5': 'Black'
        };
        const normalized = String(value ?? '').trim();
        if (['White', 'Yellow', 'Green', 'Blue', 'Red', 'Black'].includes(normalized)) return normalized;
        if (Object.prototype.hasOwnProperty.call(mapping, normalized)) return mapping[normalized];
        return normalized || '';
    }

    // Convert a jeja_no like "STD-00057" or "57" to its numeric value 57
    function toStdNum(s) {
        const raw = String(s || '')
            .replace(/^STD-?/i, '')
            .replace(/^0+/, '');
        const n = parseInt(raw, 10);
        return Number.isFinite(n) ? n : 0;
    }

    async function handleFormSubmit(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        
        try {
            const response = await fetch('save_student.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                alert(result.message);
                loadStudents(); // Reload the students table
                form.reset(); // Clear the form
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error submitting form: ' + error.message);
        }
        
        return false;
    }

    async function loadStudents() {
        try {
            const response = await fetch('get_students.php');
            const result = await response.json();
            
            if (result.status === 'success') {
                const tbody = document.getElementById('studentTableBody');
                tbody.innerHTML = ''; // Clear existing rows

                // Ensure STD No. is always in ascending order before rendering
                (result.data || []).sort((a, b) => toStdNum(a.jeja_no) - toStdNum(b.jeja_no));

                result.data.forEach(student => {
                    const beltLabel = mapBeltRankToLabel(student.belt_rank);
                    const discountNumber = parseFloat(student.discount);
                    const discountDisplay = isNaN(discountNumber) ? '0.00' : discountNumber.toFixed(2);
                    const statusText = (student.status || '').toString();
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : ''}</td>
                        <td class="d-none d-md-table-cell">${student.date_enrolled}</td>
                        <td>${student.full_name}</td>
                        <td class="d-none d-md-table-cell">${student.address}</td>
                        <td>${student.phone}</td>
                        <td class="d-none d-md-table-cell">${student.email || ''}</td>
                        <td class="d-none d-md-table-cell">${student.gender || ''}</td>
                        <td class="d-none d-md-table-cell">${student.school || ''}</td>
                        <td class="d-none d-md-table-cell">${student.parent_name || ''}</td>
                        <td class="d-none d-md-table-cell">${student.parent_phone || ''}</td>
                        <td class="d-none d-md-table-cell">${student.parent_email || ''}</td>
                        <td class="d-none d-md-table-cell">${beltLabel}</td>
                        <td class="d-none d-md-table-cell">₱${discountDisplay}</td>
                        <td class="d-none d-md-table-cell">${student.schedule}</td>
                        <td class="d-none d-md-table-cell">${student.class || ''}</td>
                        <td class="status-${statusText.toLowerCase()}">${statusText}</td>
                        <td>
                            <button onclick="editStudent('${student.jeja_no}')" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteStudent('${student.jeja_no}')" class="btn-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                // Re-apply active desktop search filter after reload
                const desktopSearch = document.getElementById('studentSearchBox');
                if (desktopSearch && desktopSearch.value) {
                    applyStudentSearch(desktopSearch.value);
                }
            }
        } catch (error) {
            console.error('Error loading students:', error);
        }
    }

    async function editStudent(jejaNo) {
        try {
            const response = await fetch(`get_students.php?jeja_no=${jejaNo}`);
            const result = await response.json();
            
            if (result.status === 'success' && result.data.length > 0) {
                const student = result.data[0];
                const form = document.getElementById('studentForm');
                
                // Fill the form with student data
                Object.keys(student).forEach(key => {
                    const input = form.elements[key];
                    if (input) {
                        input.value = student[key];
                    }
                });

                // Normalize belt rank to label selection if backend returns numeric values
                const beltInput = form.elements['belt_rank'];
                if (beltInput) {
                    beltInput.value = mapBeltRankToLabel(student.belt_rank);
                }

                // Ensure discount is displayed as a fixed 2-decimal number
                const discountInput = form.elements['discount'];
                if (discountInput) {
                    const dn = parseFloat(student.discount);
                    discountInput.value = isNaN(dn) ? '0.00' : dn.toFixed(2);
                }
            }
        } catch (error) {
            console.error('Error loading student details:', error);
        }
    }

    async function deleteStudent(jejaNo) {
        if (confirm('Are you sure you want to delete this student?')) {
            try {
                const response = await fetch('delete_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ jeja_no: jejaNo })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    alert(result.message);
                    loadStudents(); // Reload the table
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error deleting student: ' + error.message);
            }
        }
    }

    // Load students when the page loads
    document.addEventListener('DOMContentLoaded', loadStudents);

    // Desktop search: filter by STD No., Name, Gender, or Status
    function applyStudentSearch(term) {
        const searchTerm = (term || '').toLowerCase();
        const tbody = document.getElementById('studentTableBody');
        if (!tbody) return;
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(tr => {
            const cells = tr.querySelectorAll('td');
            const stdNo = (cells[0]?.textContent || '').toLowerCase();
            const name = (cells[2]?.textContent || '').toLowerCase();
            const gender = (cells[6]?.textContent || '').toLowerCase();
            const statusCell = tr.querySelector('td[class^="status-"]');
            const status = (statusCell?.textContent || '').toLowerCase();
            const matches = !searchTerm || [stdNo, name, gender, status].some(v => v.includes(searchTerm));
            tr.style.display = matches ? '' : 'none';
        });
    }

    // Initialize desktop search box
    document.addEventListener('DOMContentLoaded', function(){
        const desktopSearch = document.getElementById('studentSearchBox');
        if (desktopSearch) {
            desktopSearch.addEventListener('input', function(){
                applyStudentSearch(desktopSearch.value);
            });
        }
    });

    // Client-side search/filter for mobile toolbar
    (function setupEnrolleesToolbar(){
        const searchInput = document.getElementById('enrolleesSearch');
        const filterSelect = document.getElementById('enrolleesFilter');
        if(!searchInput || !filterSelect) return;

        function applyFilters(){
            const term = (searchInput.value || '').toLowerCase();
            const statusFilter = (filterSelect.value || '').toLowerCase();
            const tbody = document.getElementById('studentTableBody');
            if(!tbody) return;
            [...tbody.querySelectorAll('tr')].forEach(tr => {
                const text = tr.textContent.toLowerCase();
                const statusCell = tr.querySelector('[class^="status-"]');
                const rowStatus = statusCell ? statusCell.textContent.toLowerCase() : '';
                const matchesText = !term || text.includes(term);
                const matchesStatus = !statusFilter || rowStatus === statusFilter;
                tr.style.display = (matchesText && matchesStatus) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', applyFilters);
        filterSelect.addEventListener('change', applyFilters);
    })();

    // Export table (visible rows) to CSV, skipping the Actions column
    function exportStudentsToCSV(){
        const table = document.querySelector('.student-table');
        if(!table) return alert('No table to export.');

        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        if(!thead || !tbody) return alert('Table is missing header or body.');

        const headerCells = [...thead.querySelectorAll('th')];
        const actionsColIndex = headerCells.findIndex(th => (th.textContent || '').trim().toLowerCase() === 'actions');

        const headers = headerCells
            .map((th, idx) => idx === actionsColIndex ? null : (th.textContent || '').trim())
            .filter(v => v !== null);

        const rows = [];
        rows.push(headers);

        [...tbody.querySelectorAll('tr')].forEach(tr => {
            if (tr.style && tr.style.display === 'none') return; // skip filtered-out rows
            const cells = [...tr.querySelectorAll('td')];
            const row = cells
                .map((td, idx) => idx === actionsColIndex ? null : (td.textContent || '').trim())
                .filter(v => v !== null);
            rows.push(row);
        });

        function toCSVLine(fields){
            return fields.map(f => {
                const s = String(f ?? '');
                const needsQuote = /[",\n]/.test(s);
                const escaped = s.replace(/"/g, '""');
                return needsQuote ? `"${escaped}"` : escaped;
            }).join(',');
        }

        const csv = rows.map(toCSVLine).join('\n');
        const bom = '\uFEFF'; // Excel-friendly BOM
        const blob = new Blob([bom + csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        const date = new Date();
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth()+1).padStart(2,'0');
        const dd = String(date.getDate()).padStart(2,'0');
        a.href = url;
        a.download = `students_${yyyy}-${mm}-${dd}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // Hook up export button
    document.addEventListener('DOMContentLoaded', function(){
        const exportBtn = document.querySelector('.btn.btn-export');
        if (exportBtn) {
            exportBtn.addEventListener('click', exportStudentsToCSV);
        }
    });
    </script>
</body>
</html> 