document.getElementById('toggleOldPassword').addEventListener('click', function () {
    var oldPasswordInput = document.getElementById('oldpassWord');
    var toggleButton = document.getElementById('toggleOldPassword');

    if (oldPasswordInput.type === 'password') {
        oldPasswordInput.type = 'text';
        toggleButton.textContent = 'Hide';
    } else {
        oldPasswordInput.type = 'password';
        toggleButton.textContent = 'Show';
    }
});

document.getElementById('toggleNewPassword').addEventListener('click', function () {
    var newPasswordInput = document.getElementById('newpassWord');
    var toggleButton = document.getElementById('toggleNewPassword');

    if (newPasswordInput.type === 'password') {
        newPasswordInput.type = 'text';
        toggleButton.textContent = 'Hide';
    } else {
        newPasswordInput.type = 'password';
        toggleButton.textContent = 'Show';
    }
});