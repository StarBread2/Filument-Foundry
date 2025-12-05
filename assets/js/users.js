document.addEventListener("DOMContentLoaded", () => {
    console.log("users.js loaded");

    // ************************************ADD MODAL************************************
        const addBtn = document.getElementById("button_modal_addUser_users");
        const addModal = document.getElementById("modal_addUser");
        const closeAdd1 = document.getElementById("closeModalBtn");
        const closeAdd2 = document.getElementById("closeModalBtn2");

        if (!addBtn || !addModal) return; // Stop if not on users page

        addBtn.addEventListener("click", () => addModal.classList.remove("hidden"));
        closeAdd1?.addEventListener("click", () => addModal.classList.add("hidden"));
        closeAdd2?.addEventListener("click", () => addModal.classList.add("hidden"));

        //SHOW AND HIDE PASSWORD
        const togglePassword = document.getElementById("toggle-password");
        const passwordInput = document.getElementById("password-input");
        const eyeOpen = document.getElementById("eye-open");
        const eyeClosed = document.getElementById("eye-closed");

        if (togglePassword && passwordInput) 
        {
            togglePassword.addEventListener("click", () => 
            {
                const isPassword = passwordInput.type === "password";
                passwordInput.type = isPassword ? "text" : "password";
                eyeOpen.classList.toggle("hidden", !isPassword);
                eyeClosed.classList.toggle("hidden", isPassword);
            });
        }
    // ************************************ADD MODAL************************************



    // ************************************DELETE MODAL************************************
        const modal = document.getElementById("deleteModal_user");
        const form = document.getElementById("deleteForm");
        const deleteMessage = document.getElementById("deleteMessage");
        const cancelBtn = document.getElementById("cancelDeleteBtn");
        const closeBtn = document.getElementById("closeDeleteModal_user");

        if (modal && form && deleteMessage) {
            document.querySelectorAll(".deleteUserBtn").forEach(btn => 
            {
                btn.addEventListener("click", () => 
                {
                    const deleteUrl = btn.dataset.deleteUrl;
                    const username = btn.dataset.username;

                    form.action = deleteUrl;
                    deleteMessage.textContent = `Are you sure you want to delete "${username}"? This action cannot be undone.`;
                    modal.classList.remove("hidden");
                });
            });

            [cancelBtn, closeBtn].forEach(button => 
            {
                button?.addEventListener("click", () => modal.classList.add("hidden"));
            });
        }
    // ************************************DELETE MODAL************************************



    // ************************************EDIT MODAL************************************
        const editModal = document.getElementById("modal_editUser");
        const formEdit = document.getElementById("editUserForm");
        const closeEdit = document.getElementById("closeEditModalBtn");
        const cancelEdit = document.getElementById("cancelEditBtn");

        if (editModal && formEdit) {
            document.querySelectorAll(".editUserBtn").forEach(button => {
                button.addEventListener("click", () => {
                    const { id, email, fullname, address, role } = button.dataset;

                    document.getElementById("edit-email").value = email;
                    document.getElementById("edit-fullName").value = fullname;
                    document.getElementById("edit-address").value = address;
                    document.getElementById("edit-role").value = role;

                    formEdit.action = `/admin/edit-user/${id}`;
                    editModal.classList.remove("hidden");
                });
            });

            [closeEdit, cancelEdit].forEach(btn =>
                btn?.addEventListener("click", () => editModal.classList.add("hidden"))
            );
        }

        // ****** Change Password Toggle ******
        const toggleBtn = document.getElementById("toggleChangePassword");
        const passwordContainer = document.getElementById("changePasswordContainer");

        if (toggleBtn && passwordContainer) {
            toggleBtn.addEventListener("click", () => passwordContainer.classList.toggle("hidden"));
        }

        const togglePass = document.getElementById("toggle-edit-password");
        const inputPass = document.getElementById("edit-password");
        const eyeOpenEdit = document.getElementById("eye-open-edit");
        const eyeClosedEdit = document.getElementById("eye-closed-edit");

        if (togglePass && inputPass) 
        {
            togglePass.addEventListener("click", () => 
            {
                const isHidden = inputPass.type === "password";
                inputPass.type = isHidden ? "text" : "password";
                eyeOpenEdit.classList.toggle("hidden");
                eyeClosedEdit.classList.toggle("hidden");
            });
        }
    // ************************************EDIT MODAL************************************

    // ************************************SAERCH BAR************************************
    //  <!-- DataTable Initialization -->
    // Initialize DataTable
        var table = $('#usersTable').DataTable({
            pageLength: 10,
            order: [[0, 'asc']],
            language: {
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ users",
                paginate: { previous: "←", next: "→" },
                emptyTable: "No users found."
            },
            dom: 'lrtip', // Remove default search input
            stripeClasses: [], // Remove default stripe classes
        });

        // Custom search input
        $('input[placeholder="Search user..."]').on('keyup', function () {
            table.search(this.value).draw();
        });

        // Role filter
        $('select').on('change', function () {
            var role = $(this).val();
            if (role === "All Roles") {
                table.columns(3).search('').draw(); // Clear role filter
            } else {
                table.columns(3).search(role.toLowerCase()).draw(); // Search by role column
            }
        });
    // ************************************SAERCH BAR************************************
});
