document.addEventListener("DOMContentLoaded", () => {
    console.log("logs.js loaded");

    // Initialize DataTable
    var table = $('#logsTable').DataTable({
        pageLength: 10,
        order: [[5, 'desc']], // newest first
        language: {
            lengthMenu: "Show _MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ logs",
            paginate: { previous: "←", next: "→" },
            emptyTable: "No logs found."
        },
        dom: 'lrtip',
        stripeClasses: [],
    });

    // Search input
    $('#logsSearch').on('keyup', function () {
        table.search(this.value).draw();
    });

    // Filter by role
    $('#roleFilter').on('change', function () {
        let role = $(this).val();

        if (role === "All Roles") {
            table.columns(2).search('').draw();
        } else {
            table.columns(2).search(role.toLowerCase()).draw();
        }
    });
})