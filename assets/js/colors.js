document.addEventListener("DOMContentLoaded", () => {
    console.log("colors.js loaded");

    // ************************************ CREATE MODAL ************************************
    const addBtn = document.getElementById("button_modal_addColors");
    const addModal = document.getElementById("modal_addColors");
    const closeAdd1 = document.getElementById("closeColorsModalBtn");
    const closeAdd2 = document.getElementById("closeColorModalBtn2");
    const modalOpened = false;

    if (addBtn && addModal) {
        addBtn.addEventListener("click", () => {
            addModal.classList.remove("hidden");
            console.log("opened add color modal");
            modalOpened=true;
        });

        closeAdd1?.addEventListener("click", () => { 
            addModal.classList.add("hidden");
            modalOpened=false;});
        closeAdd2?.addEventListener("click", () => {
            addModal.classList.add("hidden");
            modalOpened=false;
        });

        addModal.addEventListener("click", (e) => {
            if (e.target === addModal) addModal.classList.add("hidden");
        });
    }

    // FILE DROPPER (CREATE)
    const dropzone = document.getElementById("dropzoneColor");
    const imageInput = document.getElementById("colorImageInput");
    const previewImage = document.getElementById("colorPreviewImage");

    if (dropzone && imageInput) {
        dropzone.addEventListener("click", () => imageInput.click());

        imageInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    previewImage.src = event.target.result;
                    previewImage.classList.remove("hidden");
                };
                reader.readAsDataURL(file);
            }
        });

        ["dragenter", "dragover"].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.add("bg-orange-50", "border-orange-400");
            });
        });

        ["dragleave", "drop"].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove("bg-orange-50", "border-orange-400");
            });
        });

        dropzone.addEventListener("drop", (e) => {
            e.preventDefault();
            const file = e.dataTransfer.files[0];
            if (file) {
                imageInput.files = e.dataTransfer.files;
                const reader = new FileReader();
                reader.onload = (event) => {
                    previewImage.src = event.target.result;
                    previewImage.classList.remove("hidden");
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // COLOR OR IMAGE TOGGLE + VALIDATION
    const selectHexOption = document.getElementById("selectHexOption");
    const selectImageOption = document.getElementById("selectImageOption");
    const hexPickerContainer = document.getElementById("hexPickerContainer");
    const imageUploadContainer = document.getElementById("imageUploadContainer");
    const appearanceTypeInput = document.getElementById("appearanceType");

    const form = document.querySelector("form");

    if (selectHexOption && selectImageOption && appearanceTypeInput) {
        selectHexOption.addEventListener("click", () => {
            hexPickerContainer.classList.remove("hidden");
            imageUploadContainer.classList.add("hidden");

            appearanceTypeInput.value = "hex";
            selectHexOption.classList.add("bg-orange-100", "border-orange-400");
            selectImageOption.classList.remove("bg-orange-100", "border-orange-400");

            console.log("Hex color picker selected");
        });

        selectImageOption.addEventListener("click", () => {
            imageUploadContainer.classList.remove("hidden");
            hexPickerContainer.classList.add("hidden");

            appearanceTypeInput.value = "image";
            selectImageOption.classList.add("bg-orange-100", "border-orange-400");
            selectHexOption.classList.remove("bg-orange-100", "border-orange-400");

            console.log("Image upload selected");
        });
    }

    // FORM VALIDATION (CREATE)
    if (modalOpened)
    {
        console.log(modalOpened);
        if (form) {
            form.addEventListener("submit", (e) => {
                const colorHexInput = document.getElementById("colorHex");
                const colorImageInput = document.getElementById("colorImageInput");

                if (!appearanceTypeInput.value) {
                    e.preventDefault();
                    alert("⚠️ Please select a color appearance (Hex Color or Image).");
                    return;
                }

                if (appearanceTypeInput.value === "hex" && !colorHexInput?.value) {
                    e.preventDefault();
                    alert("⚠️ Please pick a hex color.");
                    return;
                }

                console.log("✅ Form validation passed — submitting...");
            });
        }
    }
    
    // ************************************ CREATE MODAL ************************************


    // ************************************ DELETE MODAL ************************************
    const modalColors = document.getElementById("deleteModal_colors");
    const formColors = document.getElementById("deleteFormColors");
    const deleteMessageColors = document.getElementById("deleteMessageColors");
    const cancelBtnColors = document.getElementById("cancelDeleteBtnColors");
    const closeBtnColors = document.getElementById("closeDeleteModal_colors");

    if (modalColors && formColors && deleteMessageColors) {
        document.querySelectorAll(".deleteColorBtn").forEach(btn => {
            btn.addEventListener("click", () => {
                const deleteUrl = btn.dataset.deleteUrl;
                const colorName = btn.dataset.colorName;

                formColors.action = deleteUrl;
                deleteMessageColors.textContent = 
                    `Are you sure you want to delete "${colorName}"? This action cannot be undone.`;
                modalColors.classList.remove("hidden");
            });
        });

        [cancelBtnColors, closeBtnColors].forEach(button => {
            button?.addEventListener("click", () => modalColors.classList.add("hidden"));
        });
    }
    // ************************************ DELETE MODAL ************************************


    // ************************************ EDIT COLOR MODAL ************************************
    const editModal = document.getElementById("modal_editColor");
    const closeEdit1 = document.getElementById("closeEditColorModalBtn");
    const closeEdit2 = document.getElementById("cancelEditColorBtn");
    const editForm = document.getElementById("editColorForm");

    if (editModal && editForm) {
        document.querySelectorAll(".editColorBtn").forEach(btn => {
            btn.addEventListener("click", () => {
                const colorId = btn.dataset.colorId;
                const colorName = btn.dataset.colorName;
                const colorType = btn.dataset.colorType; // "hex" or "image"
                const colorValue = btn.dataset.colorValue; // hex code or image URL
                const colorPrice = btn.dataset.colorPrice;
                const colorAvailable = btn.dataset.colorAvailable;  

                // Fill the form
                editForm.action = `/admin/edit-color/${colorId}`;
                document.getElementById("editColorName").value = colorName;
                document.getElementById("editColorPrice").value = colorPrice;
                document.getElementById("editColorAvailable").checked = colorAvailable === "1";

                const hexPicker = document.getElementById("editHexPickerContainer");
                const imageContainer = document.getElementById("editImageUploadContainer");
                const imagePreview = document.getElementById("editColorPreviewImage");
                const editAppearanceType = document.getElementById("editAppearanceType");

                if (colorType === "hex") {
                    editAppearanceType.value = "hex";
                    hexPicker.classList.remove("hidden");
                    imageContainer.classList.add("hidden");
                    document.getElementById("editColorHex").value = colorValue;
                    imagePreview.classList.add("hidden"); // hide image preview if switching from image
                } else if (colorType === "image") {
                    editAppearanceType.value = "image";
                    imageContainer.classList.remove("hidden");
                    hexPicker.classList.add("hidden");
                    imagePreview.src = colorValue;
                    imagePreview.classList.remove("hidden");
                }

                editModal.classList.remove("hidden");
                console.log(`Opened edit modal for ${colorName}`);
            });
        });

        closeEdit1?.addEventListener("click", () => editModal.classList.add("hidden"));
        closeEdit2?.addEventListener("click", () => editModal.classList.add("hidden"));

        // Optional: close via backdrop click
        editModal.addEventListener("click", (e) => {
            if (e.target === editModal) editModal.classList.add("hidden");
        });
    }

    const editSelectHexOption = document.getElementById("editSelectHexOption");
    const editSelectImageOption = document.getElementById("editSelectImageOption");
    const hexPicker = document.getElementById("editHexPickerContainer");
    const imageContainer = document.getElementById("editImageUploadContainer");

    editSelectHexOption.addEventListener("click", () => {
        editAppearanceType.value = "hex";
        hexPicker.classList.remove("hidden");
        imageContainer.classList.add("hidden");
    });

    editSelectImageOption.addEventListener("click", () => {
        editAppearanceType.value = "image";
        imageContainer.classList.remove("hidden");
        hexPicker.classList.add("hidden");
    });

    // 🖼️ EDIT IMAGE UPLOAD HANDLER
    const editDropzone = document.getElementById("editDropzoneColor");
    const editImageInput = document.getElementById("editColorImageInput");
    const editPreview = document.getElementById("editColorPreviewImage");

    if (editDropzone && editImageInput && editPreview) {
        // click to open file picker
        editDropzone.addEventListener("click", () => editImageInput.click());

        // preview when selecting file
        editImageInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    editPreview.src = event.target.result;
                    editPreview.classList.remove("hidden");
                };
                reader.readAsDataURL(file);
            }
        });

        // drag & drop support
        ["dragenter", "dragover"].forEach(ev =>
            editDropzone.addEventListener(ev, e => {
                e.preventDefault();
                editDropzone.classList.add("bg-orange-50", "border-orange-400");
            })
        );

        ["dragleave", "drop"].forEach(ev =>
            editDropzone.addEventListener(ev, e => {
                e.preventDefault();
                editDropzone.classList.remove("bg-orange-50", "border-orange-400");
            })
        );

        editDropzone.addEventListener("drop", e => {
            e.preventDefault();
            const file = e.dataTransfer.files[0];
            if (file) {
                editImageInput.files = e.dataTransfer.files;
                const reader = new FileReader();
                reader.onload = (event) => {
                    editPreview.src = event.target.result;
                    editPreview.classList.remove("hidden");
                };
                reader.readAsDataURL(file);
            }
        });
    }
    // ************************************ EDIT COLOR MODAL ************************************
    
    // ************************************ SEARCH BAR ************************************
        document.getElementById("searchInput_colors").addEventListener("input", function () {
            const searchValue_colors = this.value.toLowerCase();
            const cards_colors = document.querySelectorAll(".color-card_colors");

            cards_colors.forEach(card => {
                const name_colors = card.dataset.name_colors;
                const availability_colors = card.dataset.availability_colors;
                const price_colors = card.dataset.price_colors;
                const hex_colors = card.dataset.hex_colors;

                const matches_colors =
                    name_colors.includes(searchValue_colors) ||
                    availability_colors.includes(searchValue_colors) ||
                    price_colors.includes(searchValue_colors) ||
                    hex_colors.includes(searchValue_colors);

                card.style.display = matches_colors ? "flex" : "none";
            });
        });
// ************************************ SEARCH BAR ************************************
});
