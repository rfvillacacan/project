$(document).ready(function() {
    $('#projectsTable').DataTable({
        ajax: {
            url: 'api/project_management_api.php?endpoint=projects',
            dataSrc: 'projects'
        },
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        language: { search: 'Filter:' },
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'start_date' },
            { data: 'due_date' }
        ]
    });

    $('#tasksTable').DataTable({
        ajax: {
            url: 'api/project_management_api.php?endpoint=tasks',
            dataSrc: 'tasks'
        },
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        language: { search: 'Filter:' },
        columns: [
            { data: 'id' },
            { data: 'project_name' },
            { data: 'title' },
            { data: 'assigned_to' },
            { data: 'status', render: function(data) {
                if (data === 'completed') return '<span class="badge bg-success">Completed</span>';
                if (data === 'pending') return '<span class="badge bg-danger">Pending</span>';
                if (data === 'inprogress') return '<span class="badge bg-warning text-dark">In Progress</span>';
                return data;
            } },
            { data: 'due_date' }
        ]
    });
});
