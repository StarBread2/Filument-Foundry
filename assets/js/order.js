document.addEventListener("DOMContentLoaded", () => {
    console.log("order.js loaded");

    const csrfWrapper = document.getElementById("create-order-wrapper");

    const csrfCreateOrder = csrfWrapper.dataset.csrfCreateOrder;
    const csrfUpdateOrder = csrfWrapper.dataset.csrfUpdateOrder;
    const csrfUpdateStatus = csrfWrapper.dataset.csrfUpdateStatus;

    //#region READ
        const modal = document.getElementById("order-details-modal");
        if (!modal) return;

        //#region PASSED DATA
                //order.user
                const customerNameField = modal.querySelector("#modal-customer-name");
                const userEmailField = modal.querySelector("#modal-user-email");
                const deliveryLocationInput = modal.querySelector("#delivery-location");
                //order
                const projectNameField = modal.querySelector("#modal-project-name");
                const quantityField = modal.querySelector("#modal-quantity");
                const multiplierField = modal.querySelector("#modal-model-multiplier");
                const totalField = modal.querySelector("#modal-total");
                //order.shit
                const materialField = modal.querySelector("#modal-material");
                const colorField = modal.querySelector("#modal-color");
                const finishField = modal.querySelector("#modal-finish");
                //order
                const createdAtField = modal.querySelector("#modal-created-at");
                const notesField = modal.querySelector("#modal-notes");
                const deliveryDateInput = modal.querySelector("#delivery-date");
                const arrivalDateInput = modal.querySelector("#arrival-date");
                //button for toggle of text to inputs
                const editProjectBtn = modal.querySelector("#edit-project-btn");
                //Make the location editable depending on the status
                const deliveryInput = document.querySelector("#delivery-location");
                //default value before toggle
                let currentOrderBtn = null;
        //#endregion

            // Function to toggle edit mode (shitties shit)
                function toggleEditMode(enable) {
                    const materialText = document.querySelector("#modal-material");
                    const colorText = document.querySelector("#modal-color");
                    const finishText = document.querySelector("#modal-finish");
                    const quantityText = document.querySelector("#modal-quantity");
                    const multiplierText = document.querySelector("#modal-model-multiplier");

                    const materialInput = document.querySelector("#edit-material");
                    const colorInput = document.querySelector("#edit-color");
                    const finishInput = document.querySelector("#edit-finish");
                    const quantityInput = document.querySelector("#edit-quantity");
                    const multiplierInput = document.querySelector("#edit-model-multiplier");

                    if (enable) {
                        // Store original values if not stored yet
                        if (!editProjectBtn.dataset.originalValues) {
                            editProjectBtn.dataset.originalValues = JSON.stringify({
                                material: materialText.textContent,
                                color: colorText.textContent,
                                finish: finishText.textContent,
                                quantity: quantityText.textContent,
                                multiplier: multiplierText.textContent
                            });
                        }

                        //only show delivery
                        deliveryInput.disabled = false;

                        // Hide read-only
                        materialText.style.display = "none";
                        colorText.style.display = "none";
                        finishText.style.display = "none";
                        quantityText.style.display = "none";
                        multiplierText.style.display = "none";

                        // Show inputs
                        materialInput.classList.remove("hidden");
                        colorInput.classList.remove("hidden");
                        finishInput.classList.remove("hidden");
                        quantityInput.classList.remove("hidden");
                        multiplierInput.classList.remove("hidden");

                        // Set current values
                        materialInput.value = currentOrderBtn.dataset.orderMaterialId;
                        colorInput.value = currentOrderBtn.dataset.orderColorId;
                        finishInput.value = currentOrderBtn.dataset.orderFinishId;
                        quantityInput.value = currentOrderBtn.dataset.orderQuantity;
                        multiplierInput.value = currentOrderBtn.dataset.orderModelMultiplier;

                        editProjectBtn.textContent = "Revert Edit";
                        editProjectBtn.style.backgroundColor = "#1D4ED8";
                        editProjectBtn.dataset.editing = "true";
                    } else {
                        const original = JSON.parse(editProjectBtn.dataset.originalValues);

                        //editable delivery
                        deliveryInput.disabled = true;

                        // Show read-only
                        materialText.style.display = "";
                        colorText.style.display = "";
                        finishText.style.display = "";
                        quantityText.style.display = "";
                        multiplierText.style.display = "";

                        // Hide inputs
                        materialInput.classList.add("hidden");
                        colorInput.classList.add("hidden");
                        finishInput.classList.add("hidden");
                        quantityInput.classList.add("hidden");
                        multiplierInput.classList.add("hidden");

                        // Restore original values
                        materialText.textContent = original.material;
                        colorText.textContent = original.color;
                        finishText.textContent = original.finish;
                        quantityText.textContent = original.quantity;
                        multiplierText.textContent = original.multiplier;

                        editProjectBtn.textContent = "Edit Project Details";
                        editProjectBtn.style.backgroundColor = "black";
                        editProjectBtn.dataset.editing = "false";
                    }
                }

        // OPEN MODAL
        document.querySelectorAll(".orderDetailsBtn").forEach((btn) => {
            btn.addEventListener("click", () => {
                currentOrderBtn = btn;
                console.log("Opening details for Order", btn.dataset.orderId);

                // Fill modal fields
                customerNameField.textContent = btn.dataset.orderCustomerName;
                userEmailField.textContent = btn.dataset.orderUserEmail || "N/A";
                deliveryLocationInput.value = btn.dataset.orderDeliverylocation || "No address";

                projectNameField.textContent = btn.dataset.orderProjectName;
                quantityField.textContent = btn.dataset.orderQuantity;
                multiplierField.textContent = btn.dataset.orderModelMultiplier;
                totalField.textContent = `₱${btn.dataset.orderPriceTotal}`;
                
                // Use camelCase for dataset attributes
                materialField.textContent = btn.dataset.orderMaterialName;
                colorField.textContent = btn.dataset.orderColorName;
                finishField.textContent = btn.dataset.orderFinishName;

                //turn num date to string
                const rawCreatedAt = btn.dataset.orderCreatedAt;
                if (rawCreatedAt) {
                    const createdAt = new Date(rawCreatedAt);
                    const options = { 
                        month: 'long', 
                        day: 'numeric', 
                        year: 'numeric', 
                        hour: 'numeric', 
                        minute: '2-digit', 
                        hour12: true 
                    };
                    createdAtField.textContent = createdAt.toLocaleString('en-US', options);
                } else {
                    createdAtField.textContent = "N/A";
                }

                notesField.textContent = btn.dataset.orderNotes || "No notes provided.";
                deliveryDateInput.value = btn.dataset.orderDeliveryDate || "";
                arrivalDateInput.value = btn.dataset.orderDeliveryArrival || "";


                // ✅ SHOW/HIDE EDIT BUTTON BASED ON STATE
                if (btn.dataset.orderState === "pending") {
                    editProjectBtn.style.display = "inline-block";
                } else {
                    editProjectBtn.style.display = "none";
                }

                modal.classList.remove("hidden");
            });
        });

        // EDIT PROJECT DETAILS TOGGLE
        editProjectBtn.addEventListener("click", () => {
            if (!currentOrderBtn) return;
            const isEditing = editProjectBtn.dataset.editing === "true";
            toggleEditMode(!isEditing);
        });

        // CLOSE MODAL
        window.closeOrderDetails = function () {
            modal.classList.add("hidden");
            if (editProjectBtn.dataset.editing === "true") {
                toggleEditMode(false); // revert on close
            }
        };
    //#endregion
    //#region READ SEARCH BAR 
        const searchInput = document.querySelector('input[placeholder="Search orders..."]');
        const orderItems = document.querySelectorAll(".order-item");
        const noOrdersMessage = document.getElementById("no-orders-found");

        if (!searchInput) return;

        searchInput.addEventListener("input", function () {
            const keyword = this.value.toLowerCase().trim();
            let visibleCount = 0;

            orderItems.forEach(order => {
                const text = order.innerText.toLowerCase();

                if (text.includes(keyword)) {
                    order.style.display = "flex";
                    visibleCount++;
                } else {
                    order.style.display = "none";
                }
            });

            if (visibleCount === 0) {
                noOrdersMessage.classList.remove("hidden");
            } else {
                noOrdersMessage.classList.add("hidden");
            }
        });

        // 👉 Check on initial page load
        (function initialCheck() {
            let visibleCount = 0;

            orderItems.forEach(order => {
                if (order.style.display !== "none") {
                    visibleCount++;
                }
            });

            if (visibleCount === 0) {
                noOrdersMessage.classList.remove("hidden");
            }
        })();
    //#endregion
    //#region READ PAGINATION
        const itemsPerPage = 5; //DEFAULT VALUE OF ORDERS TO SHOW
        let currentPage = 1;
        const orderItemsArray = Array.from(document.querySelectorAll(".order-item"));
        const totalPages = Math.ceil(orderItemsArray.length / itemsPerPage);

        const prevBtn = document.getElementById("prev-page");
        const nextBtn = document.getElementById("next-page");
        const pageInfo = document.getElementById("page-info");

        function showPage(page) {
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            orderItemsArray.forEach((item, index) => {
                item.style.display = (index >= start && index < end) ? "flex" : "none";
            });

            pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;

            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages;

            // Check if no orders visible after search
            const visibleCount = orderItemsArray.filter(i => i.style.display !== "none").length;
            noOrdersMessage.classList.toggle("hidden", visibleCount > 0);
        }

        // Initialize
        showPage(currentPage);

        // Pagination button events
        prevBtn.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });

        nextBtn.addEventListener("click", () => {
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });

        // Update pagination when search is used
        searchInput.addEventListener("input", function () {
            const keyword = this.value.toLowerCase().trim();
            let filteredItems = 0;

            orderItemsArray.forEach(order => {
                const text = order.innerText.toLowerCase();
                if (text.includes(keyword)) {
                    order.style.display = "flex";
                    filteredItems++;
                } else {
                    order.style.display = "none";
                }
            });

            currentPage = 1;
            showPage(currentPage);
        });
    //#endregion

    //#region EDIT
    document.getElementById("save-order-btn").addEventListener("click", async (e) => {
        const btn = e.target;

        // Prevent double-click
        btn.disabled = true;
        btn.textContent = "Saving...";

        //READ IF EDITING
        const isEditing = editProjectBtn.dataset.editing === "true";

        //CLOSE MODAL (READ FUNCTION)
        closeOrderDetails();

        const orderId = currentOrderBtn.dataset.orderId;
        const payload = {
            isEditing: isEditing,
            quantity: document.getElementById("edit-quantity").value,
            modelMultiplier: document.getElementById("edit-model-multiplier").value,
            materialId: document.getElementById("edit-material").value,
            finishId: document.getElementById("edit-finish").value,
            colorId: document.getElementById("edit-color").value,
            deliveryLocation: document.getElementById("delivery-location").value,
            deliveryDate: document.getElementById("delivery-date").value,
            arrivalDate: document.getElementById("arrival-date").value,
            _csrf_token: csrfUpdateOrder
        };

        const res = await fetch(`/management/orders/update/${orderId}`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const result = await res.json();

        if (result.success) {
            closeOrderDetails();
            window.location.reload(); // Refresh list
        } else {

            btn.disabled = false;
            btn.textContent = "Save";
        }
    });
    //#endregion
    //#region EDIT ORDER STATE MODAL
        const statusModal = document.getElementById("statusConfirmModal");
        const statusMessage = document.getElementById("statusMessage");
        const confirmStatusBtn = document.getElementById("confirmStatusBtn");
        const cancelStatusBtn = document.getElementById("cancelStatusBtn");
        const closeStatusModal = document.getElementById("closeStatusModal");

        let selectedOrderId = null;
        let newStatusValue = null;
        let originalStatusValue = null;

        // Open modal on status change
        document.querySelectorAll(".orderStatusSelect").forEach(select => {

            select.addEventListener("change", function () {

                selectedOrderId = this.dataset.orderId;
                originalStatusValue = this.dataset.currentStatus;
                newStatusValue = this.value;

                statusMessage.textContent =
                    `Are you sure you want to update order ORD-${selectedOrderId} from '${originalStatusValue}' to '${newStatusValue}'?`;

                statusModal.classList.remove("hidden");
            });
        });

        // Cancel → revert dropdown value
        cancelStatusBtn.addEventListener("click", () => {
            revertSelect();
            statusModal.classList.add("hidden");
        });
        closeStatusModal.addEventListener("click", () => {
            revertSelect();
            statusModal.classList.add("hidden");
        });

        function revertSelect() {
            const select = document.querySelector(`.orderStatusSelect[data-order-id="${selectedOrderId}"]`);
            if (select) select.value = originalStatusValue;
        }

        // Confirm → send AJAX request
        confirmStatusBtn.addEventListener("click", async () => {

            // Immediately hide modal so it can't be pressed again
            statusModal.classList.add("hidden");

            const payload = {
                newStatus: newStatusValue,
                _csrf_token: csrfUpdateStatus
            };

            const res = await fetch(`/management/orders/update-status/${selectedOrderId}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const result = await res.json();

            if (result.success) {
                window.location.reload();
            } else {
                revertSelect();
                alert("Failed to update status.");
            }
        });
    //#endregion

    //#region CREATE
        //#region email dependent user selection
            const customerSelect = document.getElementById('customer-select');
            const nameInput = document.getElementById('customer-name');
            const addressInput = document.getElementById('delivery-location_add');

            customerSelect.addEventListener('change', () => {
                const selectedOption = customerSelect.selectedOptions[0];

                // Fill inputs if a user is selected
                nameInput.value = selectedOption.dataset.fullname || '';
                addressInput.value = selectedOption.dataset.address || '';
            });
        //#endregion

        //#region close and open of the modal && form submision
            // MODAL ELEMENTS
            const modal_add = document.getElementById('modal_add');
            const addOrderBtn_add = document.getElementById('add-order-btn');
            const closeEditModalBtn_add = modal_add.querySelector('#closeEditModalBtn_add');
            const cancelCreateBtn_add = modal_add.querySelector('#cancelCreateBtn_add');
            const customerSelect_add = modal_add.querySelector('#customer-select');
            const customerName_add = modal_add.querySelector('#customer-name');
            const deliveryLocation_add = modal_add.querySelector('#delivery-location_add');
            const form_add = modal_add.querySelector('form');

            // Show modal
            addOrderBtn_add.addEventListener('click', () => modal_add.classList.remove('hidden'));

            // Hide modal
            [closeEditModalBtn_add, cancelCreateBtn_add].forEach(btn => {
                btn.addEventListener('click', () => modal_add.classList.add('hidden'));
            });

            modal_add.addEventListener('click', e => {
                if (e.target === modal_add) modal_add.classList.add('hidden');
            });

            // Autofill name when selecting customer
            customerSelect_add.addEventListener('change', () => {
                const selected = customerSelect_add.selectedOptions[0];
                customerName_add.value = selected.dataset.fullname || '';
                deliveryLocation_add.value = selected.dataset.address || '';
            });

            // Submit form via AJAX
            form_add.addEventListener('submit', e => {
            e.preventDefault();
            console.log("PRESSED")

            modal_add.classList.add('hidden');

            const selected = customerSelect_add.selectedOptions[0];
            const userId = selected.value;

            const payload = {
                _csrf_token: csrfCreateOrder,
                userId: userId,
                materialId: form_add.querySelector('select[name="material"]').value,
                finishId: form_add.querySelector('select[name="finish"]').value,
                colorId: form_add.querySelector('select[name="color"]').value,
                quantity: form_add.querySelector('input[type="number"]').value,
                modelMultiplier: form_add.querySelector('input[name="modelMultiplier"]').value,
                filePath: form_add.querySelector('input[name="filePath"]').value,
                deliveryLocation: deliveryLocation_add.value,
                notes: form_add.querySelector('input[name="notes_add"]').value,
                deliveryDate: form_add.querySelector('input[name="deliveryDate"]').value,
                arrivalDate: form_add.querySelector('input[name="arrivalDate"]').value
            };

            fetch('/management/orders/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    location.reload();
                } else {
                }
            });
        });

        //#endregion
    //#endregion
    
    //#region DELETE
        const deleteModal = document.getElementById("deleteOrderModal");
        const closeDeleteModal = document.getElementById("closeDeleteModal");
        const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
        const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

        const deleteOrderIdInput = document.getElementById("deleteOrderId");
        const csrfDelete = document.getElementById("csrfDelete").value;

        // Open modal when clicking delete button
        document.querySelectorAll(".deleteOrderBtn").forEach(btn => {
            btn.addEventListener("click", () => {
                const orderId = btn.getAttribute("data-order-id");
                deleteOrderIdInput.value = orderId;
                deleteModal.classList.remove("hidden");
            });
        });

        // Close modal
        closeDeleteModal.addEventListener("click", () => deleteModal.classList.add("hidden"));
        cancelDeleteBtn.addEventListener("click", () => deleteModal.classList.add("hidden"));

        // Confirm delete
        confirmDeleteBtn.addEventListener("click", async () => {
            const orderId = deleteOrderIdInput.value;

            // Immediately close modal to prevent double-clicks
            deleteModal.classList.add("hidden");

            // Disable the confirm button temporarily (optional, but good UX)
            confirmDeleteBtn.disabled = true;

            const response = await fetch(`/management/orders/delete/${orderId}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    _csrf_token: csrfDelete
                })
            });

            const result = await response.json();

            // Re-enable button (in case of error)
            confirmDeleteBtn.disabled = false;

            if (result.success) {
                location.reload();
            } else {
                alert("Delete failed: " + result.error);
            }
        });
    //#endregion
});
