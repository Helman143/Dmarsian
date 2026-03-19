let globalStudents = [];
let currentStudentPage = 1;
const studentsPerPage = 10;

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

function toStdNum(s) {
    const raw = String(s || '').replace(/^STD-?/i, '').replace(/^0+/, '');
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
            Swal.fire({
                title: 'Success!',
                text: result.message,
                icon: 'success',
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#00ff6a'
            });
            loadStudents(); 
            form.reset(); 
            
            if (typeof BroadcastChannel !== 'undefined') {
                const channel = new BroadcastChannel('student-status-updates');
                channel.postMessage({ type: 'student-status-updated', timestamp: Date.now() });
                channel.close();
            }
            try {
                localStorage.setItem('student-status-update-trigger', Date.now().toString());
                setTimeout(() => { localStorage.removeItem('student-status-update-trigger'); }, 100);
            } catch (e) {}
            if (typeof window.dispatchEvent !== 'undefined') {
                window.dispatchEvent(new CustomEvent('student-status-updated', { detail: { timestamp: Date.now() } }));
            }
        } else {
            Swal.fire({
                title: 'Error',
                text: result.message,
                icon: 'error',
                background: '#1a1a1a',
                color: '#fff'
            });
        }
    } catch (error) {
        Swal.fire({
            title: 'Error',
            text: 'Error submitting form: ' + error.message,
            icon: 'error',
            background: '#1a1a1a',
            color: '#fff'
        });
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
        const cardList = document.getElementById('adminStudentCardList');
        
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="15" style="text-align: center; padding: 20px; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle"></i> Error loading students: ${error.message}
                    </td>
                </tr>
            `;
        }
        if (cardList) {
            cardList.innerHTML = `<div style="text-align: center; padding: 20px; color: #dc3545;">Error: ${error.message}</div>`;
        }
        
        if (error.message.includes('JSON') || error.message.includes('504')) {
            Swal.fire({
                title: 'Error',
                text: 'Failed to load students. Server may be down.',
                icon: 'error',
                background: '#1a1a1a',
                color: '#fff'
            });
        }
    }
}

function applyStudentFilters() {
    const desktopTerm = (document.getElementById('studentSearchBox')?.value || '').toLowerCase();
    const mobileTerm = (document.getElementById('enrolleesSearch')?.value || '').toLowerCase();
    const term = desktopTerm || mobileTerm;
    
    const statusFilterSelect = document.getElementById('enrolleesFilter');
    const statusFilter = statusFilterSelect ? (statusFilterSelect.value || '').toLowerCase() : '';

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
    const cardList = document.getElementById('adminStudentCardList');
    if (tbody) tbody.innerHTML = '';
    if (cardList) cardList.innerHTML = '';

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
        const stdNo = student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : '';

        // Table Row (Desktop)
        if (tbody) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${stdNo}</td>
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
                    <button onclick="editStudent('${student.jeja_no}')" class="btn-edit"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteStudent('${student.jeja_no}')" class="btn-delete"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(row);
        }

        // Card (Mobile)
        if (cardList) {
            const card = document.createElement('div');
            card.className = 'student-card';
            card.innerHTML = `
                <div class="card-header">
                    <div class="title">${student.full_name}</div>
                    <div class="meta">STD ${stdNo} • ${student.phone} • <span class="status-${statusText.toLowerCase()}">${statusText}</span></div>
                </div>
                <div class="card-actions">
                    <button class="btn-edit" onclick="editStudent('${student.jeja_no}')" aria-label="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn-delete" onclick="deleteStudent('${student.jeja_no}')" aria-label="Delete"><i class="fas fa-trash"></i></button>
                    <button class="btn-toggle" aria-label="More"><i class="fas fa-chevron-down"></i></button>
                </div>
                <div class="card-body" hidden>
                    <div><strong>Email:</strong> ${student.email || ''}</div>
                    <div><strong>Gender:</strong> ${student.gender || ''}</div>
                    <div><strong>Address:</strong> ${student.address || ''}</div>
                    <div><strong>School:</strong> ${student.school || ''}</div>
                    <div><strong>Parent:</strong> ${student.parent_name || ''} (${student.parent_phone || ''})</div>
                    <div><strong>Belt:</strong> ${beltLabel || ''}</div>
                    <div><strong>Discount:</strong> ₱${discountDisplay}</div>
                    <div><strong>Schedule:</strong> ${student.schedule || ''}</div>
                    <div><strong>Class:</strong> ${student.class || ''}</div>
                </div>
            `;
            card.querySelector('.btn-toggle').addEventListener('click', () => {
                const body = card.querySelector('.card-body');
                const icon = card.querySelector('.btn-toggle i');
                const isHidden = body.hasAttribute('hidden');
                if (isHidden) {
                    body.removeAttribute('hidden');
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    body.setAttribute('hidden', '');
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            });
            cardList.appendChild(card);
        }
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
    const statusFilter = statusFilterSelect ? (statusFilterSelect.value || '').toLowerCase() : '';

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
                if (input) input.value = student[key];
            });

            const beltInput = form.elements['belt_rank'];
            if (beltInput) beltInput.value = mapBeltRankToLabel(student.belt_rank);

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
    const result = await Swal.fire({
        title: 'Delete Student?',
        text: "Are you sure you want to delete this student?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#00ff6a',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete!',
        background: '#1a1a1a',
        color: '#fff'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch('delete_student.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ jeja_no: jejaNo })
            });
            const res = await response.json();
            
            if (res.status === 'success') {
                Swal.fire({
                    title: 'Deleted!',
                    text: res.message,
                    icon: 'success',
                    background: '#1a1a1a',
                    color: '#fff',
                    confirmButtonColor: '#00ff6a'
                });
                loadStudents(); 
            } else {
                Swal.fire({
                    title: 'Error',
                    text: res.message,
                    icon: 'error',
                    background: '#1a1a1a',
                    color: '#fff'
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'Error',
                text: 'Error deleting student: ' + error.message,
                icon: 'error',
                background: '#1a1a1a',
                color: '#fff'
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', loadStudents);

if (typeof BroadcastChannel !== 'undefined') {
    const enrollmentChannel = new BroadcastChannel('enrollment-updates');
    enrollmentChannel.addEventListener('message', (event) => {
        if (event.data && event.data.type === 'enrollment_approved') {
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
    if(!globalStudents || globalStudents.length === 0) {
        Swal.fire({ title: 'Note', text: 'No students to export.', icon: 'info', background: '#1a1a1a', color: '#fff' });
        return;
    }

    const desktopTerm = (document.getElementById('studentSearchBox')?.value || '').toLowerCase();
    const mobileTerm = (document.getElementById('enrolleesSearch')?.value || '').toLowerCase();
    const term = desktopTerm || mobileTerm;
    const statusFilterSelect = document.getElementById('enrolleesFilter');
    const statusFilter = statusFilterSelect ? (statusFilterSelect.value || '').toLowerCase() : '';

    const filtered = globalStudents.filter(student => {
        const stdNo = (student.jeja_no ? student.jeja_no.replace(/^STD-/, '') : '').toLowerCase();
        const name = (student.full_name || '').toLowerCase();
        const gender = (student.gender || '').toLowerCase();
        const status = (student.status || '').toLowerCase();
        const matchesText = !term || [stdNo, name, gender, status].some(v => v.includes(term));
        const matchesStatus = !statusFilter || status === statusFilter;
        return matchesText && matchesStatus;
    });

    if (filtered.length === 0) {
        Swal.fire({ title: 'Note', text: 'No students match filters.', icon: 'info', background: '#1a1a1a', color: '#fff' });
        return;
    }

    const headers = [
        "STD No.", "Date Enrolled", "Fullname", "Address", "Phone No.", 
        "Email", "Gender", "School", "Parent's Name", "Parent's Phone", 
        "Parent's Email", "Belt Rank", "Discount", "Schedule", "Class", "Status"
    ];

    const rows = [headers];

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