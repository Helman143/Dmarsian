document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editProfileBtn');
    const saveBtn = document.getElementById('saveProfileBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const inputs = [
        document.getElementById('email'),
        document.getElementById('username'),
        document.getElementById('password')
    ];
    let originalValues = inputs.map(input => input.value);

    editBtn.addEventListener('click', function() {
        inputs.forEach(input => input.disabled = false);
        editBtn.style.display = 'none';
        saveBtn.style.display = 'inline-block';
        cancelBtn.style.display = 'inline-block';
    });

    cancelBtn.addEventListener('click', function() {
        inputs.forEach((input, i) => {
            input.value = originalValues[i];
            input.disabled = true;
        });
        editBtn.style.display = 'inline-block';
        saveBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
    });

    document.getElementById('profileForm').addEventListener('submit', function(e) {
        // Allow default form submission to server for PHP handling
        // originalValues = inputs.map(input => input.value);
        // inputs.forEach(input => input.disabled = true);
        // editBtn.style.display = 'inline-block';
        // saveBtn.style.display = 'none';
        // cancelBtn.style.display = 'none';
        // alert('Profile updated! (Demo only, not saved to server)');
    });
}); 