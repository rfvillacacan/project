<?php
header("Content-Type: application/javascript");
require_once __DIR__.'/../includes/config.php';
?>
$(document).ready(function() {
    const isAdmin = <?php echo json_encode($isAdmin); ?>;
    const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
    let logbookTable = $('#logbookTable').DataTable({
        ajax: {
            url: 'logbook_api.php',
            data: { action: 'fetch' },
            dataSrc: ''
        },
        columns: [
            { data: 'shift' },
            { data: 'date' },
            { data: 'time' },
            { data: 'activity', render: $.fn.dataTable.render.text() },
            { data: 'status', render: function(data) {
                let badgeClass = {
                    'Pending': 'bg-warning text-dark',
                    'In Progress': 'bg-info text-dark',
                    'Completed': 'bg-success',
                    'Escalated': 'bg-primary',
                    'Postponed': 'bg-secondary'
                }[data] || 'bg-light text-dark';
                return `<span class="badge ${badgeClass}">${data}</span>`;
            }},
            { data: 'severity', render: function(data) {
                let sevClass = {
                    'Critical': 'badge-critical',
                    'High': 'badge-high',
                    'Medium': 'badge-medium',
                    'Low': 'badge-low'
                }[data] || 'badge-low';
                return `<span class="badge ${sevClass}">${data}</span>`;
            }},
            { data: 'assigned_to', defaultContent: '' },
            { data: 'category' },
            { data: 'action_needed', defaultContent: '' },
            { data: 'notes', defaultContent: '' },
            { data: 'attachment', render: function(data) {
                if (!data) return '';
                let ext = data.split('.').pop().toLowerCase();
                let icon = (ext === 'pdf') ? '📄' : '🖼️';
                return `<a href="logbook_api.php?action=download&file=${encodeURIComponent(data)}" class="attachment-link" target="_blank">${icon}</a>`;
            }},
            { data: 'is_handover', render: function(data) {
                return data == 1 ? '<span class="handover-flag" title="Handover">&#9873;</span>' : '';
            }},
            { data: 'created_by' },
            { data: 'created_at' },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    let buttons = '';
                    if (isAdmin || row.created_by_id == currentUserId) {
                        buttons = `
                            <button class="btn btn-sm btn-warning edit-log-btn" data-id="${row.id}">Edit</button>
                            <button class="btn btn-sm btn-danger delete-log-btn" data-id="${row.id}">Delete</button>
                        `;
                    }
                    return buttons;
                }
            }
        ],
        order: [[1, 'desc'], [2, 'desc']],
        responsive: true
    });

    // Add New Entry
    $('#addLogEntryBtn').on('click', function() {
        $('#logEntryForm')[0].reset();
        $('#logEntryId').val('');
        $('#currentAttachment').html('');
        $('#logEntryModalLabel').text('Add Log Entry');
        $('#logEntryModal').modal('show');
    });

    // Edit Entry
    $('#logbookTable tbody').on('click', '.edit-log-btn', function() {
        let data = logbookTable.row($(this).parents('tr')).data();
        $('#logEntryForm')[0].reset();
        $('#logEntryId').val(data.id);
        $('#logEntryShift').val(data.shift);
        $('#logEntryDate').val(data.date);
        $('#logEntryTime').val(data.time);
        $('#logEntryCategory').val(data.category);
        $('#logEntryActivity').val(data.activity);
        $('#logEntryStatus').val(data.status);
        $('#logEntrySeverity').val(data.severity);
        $('#logEntryAssignedTo').val(data.assigned_to);
        $('#logEntryActionNeeded').val(data.action_needed);
        $('#logEntryNotes').val(data.notes);
        $('#logEntryHandover').prop('checked', data.is_handover == 1);
        if (data.attachment) {
            let ext = data.attachment.split('.').pop().toLowerCase();
            let icon = (ext === 'pdf') ? '📄' : '🖼️';
            $('#currentAttachment').html(`<a href="logbook_api.php?action=download&file=${encodeURIComponent(data.attachment)}" target="_blank">${icon} View Current</a>`);
        } else {
            $('#currentAttachment').html('');
        }
        $('#logEntryModalLabel').text('Edit Log Entry');
        $('#logEntryModal').modal('show');
    });

    // Delete Entry
    $('#logbookTable tbody').on('click', '.delete-log-btn', function() {
        showConfirm('Are you sure you want to delete this entry?').then(result => {
            if (!result) return;
            let data = logbookTable.row($(this).parents('tr')).data();
            $.post('logbook_api.php', { action: 'delete', id: data.id }, function(resp) {
                logbookTable.ajax.reload();
            }, 'json');
        });
    });

    // Save (Add/Edit) Entry
    $('#logEntryForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let id = $('#logEntryId').val();
        formData.append('action', id ? 'edit' : 'add');
        if (!$('#logEntryHandover').is(':checked')) formData.set('is_handover', 0);
        $.ajax({
            url: 'logbook_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    $('#logEntryModal').modal('hide');
                    logbookTable.ajax.reload();
                } else {
                    showToast(resp.error || 'Error saving entry');
                }
            },
            error: function() { showToast('Error saving entry'); }
        });
    });

    // --- Current Shift Logs Table ---
    function getToday() {
        let now = new Date();
        return now.toISOString().slice(0,10);
    }
    let currentShift = (new Date().getHours() < 18) ? 'day' : 'night';
    let currentShiftTable = $('#currentShiftTable').DataTable({
        ajax: {
            url: 'logbook_api.php',
            data: function(d) {
                d.action = 'fetch';
            },
            dataSrc: function(json) {
                let today = getToday();
                return json.filter(e => e.date === today && e.shift === currentShift);
            }
        },
        columns: [
            { data: 'shift' },
            { data: 'date' },
            { data: 'time' },
            { data: 'activity', render: $.fn.dataTable.render.text() },
            { data: 'status', render: function(data) {
                let badgeClass = {
                    'Pending': 'bg-warning text-dark',
                    'In Progress': 'bg-info text-dark',
                    'Completed': 'bg-success',
                    'Escalated': 'bg-primary',
                    'Postponed': 'bg-secondary'
                }[data] || 'bg-light text-dark';
                return `<span class="badge ${badgeClass}">${data}</span>`;
            }},
            { data: 'severity', render: function(data) {
                let sevClass = {
                    'Critical': 'badge-critical',
                    'High': 'badge-high',
                    'Medium': 'badge-medium',
                    'Low': 'badge-low'
                }[data] || 'badge-low';
                return `<span class="badge ${sevClass}">${data}</span>`;
            }},
            { data: 'assigned_to', defaultContent: '' },
            { data: 'category' },
            { data: 'action_needed', defaultContent: '' },
            { data: 'notes', defaultContent: '' },
            { data: 'attachment', render: function(data) {
                if (!data) return '';
                let ext = data.split('.').pop().toLowerCase();
                let icon = (ext === 'pdf') ? '📄' : '🖼️';
                return `<a href="logbook_api.php?action=download&file=${encodeURIComponent(data)}" class="attachment-link" target="_blank">${icon}</a>`;
            }},
            { data: 'is_handover', render: function(data) {
                return data == 1 ? '<span class="handover-flag" title="Handover">&#9873;</span>' : '';
            }},
            { data: 'created_by' },
            { data: 'created_at' }
        ],
        order: [[2, 'desc']],
        responsive: true
    });

    // Shift filter buttons
    $('.shift-filter-btn').on('click', function() {
        $('.shift-filter-btn').removeClass('active');
        $(this).addClass('active');
        currentShift = $(this).data('shift');
        currentShiftTable.ajax.reload();
    });

    // Auto-refresh logic
    let refreshInterval = 0;
    let refreshTimer = null;
    function setRefreshInterval(sec) {
        if (refreshTimer) clearInterval(refreshTimer);
        if (sec > 0) {
            refreshTimer = setInterval(() => {
                if ($('#current-shift-table').hasClass('show')) {
                    currentShiftTable.ajax.reload(null, false);
                }
            }, sec * 1000);
        }
    }
    $('#refresh-interval').on('change', function() {
        refreshInterval = parseInt($(this).val(), 10);
        setRefreshInterval(refreshInterval);
    });
    // Start/stop refresh on tab switch
    $('a[data-bs-toggle="tab"][data-bs-target="#current-shift-table"]').on('shown.bs.tab', function() {
        setRefreshInterval(refreshInterval);
        currentShiftTable.ajax.reload();
    });
    $('a[data-bs-toggle="tab"][data-bs-target="#logbook-table"]').on('shown.bs.tab', function() {
        if (refreshTimer) clearInterval(refreshTimer);
    });
});

