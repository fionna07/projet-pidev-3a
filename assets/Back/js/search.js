function filterAchats() {
    const input = document.getElementById('searchbar');
    const filter = input.value.trim().toLowerCase();
    const tableBody = document.getElementById('tables');
    const rows = tableBody.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let rowMatch = false;

        for (let j = 0; j < cells.length; j++) {
            const cellValue = cells[j].textContent || cells[j].innerText;
            if (cellValue.toLowerCase().includes(filter)) {
                rowMatch = true;
                break;
            }
        }

        rows[i].style.display = rowMatch ? "" : "none";
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('searchbar').addEventListener('input', filterAchats);
});