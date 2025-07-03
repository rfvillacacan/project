<?php
require_once 'includes/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ?p=login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/dashboard6.css">
</head>
<body class="bg-dark text-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">My Tasks</h1>
        <div class="d-flex align-items-center">
            <label class="form-check-label me-2" for="viewToggle">Card View</label>
            <input class="form-check-input me-3" type="checkbox" id="viewToggle">
            <a href="?p=logout.php" class="btn btn-sm btn-danger">Logout</a>
        </div>
    </div>
    <div id="taskTableWrapper">
        <table id="taskTable" class="table table-dark table-striped w-100">
            <thead>
            <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Priority</th>
                <th>Due</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="taskCards" class="row row-cols-1 row-cols-md-2 g-3" style="display:none;"></div>
</div>

<div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header">
        <h5 class="modal-title">Update Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group mb-3" id="updateList" style="max-height:200px; overflow-y:auto;"></ul>
        <div class="mb-3">
            <label class="form-label">Progress (%)</label>
            <input type="number" class="form-control" id="updateProgress" min="0" max="100" value="0">
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select id="updateStatus" class="form-select">
                <option value="pending">Pending</option>
                <option value="inprogress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Comment</label>
            <textarea class="form-control" id="updateComment"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveUpdate">Save</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>const currentUserId = <?php echo (int)$_SESSION['user_id']; ?>;</script>
<script>
let currentTask = null;
function fetchUpdates(type,id){
    $.getJSON('task_updates.php',{task_type:type,task_id:id},function(data){
        const list=$('#updateList').empty();
        data.updates.forEach(u=>{
            const ts=new Date(u.created_at).toLocaleString();

        });
    });
}
$(function(){
    fetch('manager_notifications.php').then(r=>r.json()).then(d=>{
        if(d.count && d.count>0){
            const toast=document.createElement('div');
            toast.className='toast align-items-center text-bg-primary position-fixed top-0 end-0 m-3';
            toast.innerHTML='<div class="d-flex"><div class="toast-body">You have '+d.count+' new task comments.</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
            document.body.appendChild(toast);
            new bootstrap.Toast(toast).show();
        }
    });
    const table = $('#taskTable').DataTable({
        ajax:'team_task_data.php',
        columns:[
            {data:'type'},
            {data:'description'},
            {data:'status'},
            {data:'progress', render:d=> d+'%'},
            {data:'priority'},
            {data:'due_date'},
            {data:null, render:function(row){
                return `<button class="btn btn-sm btn-info update-btn" data-type="${row.type}" data-id="${row.id}">Update</button>`;
            }}
        ]
    });

    $('#taskTable').on('click','.update-btn',function(){
        const id=$(this).data('id');
        const type=$(this).data('type');
        currentTask={id,type};
        fetchUpdates(type,id);
        $('#updateModal').modal('show');
    });

   $('#saveUpdate').click(function(){
        const payload={comment:$('#updateComment').val(),progress:$('#updateProgress').val(),status:$('#updateStatus').val()};
        $.ajax({url:'task_updates.php?task_type='+currentTask.type+'&task_id='+currentTask.id,method:'POST',data:JSON.stringify(payload),contentType:'application/json'}).done(function(){
            $('#updateComment').val('');
            table.ajax.reload();
            fetchUpdates(currentTask.type,currentTask.id);
        });
    });

    $('#updateList').on('click','.delete-update-btn',function(){
        if(!confirm('Delete this comment?')) return;
        const id=$(this).data('id');
        $.ajax({url:'task_updates.php?id='+id,method:'DELETE'}).done(function(){
            fetchUpdates(currentTask.type,currentTask.id);
            table.ajax.reload();
        });
    });

    $('#viewToggle').change(function(){
        if(this.checked){
            $('#taskTableWrapper').hide();
            $('#taskCards').show();
            $.getJSON('team_task_data.php',function(d){
                const row=$('#taskCards').empty();
                d.data.forEach(t=>{
                    row.append(`<div class="col"><div class="card h-100 bg-secondary"><div class="card-body"><h5 class="card-title">${t.description}</h5><p class="card-text">Priority: ${t.priority}<br>Status: ${t.status} (${t.progress}%)<br>Due: ${t.due_date}</p><button class="btn btn-sm btn-info update-btn" data-type="${t.type}" data-id="${t.id}">Update</button></div></div></div>`);
                });
            });
        } else {
            $('#taskCards').hide();
            $('#taskTableWrapper').show();
        }
    });
});
</script>
</body>
</html>
