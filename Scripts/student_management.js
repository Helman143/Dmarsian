let globalStudents = [];
let currentStudentPage = 1;
const studentsPerPage = 10;

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
            
            if (typeof BroadcastChannel !== 'undefined') {
                const channel = new BroadcastChannel('student-status-updates');
                channel.postMessage({ type: 'student-status-updated', timestamp: Date.now() });
                channel.close();
            }
            try {
                localStorage.setItem('student-status-update-trigger', Date.now().toString());
                setTimeout(() => {
                    localStorage.removeItem('student-status-update-trigger');
                }, 100);
            } catch (e) {
                console.warn('localStorage not available for student status updates:', e);
            }
            if (typeof window.dispatchEvent !== 'undefined') {
                window.dispatchEvent(new CustomEvent('student-status-updated', {
                    detail: { timestamp: Date.now() }
                }));
            }
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

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Expected JSON but got:', text.substring(0, 200));
            throw new Error('Server returned non-JSON response. Check console for details.');
        }
        
        const result = await response.json();
        
        if (result.status === 'success') {
            globalStudents = result.data || [];
            globalStudents.sort((a, b) => toStdNum(a.jeja_no) - toStdNum(b.jeja_no));
            applyStudentFilters(); 
        }
    } catch (error) {
        console.error('Error loading students:', error);
        const tbody = document.getElementById('studentTableBody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="17" style="text-align: center; padding: 20px; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Error loading students: ${error.message}
                        <br><small>Please refresh the page or contact support if the problem persists.</small>
                    </td>
                </tr>
            `;
        }
        if (error.message.includes('JSON') || error.message.includes('504')) {
            alert('Failed to load students. The server may be experiencing issues. Please try again in a moment.');
        }
    }
}

function applyStudentFilters() {
    const desktopTerm = (document.getElementById('studentSearchBox')?.value || '').toLowerCase();
    const mobileTerm = (document.getElementById('enrolleesSearch')?.value || '').toLowerCase();
    const term = desktopTerm || mobileTerm;
    
    const statusFilterSelect = document.getElementById('enrolleesFilter');
    const statusFilter = statusFilterSelect && statusFilterSelect.offsetParent !== null ? (statusFilterSelect.value || '').toLowerCase() : '';

    const filtered = globalStudents.filter(student => {
        const stdNo = (student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : '').toLowerCase();
        const name = (student.full_name || '').toLowerCase();
        const gender = (student.gender || '').toLowerCase();
        const status = (student.status || '').toLowerCase();
        
        const matchesText = !term || [stdNo, name, gender, status].some(v => v.includes(term));
        const matchesStatus = !statusFilter || status === statusFilter;
        
        return matchesText && matchesStatus;
    });

    renderStudentTable(filtered);
}

function renderStudentTable(filteredStudents) {
    const tbody = document.getElementById('studentTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    const totalItems = filteredStudents.length;
    const totalPages = Math.ceil(totalItems / studentsPerPage) || 1;
    if (currentStudentPage > totalPages) currentStudentPage = totalPages;

    const startIdx = (currentStudentPage - 1) * studentsPerPage;
    const pageData = filteredStudents.slice(startIdx, startIdx + studentsPerPage);

    pageData.forEach(student => {
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
            <td class="d-none d-md-table-cell">${student.gender || '—'}</td>
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

    renderStudentPagination(totalItems, totalPages);
}

function renderStudentPagination(totalItems, totalPages) {
    const container = document.getElementById('studentPagination');
    if (!container) return;
    
    if (totalItems <= studentsPerPage) {
        container.innerHTML = '';
        return;
    }

    let html = '<div class="pagination">';
    html += `<button class="page-btn" ${currentStudentPage === 1 ? 'disabled' : ''} onclick="changeStudentPage(${currentStudentPage - 1})"><i class="fas fa-chevron-left"></i></button>`;
    
    let startPage = Math.max(1, currentStudentPage - 1);
    let endPage = Math.min(totalPages, startPage + 2);
    if (endPage - startPage < 2 && startPage > 1) {
        startPage = Math.max(1, endPage - 2);
    }
    
    if (startPage > 1) {
        html += `<button class="page-link" onclick="changeStudentPage(1)">1</button>`;
        if (startPage > 2) html += `<span class="pager-dots">...</span>`;
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === currentStudentPage ? 'active' : '';
        html += `<button class="page-link ${activeClass}" onclick="changeStudentPage(${i})">${i}</button>`;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += `<span class="pager-dots">...</span>`;
        html += `<button class="page-link" onclick="changeStudentPage(${totalPages})">${totalPages}</button>`;
    }
    
    html += `<button class="page-btn" ${currentStudentPage === totalPages ? 'disabled' : ''} onclick="changeStudentPage(${currentStudentPage + 1})"><i class="fas fa-chevron-right"></i></button>`;
    html += '</div>';
    
    container.innerHTML = html;
}

window.changeStudentPage = function(newPage) {
    const desktopTerm = (document.getElementById('studentSearchBox')?.value || '').toLowerCase();
    const mobileTerm = (document.getElementById('enrolleesSearch')?.value || '').toLowerCase();
    const term = desktopTerm || mobileTerm;
    const statusFilterSelect = document.getElementById('enrolleesFilter');
    const statusFilter = statusFilterSelect && statusFilterSelect.offsetParent !== null ? (statusFilterSelect.value || '').toLowerCase() : '';

    const filtered = globalStudents.filter(student => {
        const stdNo = (student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : '').toLowerCase();
        const name = (student.full_name || '').toLowerCase();
        const gender = (student.gender || '').toLowerCase();
        const status = (student.status || '').toLowerCase();
        const matchesText = !term || [stdNo, name, gender, status].some(v => v.includes(term));
        const matchesStatus = !statusFilter || status === statusFilter;
        return matchesText && matchesStatus;
    });

    const totalPages = Math.ceil(filtered.length / studentsPerPage) || 1;
    if (newPage < 1) newPage = 1;
    if (newPage > totalPages) newPage = totalPages;
    
    currentStudentPage = newPage;
    renderStudentTable(filtered);
};


async function editStudent(jejaNo) {
    try {
        const response = await fetch(`get_students.php?jeja_no=${jejaNo}`);
        const result = await response.json();
        
        if (result.status === 'success' && result.data.length > 0) {
            const student = result.data[0];
            const form = document.getElementById('studentForm');
            
            Object.keys(student).forEach(key => {
                const input = form.elements[key];
                if (input) {
                    input.value = student[key];
                }
            });

            const beltInput = form.elements['belt_rank'];
            if (beltInput) {
                beltInput.value = mapBeltRankToLabel(student.belt_rank);
            }

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
                loadStudents(); 
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error deleting student: ' + error.message);
        }
    }
}

document.addEventListener('DOMContentLoaded', loadStudents);

if (typeof BroadcastChannel !== 'undefined') {
    const enrollmentChannel = new BroadcastChannel('enrollment-updates');
    enrollmentChannel.addEventListener('message', (event) => {
        if (event.data && event.data.type === 'enrollment_approved') {
            console.log('Enrollment approved in another tab, refreshing student list...');
            loadStudents();
        }
    });
}

document.addEventListener('DOMContentLoaded', function(){
    const desktopSearch = document.getElementById('studentSearchBox');
    if (desktopSearch) {
        desktopSearch.addEventListener('input', function(){
            currentStudentPage = 1;
            applyStudentFilters();
        });
    }

    const searchInput = document.getElementById('enrolleesSearch');
    const filterSelect = document.getElementById('enrolleesFilter');
    if(searchInput) {
        searchInput.addEventListener('input', () => { currentStudentPage = 1; applyStudentFilters(); });
    }
    if(filterSelect) {
        filterSelect.addEventListener('change', () => { currentStudentPage = 1; applyStudentFilters(); });
    }

    const exportBtn = document.querySelector('.btn.btn-export');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportStudentsToCSV);
    }
});

function exportStudentsToCSV(){
    if(!globalStudents || globalStudents.length === 0) return alert('No students to export.');

    const desktopTerm = (document.getElementById('studentSearchBox')?.value || '').toLowerCase();
    const mobileTerm = (document.getElementById('enrolleesSearch')?.value || '').toLowerCase();
    const term = desktopTerm || mobileTerm;
    const statusFilterSelect = document.getElementById('enrolleesFilter');
    const statusFilter = statusFilterSelect && statusFilterSelect.offsetParent !== null ? (statusFilterSelect.value || '').toLowerCase() : '';

    const filtered = globalStudents.filter(student => {
        const stdNo = (student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : '').toLowerCase();
        const name = (student.full_name || '').toLowerCase();
        const gender = (student.gender || '').toLowerCase();
        const status = (student.status || '').toLowerCase();
        const matchesText = !term || [stdNo, name, gender, status].some(v => v.includes(term));
        const matchesStatus = !statusFilter || status === statusFilter;
        return matchesText && matchesStatus;
    });

    if (filtered.length === 0) return alert('No students match the current filters.');

    const headers = [
        "STD No.", "Date Enrolled", "Fullname", "Address", "Phone No.", 
        "Email", "Gender", "School", "Parent's Name", "Parent's Phone", 
        "Parent's Email", "Belt Rank", "Discount", "Schedule", "Class", "Status"
    ];

    const rows = [];
    rows.push(headers);

    filtered.forEach(student => {
        const beltLabel = mapBeltRankToLabel(student.belt_rank);
        const discountNumber = parseFloat(student.discount);
        const discountDisplay = isNaN(discountNumber) ? '0.00' : discountNumber.toFixed(2);
        
        rows.push([
            student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : '',
            student.date_enrolled,
            student.full_name,
            student.address,
            student.phone,
            student.email || '',
            student.gender || '—',
            student.school || '',
            student.parent_name || '',
            student.parent_phone || '',
            student.parent_email || '',
            beltLabel,
            `₱${discountDisplay}`,
            student.schedule,
            student.class || '',
            student.status
        ]);
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
    const bom = '\uFEFF'; 
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