document.addEventListener("DOMContentLoaded", () => 
{
    console.log("materials.js loaded ✅");

    //************************************ADD MODAL************************************
        const addBtn = document.getElementById("button_modal_addMaterials");
        const addModal = document.getElementById("modal_addmaterials");
        const closeAdd1 = document.getElementById("closeModalBtn");
        const closeAdd2 = document.getElementById("closeModalBtn2");

        if (addBtn && addModal) 
        {
            addBtn.addEventListener("click", () => 
            {
                addModal.classList.remove("hidden");
                console.log("opened modal");
            });

            closeAdd1?.addEventListener("click", () => 
            {
                addModal.classList.add("hidden");
                console.log("closed modal via X");
            });

            closeAdd2?.addEventListener("click", () => 
            {
                addModal.classList.add("hidden");
                console.log("closed modal via Cancel");
            });
        }
        

        // ************************************File Dropper************************************
        const dropzone = document.getElementById("dropzone");
        const imageInput = document.getElementById("imageInput");
        const previewImage = document.getElementById("previewImage");

        if (dropzone && imageInput) {
            // Open file browser on click
            dropzone.addEventListener("click", () => imageInput.click());

            // Preview on file select
            imageInput.addEventListener("change", (e) => 
            {
                const file = e.target.files[0];
                if (file) 
                {
                    const reader = new FileReader();
                    reader.onload = (event) => 
                    {
                        previewImage.src = event.target.result;
                        previewImage.classList.remove("hidden");
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Drag and drop functionality
            ["dragenter", "dragover"].forEach(eventName => 
            {
                dropzone.addEventListener(eventName, (e) => 
                {
                    e.preventDefault();
                    dropzone.classList.add("bg-orange-50", "border-orange-400");
                });
            });

            ["dragleave", "drop"].forEach(eventName => 
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
    //************************************ADD MODAL************************************



    // ************************************DELETE MODAL************************************
        const modal = document.getElementById("deleteModal_materials");
        const form = document.getElementById("deleteForm");
        const deleteMessage = document.getElementById("deleteMessage");
        const cancelBtn = document.getElementById("cancelDeleteBtn");
        const closeBtn = document.getElementById("closeDeleteModal_materials");

        if (modal && form && deleteMessage) 
        {
            document.querySelectorAll(".deleteMaterialBtn").forEach(btn => 
            {
                btn.addEventListener("click", () => {
                    const deleteUrl = btn.dataset.deleteUrl;
                    const materialName = btn.dataset.materialName;

                    form.action = deleteUrl;
                    deleteMessage.textContent = `Are you sure you want to delete "${materialName}"? This action cannot be undone.`;
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
        const editModal = document.getElementById("modal_editMaterial");
        const editForm = document.getElementById("editMaterialForm");

        if (editModal && editForm) 
        {
            document.querySelectorAll(".editMaterialBtn").forEach(btn => 
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

                    // Fill in modal fields
                    document.getElementById("edit-material-name").value = name;
                    document.getElementById("edit-description").value = details;
                    document.getElementById("edit-price").value = price;
                    document.getElementById("edit-properties").value = properties;
                    document.getElementById("edit-available").checked = availability;

                    // Elements
                    const imgEl = document.getElementById("edit-current-image");
                    const removeCheckbox = document.getElementById("remove-image");
                    const removeWrapper = removeCheckbox.closest("div.flex");
                    const noImageText = document.getElementById("no-image-text");

                    // Show/hide based on whether image exists
                    if (image) 
                    {
                        imgEl.src = image.startsWith("/") ? image : "/" + image;
                        imgEl.classList.remove("hidden");
                        removeWrapper.classList.remove("hidden");
                        noImageText.classList.add("hidden");
                    } else 
                    {
                        imgEl.src = "";
                        imgEl.classList.add("hidden");
                        removeWrapper.classList.add("hidden");
                        noImageText.classList.remove("hidden");
                    }

                    // When new image is uploaded → auto-uncheck and hide "remove image"
                    const newImageInput = document.getElementById("edit-image");
                    newImageInput.value = ""; // reset any old file input
                    newImageInput.addEventListener("change", () => 
                    {
                        if (newImageInput.files.length > 0) 
                        {
                            removeCheckbox.checked = false;
                        }
                    });

                    // Set form action dynamically
                    editForm.action = `/management/edit-material/${id}`;

                    // Show modal
                    editModal.classList.remove("hidden");
                });
            });

            // Close buttons
            document.getElementById("closeEditModalBtn_material")?.addEventListener("click", () => 
            {
                editModal.classList.add("hidden");
            });
            document.getElementById("cancelEditBtn_material")?.addEventListener("click", () => 
            {
                editModal.classList.add("hidden");
            });
        }
    // ************************************EDIT MODAL************************************

    // ************************************SEARCH BAR************************************
        const searchInput_materials = document.querySelector("#searchInput_materials");
        const availabilityFilter_materials = document.querySelector("#availabilityFilter_materials"); // <-- new ID you'll add
        const cards_materials = document.querySelectorAll(".material-card_materials");

        function filterMaterials() {
            const searchValue_materials = searchInput_materials.value.toLowerCase();
            const selectedAvailability_materials = availabilityFilter_materials.value.toLowerCase();

            cards_materials.forEach(card => {
                const name_materials = card.dataset.name_materials;
                const details_materials = card.dataset.details_materials;
                const properties_materials = card.dataset.properties_materials;
                const price_materials = card.dataset.price_materials.toString();

                // Check availability (badge text)
                const availability_materials = card.querySelector("span").textContent.toLowerCase();  
                //     → "available" / "unavailable"

                // Search match
                const matchesSearch_materials =
                    name_materials.includes(searchValue_materials) ||
                    details_materials.includes(searchValue_materials) ||
                    properties_materials.includes(searchValue_materials) ||
                    price_materials.includes(searchValue_materials);

                // Availability match
                const matchesAvailability_materials =
                    selectedAvailability_materials === "all" ||
                    availability_materials === selectedAvailability_materials;

                // Final visibility
                card.style.display = (matchesSearch_materials && matchesAvailability_materials) ? "flex" : "none";
            });
        }

        // Event listeners
        searchInput_materials.addEventListener("input", filterMaterials);
        availabilityFilter_materials.addEventListener("change", filterMaterials);
    // ************************************SEARCH BAR************************************
});
