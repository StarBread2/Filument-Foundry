document.addEventListener("DOMContentLoaded", () => 
{
    console.log("material.js loaded");

    // ************************************ ADD FINISH MODAL ************************************
        const addBtn = document.getElementById("button_modal_addFinishes");
        const addModal = document.getElementById("modal_addFinishes");
        const closeAdd1 = document.getElementById("closeFinishModalBtn");
        const closeAdd2 = document.getElementById("closeFinishModalBtn2");

        if (addBtn && addModal) 
        {
            addBtn.addEventListener("click", () => 
            {
                addModal.classList.remove("hidden");
                console.log("opened finish modal");
            });

            closeAdd1?.addEventListener("click", () => 
            {
                addModal.classList.add("hidden");
                console.log("closed finish modal via X");
            });

            closeAdd2?.addEventListener("click", () => 
            {
                addModal.classList.add("hidden");
                console.log("closed finish modal via Cancel");
            });
        }

        // ************************************ FILE DROPPER ************************************
        const dropzone = document.getElementById("dropzoneFinish");
        const imageInput = document.getElementById("finishImageInput");
        const previewImage = document.getElementById("finishPreviewImage");

        if (dropzone && imageInput) 
        {
            dropzone.addEventListener("click", () => imageInput.click());

            imageInput.addEventListener("change", (e) => 
            {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (event) => 
                    {
                        previewImage.src = event.target.result;
                        previewImage.classList.remove("hidden");
                    };
                    reader.readAsDataURL(file);
                }
            });

            ["dragenter", "dragover"].forEach((eventName) => 
            {
                dropzone.addEventListener(eventName, (e) => 
                {
                    e.preventDefault();
                    dropzone.classList.add("bg-orange-50", "border-orange-400");
                });
            });

            ["dragleave", "drop"].forEach((eventName) => 
            {
                dropzone.addEventListener(eventName, (e) => 
                {
                    e.preventDefault();
                    dropzone.classList.remove("bg-orange-50", "border-orange-400");
                });
            });

            dropzone.addEventListener("drop", (e) => 
            {
                e.preventDefault();
                const file = e.dataTransfer.files[0];
                if (file) 
                {
                    imageInput.files = e.dataTransfer.files;
                    const reader = new FileReader();
                    reader.onload = (event) => 
                    {
                        previewImage.src = event.target.result;
                        previewImage.classList.remove("hidden");
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    // ************************************ ADD FINISH MODAL ************************************

    // ************************************ DELETE MODAL ************************************
        const modalFinishes = document.getElementById("deleteModal_finishes");
        const formFinishes = document.getElementById("deleteFormFinishes");
        const deleteMessageFinishes = document.getElementById("deleteMessageFinishes");
        const cancelBtnFinishes = document.getElementById("cancelDeleteBtnFinishes");
        const closeBtnFinishes = document.getElementById("closeDeleteModal_finishes");

        if (modalFinishes && formFinishes && deleteMessageFinishes) 
        {
            document.querySelectorAll(".deleteFinishBtn").forEach(btn => 
            {
                btn.addEventListener("click", () => {
                    const deleteUrl = btn.dataset.deleteUrl;
                    const finishName = btn.dataset.finishName;

                    formFinishes.action = deleteUrl;
                    deleteMessageFinishes.textContent = `Are you sure you want to delete "${finishName}"? This action cannot be undone.`;
                    modalFinishes.classList.remove("hidden");
                });
            });

            [cancelBtnFinishes, closeBtnFinishes].forEach(button => 
            {
                button?.addEventListener("click", () => modalFinishes.classList.add("hidden"));
            });
        }
    // ************************************ DELETE MODAL ************************************

    // ************************************EDIT FINISH MODAL************************************
        const editFinishModal = document.getElementById("modal_editFinish");
        const editFinishForm = document.getElementById("editFinishForm");

        if (editFinishModal && editFinishForm) 
        {
            document.querySelectorAll(".editFinishBtn").forEach(btn => 
            {
                btn.addEventListener("click", () => 
                {
                    const id = btn.dataset.id;
                    const name = btn.dataset.name;
                    const details = btn.dataset.details;
                    const price = btn.dataset.price;
                    const properties = btn.dataset.properties;
                    const availability = btn.dataset.availability === "1";
                    const image = btn.dataset.image;

                    // Fill fields
                    document.getElementById("edit-finish-name").value = name;
                    document.getElementById("edit-finish-details").value = details;
                    document.getElementById("edit-finish-price").value = price;
                    document.getElementById("edit-finish-properties").value = properties;
                    document.getElementById("edit-finish-available").checked = availability;

                    // Handle image display
                    const imgEl = document.getElementById("edit-finish-current-image");
                    const removeCheckbox = document.getElementById("edit-finish-remove-image");
                    const noImageText = document.getElementById("edit-finish-no-image-text");

                    if (image) 
                    {
                        imgEl.src = image.startsWith("/") ? image : "/" + image;
                        imgEl.classList.remove("hidden");
                        removeCheckbox.parentElement.classList.remove("hidden");
                        noImageText.classList.add("hidden");
                    } 
                    else 
                    {
                        imgEl.src = "";
                        imgEl.classList.add("hidden");
                        removeCheckbox.parentElement.classList.add("hidden");
                        noImageText.classList.remove("hidden");
                    }

                    // Reset file input
                    const newImageInput = document.getElementById("edit-finish-image");
                    newImageInput.value = "";
                    newImageInput.addEventListener("change", () => 
                    {
                        if (newImageInput.files.length > 0) 
                        {
                            removeCheckbox.checked = false;
                        }
                    });

                    // Set form action
                    editFinishForm.action = `/admin/edit-finish/${id}`;

                    // Show modal
                    editFinishModal.classList.remove("hidden");
                });
            });

            // Close modal events
            document.getElementById("closeEditModalBtn_finish")?.addEventListener("click", () => 
            {
                editFinishModal.classList.add("hidden");
            });
            document.getElementById("cancelEditBtn_finish")?.addEventListener("click", () => 
            {
                editFinishModal.classList.add("hidden");
            });
        }
    // ************************************EDIT FINISH MODAL************************************

    // ************************************SEARCH BAR************************************
        document.getElementById("searchInput_finishes").addEventListener("input", function () {
            const searchValue_finishes = this.value.toLowerCase();
            const cards_finishes = document.querySelectorAll(".finish-card_finishes");

            cards_finishes.forEach(card => {
                const name_finishes = card.dataset.name_finishes;
                const details_finishes = card.dataset.details_finishes;
                const properties_finishes = card.dataset.properties_finishes;
                const price_finishes = card.dataset.price_finishes;

                const matches_finishes =
                    name_finishes.includes(searchValue_finishes) ||
                    details_finishes.includes(searchValue_finishes) ||
                    properties_finishes.includes(searchValue_finishes) ||
                    price_finishes.includes(searchValue_finishes);

                card.style.display = matches_finishes ? "flex" : "none";
            });
        });
    // ************************************SEARCH BAR************************************
});