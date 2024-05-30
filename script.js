document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('group').addEventListener('change', populateNames);
});

function populateNames() {
    const groupId = document.getElementById('group').value;
    const nameSelect = document.getElementById('name');
    nameSelect.innerHTML = ''; // Clear existing options

    // Fetch names based on group selection from the database using AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_names.php?group_id=' + groupId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const names = JSON.parse(xhr.responseText);
            names.forEach(function(name) {
                const option = document.createElement('option');
                option.value = name.id;
                option.textContent = name.name;
                nameSelect.appendChild(option);
            });
        } else {
            console.error('Failed to fetch names');
        }
    };
    xhr.send();
}
