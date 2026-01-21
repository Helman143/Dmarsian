// Sidebar dropdown
function toggleDropdown() {
    var content = document.getElementById('dropdown-content');
    var arrow = document.getElementById('arrow');
    if (content.style.display === 'flex') {
        content.style.display = 'none';
        arrow.innerHTML = '\u25BC';
    } else {
        content.style.display = 'flex';
        arrow.innerHTML = '\u25B2';
    }
}
// Table search
function searchTable(tableId, value) {
    var table = document.getElementById(tableId);
    var filter = value.toUpperCase();
    var trs = table.getElementsByTagName('tr');
    for (var i = 1; i < trs.length; i++) {
        var tds = trs[i].getElementsByTagName('td');
        var show = false;
        for (var j = 0; j < tds.length; j++) {
            if (tds[j].innerText.toUpperCase().indexOf(filter) > -1) {
                show = true;
                break;
            }
        }
        trs[i].style.display = show ? '' : 'none';
    }
} 