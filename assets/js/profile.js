document.addEventListener("DOMContentLoaded", () => {
    console.log("profile.js loaded");

    //#region PROFILE
        const editBtn = document.getElementById("editBtn");
        const editActions = document.getElementById("editActions");

        const fullNameInput = document.getElementById("fullNameInput");
        const emailInput = document.getElementById("emailInput");

        const cancelBtn = document.getElementById("cancelBtn");
        const saveBtn = document.getElementById("saveBtn");

        // Store original values
        let originalFullName = fullNameInput.value;
        let originalEmail = emailInput.value;

        let isSaving = false; // prevent multiple clicks

        // --- ENTER EDIT MODE ---
        editBtn.addEventListener("click", () => {
            editBtn.classList.add("hidden");
            editActions.classList.remove("hidden");

            fullNameInput.disabled = false;
            emailInput.disabled = false;

            fullNameInput.classList.add("border-blue-500");
            emailInput.classList.add("border-blue-500");
        });

        // --- CANCEL EDIT MODE ---
        cancelBtn.addEventListener("click", () => {
            if (isSaving) return; // ignore cancel while saving

            editBtn.classList.remove("hidden");
            editActions.classList.add("hidden");

            fullNameInput.disabled = true;
            emailInput.disabled = true;

            // Reset original values
            fullNameInput.value = originalFullName;
            emailInput.value = originalEmail;

            fullNameInput.classList.remove("border-blue-500");
            emailInput.classList.remove("border-blue-500");
        });

        // --- SAVE CHANGES ---
        saveBtn.addEventListener("click", async () => {
            if (isSaving) return; // prevent multiple saves
            isSaving = true;

            saveBtn.disabled = true;
            cancelBtn.disabled = true;

            const form = saveBtn.closest("form");
            if (form) {
                form.submit();
            }
        });
    //#endregion

    //#region PASSWORD
        const changeBtn = document.getElementById('changePasswordBtn');
        const cancelBtn_password = document.getElementById('cancelBtn_password');
        const passwordInputs = document.querySelectorAll('.password-input');
        const passwordRules = document.getElementById('passwordRules');
        const passwordActions = document.getElementById('passwordActions');
        const eyeToggles = document.querySelectorAll('.eye-toggle');

        const toggleInputs = (enabled) => {
            passwordInputs.forEach(input => input.disabled = !enabled);
        };

        // Show inputs and buttons when "Change Password" is clicked
        changeBtn.addEventListener('click', () => {
            toggleInputs(true);
            passwordRules.classList.add('hidden');
            passwordActions.classList.remove('hidden');
            changeBtn.classList.add('hidden');
        });

        // Cancel button resets everything
        cancelBtn_password.addEventListener('click', () => {
            toggleInputs(false);
            passwordRules.classList.remove('hidden');
            passwordActions.classList.add('hidden');
            passwordInputs.forEach(input => input.value = '');
            changeBtn.classList.remove('hidden');
        });

        // Toggle password visibility
        eyeToggles.forEach(eye => {
            eye.addEventListener('click', () => {
                const input = eye.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    eye.src = eye.dataset.eyeOff;
                } else {
                    input.type = 'password';
                    eye.src = eye.dataset.eye;
                }
            });
        });

        // new 2 pass not same
        const newInput = document.querySelector('input[name="new_password"]');
        const confirmInput = document.querySelector('input[name="confirm_new_password"]');

        confirmInput.addEventListener('input', () => {
            if (confirmInput.value !== newInput.value) {
                confirmInput.setCustomValidity("Passwords do not match");
            } else {
                confirmInput.setCustomValidity("");
            }
        });
    //#endregion
})