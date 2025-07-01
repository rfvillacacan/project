<?php
header("Content-Type: application/javascript");
require_once __DIR__.'/../includes/config.php';
?>
    document.addEventListener('DOMContentLoaded', function () {
      // Get user role from PHP session
      const userRole = '<?php echo $_SESSION['role']; ?>';
      
      // DataTables initialization
      $('.datatable-app').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url": "servers_datatable.php",
          "type": "GET",
          "data": { type: 'Application' }
        },
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "language": {
          "search": "Filter:"
        }
      });
      $('.datatable-it').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url": "servers_datatable.php",
          "type": "GET",
          "data": { type: 'IT' }
        },
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "language": {
          "search": "Filter:"
        }
      });
      $('.datatable-ot').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url": "servers_datatable.php",
          "type": "GET",
          "data": { type: 'OT' }
        },
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "language": {
          "search": "Filter:"
        }
      });
      $('.datatable-net').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url": "network_devices_datatable.php",
          "type": "GET"
        },
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "language": {
          "search": "Filter:"
        }
      });
      $('.datatable-urls').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url": "urls_datatable.php",
          "type": "GET"
        },
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "language": {
          "search": "Filter:"
        }
      });
      $('.datatable-service-progress').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url": "service_progress_datatable.php",
          "type": "GET"
        },
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "language": {
          "search": "Filter:"
        },
        "columns": [
          {
            "data": null,
            "render": function (data, type, row, meta) {
              return meta.row + meta.settings._iDisplayStart + 1;
            }
          },
          { "data": "service" },
          { "data": "due_date" },
          { "data": "progress" },
          { "data": "comments" },
          { "data": null, "orderable": false, "render": function(data, type, row) {
              if (userRole !== 'admin' && userRole !== 'operator') return '';
              return `
                <button class="btn btn-sm btn-warning edit-service-progress-btn"
                  data-id="${row.id}"
                  data-service="${$('<div>').text(row.service).html()}"
                  data-due_date="${row.due_date}"
                  data-progress="${row.progress}"
                  data-comments="${$('<div>').text(row.comments).html()}"
                >Edit</button>
                <button class="btn btn-sm btn-danger delete-service-progress-btn" data-id="${row.id}">Delete</button>
              `;
            }
          }
        ]
      });
      $('.datatable-dailytask').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url": "daily_tasks_datatable.php",
          "type": "GET"
        },
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "language": {
          "search": "Filter:"
        },
        "columns": [
          { 
            "data": null,
            "render": function (data, type, row, meta) {
              return meta.row + meta.settings._iDisplayStart + 1;
            }
          },
          { "data": "datetime", "render": function(data) {
              if (!data) return '';
              var d = new Date(data.replace(' ', 'T'));
              if (isNaN(d.getTime())) return data;
              var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
              var year = d.getFullYear();
              var month = months[d.getMonth()];
              var day = d.getDate();
              var hour = d.getHours();
              var min = d.getMinutes();
              var ampm = hour >= 12 ? 'PM' : 'AM';
              hour = hour % 12;
              hour = hour ? hour : 12;
              min = min < 10 ? '0'+min : min;
              return `${hour}:${min} ${ampm} ${month} ${day}, ${year}`;
            }
          },
          { "data": "shift" },
          { "data": "task_description" },
          { "data": "responsibility", "visible": false },
          { "data": "assigned_to" },
          { "data": "created_by" },
          { "data": "required_action" },
          { "data": "status",
            "render": function(data) {
              if (data === 'completed') return '<span class="badge bg-success">Completed</span>';
              if (data === 'pending') return '<span class="badge bg-danger">Pending</span>';
              if (data === 'inprogress') return '<span class="badge bg-warning text-dark">In Progress</span>';
              return data;
            }
          },
          { "data": "percent_completed", "render": function(data) { return data + '%'; } },
          { "data": "comment" },
          { "data": null, "orderable": false, "render": function(data, type, row) {
              if (userRole !== 'admin' && userRole !== 'operator') return '';
              return `
                <button class="btn btn-sm btn-warning edit-dailytask-btn"
                  data-id="${row.id}"
                  data-datetime="${row.datetime.replace(' ', 'T')}"
                  data-shift="${row.shift}"
                  data-task_description="${$('<div>').text(row.task_description).html()}"
                  data-assigned_to="${$('<div>').text(row.assigned_to).html()}"
                  data-required_action="${row.required_action}"
                  data-status="${row.status}"
                  data-percent_completed="${row.percent_completed}"
                  data-comment="${$('<div>').text(row.comment).html()}"
                  data-created_by="${row.created_by}"
                >Edit</button>
                <button class="btn btn-sm btn-danger delete-dailytask-btn" data-id="${row.id}">Delete</button>
              `;
            }
          }
        ]
      });

      // AJAX for APP sub-dashboard
      function updateAppSubDashboard() {
        fetch('sub_dashboard_stats.php?type=Application')
          .then(response => response.json())
          .then(data => {
            const elAgent = document.getElementById('app-agent-online');
            if (elAgent) elAgent.textContent = data.agent_online;
            const elSiem = document.getElementById('app-siem-monitored');
            if (elSiem) elSiem.textContent = data.siem_monitored;
            const elPen = document.getElementById('app-penetration-tested');
            if (elPen) elPen.textContent = data.penetration_tested;
            const elUser = document.getElementById('app-user-access-review');
            if (elUser) elUser.textContent = data.user_access_review;
            const elVapt = document.getElementById('app-vapt');
            if (elVapt) elVapt.textContent = data.vapt;
            const elAvail = document.getElementById('app-availability');
            if (elAvail) elAvail.textContent = data.availability;
          });
      }
      updateAppSubDashboard();

      // AJAX for IT sub-dashboard
      function updateITSubDashboard() {
        fetch('sub_dashboard_stats.php?type=IT')
          .then(response => response.json())
          .then(data => {
            const elAgent = document.getElementById('it-agent-online');
            if (elAgent) elAgent.textContent = data.agent_online;
            const elSiem = document.getElementById('it-siem-monitored');
            if (elSiem) elSiem.textContent = data.siem_monitored;
            const elPen = document.getElementById('it-penetration-tested');
            if (elPen) elPen.textContent = data.penetration_tested;
            const elUser = document.getElementById('it-user-access-review');
            if (elUser) elUser.textContent = data.user_access_review;
            const elVapt = document.getElementById('it-vapt');
            if (elVapt) elVapt.textContent = data.vapt;
            const elAvail = document.getElementById('it-availability');
            if (elAvail) elAvail.textContent = data.availability;
          });
      }
      updateITSubDashboard();

      // AJAX for OT sub-dashboard
      function updateOTSubDashboard() {
        fetch('sub_dashboard_stats.php?type=OT')
          .then(response => response.json())
          .then(data => {
            const elAgent = document.getElementById('ot-agent-online');
            if (elAgent) elAgent.textContent = data.agent_online;
            const elSiem = document.getElementById('ot-siem-monitored');
            if (elSiem) elSiem.textContent = data.siem_monitored;
            const elPen = document.getElementById('ot-penetration-tested');
            if (elPen) elPen.textContent = data.penetration_tested;
            const elUser = document.getElementById('ot-user-access-review');
            if (elUser) elUser.textContent = data.user_access_review;
            const elVapt = document.getElementById('ot-vapt');
            if (elVapt) elVapt.textContent = data.vapt;
            const elAvail = document.getElementById('ot-availability');
            if (elAvail) elAvail.textContent = data.availability;
          });
      }
      updateOTSubDashboard();

      // AJAX for NT sub-dashboard
      function updateNTSubDashboard() {
        fetch('sub_dashboard_stats.php?type=Network')
          .then(response => response.json())
          .then(data => {
            const elAgent = document.getElementById('nt-agent-online');
            if (elAgent) elAgent.textContent = data.agent_online;
            const elSiem = document.getElementById('nt-siem-monitored');
            if (elSiem) elSiem.textContent = data.siem_monitored;
            const elPen = document.getElementById('nt-penetration-tested');
            if (elPen) elPen.textContent = data.penetration_tested;
            const elUser = document.getElementById('nt-user-access-review');
            if (elUser) elUser.textContent = data.user_access_review;
            const elVapt = document.getElementById('nt-vapt');
            if (elVapt) elVapt.textContent = data.vapt;
            const elAvail = document.getElementById('nt-availability');
            if (elAvail) elAvail.textContent = data.availability;
          });
      }
      updateNTSubDashboard();

      // AJAX for URLs sub-dashboard
      function updateURLSSubDashboard() {
        fetch('urls_sub_dashboard_stats.php')
          .then(response => response.json())
          .then(data => {
            const elAgent = document.getElementById('urls-agent-online');
            if (elAgent) elAgent.textContent = data.agent_online;
            const elSiem = document.getElementById('urls-siem-monitored');
            if (elSiem) elSiem.textContent = data.siem_monitored;
            const elPen = document.getElementById('urls-penetration-tested');
            if (elPen) elPen.textContent = data.penetration_tested;
            const elUser = document.getElementById('urls-user-access-review');
            if (elUser) elUser.textContent = data.user_access_review;
            const elVapt = document.getElementById('urls-vapt');
            if (elVapt) elVapt.textContent = data.vapt;
            const elAvail = document.getElementById('urls-availability');
            if (elAvail) elAvail.textContent = data.availability;
          });
      }
      updateURLSSubDashboard();

      // AJAX for Dashboard 1 summary
      function updateDashboardSummary() {
        fetch('dashboard_summary_stats.php')
          .then(response => response.json())
          .then(data => {
            const elCompliance = document.getElementById('dashboard-overall-compliance');
            if (elCompliance) elCompliance.textContent = `${data.overall_compliance}%`;
            const elAssets = document.getElementById('dashboard-total-assets');
            if (elAssets) elAssets.textContent = data.total_assets;
            const elAgents = document.getElementById('dashboard-agents-online');
            if (elAgents) elAgents.textContent = `${data.agents_online} / ${data.total_assets}`;
            const elSiem = document.getElementById('dashboard-siem-monitored');
            if (elSiem) elSiem.textContent = `${data.siem_monitored} / ${data.total_assets}`;
            const elPen = document.getElementById('dashboard-pen-tested');
            if (elPen) elPen.textContent = `${data.pen_tested} / ${data.total_assets}`;
            const elUser = document.getElementById('dashboard-user-access-reviewed');
            if (elUser) elUser.textContent = `${data.user_access_reviewed} / ${data.total_assets}`;
            const elVapt = document.getElementById('dashboard-vapt');
            if (elVapt) elVapt.textContent = `${data.vapt} / ${data.total_assets}`;
          });
      }
      updateDashboardSummary();

      const mainTabTitles = {
        'overview-tab': 'Overview',
        'dailytask-tab': 'Daily Task Tracking',
        'pm-tab': 'Project & Task Management',
        'usermanagement-main-tab': 'User Management',
        'app-tab': 'Application Servers',
        'it-tab': 'IT Servers',
        'ot-tab': 'OT Servers'
      };

      const overviewSubTabTitles = {
        'taskanalytics-tab': 'Task Analytics',
        'taskcards-tab': 'Task Cards',
        'serviceanalytics-tab': 'Service Analytics',
        'servicecards-tab': 'Service Cards',
      };

      function setDashboardTitle(title) {
        document.getElementById('dashboard-title').textContent = `Cybersecurity Project Management - ${title}`;
      }

      // Add style for dashboard title
      document.getElementById('dashboard-title').style.fontSize = '80%';

      // Main tab change
      var dashboardTabs = document.getElementById('dashboardTabs');
      if (dashboardTabs) {
        dashboardTabs.addEventListener('shown.bs.tab', function (e) {
          const tabId = e.target.id;
          if (tabId === 'overview-tab') {
            // On overview, check which subtab is active
            const activeSubTab = document.querySelector('#overviewSubTabs .nav-link.active');
            if (activeSubTab) {
              setDashboardTitle(overviewSubTabTitles[activeSubTab.id] || 'Hajj Program Overview');
            } else {
              setDashboardTitle('Hajj Program Overview');
            }
          } else {
            setDashboardTitle(mainTabTitles[tabId] || '');
          }
        });
      }

      // Overview sub-tab change
      var overviewSubTabs = document.getElementById('overviewSubTabs');
      if (overviewSubTabs) {
        overviewSubTabs.addEventListener('shown.bs.tab', function (e) {
          const subTabId = e.target.id;
          setDashboardTitle(overviewSubTabTitles[subTabId] || 'Hajj Program Overview');
        });
      }

      // Set default on load
      setDashboardTitle('Hajj Program Overview');

      // Add tab toggle functionality
      const toggleTabsBtn = document.getElementById('toggleTabs');
      const mainTabs = document.getElementById('dashboardTabs');
      const subTabs = document.getElementById('overviewSubTabs');
      let tabsVisible = true;
      let lastTabsHiddenTime = 0;

      function toggleTabs() {
        tabsVisible = !tabsVisible;
        mainTabs.style.display = tabsVisible ? 'flex' : 'none';
        if (subTabs) {
          subTabs.style.display = tabsVisible ? 'flex' : 'none';
        }
        toggleTabsBtn.innerHTML = tabsVisible ? 
          '<i class="fas fa-chevron-up"></i> Hide Tabs' : 
          '<i class="fas fa-chevron-down"></i> Show Tabs';
        if (!tabsVisible) {
          lastTabsHiddenTime = Date.now();
        }
      }

      toggleTabsBtn.addEventListener('click', toggleTabs);

      // Auto-hide tabs after 5 seconds if auto-switch is enabled
      // Removed setupAutoHide and autoSubTabSwitch event listener as auto-switch feature is gone

      // Show tabs when hovering over the top of the page
      document.addEventListener('mousemove', function(e) {
        const toggleTabsBtn = document.getElementById('toggleTabs');
        const btnRect = toggleTabsBtn.getBoundingClientRect();
        const overButton =
          e.clientX >= btnRect.left &&
          e.clientX <= btnRect.right &&
          e.clientY >= btnRect.top &&
          e.clientY <= btnRect.bottom;

        if (
          !tabsVisible &&
          e.clientY < 10 && // much smaller area, just the very top
          !overButton &&
          Date.now() - lastTabsHiddenTime > 1000 // 1 second
        ) {
          toggleTabs();
        }
      });

      function updateDashboardDateTime() {
        const timeElem = document.getElementById('dashboard-time');
        const dateElem = document.getElementById('dashboard-date');
        if (!timeElem || !dateElem) return;
        const now = new Date();
        // Convert to KSA time (+3 GMT)
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const ksa = new Date(utc + (3600000 * 3));
        const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const day = days[ksa.getDay()];
        const month = months[ksa.getMonth()];
        const date = ksa.getDate();
        const year = ksa.getFullYear();
        let hour = ksa.getHours();
        const min = ksa.getMinutes().toString().padStart(2, '0');
        const sec = ksa.getSeconds().toString().padStart(2, '0');
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12;
        hour = hour ? hour : 12;

        // Build the time string with a container for seconds
        timeElem.innerHTML = `${hour}:${min}<span id="dashboard-seconds-container" class="scroll-seconds-container"></span>${ampm} +3`;
        dateElem.textContent = `${day} ${month} ${date}, ${year}`;

        // Animate seconds
        const secContainer = document.getElementById('dashboard-seconds-container');
        if (!secContainer) return;

        // Use a global variable to store lastSec
        if (typeof window.lastSec === 'undefined') window.lastSec = null;
        let lastSec = window.lastSec;

        // If first run, just show the number
        if (lastSec === null) {
          secContainer.innerHTML = `
            <span class="scroll-seconds-list" style="transform: translateY(0);">
              <span class="scroll-second old">${sec}</span>
              <span class="scroll-second new"></span>
            </span>
          `;
          window.lastSec = sec;
          return;
        }

        // If second hasn't changed, do nothing
        if (lastSec === sec) return;

        // Otherwise, animate from lastSec to sec
        secContainer.innerHTML = `
          <span class="scroll-seconds-list" style="transform: translateY(0);">
            <span class="scroll-second old">${lastSec}</span>
            <span class="scroll-second new">${sec}</span>
          </span>
        `;
        const list = secContainer.querySelector('.scroll-seconds-list');
        setTimeout(() => {
          list.style.transform = 'translateY(-1em)';
        }, 10);

        // After animation, set only the new second as visible
        setTimeout(() => {
          secContainer.innerHTML = `
            <span class="scroll-seconds-list" style="transform: translateY(0);">
              <span class="scroll-second old">${sec}</span>
              <span class="scroll-second new"></span>
            </span>
          `;
          window.lastSec = sec;
        }, 310);
      }
      setInterval(updateDashboardDateTime, 1000);
      updateDashboardDateTime();

      // Network Device Edit Button Handler
      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-network-btn')) {
          if (userRole !== 'admin' && userRole !== 'operator') {
            alert('You do not have permission to edit network devices.');
            return;
          }
          const btn = event.target;
          document.getElementById('edit-network-id').value = btn.getAttribute('data-id');
          document.getElementById('edit-network-hostname').value = btn.getAttribute('data-hostname');
          document.getElementById('edit-network-domain').value = btn.getAttribute('data-domain');
          document.getElementById('edit-network-ip').value = btn.getAttribute('data-ip');
          document.getElementById('edit-network-os').value = btn.getAttribute('data-os');
          document.getElementById('edit-network-role').value = btn.getAttribute('data-role');
          document.getElementById('edit-network-criticality').value = btn.getAttribute('data-criticality');
          
          // Fix for status dropdown
          const statusValue = btn.getAttribute('data-status');
          const statusSelect = document.getElementById('edit-network-status');
          if (statusValue) {
            // Remove any HTML tags from the status value
            const cleanStatus = statusValue.replace(/<[^>]*>/g, '').trim();
            statusSelect.value = cleanStatus;
          } else {
            statusSelect.value = 'Offline'; // Default value
          }
          
          document.getElementById('edit-network-notes').value = btn.getAttribute('data-notes');
          
          var modal = new bootstrap.Modal(document.getElementById('editNetworkDeviceModal'));
          modal.show();
        }
      });

      // Network Device Save Button Handler
      var saveNetworkDeviceBtn = document.getElementById('saveNetworkDeviceBtn');
      if (saveNetworkDeviceBtn) {
        saveNetworkDeviceBtn.addEventListener('click', function() {
          var form = document.getElementById('editNetworkDeviceForm');
          var formData = new FormData(form);
          fetch('edit_network_device.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.text())
          .then(data => {
            if (data.trim() !== 'success') throw new Error(data);
            var modal = bootstrap.Modal.getInstance(document.getElementById('editNetworkDeviceModal'));
            modal.hide();
            var $table = $('.datatable-net');
            if ($table.length && $table.DataTable().settings().length) {
              $table.DataTable().ajax.reload(null, false);
            }
          })
          .catch((err) => alert(err));
        });
      }

      // URL Edit Button Handler
      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-url-btn')) {
          if (userRole !== 'admin' && userRole !== 'operator') {
            alert('You do not have permission to edit URLs.');
            return;
          }
          const btn = event.target;
          document.getElementById('edit-url-id').value = btn.getAttribute('data-id');
          document.getElementById('edit-url').value = btn.getAttribute('data-url');
          document.getElementById('edit-category').value = btn.getAttribute('data-category');
          
          // Fix for status dropdown
          const statusValue = btn.getAttribute('data-status');
          const statusSelect = document.getElementById('edit-status');
          if (statusValue) {
            // Remove any HTML tags from the status value
            const cleanStatus = statusValue.replace(/<[^>]*>/g, '').trim();
            statusSelect.value = cleanStatus;
          } else {
            statusSelect.value = 'Active'; // Default value
          }
          
          document.getElementById('edit-last-checked').value = btn.getAttribute('data-last-checked');
          document.getElementById('edit-notes').value = btn.getAttribute('data-notes');
          
          var modal = new bootstrap.Modal(document.getElementById('editUrlModal'));
          modal.show();
        }
      });

      // URL Save Button Handler
      var saveUrlBtn = document.getElementById('saveUrlBtn');
      if (saveUrlBtn) {
        saveUrlBtn.addEventListener('click', function() {
          var form = document.getElementById('editUrlForm');
          var formData = new FormData(form);
          fetch('edit_url.php', {
            method: 'POST',
            body: formData
          })
          .then(response => {
            if (!response.ok) throw new Error('Failed to save');
            return response.text();
          })
          .then(data => {
            var modal = bootstrap.Modal.getInstance(document.getElementById('editUrlModal'));
            modal.hide();
            $('.datatable-urls').DataTable().ajax.reload(null, false);
          })
          .catch(() => alert('Failed to save changes.'));
        });
      }

      // Server Edit Button Handler (for Application, IT, OT servers)
      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-server-btn')) {
          if (userRole !== 'admin' && userRole !== 'operator') {
            alert('You do not have permission to edit servers.');
            return;
          }
          const btn = event.target;
          document.getElementById('edit-server-id').value = btn.getAttribute('data-id');
          document.getElementById('edit-server-name').value = btn.getAttribute('data-name');
          document.getElementById('edit-server-domain').value = btn.getAttribute('data-domain');
          document.getElementById('edit-server-ip').value = btn.getAttribute('data-ip');
          document.getElementById('edit-server-os').value = btn.getAttribute('data-os');
          document.getElementById('edit-server-appname').value = btn.getAttribute('data-appname');
          
          // Fix for status dropdown
          const statusValue = btn.getAttribute('data-status');
          const statusSelect = document.getElementById('edit-server-status');
          if (statusValue) {
            // Remove any HTML tags from the status value
            const cleanStatus = statusValue.replace(/<[^>]*>/g, '').trim();
            statusSelect.value = cleanStatus;
          } else {
            statusSelect.value = 'Offline'; // Default value
          }
          
          document.getElementById('edit-server-notes').value = btn.getAttribute('data-notes');
          
          var modal = new bootstrap.Modal(document.getElementById('editServerModal'));
          modal.show();
        }
      });

      // Server Save Button Handler (for Application, IT, OT servers)
      var saveServerBtn = document.getElementById('saveServerBtn');
      if (saveServerBtn) {
        saveServerBtn.addEventListener('click', function() {
          var form = document.getElementById('editServerForm');
          var formData = new FormData(form);
          fetch('edit_server.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.text())
          .then(data => {
            if (data.trim() !== 'success') throw new Error(data);
            var modal = bootstrap.Modal.getInstance(document.getElementById('editServerModal'));
            modal.hide();
            if ($('.datatable-app').length && $('.datatable-app').DataTable().settings().length) $('.datatable-app').DataTable().ajax.reload(null, false);
            if ($('.datatable-it').length && $('.datatable-it').DataTable().settings().length) $('.datatable-it').DataTable().ajax.reload(null, false);
            if ($('.datatable-ot').length && $('.datatable-ot').DataTable().settings().length) $('.datatable-ot').DataTable().ajax.reload(null, false);
          })
          .catch((err) => alert(err));
        });
      }

      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('check-server-btn')) {
          if (userRole !== 'admin' && userRole !== 'operator') {
            alert('You do not have permission to check servers.');
            return;
          }
          const btn = event.target;
          document.getElementById('check-server-id').value = btn.getAttribute('data-id');
          document.getElementById('check-server-title').textContent = btn.getAttribute('data-name');
          document.getElementById('check-server-ip').textContent = btn.getAttribute('data-ip');
          document.getElementById('check-agent-online').value = btn.getAttribute('data-agent-online') || 'No';
          document.getElementById('check-siem-monitored').value = btn.getAttribute('data-siem-monitored') || 'No';
          document.getElementById('check-penetration-tested').value = btn.getAttribute('data-penetration-tested') || 'No';
          document.getElementById('check-user-access-review').value = btn.getAttribute('data-user-access-review') || 'No';
          document.getElementById('check-vapt').value = btn.getAttribute('data-vapt') || 'No';
          document.getElementById('check-availability').value = btn.getAttribute('data-availability') || 'No';
          var modal = new bootstrap.Modal(document.getElementById('checkServerModal'));
          modal.show();
        }
      });

      var saveCheckServerBtn = document.getElementById('saveCheckServerBtn');
      if (saveCheckServerBtn) {
        saveCheckServerBtn.addEventListener('click', function() {
          var form = document.getElementById('checkServerForm');
          var formData = new FormData(form);
          fetch('edit_server.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.text())
          .then(data => {
            if (data.trim() !== 'success') throw new Error(data);
            var modal = bootstrap.Modal.getInstance(document.getElementById('checkServerModal'));
            modal.hide();
            if ($('.datatable-app').length && $('.datatable-app').DataTable().settings().length) $('.datatable-app').DataTable().ajax.reload(null, false);
            if ($('.datatable-it').length && $('.datatable-it').DataTable().settings().length) $('.datatable-it').DataTable().ajax.reload(null, false);
            if ($('.datatable-ot').length && $('.datatable-ot').DataTable().settings().length) $('.datatable-ot').DataTable().ajax.reload(null, false);
          })
          .catch((err) => alert(err));
        });
      }

      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('check-network-btn')) {
          if (userRole !== 'admin' && userRole !== 'operator') {
            alert('You do not have permission to check network devices.');
            return;
          }
          const btn = event.target;
          document.getElementById('check-network-id').value = btn.getAttribute('data-id');
          document.getElementById('check-network-title').textContent = btn.getAttribute('data-hostname');
          document.getElementById('check-network-ip').textContent = btn.getAttribute('data-ip');
          document.getElementById('check-network-agent-online').value = btn.getAttribute('data-agent-online') || 'No';
          document.getElementById('check-network-siem-monitored').value = btn.getAttribute('data-siem-monitored') || 'No';
          document.getElementById('check-network-penetration-tested').value = btn.getAttribute('data-penetration-tested') || 'No';
          document.getElementById('check-network-user-access-review').value = btn.getAttribute('data-user-access-review') || 'No';
          document.getElementById('check-network-vapt').value = btn.getAttribute('data-vapt') || 'No';
          document.getElementById('check-network-availability').value = btn.getAttribute('data-availability') || 'No';
          var modal = new bootstrap.Modal(document.getElementById('checkNetworkDeviceModal'));
          modal.show();
        }
      });

      var saveCheckNetworkBtn = document.getElementById('saveCheckNetworkBtn');
      if (saveCheckNetworkBtn) {
        saveCheckNetworkBtn.addEventListener('click', function() {
          var form = document.getElementById('checkNetworkDeviceForm');
          var formData = new FormData(form);
          fetch('edit_network_device.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.text())
          .then(data => {
            if (data.trim() !== 'success') throw new Error(data);
            var modal = bootstrap.Modal.getInstance(document.getElementById('checkNetworkDeviceModal'));
            modal.hide();
            var $table = $('.datatable-net');
            if ($table.length && $table.DataTable().settings().length) {
              $table.DataTable().ajax.reload(null, false);
            }
          })
          .catch((err) => alert(err));
        });
      }

      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('check-url-btn')) {
          if (userRole !== 'admin' && userRole !== 'operator') {
            alert('You do not have permission to check URLs.');
            return;
          }
          const btn = event.target;
          document.getElementById('check-url-id').value = btn.getAttribute('data-id');
          document.getElementById('check-url-title').textContent = btn.getAttribute('data-url');
          document.getElementById('check-url-agent-online').value = btn.getAttribute('data-agent-online') || 'No';
          document.getElementById('check-url-siem-monitored').value = btn.getAttribute('data-siem-monitored') || 'No';
          document.getElementById('check-url-penetration-tested').value = btn.getAttribute('data-penetration-tested') || 'No';
          document.getElementById('check-url-user-access-review').value = btn.getAttribute('data-user-access-review') || 'No';
          document.getElementById('check-url-vapt').value = btn.getAttribute('data-vapt') || 'No';
          document.getElementById('check-url-availability').value = btn.getAttribute('data-availability') || 'No';
          var modal = new bootstrap.Modal(document.getElementById('checkUrlModal'));
          modal.show();
        }
      });

      var saveCheckUrlBtn = document.getElementById('saveCheckUrlBtn');
      if (saveCheckUrlBtn) {
        saveCheckUrlBtn.addEventListener('click', function() {
          var form = document.getElementById('checkUrlForm');
          var formData = new FormData(form);
          fetch('edit_url.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.text())
          .then(data => {
            if (data.trim() !== 'success') throw new Error(data);
            var modal = bootstrap.Modal.getInstance(document.getElementById('checkUrlModal'));
            modal.hide();
            var $table = $('.datatable-urls');
            if ($table.length && $table.DataTable().settings().length) {
              $table.DataTable().ajax.reload(null, false);
            }
          })
          .catch((err) => alert(err));
        });
      }

      // Service Progress Edit Button Handler
      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-service-progress-btn')) {
          const btn = event.target;
          document.getElementById('edit-service-progress-id').value = btn.getAttribute('data-id');
          document.getElementById('edit-service-progress-service').value = btn.getAttribute('data-service');
          document.getElementById('edit-service-progress-due-date').value = btn.getAttribute('data-due_date');
          document.getElementById('edit-service-progress-progress').value = btn.getAttribute('data-progress');
          document.getElementById('edit-service-progress-comments').value = btn.getAttribute('data-comments');
          var modal = new bootstrap.Modal(document.getElementById('editServiceProgressModal'));
          modal.show();
        }
      });

      // Service Progress Save Button Handler
      var saveServiceProgressBtn = document.getElementById('saveServiceProgressBtn');
      if (saveServiceProgressBtn) {
        saveServiceProgressBtn.addEventListener('click', function() {
          var form = document.getElementById('editServiceProgressForm');
          var formData = new FormData(form);
          fetch('edit_service_progress.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.text())
          .then(data => {
            if (data.trim() !== 'success') throw new Error(data);
            var modal = bootstrap.Modal.getInstance(document.getElementById('editServiceProgressModal'));
            modal.hide();
            if ($('.datatable-service-progress').length && $('.datatable-service-progress').DataTable().settings().length) {
              $('.datatable-service-progress').DataTable().ajax.reload(null, false);
            }
          })
          .catch((err) => alert('Failed to save changes: ' + err));
        });
      }

      // DELETE BUTTON HANDLERS
      // Application, IT, OT Servers (DataTables and static)
      $(document).on('click', '.delete-server-btn, .datatable-app .btn-danger, .datatable-it .btn-danger, .datatable-ot .btn-danger', function() {
        if (!confirm('Are you sure you want to delete this server?')) return;
        var id = $(this).data('id');
        $.post('edit_server.php', { delete: 1, id: id }, function(resp) {
          if (resp.trim() === 'success') {
            if ($('.datatable-app').length) $('.datatable-app').DataTable().ajax.reload(null, false);
            if ($('.datatable-it').length) $('.datatable-it').DataTable().ajax.reload(null, false);
            if ($('.datatable-ot').length) $('.datatable-ot').DataTable().ajax.reload(null, false);
          } else {
            alert('Delete failed: ' + resp);
          }
        });
      });
      // URLs
      $(document).on('click', '.datatable-urls .btn-danger', function() {
        if (!confirm('Are you sure you want to delete this URL?')) return;
        var row = $(this).closest('tr');
        var id = row.find('td').eq(0).text(); // Assuming first column is ID
        $.post('edit_url.php', { delete: 1, id: id }, function(resp) {
          if (resp.trim() === 'success') {
            if ($('.datatable-urls').length) $('.datatable-urls').DataTable().ajax.reload(null, false);
          } else {
            alert('Delete failed: ' + resp);
          }
        });
      });
      // Service Progress
      $(document).on('click', '.delete-service-progress-btn', function() {
        if (!confirm('Are you sure you want to delete this service progress item?')) return;
        var id = $(this).data('id');
        $.post('edit_service_progress.php', { delete: 1, id: id }, function(resp) {
          if (resp.trim() === 'success') {
            if ($('.datatable-service-progress').length && $('.datatable-service-progress').DataTable().settings().length) {
              $('.datatable-service-progress').DataTable().ajax.reload(null, false);
            } else {
              location.reload();
            }
          } else {
            alert('Delete failed: ' + resp);
          }
        });
      });
      // Network Devices
      $(document).on('click', '.delete-network-btn', function() {
        if (!confirm('Are you sure you want to delete this network device?')) return;
        var id = $(this).data('id');
        $.post('edit_network_device.php', { delete: 1, id: id }, function(resp) {
          if (resp.trim() === 'success') {
            if ($('.datatable-net').length && $('.datatable-net').DataTable().settings().length) {
              $('.datatable-net').DataTable().ajax.reload(null, false);
            }
          } else {
            alert('Delete failed: ' + resp);
          }
        });
      });

      // Edit Button Handler
      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-dailytask-btn')) {
          const btn = event.target;
          // Prefill all fields
          document.getElementById('edit-dailytask-id').value = btn.getAttribute('data-id');
          document.getElementById('edit-dailytask-datetime').value = btn.getAttribute('data-datetime');
          document.getElementById('edit-dailytask-shift').value = btn.getAttribute('data-shift');
          document.getElementById('edit-dailytask-description').value = btn.getAttribute('data-task_description');
          document.getElementById('edit-dailytask-responsibility').value = btn.getAttribute('data-responsibility');
          loadUsersDropdown(btn.getAttribute('data-assigned_to'));
          document.getElementById('edit-dailytask-required-action').value = btn.getAttribute('data-required_action');
          document.getElementById('edit-dailytask-status').value = btn.getAttribute('data-status');
          document.getElementById('edit-dailytask-percent-completed').value = btn.getAttribute('data-percent_completed');
          document.getElementById('edit-dailytask-comment').value = btn.getAttribute('data-comment');

          // Permissions logic
          const createdBy = btn.getAttribute('data-created_by');
          const assignedTo = btn.getAttribute('data-assigned_to');

          // Enable all fields by default
          $('#editDailyTaskForm input, #editDailyTaskForm select, #editDailyTaskForm textarea').prop('disabled', false);

          if (currentRole !== 'admin' && currentUser !== createdBy && currentUser !== assignedTo) {
            alert('You do not have permission to edit this task.');
            return;
          }

          if (currentUser === assignedTo && currentUser !== createdBy) {
            // Only allow Required Action, Status, Comment
            $('#edit-dailytask-required-action').prop('disabled', false);
            $('#edit-dailytask-status').prop('disabled', false);
            $('#edit-dailytask-comment').prop('disabled', false);

            // Disable all others
            $('#edit-dailytask-datetime').prop('disabled', true);
            $('#edit-dailytask-shift').prop('disabled', true);
            $('#edit-dailytask-description').prop('disabled', true);
            $('#edit-dailytask-responsibility').prop('disabled', true);
            $('#edit-dailytask-assigned-to').prop('disabled', true);
          }
          // If currentUser === createdBy, all fields remain enabled

          var modal = new bootstrap.Modal(document.getElementById('editDailyTaskModal'));
          modal.show();
        }
      });

      // Save Button Handler
      document.getElementById('saveDailyTaskBtn').addEventListener('click', function() {
        var form = document.getElementById('editDailyTaskForm');
        var formData = new FormData(form);
        fetch('edit_daily_task.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(data => {
          if (data.trim() !== 'success') throw new Error(data);
          var modal = bootstrap.Modal.getInstance(document.getElementById('editDailyTaskModal'));
          modal.hide();
          $('.datatable-dailytask').DataTable().ajax.reload(null, false);
        })
        .catch((err) => alert('Failed to save changes: ' + err));
      });

      // Delete Button Handler
      $(document).on('click', '.delete-dailytask-btn', function() {
        if (!confirm('Are you sure you want to delete this task?')) return;
        var id = $(this).data('id');
        $.post('edit_daily_task.php', { delete: 1, id: id }, function(resp) {
          if (resp.trim() === 'success') {
            if ($('.datatable-dailytask').length) $('.datatable-dailytask').DataTable().ajax.reload(null, false);
          } else {
            alert('Delete failed: ' + resp);
          }
        });
      });

      function loadUsersDropdown(selectedUser = '') {
        fetch('users_list.php')
          .then(response => response.json())
          .then(users => {
            const select = document.getElementById('edit-dailytask-assigned-to');
            select.innerHTML = '<option value="">Select user</option>';
            users.forEach(username => {
              const opt = document.createElement('option');
              opt.value = username;
              opt.textContent = username;
              if (username === selectedUser) opt.selected = true;
              select.appendChild(opt);
            });
          });
      }

      // When opening the modal for edit/add
      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-dailytask-btn')) {
          // ... your existing code ...
          // Prefill dropdown with users and select the current one
          loadUsersDropdown(btn.getAttribute('data-assigned_to'));
          // ... rest of your code ...
        }
      });

      // When opening the modal for "Add" (if you have an Add button)
      document.getElementById('addDailyTaskBtn')?.addEventListener('click', function() {
        loadUsersDropdown('');
      });

      const currentUser = '<?php echo $_SESSION['username']; ?>';
      const currentRole = '<?php echo $_SESSION['role']; ?>';

      // Add Task button handler
      document.getElementById('addDailyTaskBtn').addEventListener('click', function() {
        // Clear all fields
        document.getElementById('edit-dailytask-id').value = '';
        document.getElementById('edit-dailytask-datetime').value = '';
        document.getElementById('edit-dailytask-shift').value = 'day';
        document.getElementById('edit-dailytask-description').value = '';
        document.getElementById('edit-dailytask-responsibility').value = '';
        loadUsersDropdown('');
        document.getElementById('edit-dailytask-required-action').value = 'pending';
        document.getElementById('edit-dailytask-status').value = '';
        document.getElementById('edit-dailytask-percent-completed').value = '';
        document.getElementById('edit-dailytask-comment').value = '';
        // Enable all fields
        $('#editDailyTaskForm input, #editDailyTaskForm select, #editDailyTaskForm textarea').prop('disabled', false);
        var modal = new bootstrap.Modal(document.getElementById('editDailyTaskModal'));
        modal.show();
      });

      // Sync Status and Percent Completed fields
      document.getElementById('edit-dailytask-status').addEventListener('change', function() {
        if (this.value === 'completed') {
          document.getElementById('edit-dailytask-percent-completed').value = 100;
        } else {
          // Only clear if it was 100 before
          if (document.getElementById('edit-dailytask-percent-completed').value == 100) {
            document.getElementById('edit-dailytask-percent-completed').value = '';
          }
        }
      });
      document.getElementById('edit-dailytask-percent-completed').addEventListener('input', function() {
        const percent = parseInt(this.value);
        const statusElem = document.getElementById('edit-dailytask-status');
        if (percent === 100) {
          statusElem.value = 'completed';
        } else if (statusElem.value === 'completed' && percent < 100) {
          statusElem.value = 'inprogress';
        }
      });

      // --- Task Analytics Dashboard ---
      let taskAnalyticsShowAll = false;
      function updateTaskAnalytics() {
        fetch('daily_tasks_summary.php')
          .then(response => response.json())
          .then(data => {
            // Update cards
            document.getElementById('task-total').textContent = data.total;
            document.getElementById('task-completed').textContent = data.completed;
            document.getElementById('task-inprogress').textContent = data.inprogress;
            document.getElementById('task-pending').textContent = data.pending;
            document.getElementById('task-completion-rate').textContent = data.completion_rate + '%';

            // Update progress bar
            const bar = document.getElementById('task-completion-bar');
            bar.style.width = data.completion_rate + '%';
            bar.setAttribute('aria-valuenow', data.completion_rate);
            bar.textContent = data.completion_rate + '%';

            // Update pie chart
            if (window.taskStatusPieChart) window.taskStatusPieChart.destroy();
            const ctx = document.getElementById('task-status-pie').getContext('2d');
            window.taskStatusPieChart = new Chart(ctx, {
              type: 'pie',
              data: {
                labels: ['Completed', 'In Progress', 'Pending'],
                datasets: [{
                  data: [data.completed, data.inprogress, data.pending],
                  backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                }]
              },
              options: {
                plugins: {
                  legend: { display: true, position: 'bottom' }
                }
              }
            });

            // Update bar chart
            if (window.taskStatusBarChart) window.taskStatusBarChart.destroy();
            const barctx = document.getElementById('task-status-bar').getContext('2d');
            window.taskStatusBarChart = new Chart(barctx, {
              type: 'bar',
              data: {
                labels: ['Completed', 'In Progress', 'Pending'],
                datasets: [{
                  label: 'Tasks',
                  data: [data.completed, data.inprogress, data.pending],
                  backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                  borderRadius: 8,
                  barPercentage: 0.6,
                  categoryPercentage: 0.6
                }]
              },
              options: {
                plugins: {
                  legend: { display: false }
                },
                scales: {
                  x: {
                    grid: { display: false },
                    ticks: { color: '#fff', font: { weight: 'bold', size: 16 } }
                  },
                  y: {
                    beginAtZero: true,
                    grid: { color: '#343a40' },
                    ticks: { color: '#fff', font: { weight: 'bold', size: 16 }, stepSize: 1 }
                  }
                }
              }
            });

            // Update latest tasks table with pagination or show all
            const latestTbody = document.getElementById('task-latest-tasks');
            let itemsPerPage = 5;
            let currentPage = 1;
            let totalPages = 1;
            let allTasks = data.latest || [];
            function updateTable() {
              latestTbody.innerHTML = '';
              let pageItems;
              if (taskAnalyticsShowAll) {
                pageItems = allTasks;
              } else {
                totalPages = Math.ceil(allTasks.length / itemsPerPage);
                pageItems = allTasks.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
              }
              if (pageItems.length) {
                pageItems.forEach(task => {
                  let statusText = '-';
                  let statusClass = '';
                  if (task.status) {
                    if (task.status.toLowerCase() === 'pending') {
                      statusText = 'Pending';
                      statusClass = 'bg-danger text-white';
                    } else if (task.status.toLowerCase() === 'inprogress') {
                      statusText = 'In Progress';
                      statusClass = 'bg-warning text-dark';
                    } else if (task.status.toLowerCase() === 'completed') {
                      statusText = 'Completed';
                      statusClass = 'bg-success text-white';
                    } else {
                      statusText = task.status;
                      statusClass = 'bg-secondary text-white';
                    }
                  }
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td>${task.datetime}</td><td>${task.task_description}</td><td><span class="badge ${statusClass}" style="font-size:1rem;">${statusText}</span></td>`;
                  latestTbody.appendChild(tr);
                });
              } else {
                latestTbody.innerHTML = '<tr><td colspan="3">No tasks found.</td></tr>';
              }
              // Update pagination info/buttons
              const infoElement = document.getElementById('task-latest-tasks_info');
              const prevButton = document.getElementById('task-latest-tasks_previous');
              const nextButton = document.getElementById('task-latest-tasks_next');
              if (!taskAnalyticsShowAll) {
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + pageItems.length;
                const totalItems = allTasks.length;
                infoElement.textContent = `Showing ${start + 1} to ${end} of ${totalItems} entries`;
                prevButton.classList.toggle('disabled', currentPage === 1);
                nextButton.classList.toggle('disabled', currentPage === totalPages);
                prevButton.onclick = (e) => {
                  e.preventDefault();
                  if (currentPage > 1) {
                    currentPage--;
                    updateTable();
                  }
                };
                nextButton.onclick = (e) => {
                  e.preventDefault();
                  if (currentPage < totalPages) {
                    currentPage++;
                    updateTable();
                  }
                };
              } else {
                infoElement.textContent = `Showing all ${allTasks.length} entries`;
                prevButton.classList.add('disabled');
                nextButton.classList.add('disabled');
                prevButton.onclick = (e) => { e.preventDefault(); };
                nextButton.onclick = (e) => { e.preventDefault(); };
              }
            }
            updateTable();
            // Attach button listeners (only once)
            if (!window.taskAnalyticsBtnListenersAdded) {
              document.getElementById('task-analytics-pagination-btn').addEventListener('click', function() {
                taskAnalyticsShowAll = false;
                this.classList.add('active');
                document.getElementById('task-analytics-showall-btn').classList.remove('active');
                updateTaskAnalytics();
              });
              document.getElementById('task-analytics-showall-btn').addEventListener('click', function() {
                taskAnalyticsShowAll = true;
                this.classList.add('active');
                document.getElementById('task-analytics-pagination-btn').classList.remove('active');
                updateTaskAnalytics();
              });
              window.taskAnalyticsBtnListenersAdded = true;
            }
          });
      }
      // Update on tab show
      if (document.getElementById('taskanalytics-tab')) {
        document.getElementById('taskanalytics-tab').addEventListener('shown.bs.tab', updateTaskAnalytics);
      }
      if (document.getElementById('taskanalytics').classList.contains('show')) updateTaskAnalytics();

      // --- Task Cards Sub-Tab ---
      let taskCardsFilter = 'all';
      function updateTaskCards() {
        fetch('daily_tasks_summary.php')
          .then(response => response.json())
          .then(data => {
            const row = document.getElementById('task-cards-row');
            row.innerHTML = '';
            let filtered = data.latest || [];
            if (taskCardsFilter === 'inprogress') {
              filtered = filtered.filter(task => task.status.toLowerCase() === 'inprogress');
            } else if (taskCardsFilter === 'pending') {
              filtered = filtered.filter(task => task.status.toLowerCase() === 'pending');
            } else if (taskCardsFilter === 'completed') {
              filtered = filtered.filter(task => task.status.toLowerCase() === 'completed');
            }
            if (filtered.length) {
              filtered.forEach(task => {
                let statusText = '-';
                let statusClass = '';
                if (task.status) {
                  if (task.status.toLowerCase() === 'pending') {
                    statusText = 'Pending';
                    statusClass = 'bg-danger text-white';
                  } else if (task.status.toLowerCase() === 'inprogress') {
                    statusText = 'In Progress';
                    statusClass = 'bg-warning text-dark';
                  } else if (task.status.toLowerCase() === 'completed') {
                    statusText = 'Completed';
                    statusClass = 'bg-success text-white';
                  } else {
                    statusText = task.status;
                    statusClass = 'bg-secondary text-white';
                  }
                }
                const card = document.createElement('div');
                card.className = 'col-3';
                card.innerHTML = `
                  <div class="card shadow-sm text-center" style="max-width:260px; width:100%; margin:0 auto; padding:1.2rem 0.7rem; min-height:220px; display:flex; flex-direction:column; justify-content:center; align-items:center; border-width:2.5px; border-style:solid; border-color:${
                    statusClass.includes('bg-danger') ? '#dc3545' : statusClass.includes('bg-warning') ? '#ffc107' : statusClass.includes('bg-success') ? '#198754' : '#343a40'
                  }">
                    <span class="badge ${statusClass}" style="font-size:1rem; margin-bottom:0.7rem;">${statusText}</span>
                    <div style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">${task.task_description || '-'}</div>
                    <div style="color:#f6ad55; font-size:1rem; margin-bottom:0.5rem;">${task.datetime || '-'}</div>
                    <div style="color:#f6ad55; font-size:1rem; font-weight:600; margin-bottom:0.5rem;">${task.shift ? task.shift.charAt(0).toUpperCase() + task.shift.slice(1) : '-'}</div>
                    <div style="font-size:0.95rem; margin-bottom:0.5rem;">Responsibility: <span style="color:#0dcaf0;">${task.responsibility || '-'}</span></div>
                    <div style="font-size:0.95rem; margin-bottom:0.5rem;">Assigned To: <span style="color:#ffc107;">${task.assigned_to || '-'}</span></div>
                    <div style="font-size:2.5rem; font-weight:900; color:#198754; margin-top:1rem; letter-spacing:1px; text-shadow:0 2px 8px #0008;">${typeof task.percent_completed === 'number' ? task.percent_completed + '%' : '-'}</div>
                  </div>
                `;
                row.appendChild(card);
              });
            } else {
              row.innerHTML = '<div class="text-center">No tasks found.</div>';
            }
          });
      }
      document.getElementById('taskcards-tab')?.addEventListener('shown.bs.tab', updateTaskCards);
      // Filter button handlers
      document.getElementById('filter-all').addEventListener('click', function() {
        taskCardsFilter = 'all';
        setTaskCardsFilterBtn(this);
        updateTaskCards();
      });
      document.getElementById('filter-inprogress').addEventListener('click', function() {
        taskCardsFilter = 'inprogress';
        setTaskCardsFilterBtn(this);
        updateTaskCards();
      });
      document.getElementById('filter-pending').addEventListener('click', function() {
        taskCardsFilter = 'pending';
        setTaskCardsFilterBtn(this);
        updateTaskCards();
      });
      document.getElementById('filter-completed').addEventListener('click', function() {
        taskCardsFilter = 'completed';
        setTaskCardsFilterBtn(this);
        updateTaskCards();
      });
      function setTaskCardsFilterBtn(activeBtn) {
        document.getElementById('filter-all').classList.remove('active');
        document.getElementById('filter-inprogress').classList.remove('active');
        document.getElementById('filter-pending').classList.remove('active');
        document.getElementById('filter-completed').classList.remove('active');
        activeBtn.classList.add('active');
      }

      // Previously handled Task Tracking toggle removed
      // On page load, activate Task Analytics sub-tab
      /*setTimeout(function() {
        const taskAnalyticsTab = document.getElementById('taskanalytics-tab');
        if (taskAnalyticsTab) {
          const tabInstance = bootstrap.Tab.getOrCreateInstance(taskAnalyticsTab);
          tabInstance.show();
        }
      }, 100);
      */

      // Show User Management tab only for admin
      if (typeof currentRole !== 'undefined' && currentRole === 'admin') {
        document.getElementById('usermanagement-main-tab-li').style.display = '';
      }

      // --- User Management Tab ---
      function loadUserManagementTable() {
        fetch('user_management_api.php?action=list')
          .then(response => response.json())
          .then(users => {
            let html = `<table class='table table-dark table-bordered'><thead><tr><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody>`;
            users.forEach(user => {
              html += `<tr>
                <td>${user.username}</td>
                <td>${user.role === 'admin' ? '<span class="badge bg-success">Admin</span>' : user.role === 'operator' ? '<span class="badge bg-warning text-dark">Operator</span>' : '<span class="badge bg-secondary">Readonly</span>'}</td>
                <td>${user.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Pending</span>'}</td>
                <td>`;
              if (currentRole === 'admin' && user.username !== currentUser) {
                html += `<select class='form-select form-select-sm d-inline w-auto me-2 user-role-select' data-username='${user.username}'>
                  <option value='readonly' ${user.role === 'readonly' ? 'selected' : ''}>Readonly</option>
                  <option value='operator' ${user.role === 'operator' ? 'selected' : ''}>Operator</option>
                  <option value='admin' ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                </select>`;
                html += `<select class='form-select form-select-sm d-inline w-auto me-2 user-status-select' data-username='${user.username}'>
                  <option value='active' ${user.status === 'active' ? 'selected' : ''}>Active</option>
                  <option value='pending' ${user.status === 'pending' ? 'selected' : ''}>Pending</option>
                </select>`;
                html += `<button class='btn btn-sm btn-primary save-user-btn' data-username='${user.username}'>Save</button>`;
              } else {
                html += '-';
              }
              html += `</td></tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('user-management-table-container').innerHTML = html;
          });
      }
      if (document.getElementById('usermanagement-main-tab')) {
        document.getElementById('usermanagement-main-tab').addEventListener('shown.bs.tab', loadUserManagementTable);
      }
      document.addEventListener('click', function(e) {
        if (e.target.classList.contains('save-user-btn')) {
          const username = e.target.getAttribute('data-username');
          const row = e.target.closest('tr');
          const role = row.querySelector('.user-role-select').value;
          const status = row.querySelector('.user-status-select').value;
          fetch('user_management_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update', username, role, status })
          })
          .then(resp => resp.json())
          .then(data => {
            if (data.success) {
              loadUserManagementTable();
            } else {
              alert('Failed to update user: ' + (data.error || 'Unknown error'));
            }
          });
        }
      });

      // Dashboard width radio buttons
      document.querySelectorAll('input[name="dashboard-width"]').forEach(radio => {
        radio.addEventListener('change', function() {
          const val = this.value;
          const container = document.getElementById('dashboard-main-container');
          container.style.maxWidth = val + 'vw';
          container.style.width = val + 'vw';
        });
      });

      // Add Service button handler
      document.getElementById('addServiceProgressBtn')?.addEventListener('click', function() {
        // Clear all fields
        document.getElementById('edit-service-progress-id').value = '';
        document.getElementById('edit-service-progress-service').value = '';
        document.getElementById('edit-service-progress-due-date').value = '';
        document.getElementById('edit-service-progress-progress').value = '';
        document.getElementById('edit-service-progress-comments').value = '';
        // Set modal title
        document.getElementById('editServiceProgressModalLabel').textContent = 'Add Service';
        var modal = new bootstrap.Modal(document.getElementById('editServiceProgressModal'));
        modal.show();
      });

      // --- Service Cards Sub-Tab ---
      let serviceCardsFilter = 'all';
      function updateServiceCards() {
        fetch('service_progress_cards.php')
          .then(response => response.json())
          .then(data => {
            const row = document.getElementById('service-cards-row');
            row.innerHTML = '';
            let filtered = data || [];
            if (serviceCardsFilter === 'inprogress') {
              filtered = filtered.filter(service => {
                let progressNum = parseInt(service.progress);
                return !isNaN(progressNum) && progressNum >= 50 && progressNum < 100;
              });
            } else if (serviceCardsFilter === 'pending') {
              filtered = filtered.filter(service => {
                let progressNum = parseInt(service.progress);
                return !isNaN(progressNum) && progressNum < 50;
              });
            } else if (serviceCardsFilter === 'completed') {
              filtered = filtered.filter(service => {
                let progressNum = parseInt(service.progress);
                return !isNaN(progressNum) && progressNum === 100;
              });
            }
            if (filtered.length) {
              filtered.forEach(service => {
                let progress = service.progress || '';
                let progressNum = parseInt(progress);
                let statusText = '-';
                let statusClass = '';
                let borderColor = '#343a40';
                if (!isNaN(progressNum)) {
                  if (progressNum === 100) {
                    statusText = 'Completed';
                    statusClass = 'bg-success text-white';
                    borderColor = '#198754';
                  } else if (progressNum >= 50) {
                    statusText = 'In Progress';
                    statusClass = 'bg-warning text-dark';
                    borderColor = '#ffc107';
                  } else {
                    statusText = 'Pending';
                    statusClass = 'bg-danger text-white';
                    borderColor = '#dc3545';
                  }
                }
                // Format date as 'Month Day, Year'
                let formattedDate = service.due_date;
                if (service.due_date && /^\d{4}-\d{2}-\d{2}$/.test(service.due_date)) {
                  const d = new Date(service.due_date);
                  if (!isNaN(d.getTime())) {
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    formattedDate = `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
                  }
                } else if (service.due_date && /\d{1,2}\/\d{1,2}\/\d{4}/.test(service.due_date)) {
                  // Handle MM/DD/YYYY
                  const parts = service.due_date.split('/');
                  const d = new Date(parts[2], parts[0] - 1, parts[1]);
                  if (!isNaN(d.getTime())) {
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    formattedDate = `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
                  }
                }
                row.innerHTML += `
                  <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card shadow-sm text-center" style="max-width:260px; width:100%; margin:0 auto; padding:1.2rem 0.7rem; min-height:220px; display:flex; flex-direction:column; justify-content:center; align-items:center; border-width:2.5px; border-style:solid; border-color:${borderColor}">
                      <span class="badge ${statusClass}" style="font-size:1rem; margin-bottom:0.7rem;">${statusText}</span>
                      <div style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">${service.service || '-'}</div>
                      <div style="color:#f6ad55; font-size:1rem; margin-bottom:0.5rem;">${formattedDate || '-'}</div>
                      <div style="font-size:0.95rem; margin-bottom:0.5rem;">Comments: <span style="color:#ffc107;">${service.comments || '-'}</span></div>
                      <div style="font-size:2.5rem; font-weight:900; color:#198754; margin-top:1rem; letter-spacing:1px; text-shadow:0 2px 8px #0008;">${typeof progressNum === 'number' && !isNaN(progressNum) ? progressNum + '%' : '-'}</div>
                    </div>
                  </div>
                `;
              });
            } else {
              row.innerHTML = '<div class="text-center">No service progress found.</div>';
            }
          });
      }
      function setServiceCardsFilterBtn(activeBtn) {
        document.getElementById('service-filter-all').classList.remove('active');
        document.getElementById('service-filter-inprogress').classList.remove('active');
        document.getElementById('service-filter-pending').classList.remove('active');
        document.getElementById('service-filter-completed').classList.remove('active');
        activeBtn.classList.add('active');
      }
      document.getElementById('service-filter-all').addEventListener('click', function() {
        serviceCardsFilter = 'all';
        setServiceCardsFilterBtn(this);
        updateServiceCards();
      });
      document.getElementById('service-filter-inprogress').addEventListener('click', function() {
        serviceCardsFilter = 'inprogress';
        setServiceCardsFilterBtn(this);
        updateServiceCards();
      });
      document.getElementById('service-filter-pending').addEventListener('click', function() {
        serviceCardsFilter = 'pending';
        setServiceCardsFilterBtn(this);
        updateServiceCards();
      });
      document.getElementById('service-filter-completed').addEventListener('click', function() {
        serviceCardsFilter = 'completed';
        setServiceCardsFilterBtn(this);
        updateServiceCards();
      });
      // Attach to sub-tab event
      document.getElementById('overviewSubTabs').addEventListener('shown.bs.tab', function (e) {
        if (e.target.id === 'servicecards-tab') updateServiceCards();
      });
      // Optionally, update on page load if tab is active
      if (document.getElementById('servicecards') && document.getElementById('servicecards').classList.contains('show')) updateServiceCards();

      // --- Service Analytics Sub-Tab ---
      let serviceAnalyticsShowAll = false;
      function updateServiceAnalytics() {
        fetch('service_analytics_summary.php')
          .then(response => response.json())
          .then(data => {
            document.getElementById('service-total').textContent = data.total;
            document.getElementById('service-completed').textContent = data.completed;
            document.getElementById('service-inprogress').textContent = data.inprogress;
            document.getElementById('service-completion-rate').textContent = data.completion_rate + '%';

            // Update progress bar
            const bar = document.getElementById('service-completion-bar');
            bar.style.width = data.completion_rate + '%';
            bar.setAttribute('aria-valuenow', data.completion_rate);
            bar.textContent = data.completion_rate + '%';

            // Update pie chart
            if (window.serviceStatusPieChart) window.serviceStatusPieChart.destroy();
            const ctx = document.getElementById('service-status-pie').getContext('2d');
            window.serviceStatusPieChart = new Chart(ctx, {
              type: 'pie',
              data: {
                labels: ['Completed', 'In Progress'],
                datasets: [{
                  data: [data.completed, data.inprogress],
                  backgroundColor: ['#198754', '#ffc107'],
                }]
              },
              options: {
                plugins: {
                  legend: { display: true, position: 'bottom' }
                }
              }
            });

            // Update bar chart
            if (window.serviceStatusBarChart) window.serviceStatusBarChart.destroy();
            const barctx = document.getElementById('service-status-bar').getContext('2d');
            window.serviceStatusBarChart = new Chart(barctx, {
              type: 'bar',
              data: {
                labels: ['Completed', 'In Progress'],
                datasets: [{
                  label: 'Services',
                  data: [data.completed, data.inprogress],
                  backgroundColor: ['#198754', '#ffc107'],
                  borderRadius: 8,
                  barPercentage: 0.6,
                  categoryPercentage: 0.6
                }]
              },
              options: {
                plugins: {
                  legend: { display: false }
                },
                scales: {
                  x: {
                    grid: { display: false },
                    ticks: { color: '#fff', font: { weight: 'bold', size: 16 } }
                  },
                  y: {
                    beginAtZero: true,
                    grid: { color: '#343a40' },
                    ticks: { color: '#fff', font: { weight: 'bold', size: 16 }, stepSize: 1 }
                  }
                }
              }
            });

            // Update latest services table with pagination or show all
            const latestServicesBody = document.getElementById('service-latest-services');
            let itemsPerPage = 5;
            let currentPage = 1;
            let totalPages = 1;
            let allServices = data.latest || [];
            function updateTable() {
              latestServicesBody.innerHTML = '';
              let pageItems;
              if (serviceAnalyticsShowAll) {
                pageItems = allServices;
              } else {
                totalPages = Math.ceil(allServices.length / itemsPerPage);
                pageItems = allServices.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
              }
              if (pageItems.length) {
                pageItems.forEach(service => {
                  let statusText = '-';
                  let statusClass = '';
                  if (service.status) {
                    if (service.status.toLowerCase() === 'completed') {
                      statusText = 'Completed';
                      statusClass = 'bg-success text-white';
                    } else if (service.status.toLowerCase() === 'inprogress') {
                      statusText = 'In Progress';
                      statusClass = 'bg-warning text-dark';
                    } else {
                      statusText = service.status;
                      statusClass = 'bg-secondary text-white';
                    }
                  }
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td>${service.datetime}</td><td>${service.service}</td><td><span class="badge ${statusClass}" style="font-size:1rem;">${statusText}</span></td>`;
                  latestServicesBody.appendChild(tr);
                });
              } else {
                latestServicesBody.innerHTML = '<tr><td colspan="3">No services found.</td></tr>';
              }
              // Update pagination info/buttons
              const infoElement = document.getElementById('service-latest-services_info');
              const prevButton = document.getElementById('service-latest-services_previous');
              const nextButton = document.getElementById('service-latest-services_next');
              if (!serviceAnalyticsShowAll) {
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + pageItems.length;
                const totalItems = allServices.length;
                infoElement.textContent = `Showing ${start + 1} to ${end} of ${totalItems} entries`;
                prevButton.classList.toggle('disabled', currentPage === 1);
                nextButton.classList.toggle('disabled', currentPage === totalPages);
                prevButton.onclick = (e) => {
                  e.preventDefault();
                  if (currentPage > 1) {
                    currentPage--;
                    updateTable();
                  }
                };
                nextButton.onclick = (e) => {
                  e.preventDefault();
                  if (currentPage < totalPages) {
                    currentPage++;
                    updateTable();
                  }
                };
              } else {
                infoElement.textContent = `Showing all ${allServices.length} entries`;
                prevButton.classList.add('disabled');
                nextButton.classList.add('disabled');
                prevButton.onclick = (e) => { e.preventDefault(); };
                nextButton.onclick = (e) => { e.preventDefault(); };
              }
            }
            updateTable();
            // Attach button listeners (only once)
            if (!window.serviceAnalyticsBtnListenersAdded) {
              document.getElementById('service-analytics-pagination-btn').addEventListener('click', function() {
                serviceAnalyticsShowAll = false;
                this.classList.add('active');
                document.getElementById('service-analytics-showall-btn').classList.remove('active');
                updateServiceAnalytics();
              });
              document.getElementById('service-analytics-showall-btn').addEventListener('click', function() {
                serviceAnalyticsShowAll = true;
                this.classList.add('active');
                document.getElementById('service-analytics-pagination-btn').classList.remove('active');
                updateServiceAnalytics();
              });
              window.serviceAnalyticsBtnListenersAdded = true;
            }
          });
      }
      // Update on tab show
      document.getElementById('serviceanalytics-tab')?.addEventListener('shown.bs.tab', updateServiceAnalytics);
      // Optionally, update on page load if tab is active
      if (document.getElementById('serviceanalytics').classList.contains('show')) updateServiceAnalytics();

      // Add title update for all sub-tabs
      document.querySelectorAll('#overviewSubTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
          const baseTitle = 'Cybersecurity Project Management';
          const tabName = e.target.textContent.trim();
          document.title = `${baseTitle} - ${tabName}`;
        });
      });

      // Initialize IS Projects DataTable
      $('.datatable-is-projects').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url": "is_projects_datatable.php",
          "type": "GET"
        },
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "language": {
          "search": "Filter:"
        },
        "columns": [
          {
            "data": null,
            "render": function (data, type, row, meta) {
              return meta.row + meta.settings._iDisplayStart + 1;
            }
          },
          { "data": "service" },
          { "data": "due_date" },
          { "data": "progress" },
          { "data": "assign_to_team" },
          { "data": "comments" },
          { "data": null, "orderable": false, "render": function(data, type, row) {
              if (userRole !== 'admin' && userRole !== 'operator') return '';
              return `
                <button class="btn btn-sm btn-info edit-is-project2-btn"
                  data-id="${row.id}"
                  data-service="${$('<div>').text(row.service).html()}"
                  data-due_date="${row.due_date}"
                  data-progress="${row.progress}"
                  data-assign_to_team="${row.assign_to_team}"
                  data-comments="${$('<div>').text(row.comments).html()}"
                >Edit</button>
                <button class="btn btn-sm btn-danger delete-is-project-btn" data-id="${row.id}">Delete</button>
              `;
            }
          }
        ]
      });

      // Add IS Project Button Handler
      document.getElementById('addIsProjectBtn')?.addEventListener('click', function() {
        // Clear all fields
        document.getElementById('edit-is-project2-id').value = '';
        document.getElementById('edit-is-project2-service').value = '';
        document.getElementById('edit-is-project2-due-date').value = '';
        document.getElementById('edit-is-project2-progress').value = '';
        document.getElementById('edit-is-project2-assign-to-team').value = 'Prep';
        document.getElementById('edit-is-project2-comments').value = '';
        // Set modal title
        document.getElementById('editIsProject2ModalLabel').textContent = 'Add Program';
        var modal = new bootstrap.Modal(document.getElementById('editIsProject2Modal'));
        modal.show();
      });

      // IS Project Delete Button Handler
      $(document).on('click', '.delete-is-project-btn', function() {
        if (!confirm('Are you sure you want to delete this project?')) return;
        var id = $(this).data('id');
        $.post('edit_is_project.php', { delete: 1, id: id }, function(resp) {
          if (resp.trim() === 'success') {
            if ($('.datatable-is-projects').length && $('.datatable-is-projects').DataTable().settings().length) {
              $('.datatable-is-projects').DataTable().ajax.reload(null, false);
            }
          } else {
            alert('Delete failed: ' + resp);
          }
        });
      });

      // Add JS handler for Edit2 button
      $(document).on('click', '.edit-is-project2-btn', function() {
        $('#edit-is-project2-id').val($(this).data('id'));
        $('#edit-is-project2-service').val($(this).data('service'));
        $('#edit-is-project2-due-date').val($(this).data('due_date'));
        $('#edit-is-project2-progress').val($(this).data('progress'));
        $('#edit-is-project2-assign-to-team').val($(this).data('assign_to_team'));
        $('#edit-is-project2-comments').val($(this).data('comments'));
        $('#editIsProject2Modal').modal('show');
      });
      $('#saveIsProject2Btn').on('click', function() {
        var form = $('#editIsProject2Form')[0];
        var formData = new FormData(form);
        fetch('edit_is_project.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(data => {
          if (data.trim() !== 'success') throw new Error(data);
          var modal = bootstrap.Modal.getInstance(document.getElementById('editIsProject2Modal'));
          modal.hide();
          if ($('.datatable-is-projects').length && $('.datatable-is-projects').DataTable().settings().length) {
            $('.datatable-is-projects').DataTable().ajax.reload(null, false);
          }
        })
        .catch((err) => alert('Failed to save changes: ' + err));
      });

      function renderISProjectAnalytics(containerId, team) {
        fetch('is_projects_analytics_summary.php?team=' + encodeURIComponent(team))
          .then(response => response.json())
          .then(data => {
            const container = document.getElementById(containerId);
            if (!container) return;
            container.innerHTML = `
              <div class="row g-4 mb-4">
                <div class="col-6 col-md-2">
                  <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #0dcaf0; border-radius: 1rem;">
                    <div class="summary-title">Total Projects</div>
                    <div class="summary-value" style="font-size:2.5rem; font-weight:900; color:#0dcaf0;">${data.total}</div>
                  </div>
                </div>
                <div class="col-6 col-md-2">
                  <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #198754; border-radius: 1rem;">
                    <div class="summary-title">Completed</div>
                    <div class="summary-value text-success" style="font-size:2.5rem; font-weight:900; color:#198754;">${data.completed}</div>
                  </div>
                </div>
                <div class="col-6 col-md-2">
                  <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #ffc107; border-radius: 1rem;">
                    <div class="summary-title">In Progress</div>
                    <div class="summary-value text-warning" style="font-size:2.5rem; font-weight:900; color:#ffc107;">${data.inprogress}</div>
                  </div>
                </div>
                <div class="col-6 col-md-2">
                  <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #dc3545; border-radius: 1rem;">
                    <div class="summary-title">Pending</div>
                    <div class="summary-value text-danger" style="font-size:2.5rem; font-weight:900; color:#dc3545;">${data.pending || 0}</div>
                  </div>
                </div>
                <div class="col-6 col-md-2">
                  <div class="summary-card p-4 text-center shadow" style="border-top: 4px solid #0dcaf0; border-radius: 1rem;">
                    <div class="summary-title">Completion Rate</div>
                    <div class="summary-value text-info" style="font-size:2.5rem; font-weight:900; color:#0dcaf0;">${data.completion_rate}%</div>
                  </div>
                </div>
              </div>
              <div class="row g-4 mb-4 align-items-stretch">
                <div class="col-md-6">
                  <div class="card shadow-sm h-100">
                    <div class="card-header fw-bold">${team} Status Distribution</div>
                    <div class="card-body text-center">
                      <canvas id="${containerId}-pie" width="80" height="80"></canvas>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card shadow-sm h-100">
                    <div class="card-header fw-bold">${team} Status Bar Chart</div>
                    <div class="card-body text-center">
                      <canvas id="${containerId}-bar" width="80" height="80"></canvas>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row g-4 mb-4">
                <div class="col-12">
                  <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Overall ${team} Completion</div>
                    <div class="card-body">
                      <div class="progress" style="height: 2.5rem; border-radius: 1.2rem; background: #23272b;">
                        <div id="${containerId}-completion-bar" class="progress-bar bg-success" role="progressbar" style="width: ${data.completion_rate}%; font-size:1.5rem; font-weight:900; border-radius: 1.2rem; background: linear-gradient(90deg, #198754 60%, #0dcaf0 100%);" aria-valuenow="${data.completion_rate}" aria-valuemin="0" aria-valuemax="100">${data.completion_rate}%</div>
                      </div>
                    </div>
                  </div>
                  <!-- Pagination Toggle Button Group (copied from Daily Task Analytics) -->
                  <div class="d-flex justify-content-center mb-3">
                    <div class="btn-group" role="group" aria-label="Hajj Overview Display Mode">
                      <button type="button" class="btn btn-outline-light active" id="${containerId}-pagination-btn">Enable Pagination</button>
                      <button type="button" class="btn btn-outline-warning" id="${containerId}-showall-btn">Display All</button>
                    </div>
                  </div>
                  <div class="card shadow-sm">
                    <div class="card-header fw-bold">Latest ${team} Projects</div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                          <thead>
                            <tr>
                              <th>Due Date</th>
                              <th>Project</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody id="${containerId}-latest-projects"></tbody>
                        </table>
                      </div>
                      <div class="d-flex justify-content-between align-items-center p-3">
                        <div id="${containerId}-latest-projects_info"></div>
                        <div>
                          <button class="btn btn-sm btn-outline-light" id="${containerId}-latest-projects_previous">Previous</button>
                          <button class="btn btn-sm btn-outline-light" id="${containerId}-latest-projects_next">Next</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `;

            // Update pie chart
            if (window[containerId + 'PieChart']) window[containerId + 'PieChart'].destroy();
            const ctx = document.getElementById(containerId + '-pie').getContext('2d');
            window[containerId + 'PieChart'] = new Chart(ctx, {
              type: 'pie',
              data: {
                labels: ['Completed', 'In Progress', 'Pending'],
                datasets: [{
                  data: [data.completed, data.inprogress, data.pending || 0],
                  backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                }]
              },
              options: {
                plugins: {
                  legend: { display: true, position: 'bottom' }
                }
              }
            });

            // Update bar chart
            if (window[containerId + 'BarChart']) window[containerId + 'BarChart'].destroy();
            const barCtx = document.getElementById(containerId + '-bar').getContext('2d');
            window[containerId + 'BarChart'] = new Chart(barCtx, {
              type: 'bar',
              data: {
                labels: ['Completed', 'In Progress', 'Pending'],
                datasets: [{
                  label: 'Projects',
                  data: [data.completed, data.inprogress, data.pending || 0],
                  backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                  borderRadius: 8,
                  barPercentage: 0.6,
                  categoryPercentage: 0.6
                }]
              },
              options: {
                plugins: { legend: { display: false } },
                scales: {
                  x: { grid: { display: false }, ticks: { color: '#fff', font: { weight: 'bold', size: 16 } } },
                  y: { beginAtZero: true, grid: { color: '#343a40' }, ticks: { color: '#fff', font: { weight: 'bold', size: 16 }, stepSize: 1 } }
                }
              }
            });

            // Handle latest projects table with pagination
            let itemsPerPage = 5;
            let currentPage = 1;
            let totalPages = 1;
            let allProjects = data.latest || [];
            let showAll = false;

            function updateTable() {
              const tbody = document.getElementById(containerId + '-latest-projects');
              let pageItems;
              if (showAll) {
                pageItems = allProjects;
              } else {
                totalPages = Math.ceil(allProjects.length / itemsPerPage);
                pageItems = allProjects.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
              }

              tbody.innerHTML = '';
              if (pageItems.length) {
                pageItems.forEach(project => {
                  let statusText = '-';
                  let statusClass = '';
                  if (project.status) {
                    if (project.status.toLowerCase() === 'pending') {
                      statusText = 'Pending';
                      statusClass = 'bg-danger text-white';
                    } else if (project.status.toLowerCase() === 'inprogress') {
                      statusText = 'In Progress';
                      statusClass = 'bg-warning text-dark';
                    } else if (project.status.toLowerCase() === 'completed') {
                      statusText = 'Completed';
                      statusClass = 'bg-success text-white';
                    }
                  }
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td>${project.datetime}</td><td>${project.service}</td><td><span class="badge ${statusClass}" style="font-size:1rem;">${statusText}</span></td>`;
                  tbody.appendChild(tr);
                });
              } else {
                tbody.innerHTML = '<tr><td colspan="3">No projects found.</td></tr>';
              }

              // Update pagination info/buttons
              const infoElement = document.getElementById(containerId + '-latest-projects_info');
              const prevButton = document.getElementById(containerId + '-latest-projects_previous');
              const nextButton = document.getElementById(containerId + '-latest-projects_next');

              if (!showAll) {
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + pageItems.length;
                const totalItems = allProjects.length;
                infoElement.textContent = `Showing ${start + 1} to ${end} of ${totalItems} entries`;
                prevButton.classList.toggle('disabled', currentPage === 1);
                nextButton.classList.toggle('disabled', currentPage === totalPages);
              } else {
                infoElement.textContent = `Showing all ${allProjects.length} entries`;
                prevButton.classList.add('disabled');
                nextButton.classList.add('disabled');
              }
            }

            // Initialize pagination
            updateTable();

            // Add pagination button handlers
            document.getElementById(containerId + '-pagination-btn').addEventListener('click', function() {
              showAll = false;
              this.classList.add('active');
              document.getElementById(containerId + '-showall-btn').classList.remove('active');
              updateTable();
            });

            document.getElementById(containerId + '-showall-btn').addEventListener('click', function() {
              showAll = true;
              this.classList.add('active');
              document.getElementById(containerId + '-pagination-btn').classList.remove('active');
              updateTable();
            });

            // Add navigation button handlers
            document.getElementById(containerId + '-latest-projects_previous').addEventListener('click', function(e) {
              e.preventDefault();
              if (currentPage > 1) {
                currentPage--;
                updateTable();
              }
            });

            document.getElementById(containerId + '-latest-projects_next').addEventListener('click', function(e) {
              e.preventDefault();
              if (currentPage < totalPages) {
                currentPage++;
                updateTable();
              }
            });
          });
      }

      document.getElementById('isproject-prep-tab')?.addEventListener('shown.bs.tab', function() {
        renderISProjectAnalytics('isproject-prep-analytics', 'Prep');
      });
      document.getElementById('isproject-grc-tab')?.addEventListener('shown.bs.tab', function() {
        renderISProjectAnalytics('isproject-grc-analytics', 'GRC');
      });
      document.getElementById('isproject-sd-tab')?.addEventListener('shown.bs.tab', function() {
        renderISProjectAnalytics('isproject-sd-analytics', 'SD');
      });
      document.getElementById('isproject-secops-tab')?.addEventListener('shown.bs.tab', function() {
        renderISProjectAnalytics('isproject-secops-analytics', 'SecOPS');
      });
      document.getElementById('isproject-ot-tab')?.addEventListener('shown.bs.tab', function() {
        renderISProjectAnalytics('isproject-ot-analytics', 'OT');
      });
      // Load Prep by default
      if (document.getElementById('isproject-prep-tab') && document.getElementById('isproject-prep').classList.contains('show')) {
        renderISProjectAnalytics('isproject-prep-analytics', 'Prep');
      }

      // Service Progress Analytics
      let serviceProgressAnalyticsShowAll = false;
      function updateServiceProgressAnalytics() {
        fetch('service_analytics_summary.php')
          .then(response => response.json())
          .then(data => {
            document.getElementById('service-progress-total').textContent = data.total;
            document.getElementById('service-progress-completed').textContent = data.completed;
            document.getElementById('service-progress-inprogress').textContent = data.inprogress;
            document.getElementById('service-progress-completion-rate').textContent = data.completion_rate + '%';

            // Update progress bar
            const bar = document.getElementById('service-progress-completion-bar');
            bar.style.width = data.completion_rate + '%';
            bar.setAttribute('aria-valuenow', data.completion_rate);
            bar.textContent = data.completion_rate + '%';

            // Update pie chart
            if (window.serviceProgressStatusPieChart) window.serviceProgressStatusPieChart.destroy();
            const ctx = document.getElementById('service-progress-status-pie').getContext('2d');
            window.serviceProgressStatusPieChart = new Chart(ctx, {
              type: 'pie',
              data: {
                labels: ['Completed', 'In Progress'],
                datasets: [{
                  data: [data.completed, data.inprogress],
                  backgroundColor: ['#198754', '#ffc107'],
                }]
              },
              options: {
                plugins: {
                  legend: { display: true, position: 'bottom' }
                }
              }
            });

            // Update bar chart
            if (window.serviceProgressStatusBarChart) window.serviceProgressStatusBarChart.destroy();
            const barctx = document.getElementById('service-progress-status-bar').getContext('2d');
            window.serviceProgressStatusBarChart = new Chart(barctx, {
              type: 'bar',
              data: {
                labels: ['Completed', 'In Progress'],
                datasets: [{
                  label: 'Services',
                  data: [data.completed, data.inprogress],
                  backgroundColor: ['#198754', '#ffc107'],
                  borderRadius: 8,
                  barPercentage: 0.6,
                  categoryPercentage: 0.6
                }]
              },
              options: {
                plugins: {
                  legend: { display: false }
                },
                scales: {
                  x: {
                    grid: { display: false },
                    ticks: { color: '#fff', font: { weight: 'bold', size: 16 } }
                  },
                  y: {
                    beginAtZero: true,
                    grid: { color: '#343a40' },
                    ticks: { color: '#fff', font: { weight: 'bold', size: 16 }, stepSize: 1 }
                  }
                }
              }
            });

            // Update latest services table with pagination or show all
            const latestServicesBody = document.getElementById('service-progress-latest-services');
            let itemsPerPage = 5;
            let currentPage = 1;
            let totalPages = 1;
            let allServices = data.latest || [];
            function updateTable() {
              latestServicesBody.innerHTML = '';
              let pageItems;
              if (serviceProgressAnalyticsShowAll) {
                pageItems = allServices;
              } else {
                totalPages = Math.ceil(allServices.length / itemsPerPage);
                pageItems = allServices.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
              }
              if (pageItems.length) {
                pageItems.forEach(service => {
                  let statusText = '-';
                  let statusClass = '';
                  if (service.status) {
                    if (service.status.toLowerCase() === 'completed') {
                      statusText = 'Completed';
                      statusClass = 'bg-success text-white';
                    } else if (service.status.toLowerCase() === 'inprogress') {
                      statusText = 'In Progress';
                      statusClass = 'bg-warning text-dark';
                    } else {
                      statusText = service.status;
                      statusClass = 'bg-secondary text-white';
                    }
                  }
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td>${service.datetime}</td><td>${service.service}</td><td><span class="badge ${statusClass}" style="font-size:1rem;">${statusText}</span></td>`;
                  latestServicesBody.appendChild(tr);
                });
              } else {
                latestServicesBody.innerHTML = '<tr><td colspan="3">No services found.</td></tr>';
              }
              // Update pagination info/buttons
              const infoElement = document.getElementById('service-progress-latest-services_info');
              const prevButton = document.getElementById('service-progress-latest-services_previous');
              const nextButton = document.getElementById('service-progress-latest-services_next');
              if (!serviceProgressAnalyticsShowAll) {
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + pageItems.length;
                const totalItems = allServices.length;
                infoElement.textContent = `Showing ${start + 1} to ${end} of ${totalItems} entries`;
                prevButton.classList.toggle('disabled', currentPage === 1);
                nextButton.classList.toggle('disabled', currentPage === totalPages);
                prevButton.onclick = (e) => {
                  e.preventDefault();
                  if (currentPage > 1) {
                    currentPage--;
                    updateTable();
                  }
                };
                nextButton.onclick = (e) => {
                  e.preventDefault();
                  if (currentPage < totalPages) {
                    currentPage++;
                    updateTable();
                  }
                };
              } else {
                infoElement.textContent = `Showing all ${allServices.length} entries`;
                prevButton.classList.add('disabled');
                nextButton.classList.add('disabled');
                prevButton.onclick = (e) => { e.preventDefault(); };
                nextButton.onclick = (e) => { e.preventDefault(); };
              }
            }
            updateTable();
            // Attach button listeners (only once)
            if (!window.serviceProgressAnalyticsBtnListenersAdded) {
              document.getElementById('service-progress-analytics-pagination-btn').addEventListener('click', function() {
                serviceProgressAnalyticsShowAll = false;
                this.classList.add('active');
                document.getElementById('service-progress-analytics-showall-btn').classList.remove('active');
                updateServiceProgressAnalytics();
              });
              document.getElementById('service-progress-analytics-showall-btn').addEventListener('click', function() {
                serviceProgressAnalyticsShowAll = true;
                this.classList.add('active');
                document.getElementById('service-progress-analytics-pagination-btn').classList.remove('active');
                updateServiceProgressAnalytics();
              });
              window.serviceProgressAnalyticsBtnListenersAdded = true;
            }
          });
      }

      // Update on tab show
      document.getElementById('service-progress-analytics-tab')?.addEventListener('shown.bs.tab', updateServiceProgressAnalytics);
      // Optionally, update on page load if tab is active
      if (document.getElementById('service-progress-analytics')?.classList.contains('show')) {
        updateServiceProgressAnalytics();
      }

      // Daily Task Analytics
      let dailyTaskAnalyticsShowAll = false;
      function updateDailyTaskAnalytics() {
        fetch('daily_tasks_summary.php')
          .then(response => response.json())
          .then(data => {
            document.getElementById('daily-task-total').textContent = data.total;
            document.getElementById('daily-task-completed').textContent = data.completed;
            document.getElementById('daily-task-inprogress').textContent = data.inprogress;
            document.getElementById('daily-task-pending').textContent = data.pending;
            document.getElementById('daily-task-completion-rate').textContent = data.completion_rate + '%';

            // Update progress bar
            const bar = document.getElementById('daily-task-completion-bar');
            bar.style.width = data.completion_rate + '%';
            bar.setAttribute('aria-valuenow', data.completion_rate);
            bar.textContent = data.completion_rate + '%';

            // Update pie chart
            if (window.dailyTaskStatusPieChart) window.dailyTaskStatusPieChart.destroy();
            const ctx = document.getElementById('daily-task-status-pie').getContext('2d');
            window.dailyTaskStatusPieChart = new Chart(ctx, {
              type: 'pie',
              data: {
                labels: ['Completed', 'In Progress', 'Pending'],
                datasets: [{
                  data: [data.completed, data.inprogress, data.pending],
                  backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                }]
              },
              options: {
                plugins: {
                  legend: { display: true, position: 'bottom' }
                }
              }
            });

            // Update bar chart
            if (window.dailyTaskStatusBarChart) window.dailyTaskStatusBarChart.destroy();
            const barctx = document.getElementById('daily-task-status-bar').getContext('2d');
            window.dailyTaskStatusBarChart = new Chart(barctx, {
              type: 'bar',
              data: {
                labels: ['Completed', 'In Progress', 'Pending'],
                datasets: [{
                  label: 'Tasks',
                  data: [data.completed, data.inprogress, data.pending],
                  backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                  borderRadius: 8,
                  barPercentage: 0.6,
                  categoryPercentage: 0.6
                }]
              },
              options: {
                plugins: {
                  legend: { display: false }
                },
                scales: {
                  x: {
                    grid: { display: false },
                    ticks: { color: '#fff', font: { weight: 'bold', size: 16 } }
                  },
                  y: {
                    beginAtZero: true,
                    grid: { color: '#343a40' },
                    ticks: { color: '#fff', font: { weight: 'bold', size: 16 }, stepSize: 1 }
                  }
                }
              }
            });

            // Update latest tasks table with pagination or show all
            const latestTbody = document.getElementById('daily-task-latest-tasks');
            let itemsPerPage = 5;
            let currentPage = 1;
            let totalPages = 1;
            let allTasks = data.latest || [];
            function updateTable() {
              latestTbody.innerHTML = '';
              let pageItems;
              if (dailyTaskAnalyticsShowAll) {
                pageItems = allTasks;
              } else {
                totalPages = Math.ceil(allTasks.length / itemsPerPage);
                pageItems = allTasks.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
              }
              if (pageItems.length) {
                pageItems.forEach(task => {
                  let statusText = '-';
                  let statusClass = '';
                  if (task.status) {
                    if (task.status.toLowerCase() === 'pending') {
                      statusText = 'Pending';
                      statusClass = 'bg-danger text-white';
                    } else if (task.status.toLowerCase() === 'inprogress') {
                      statusText = 'In Progress';
                      statusClass = 'bg-warning text-dark';
                    } else if (task.status.toLowerCase() === 'completed') {
                      statusText = 'Completed';
                      statusClass = 'bg-success text-white';
                    } else {
                      statusText = task.status;
                      statusClass = 'bg-secondary text-white';
                    }
                  }
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td>${task.datetime}</td><td>${task.task_description}</td><td><span class="badge ${statusClass}" style="font-size:1rem;">${statusText}</span></td>`;
                  latestTbody.appendChild(tr);
                });
              } else {
                latestTbody.innerHTML = '<tr><td colspan="3">No tasks found.</td></tr>';
              }
              // Update pagination info/buttons
              const infoElement = document.getElementById('daily-task-latest-tasks_info');
              const prevButton = document.getElementById('daily-task-latest-tasks_previous');
              const nextButton = document.getElementById('daily-task-latest-tasks_next');
              if (!dailyTaskAnalyticsShowAll) {
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + pageItems.length;
                const totalItems = allTasks.length;
                infoElement.textContent = `Showing ${start + 1} to ${end} of ${totalItems} entries`;
                prevButton.classList.toggle('disabled', currentPage === 1);
                nextButton.classList.toggle('disabled', currentPage === totalPages);
                prevButton.onclick = (e) => {
                  e.preventDefault();
                  if (currentPage > 1) {
                    currentPage--;
                    updateTable();
                  }
                };
                nextButton.onclick = (e) => {
                  e.preventDefault();
                  if (currentPage < totalPages) {
                    currentPage++;
                    updateTable();
                  }
                };
              } else {
                infoElement.textContent = `Showing all ${allTasks.length} entries`;
                prevButton.classList.add('disabled');
                nextButton.classList.add('disabled');
                prevButton.onclick = (e) => { e.preventDefault(); };
                nextButton.onclick = (e) => { e.preventDefault(); };
              }
            }
            updateTable();
            // Attach button listeners (only once)
            if (!window.dailyTaskAnalyticsBtnListenersAdded) {
              document.getElementById('daily-task-analytics-pagination-btn').addEventListener('click', function() {
                dailyTaskAnalyticsShowAll = false;
                this.classList.add('active');
                document.getElementById('daily-task-analytics-showall-btn').classList.remove('active');
                updateDailyTaskAnalytics();
              });
              document.getElementById('daily-task-analytics-showall-btn').addEventListener('click', function() {
                dailyTaskAnalyticsShowAll = true;
                this.classList.add('active');
                document.getElementById('daily-task-analytics-pagination-btn').classList.remove('active');
                updateDailyTaskAnalytics();
              });
              window.dailyTaskAnalyticsBtnListenersAdded = true;
            }
          });
      }

      // Update on tab show
      document.getElementById('daily-task-analytics-tab')?.addEventListener('shown.bs.tab', updateDailyTaskAnalytics);
      // Optionally, update on page load if tab is active
      if (document.getElementById('daily-task-analytics')?.classList.contains('show')) {
        updateDailyTaskAnalytics();
      }

      // --- Daily Task Cards Sub-Tab ---
      let dailyTaskCardsFilter = 'all';
      function updateDailyTaskCards() {
        fetch('daily_tasks_summary.php')
          .then(response => response.json())
          .then(data => {
            const row = document.getElementById('daily-task-cards-row');
            row.innerHTML = '';
            let filtered = data.latest || [];
            if (dailyTaskCardsFilter === 'inprogress') {
              filtered = filtered.filter(task => task.status.toLowerCase() === 'inprogress');
            } else if (dailyTaskCardsFilter === 'pending') {
              filtered = filtered.filter(task => task.status.toLowerCase() === 'pending');
            } else if (dailyTaskCardsFilter === 'completed') {
              filtered = filtered.filter(task => task.status.toLowerCase() === 'completed');
            }
            if (filtered.length) {
              filtered.forEach(task => {
                let statusText = '-';
                let statusClass = '';
                if (task.status) {
                  if (task.status.toLowerCase() === 'pending') {
                    statusText = 'Pending';
                    statusClass = 'bg-danger text-white';
                  } else if (task.status.toLowerCase() === 'inprogress') {
                    statusText = 'In Progress';
                    statusClass = 'bg-warning text-dark';
                  } else if (task.status.toLowerCase() === 'completed') {
                    statusText = 'Completed';
                    statusClass = 'bg-success text-white';
                  } else {
                    statusText = task.status;
                    statusClass = 'bg-secondary text-white';
                  }
                }
                const card = document.createElement('div');
                card.className = 'col-3';
                card.innerHTML = `
                  <div class="card shadow-sm text-center" style="max-width:260px; width:100%; margin:0 auto; padding:1.2rem 0.7rem; min-height:220px; display:flex; flex-direction:column; justify-content:center; align-items:center; border-width:2.5px; border-style:solid; border-color:${
                    statusClass.includes('bg-danger') ? '#dc3545' : statusClass.includes('bg-warning') ? '#ffc107' : statusClass.includes('bg-success') ? '#198754' : '#343a40'
                  }">
                    <span class="badge ${statusClass}" style="font-size:1rem; margin-bottom:0.7rem;">${statusText}</span>
                    <div style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">${task.task_description || '-'}</div>
                    <div style="color:#f6ad55; font-size:1rem; margin-bottom:0.5rem;">${task.datetime || '-'}</div>
                    <div style="color:#f6ad55; font-size:1rem; font-weight:600; margin-bottom:0.5rem;">${task.shift ? task.shift.charAt(0).toUpperCase() + task.shift.slice(1) : '-'}</div>
                    <div style="font-size:0.95rem; margin-bottom:0.5rem;">Responsibility: <span style="color:#0dcaf0;">${task.responsibility || '-'}</span></div>
                    <div style="font-size:0.95rem; margin-bottom:0.5rem;">Assigned To: <span style="color:#ffc107;">${task.assigned_to || '-'}</span></div>
                    <div style="font-size:2.5rem; font-weight:900; color:#198754; margin-top:1rem; letter-spacing:1px; text-shadow:0 2px 8px #0008;">${typeof task.percent_completed === 'number' ? task.percent_completed + '%' : '-'}</div>
                  </div>
                `;
                row.appendChild(card);
              });
            } else {
              row.innerHTML = '<div class="text-center">No tasks found.</div>';
            }
          });
      }

      // Attach to sub-tab event
      document.getElementById('daily-task-cards-tab')?.addEventListener('shown.bs.tab', updateDailyTaskCards);
      // Optionally, update on page load if tab is active
      if (document.getElementById('daily-task-cards')?.classList.contains('show')) {
        updateDailyTaskCards();
      }

      // Filter button handlers
      document.getElementById('daily-filter-all').addEventListener('click', function() {
        dailyTaskCardsFilter = 'all';
        setDailyTaskCardsFilterBtn(this);
        updateDailyTaskCards();
      });
      document.getElementById('daily-filter-inprogress').addEventListener('click', function() {
        dailyTaskCardsFilter = 'inprogress';
        setDailyTaskCardsFilterBtn(this);
        updateDailyTaskCards();
      });
      document.getElementById('daily-filter-pending').addEventListener('click', function() {
        dailyTaskCardsFilter = 'pending';
        setDailyTaskCardsFilterBtn(this);
        updateDailyTaskCards();
      });
      document.getElementById('daily-filter-completed').addEventListener('click', function() {
        dailyTaskCardsFilter = 'completed';
        setDailyTaskCardsFilterBtn(this);
        updateDailyTaskCards();
      });

      function setDailyTaskCardsFilterBtn(activeBtn) {
        document.getElementById('daily-filter-all').classList.remove('active');
        document.getElementById('daily-filter-inprogress').classList.remove('active');
        document.getElementById('daily-filter-pending').classList.remove('active');
        document.getElementById('daily-filter-completed').classList.remove('active');
        activeBtn.classList.add('active');
      }

      // --- Service Progress Cards Sub-Tab ---
      let serviceProgressCardsFilter = 'all';
      function updateServiceProgressCards() {
        fetch('service_progress_cards.php')
          .then(response => response.json())
          .then(data => {
            const row = document.getElementById('service-progress-cards-row');
            row.innerHTML = '';
            let filtered = data.latest || data || [];
            if (serviceProgressCardsFilter === 'inprogress') {
              filtered = filtered.filter(service => {
                let progressNum = parseInt(service.progress);
                return !isNaN(progressNum) && progressNum >= 50 && progressNum < 100;
              });
            } else if (serviceProgressCardsFilter === 'pending') {
              filtered = filtered.filter(service => {
                let progressNum = parseInt(service.progress);
                return !isNaN(progressNum) && progressNum < 50;
              });
            } else if (serviceProgressCardsFilter === 'completed') {
              filtered = filtered.filter(service => {
                let progressNum = parseInt(service.progress);
                return !isNaN(progressNum) && progressNum === 100;
              });
            }
            if (filtered.length) {
              filtered.forEach(service => {
                let progress = service.progress || '';
                let progressNum = parseInt(progress);
                let statusText = '-';
                let statusClass = '';
                let borderColor = '#343a40';
                if (!isNaN(progressNum)) {
                  if (progressNum === 100) {
                    statusText = 'Completed';
                    statusClass = 'bg-success';
                    borderColor = '#198754';
                  } else if (progressNum >= 50) {
                    statusText = 'In Progress';
                    statusClass = 'bg-warning text-dark';
                    borderColor = '#ffc107';
                  } else {
                    statusText = 'Pending';
                    statusClass = 'bg-danger';
                    borderColor = '#dc3545';
                  }
                }
                // Format date as 'Month Day, Year'
                let formattedDate = service.due_date;
                if (service.due_date && /^\d{4}-\d{2}-\d{2}$/.test(service.due_date)) {
                  const d = new Date(service.due_date);
                  if (!isNaN(d.getTime())) {
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    formattedDate = `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
                  }
                } else if (service.due_date && /\d{1,2}\/\d{1,2}\/\d{4}/.test(service.due_date)) {
                  // Handle MM/DD/YYYY
                  const parts = service.due_date.split('/');
                  const d = new Date(parts[2], parts[0] - 1, parts[1]);
                  if (!isNaN(d.getTime())) {
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    formattedDate = `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
                  }
                }
                row.innerHTML += `
                  <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card shadow-sm text-center" style="max-width:260px; width:100%; margin:0 auto; padding:1.2rem 0.7rem; min-height:220px; display:flex; flex-direction:column; justify-content:center; align-items:center; border-width:2.5px; border-style:solid; border-color:${borderColor}">
                      <span class="badge ${statusClass}" style="font-size:1rem; margin-bottom:0.7rem;">${statusText}</span>
                      <div style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">${service.service || '-'}</div>
                      <div style="color:#f6ad55; font-size:1rem; margin-bottom:0.5rem;">${formattedDate || '-'}</div>
                      <div style="font-size:0.95rem; margin-bottom:0.5rem;">Comments: <span style="color:#ffc107;">${service.comments || '-'}</span></div>
                      <div style="font-size:2.5rem; font-weight:900; color:#198754; margin-top:1rem; letter-spacing:1px; text-shadow:0 2px 8px #0008;">${typeof progressNum === 'number' && !isNaN(progressNum) ? progressNum + '%' : '-'}</div>
                    </div>
                  </div>
                `;
              });
            } else {
              row.innerHTML = '<div class="text-center">No service progress found.</div>';
            }
          });
      }

      // Attach to sub-tab event
      document.getElementById('service-progress-cards-tab')?.addEventListener('shown.bs.tab', updateServiceProgressCards);
      // Optionally, update on page load if tab is active
      if (document.getElementById('service-progress-cards')?.classList.contains('show')) {
        updateServiceProgressCards();
      }

      // Filter button handlers
      document.getElementById('service-progress-filter-all').addEventListener('click', function() {
        serviceProgressCardsFilter = 'all';
        setServiceProgressCardsFilterBtn(this);
        updateServiceProgressCards();
      });
      document.getElementById('service-progress-filter-inprogress').addEventListener('click', function() {
        serviceProgressCardsFilter = 'inprogress';
        setServiceProgressCardsFilterBtn(this);
        updateServiceProgressCards();
      });
      document.getElementById('service-progress-filter-pending').addEventListener('click', function() {
        serviceProgressCardsFilter = 'pending';
        setServiceProgressCardsFilterBtn(this);
        updateServiceProgressCards();
      });
      document.getElementById('service-progress-filter-completed').addEventListener('click', function() {
        serviceProgressCardsFilter = 'completed';
        setServiceProgressCardsFilterBtn(this);
        updateServiceProgressCards();
      });

      function setServiceProgressCardsFilterBtn(activeBtn) {
        document.getElementById('service-progress-filter-all').classList.remove('active');
        document.getElementById('service-progress-filter-inprogress').classList.remove('active');
        document.getElementById('service-progress-filter-pending').classList.remove('active');
        document.getElementById('service-progress-filter-completed').classList.remove('active');
        activeBtn.classList.add('active');
      }

      // IS Project Edit Button Handler
      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-is-project-btn')) {
          const btn = event.target;
          document.getElementById('edit-is-project-id').value = btn.getAttribute('data-id');
          document.getElementById('edit-is-project-service').value = btn.getAttribute('data-service');
          document.getElementById('edit-is-project-due-date').value = btn.getAttribute('data-due_date');
          document.getElementById('edit-is-project-progress').value = btn.getAttribute('data-progress');
          document.getElementById('edit-is-project-assign-to-team').value = btn.getAttribute('data-assign_to_team');
          document.getElementById('edit-is-project-comments').value = btn.getAttribute('data-comments');
          var modal = new bootstrap.Modal(document.getElementById('editIsProjectModal'));
          modal.show();
        }
      });

      // --- IS Project Service Progress Sub-Tab ---
      let isprojectServiceProgressAnalyticsShowAll = false;
      function updateIsProjectServiceProgressAnalytics() {
        fetch('service_analytics_summary.php')
          .then(response => response.json())
          .then(data => {
            document.getElementById('isproject-service-progress-total').textContent = data.total;
            document.getElementById('isproject-service-progress-completed').textContent = data.completed;
            document.getElementById('isproject-service-progress-inprogress').textContent = data.inprogress;
            document.getElementById('isproject-service-progress-completion-rate').textContent = data.completion_rate + '%';

            // Update progress bar
            const bar = document.getElementById('isproject-service-progress-completion-bar');
            bar.style.width = data.completion_rate + '%';
            bar.setAttribute('aria-valuenow', data.completion_rate);
            bar.textContent = data.completion_rate + '%';

            // Update latest services table with pagination or show all
            const latestServicesBody = document.getElementById('isproject-service-progress-latest-services');
            let itemsPerPage = 5;
            let currentPage = 1;
            let totalPages = 1;
            let allServices = data.latest || [];

            function updateTable() {
              latestServicesBody.innerHTML = '';
              let pageItems;
              if (isprojectServiceProgressAnalyticsShowAll) {
                pageItems = allServices;
              } else {
                totalPages = Math.ceil(allServices.length / itemsPerPage);
                pageItems = allServices.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
              }
              if (pageItems.length) {
                pageItems.forEach(service => {
                  let statusText = '-';
                  let statusClass = '';
                  if (service.status) {
                    if (service.status.toLowerCase() === 'completed') {
                      statusText = 'Completed';
                      statusClass = 'bg-success text-white';
                    } else if (service.status.toLowerCase() === 'inprogress') {
                      statusText = 'In Progress';
                      statusClass = 'bg-warning text-dark';
                    } else {
                      statusText = service.status;
                      statusClass = 'bg-secondary text-white';
                    }
                  }
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td>${service.datetime}</td><td>${service.service}</td><td><span class="badge ${statusClass}" style="font-size:1rem;">${statusText}</span></td>`;
                  latestServicesBody.appendChild(tr);
                });
              } else {
                latestServicesBody.innerHTML = '<tr><td colspan="3">No services found.</td></tr>';
              }
              // Update pagination info/buttons
              const infoElement = document.getElementById('isproject-service-progress-latest-services_info');
              const prevButton = document.getElementById('isproject-service-progress-latest-services_previous');
              const nextButton = document.getElementById('isproject-service-progress-latest-services_next');
              
              if (infoElement) {
                infoElement.textContent = `Showing ${(currentPage - 1) * itemsPerPage + 1} to ${Math.min(currentPage * itemsPerPage, allServices.length)} of ${allServices.length} entries`;
              }
              if (prevButton) {
                prevButton.classList.toggle('disabled', currentPage === 1);
              }
              if (nextButton) {
                nextButton.classList.toggle('disabled', currentPage === totalPages);
              }
            }
            updateTable();

            // Add pagination event listeners if not already added
            if (!window.isprojectServiceProgressAnalyticsBtnListenersAdded) {
              document.getElementById('isproject-service-progress-analytics-pagination-btn').addEventListener('click', function() {
                isprojectServiceProgressAnalyticsShowAll = false;
                this.classList.add('active');
                document.getElementById('isproject-service-progress-analytics-showall-btn').classList.remove('active');
                updateIsProjectServiceProgressAnalytics();
              });
              document.getElementById('isproject-service-progress-analytics-showall-btn').addEventListener('click', function() {
                isprojectServiceProgressAnalyticsShowAll = true;
                this.classList.add('active');
                document.getElementById('isproject-service-progress-analytics-pagination-btn').classList.remove('active');
                updateIsProjectServiceProgressAnalytics();
              });
              // Pagination button listeners
              document.getElementById('isproject-service-progress-latest-services_previous').addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage > 1 && !this.classList.contains('disabled')) {
                  currentPage--;
                  updateTable();
                }
              });
              document.getElementById('isproject-service-progress-latest-services_next').addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage < totalPages && !this.classList.contains('disabled')) {
                  currentPage++;
                  updateTable();
                }
              });
              window.isprojectServiceProgressAnalyticsBtnListenersAdded = true;
            }
          });
      }

      // Update on tab show
      document.getElementById('isproject-service-progress-analytics-tab')?.addEventListener('shown.bs.tab', updateIsProjectServiceProgressAnalytics);
      // Optionally, update on page load if tab is active
      if (document.getElementById('isproject-service-progress-analytics')?.classList.contains('show')) {
        updateIsProjectServiceProgressAnalytics();
      }

      // --- IS Project Service Progress Cards Sub-Tab ---
      let isprojectServiceProgressCardsFilter = 'all';
      function updateIsProjectServiceProgressCards() {
        fetch('service_progress_cards.php')
          .then(response => response.json())
          .then(data => {
            const row = document.getElementById('isproject-service-progress-cards-row');
            row.innerHTML = '';
            let filtered = data.latest || data || [];
            if (isprojectServiceProgressCardsFilter === 'inprogress') {
              filtered = filtered.filter(service => {
                let progressNum = parseInt(service.progress);
                return !isNaN(progressNum) && progressNum >= 50 && progressNum < 100;
              });
            } else if (isprojectServiceProgressCardsFilter === 'pending') {
              filtered = filtered.filter(service => {
                let progressNum = parseInt(service.progress);
                return !isNaN(progressNum) && progressNum < 50;
              });
            } else if (isprojectServiceProgressCardsFilter === 'completed') {
              filtered = filtered.filter(service => {
                let progressNum = parseInt(service.progress);
                return !isNaN(progressNum) && progressNum === 100;
              });
            }
            if (filtered.length) {
              filtered.forEach(service => {
                const card = document.createElement('div');
                card.className = 'col-12 col-sm-6 col-md-4 col-lg-3';
                let statusText = '-';
                let statusClass = '';
                if (service.status) {
                  if (service.status.toLowerCase() === 'completed') {
                    statusText = 'Completed';
                    statusClass = 'bg-success text-white';
                  } else if (service.status.toLowerCase() === 'inprogress') {
                    statusText = 'In Progress';
                    statusClass = 'bg-warning text-dark';
                  } else {
                    statusText = service.status;
                    statusClass = 'bg-secondary text-white';
                  }
                }
                // Use progress-based status for the badge
                let progressNum = parseInt(service.progress);
                let borderColor = '#343a40';
                if (!isNaN(progressNum)) {
                  if (progressNum === 100) {
                    statusText = 'Completed';
                    statusClass = 'bg-success text-white';
                    borderColor = '#198754';
                  } else if (progressNum >= 50) {
                    statusText = 'In Progress';
                    statusClass = 'bg-warning text-dark';
                    borderColor = '#ffc107';
                  } else {
                    statusText = 'Pending';
                    statusClass = 'bg-danger text-white';
                    borderColor = '#dc3545';
                  }
                }
                card.innerHTML = `
                  <div class="card shadow-sm text-center" style="max-width:260px; width:100%; margin:0 auto; padding:1.2rem 0.7rem; min-height:220px; display:flex; flex-direction:column; justify-content:center; align-items:center; border-width:2.5px; border-style:solid; border-color:${borderColor}">
                    <span class="badge ${statusClass}" style="font-size:1rem; margin-bottom:0.7rem;">${statusText}</span>
                    <div style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">${service.service || '-'}</div>
                    <div style="color:#f6ad55; font-size:1rem; margin-bottom:0.5rem;">${service.due_date ? new Date(service.due_date).toLocaleDateString() : '-'}</div>
                    <div style="font-size:0.95rem; margin-bottom:0.5rem;">Comments: <span style="color:#ffc107;">${service.comments || '-'}</span></div>
                    <div style="font-size:2.5rem; font-weight:900; color:#198754; margin-top:1rem; letter-spacing:1px; text-shadow:0 2px 8px #0008;">${typeof progressNum === 'number' && !isNaN(progressNum) ? progressNum + '%' : '-'}</div>
                  </div>
                `;
                row.appendChild(card);
              });
            } else {
              row.innerHTML = '<div class="text-center">No service progress found.</div>';
            }
          });
      }

      // Attach to sub-tab event
      document.getElementById('isproject-service-progress-cards-tab')?.addEventListener('shown.bs.tab', updateIsProjectServiceProgressCards);
      // Optionally, update on page load if tab is active
      if (document.getElementById('isproject-service-progress-cards')?.classList.contains('show')) {
        updateIsProjectServiceProgressCards();
      }

      // Filter button handlers for IS Project Service Progress Cards
      document.getElementById('isproject-service-progress-filter-all')?.addEventListener('click', function() {
        isprojectServiceProgressCardsFilter = 'all';
        setIsProjectServiceProgressCardsFilterBtn(this);
        updateIsProjectServiceProgressCards();
      });
      document.getElementById('isproject-service-progress-filter-inprogress')?.addEventListener('click', function() {
        isprojectServiceProgressCardsFilter = 'inprogress';
        setIsProjectServiceProgressCardsFilterBtn(this);
        updateIsProjectServiceProgressCards();
      });
      document.getElementById('isproject-service-progress-filter-pending')?.addEventListener('click', function() {
        isprojectServiceProgressCardsFilter = 'pending';
        setIsProjectServiceProgressCardsFilterBtn(this);
        updateIsProjectServiceProgressCards();
      });
      document.getElementById('isproject-service-progress-filter-completed')?.addEventListener('click', function() {
        isprojectServiceProgressCardsFilter = 'completed';
        setIsProjectServiceProgressCardsFilterBtn(this);
        updateIsProjectServiceProgressCards();
      });

      function setIsProjectServiceProgressCardsFilterBtn(activeBtn) {
        document.getElementById('isproject-service-progress-filter-all').classList.remove('active');
        document.getElementById('isproject-service-progress-filter-inprogress').classList.remove('active');
        document.getElementById('isproject-service-progress-filter-pending').classList.remove('active');
        document.getElementById('isproject-service-progress-filter-completed').classList.remove('active');
        activeBtn.classList.add('active');
      }

      // Add Service Progress Button Handler for IS Project
      document.getElementById('isproject-addServiceProgressBtn')?.addEventListener('click', function() {
        // Clear all fields
        document.getElementById('edit-service-progress-id').value = '';
        document.getElementById('edit-service-progress-service').value = '';
        document.getElementById('edit-service-progress-due-date').value = '';
        document.getElementById('edit-service-progress-progress').value = '';
        document.getElementById('edit-service-progress-comments').value = '';
        // Set modal title
        document.getElementById('editServiceProgressModalLabel').textContent = 'Add Service';
        var modal = new bootstrap.Modal(document.getElementById('editServiceProgressModal'));
        modal.show();
      });

      // --- IS Project Service Progress Analytics Charts ---
      function renderIsProjectServiceProgressCharts(data) {
        // Pie Chart
        if (window.isprojectServiceStatusPieChart) window.isprojectServiceStatusPieChart.destroy();
        const pieCtx = document.getElementById('isproject-service-status-pie').getContext('2d');
        window.isprojectServiceStatusPieChart = new Chart(pieCtx, {
          type: 'pie',
          data: {
            labels: ['Completed', 'In Progress'],
            datasets: [{
              data: [data.completed, data.inprogress],
              backgroundColor: ['#198754', '#ffc107'],
            }]
          },
          options: {
            plugins: {
              legend: { display: true, position: 'bottom' }
            }
          }
        });
        // Bar Chart
        if (window.isprojectServiceStatusBarChart) window.isprojectServiceStatusBarChart.destroy();
        const barCtx = document.getElementById('isproject-service-status-bar').getContext('2d');
        window.isprojectServiceStatusBarChart = new Chart(barCtx, {
          type: 'bar',
          data: {
            labels: ['Completed', 'In Progress'],
            datasets: [{
              label: 'Services',
              data: [data.completed, data.inprogress],
              backgroundColor: ['#198754', '#ffc107'],
              borderRadius: 8,
              barPercentage: 0.6,
              categoryPercentage: 0.6
            }]
          },
          options: {
            plugins: {
              legend: { display: false }
            },
            scales: {
              x: {
                grid: { display: false },
                ticks: { color: '#fff', font: { weight: 'bold', size: 16 } }
              },
              y: {
                beginAtZero: true,
                grid: { color: '#343a40' },
                ticks: { color: '#fff', font: { weight: 'bold', size: 16 }, stepSize: 1 }
              }
            }
          }
        });
      }

      // Patch updateIsProjectServiceProgressAnalytics to render charts and fix pagination
      function updateIsProjectServiceProgressAnalytics() {
        fetch('service_analytics_summary.php')
          .then(response => response.json())
          .then(data => {
            document.getElementById('isproject-service-progress-total').textContent = data.total;
            document.getElementById('isproject-service-progress-completed').textContent = data.completed;
            document.getElementById('isproject-service-progress-inprogress').textContent = data.inprogress;
            document.getElementById('isproject-service-progress-completion-rate').textContent = data.completion_rate + '%';

            // Render charts
            renderIsProjectServiceProgressCharts(data);

            // Update progress bar
            const bar = document.getElementById('isproject-service-progress-completion-bar');
            bar.style.width = data.completion_rate + '%';
            bar.setAttribute('aria-valuenow', data.completion_rate);
            bar.textContent = data.completion_rate + '%';

            // Update latest services table with pagination or show all
            const latestServicesBody = document.getElementById('isproject-service-progress-latest-services');
            let itemsPerPage = 5;
            let currentPage = 1;
            let totalPages = 1;
            let allServices = data.latest || [];

            function updateTable() {
              latestServicesBody.innerHTML = '';
              let pageItems;
              if (isprojectServiceProgressAnalyticsShowAll) {
                pageItems = allServices;
              } else {
                totalPages = Math.ceil(allServices.length / itemsPerPage);
                pageItems = allServices.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
              }
              if (pageItems.length) {
                pageItems.forEach(service => {
                  let statusText = '-';
                  let statusClass = '';
                  if (service.status) {
                    if (service.status.toLowerCase() === 'completed') {
                      statusText = 'Completed';
                      statusClass = 'bg-success text-white';
                    } else if (service.status.toLowerCase() === 'inprogress') {
                      statusText = 'In Progress';
                      statusClass = 'bg-warning text-dark';
                    } else {
                      statusText = service.status;
                      statusClass = 'bg-secondary text-white';
                    }
                  }
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td>${service.datetime}</td><td>${service.service}</td><td><span class="badge ${statusClass}" style="font-size:1rem;">${statusText}</span></td>`;
                  latestServicesBody.appendChild(tr);
                });
              } else {
                latestServicesBody.innerHTML = '<tr><td colspan="3">No services found.</td></tr>';
              }
              // Update pagination info/buttons
              const infoElement = document.getElementById('isproject-service-progress-latest-services_info');
              const prevButton = document.getElementById('isproject-service-progress-latest-services_previous');
              const nextButton = document.getElementById('isproject-service-progress-latest-services_next');
              if (!isprojectServiceProgressAnalyticsShowAll) {
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + pageItems.length;
                const totalItems = allServices.length;
                if (infoElement) infoElement.textContent = `Showing ${start + 1} to ${end} of ${totalItems} entries`;
                if (prevButton) prevButton.classList.toggle('disabled', currentPage === 1);
                if (nextButton) nextButton.classList.toggle('disabled', currentPage === totalPages);
              } else {
                if (infoElement) infoElement.textContent = `Showing all ${allServices.length} entries`;
                if (prevButton) prevButton.classList.add('disabled');
                if (nextButton) nextButton.classList.add('disabled');
              }
            }
            updateTable();

            // Add pagination event listeners if not already added
            if (!window.isprojectServiceProgressAnalyticsBtnListenersAdded) {
              document.getElementById('isproject-service-progress-analytics-pagination-btn').addEventListener('click', function() {
                isprojectServiceProgressAnalyticsShowAll = false;
                this.classList.add('active');
                document.getElementById('isproject-service-progress-analytics-showall-btn').classList.remove('active');
                updateIsProjectServiceProgressAnalytics();
              });
              document.getElementById('isproject-service-progress-analytics-showall-btn').addEventListener('click', function() {
                isprojectServiceProgressAnalyticsShowAll = true;
                this.classList.add('active');
                document.getElementById('isproject-service-progress-analytics-pagination-btn').classList.remove('active');
                updateIsProjectServiceProgressAnalytics();
              });
              // Pagination button listeners
              document.getElementById('isproject-service-progress-latest-services_previous').addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage > 1 && !this.classList.contains('disabled')) {
                  currentPage--;
                  updateTable();
                }
              });
              document.getElementById('isproject-service-progress-latest-services_next').addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage < totalPages && !this.classList.contains('disabled')) {
                  currentPage++;
                  updateTable();
                }
              });
              window.isprojectServiceProgressAnalyticsBtnListenersAdded = true;
            }
          });
      }

      document.getElementById('isproject-is-tab')?.addEventListener('shown.bs.tab', function() {
        renderISProjectAnalytics('isproject-is-analytics', 'IS');
      });

      // --- Hajj Program Overview Dashboard ---
      let hajjOverviewShowAll = false;
      function updateHajjOverview() {
        fetch('hajj_overview_summary_fixed.php')
          .then(response => response.json())
          .then(data => {
            // Update cards
            document.getElementById('hajj-overview-completion-rate').textContent = data.overall_health + '%';
            document.getElementById('hajj-overview-total').textContent = data.total_projects;
            document.getElementById('hajj-overview-completed').textContent = data.completed_projects;
            document.getElementById('hajj-overview-inprogress').textContent = data.active_projects;

            // Update overall completion progress bar
            const hajjBar = document.getElementById('hajj-overall-completion-bar');
            if (hajjBar) {
              hajjBar.style.width = data.overall_health + '%';
              hajjBar.setAttribute('aria-valuenow', data.overall_health);
              hajjBar.textContent = data.overall_health + '%';
            }            

            // Update pie chart
            if (window.hajjOverviewStatusPieChart) window.hajjOverviewStatusPieChart.destroy();
            const ctxPie = document.getElementById('hajj-overview-status-pie').getContext('2d');
            window.hajjOverviewStatusPieChart = new Chart(ctxPie, {
              type: 'pie',
              data: {
                labels: ['Completed', 'In Progress'],
                datasets: [{
                  data: [data.completed_projects, data.active_projects],
                  backgroundColor: ['#198754', '#ffc107'],
                }]
              },
              options: {
                plugins: { legend: { display: true, position: 'bottom' } }
              }
            });

            // Update bar chart
            if (window.hajjOverviewStatusBarChart) window.hajjOverviewStatusBarChart.destroy();
            const ctxBar = document.getElementById('hajj-overview-status-bar').getContext('2d');
            const teams = Object.keys(data.bar_data);
            window.hajjOverviewStatusBarChart = new Chart(ctxBar, {
              type: 'bar',
              data: {
                labels: teams,
                datasets: [
                  {
                    label: 'Completed',
                    data: teams.map(t => data.bar_data[t].completed),
                    backgroundColor: '#198754',
                  },
                  {
                    label: 'In Progress',
                    data: teams.map(t => data.bar_data[t].inprogress),
                    backgroundColor: '#ffc107',
                  }
                ]
              },
              options: {
                plugins: { legend: { display: true, position: 'bottom' } },
                responsive: true,
                scales: { y: { beginAtZero: true } }
              }
            });

            // Update in-progress table with pagination or show all
            const tbody = document.getElementById('hajj-overview-inprogress-table');
            let itemsPerPage = 5;
            let currentPage = 1;
            let totalPages = 1;
            let allProjects = data.inprogress_projects || [];
            function updateTable() {
              tbody.innerHTML = '';
              let pageItems;
              if (hajjOverviewShowAll) {
                pageItems = allProjects;
              } else {
                totalPages = Math.ceil(allProjects.length / itemsPerPage);
                pageItems = allProjects.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
              }
              if (pageItems.length) {
                pageItems.forEach(proj => {
                  const tr = document.createElement('tr');
                  tr.innerHTML = `<td>${proj.due_date || '-'}</td><td>${proj.service || '-'}</td><td>${proj.progress || '-'}%</td><td>${proj.assign_to_team || '-'}</td><td>${proj.comments || ''}</td>`;
                  tbody.appendChild(tr);
                });
              } else {
                tbody.innerHTML = '<tr><td colspan="5">No in-progress projects found.</td></tr>';
              }
              // Update pagination info/buttons
              const infoElement = document.getElementById('hajj-overview-inprogress-table_info');
              const prevButton = document.getElementById('hajj-overview-inprogress-table_previous');
              const nextButton = document.getElementById('hajj-overview-inprogress-table_next');
              if (!hajjOverviewShowAll) {
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + pageItems.length;
                const totalItems = allProjects.length;
                infoElement.textContent = `Showing ${start + 1} to ${end} of ${totalItems} entries`;
                prevButton.classList.toggle('disabled', currentPage === 1);
                nextButton.classList.toggle('disabled', currentPage === totalPages);
                prevButton.onclick = (e) => {
                  e.preventDefault();
                  if (currentPage > 1) {
                    currentPage--;
                    updateTable();
                  }
                };
                nextButton.onclick = (e) => {
                  e.preventDefault();
                  if (currentPage < totalPages) {
                    currentPage++;
                    updateTable();
                  }
                };
              } else {
                infoElement.textContent = `Showing all ${allProjects.length} entries`;
                prevButton.classList.add('disabled');
                nextButton.classList.add('disabled');
                prevButton.onclick = (e) => { e.preventDefault(); };
                nextButton.onclick = (e) => { e.preventDefault(); };
              }
            }
            updateTable();
            // Attach button listeners (only once)
            if (!window.hajjOverviewBtnListenersAdded) {
              document.getElementById('hajj-overview-pagination-btn').addEventListener('click', function() {
                hajjOverviewShowAll = false;
                this.classList.add('active');
                document.getElementById('hajj-overview-showall-btn').classList.remove('active');
                updateHajjOverview();
              });
              document.getElementById('hajj-overview-showall-btn').addEventListener('click', function() {
                hajjOverviewShowAll = true;
                this.classList.add('active');
                document.getElementById('hajj-overview-pagination-btn').classList.remove('active');
                updateHajjOverview();
              });
              window.hajjOverviewBtnListenersAdded = true;
            }
          });
      }
      // Update on tab show
      if (document.getElementById('hajjprogramoverview-tab')) {
        document.getElementById('hajjprogramoverview-tab').addEventListener('shown.bs.tab', updateHajjOverview);
      }
      if (document.getElementById('hajjprogramoverview') && document.getElementById('hajjprogramoverview').classList.contains('show')) updateHajjOverview();

      // ... existing code ...
      $(document).ready(function() {
        // Initialize Projects Table only once
        if ($.fn.DataTable.isDataTable('#projectsTable')) {
          $('#projectsTable').DataTable().clear().destroy();
        }
        var selectedProjectId = null;
        var projectsTable = $('#projectsTable').DataTable({
          ajax: {
            url: 'project_management/api/project_management_api.php?endpoint=projects',
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
            { data: 'description' },
            { data: 'start_date' },
            { data: 'due_date' },
            { data: 'created_by' },
            { data: 'created_at' },
            { data: null, orderable: false, render: function(data, type, row) {
                if (userRole !== 'admin' && userRole !== 'operator') return '';
                return `
                  <button class="btn btn-sm btn-warning edit-project-btn"
                    data-id="${row.id}"
                    data-name="${$('<div>').text(row.name).html()}"
                    data-description="${$('<div>').text(row.description).html()}"
                    data-start_date="${row.start_date}"
                    data-due_date="${row.due_date}"
                    data-created_by="${row.created_by}"
                    data-created_at="${row.created_at}"
                  >Edit</button>
                  <button class="btn btn-sm btn-danger delete-project-btn" data-id="${row.id}">Delete</button>
                `;
              }
            }
          ]
        });

        // Initialize Project Tasks Table only once
        if ($.fn.DataTable.isDataTable('#projectTasksTable')) {
          $('#projectTasksTable').DataTable().clear().destroy();
        }
        var projectTasksTable = $('#projectTasksTable').DataTable({
          ajax: {
            url: 'project_management/api/project_management_api.php?endpoint=tasks',
            data: function(d) {
              if (selectedProjectId) d.project_id = selectedProjectId;
            },
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
            { data: 'description' },
            { data: 'assigned_to_username' },
            { data: 'due_date' },
            { data: 'status', render: function(data) {
              if (data === 'completed') return '<span class="badge bg-success">Completed</span>';
              if (data === 'pending') return '<span class="badge bg-danger">Pending</span>';
              if (data === 'inprogress') return '<span class="badge bg-warning text-dark">In Progress</span>';
              return data;
            } },
            { data: 'created_at' },
            { data: null, orderable: false, render: function(data, type, row) {
                if (userRole !== 'admin' && userRole !== 'operator') return '';
                return `
                  <button class="btn btn-sm btn-warning edit-projecttask-btn"
                    data-id="${row.id}"
                    data-project_id="${row.project_id}"
                    data-project_name="${$('<div>').text(row.project_name).html()}"
                    data-title="${$('<div>').text(row.title).html()}"
                    data-description="${$('<div>').text(row.description).html()}"
                    data-assigned_to_username="${$('<div>').text(row.assigned_to_username).html()}"
                    data-due_date="${row.due_date}"
                    data-status="${row.status}"
                    data-created_at="${row.created_at}"
                  >Edit</button>
                  <button class="btn btn-sm btn-danger delete-projecttask-btn" data-id="${row.id}">Delete</button>
                `;
              }
            }
          ]
        });
        // ... existing code ...
      });
      // ... existing code ...
    });
  document.addEventListener('DOMContentLoaded', function() {
      // Set initial width based on checked radio
      const checkedRadio = document.querySelector('input[name="dashboard-width"]:checked');
      const container = document.getElementById('dashboard-main-container');
      if (checkedRadio && container) {
          const val = checkedRadio.value;
          container.style.maxWidth = val + 'vw';
          container.style.width = val + 'vw';
      }

      // Handle radio changes
      document.querySelectorAll('input[name="dashboard-width"]').forEach(radio => {
          radio.addEventListener('change', function() {
              if (this.checked) {
                  const val = this.value;
                  container.style.maxWidth = val + 'vw';
                  container.style.width = val + 'vw';
              }
          });
      });
  });
  $(document).ready(function() {
    // ... existing code ...
    $('#logbook-tab').on('click', function(e) {
      e.preventDefault();
      var pageName = 'logbook.php';
      window.open('?p=' + pageName, '_blank');
    });
    $('#pm-tab').on('click', function(e) {
      e.preventDefault();
      var pageName = 'project_management/project_management_dashboard.php';
      window.open('?p=' + pageName, '_blank');
    });
    // ... existing code ...
  });

  // Add Project button/modal logic
  $('#addProjectBtn').on('click', function() {
    $('#addProjectModal').modal('show');
  });
  $('#addProjectForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serializeArray();
    var data = {};
    formData.forEach(function(item) { data[item.name] = item.value; });
    $.ajax({
      url: 'project_management/api/project_management_api.php?endpoint=projects',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: function(res) {
        $('#addProjectModal').modal('hide');
        $('#addProjectForm')[0].reset();
        $('#projectsTable').DataTable().ajax.reload();
      },
      error: function(xhr) {
        alert('Failed to add project.');
      }
    });
  });

  // Add Project Task button/modal logic
  $('#addProjectTaskBtn').on('click', function() {
    // Fetch projects for the dropdown
    $.get('project_management/api/project_management_api.php?endpoint=projects', function(res) {
      var select = $('#projectTaskProjectSelect');
      select.empty();
      select.append('<option value="">Select project...</option>');
      if (res.projects && res.projects.length) {
        res.projects.forEach(function(proj) {
          select.append('<option value="' + proj.id + '">' + proj.name + '</option>');
        });
      }
      // Pre-select the current project if one is selected
      if (selectedProjectId) {
        select.val(selectedProjectId);
      }
      // Enhance with Select2 if available
      if ($.fn.select2) {
        select.select2({ dropdownParent: $('#addProjectTaskModal'), width: '100%', placeholder: 'Select project...' });
      }
    });
    // Fetch users for the Assigned To dropdown
    $.get('user_management_api.php?action=list', function(res) {
      var select = $('#projectTaskAssignedToSelect');
      select.empty();
      select.append('<option value="">Select user...</option>');
      if (res && res.length) {
        res.forEach(function(user) {
          select.append('<option value="' + user.username + '">' + user.username + ' (' + user.role + ')</option>');
        });
      }
      // Enhance with Select2 if available
      if ($.fn.select2) {
        select.select2({ dropdownParent: $('#addProjectTaskModal'), width: '100%', placeholder: 'Select user...' });
      }
    });
    $('#addProjectTaskModal').modal('show');
  });
  // Optional: live filter for the dropdown (simple client-side for now)
  $('#projectTaskProjectSelect').on('change keyup', function() {
    var search = $(this).val().toLowerCase();
    $(this).find('option').each(function() {
      var text = $(this).text().toLowerCase();
      $(this).toggle(text.indexOf(search) > -1 || !search);
    });
  });
  $('#addProjectTaskForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serializeArray();
    var data = {};
    formData.forEach(function(item) { data[item.name] = item.value; });
    if (!data.project_id) {
      alert('Please select a project.');
      return;
    }
    $.ajax({
      url: 'project_management/api/project_management_api.php?endpoint=tasks',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: function(res) {
        $('#addProjectTaskModal').modal('hide');
        $('#addProjectTaskForm')[0].reset();
        $('#projectTasksTable').DataTable().ajax.reload();
      },
      error: function(xhr) {
        alert('Failed to add project task.');
      }
    });
  });

  // ... existing code ...
  // Project Edit Button Handler
  $(document).on('click', '#projectsTable .edit-project-btn', function() {
    const btn = this;
    $('#edit-project-id').val($(btn).data('id'));
    $('#edit-project-name').val($(btn).data('name'));
    $('#edit-project-description').val($(btn).data('description'));
    $('#edit-project-start-date').val($(btn).data('start_date'));
    $('#edit-project-due-date').val($(btn).data('due_date'));
    $('#editProjectModal').modal('show');
  });
  // Project Save Button Handler
  $('#saveProjectBtn').on('click', function() {
    const data = {
      id: $('#edit-project-id').val(),
      name: $('#edit-project-name').val(),
      description: $('#edit-project-description').val(),
      start_date: $('#edit-project-start-date').val(),
      due_date: $('#edit-project-due-date').val()
    };
    $.ajax({
      url: 'project_management/api/project_management_api.php?endpoint=projects',
      method: 'PUT',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: function(res) {
        $('#editProjectModal').modal('hide');
        $('#projectsTable').DataTable().ajax.reload();
      },
      error: function(xhr) {
        alert('Failed to update project.');
      }
    });
  });
  // Project Delete Button Handler
  $(document).on('click', '#projectsTable .delete-project-btn', function() {
    if (!confirm('Are you sure you want to delete this project?')) return;
    const id = $(this).data('id');
    $.ajax({
      url: 'project_management/api/project_management_api.php?endpoint=projects',
      method: 'DELETE',
      contentType: 'application/json',
      data: JSON.stringify({ id }),
      success: function(res) {
        $('#projectsTable').DataTable().ajax.reload();
      },
      error: function(xhr) {
        alert('Failed to delete project.');
      }
    });
  });
  // Project Task Edit Button Handler
  $(document).on('click', '#projectTasksTable .edit-projecttask-btn', function() {
    const btn = this;
    // Populate dropdowns first
    $.get('project_management/api/project_management_api.php?endpoint=projects', function(res) {
      var select = $('#edit-projecttask-project');
      select.empty();
      select.append('<option value="">Select project...</option>');
      if (res.projects && res.projects.length) {
        res.projects.forEach(function(proj) {
          select.append('<option value="' + proj.id + '">' + proj.name + '</option>');
        });
      }
      select.val($(btn).data('project_id'));
    });
    $.get('user_management_api.php?action=list', function(res) {
      var select = $('#edit-projecttask-assigned-to');
      select.empty();
      select.append('<option value="">Select user...</option>');
      if (res && res.length) {
        res.forEach(function(user) {
          select.append('<option value="' + user.username + '">' + user.username + ' (' + user.role + ')</option>');
        });
      }
      select.val($(btn).data('assigned_to_username'));
    });
    $('#edit-projecttask-id').val($(btn).data('id'));
    $('#edit-projecttask-title').val($(btn).data('title'));
    $('#edit-projecttask-description').val($(btn).data('description'));
    $('#edit-projecttask-due-date').val($(btn).data('due_date'));
    $('#edit-projecttask-status').val($(btn).data('status'));
    $('#editProjectTaskModal').modal('show');
  });
  // Project Task Save Button Handler
  $('#saveProjectTaskBtn').on('click', function() {
    const data = {
      id: $('#edit-projecttask-id').val(),
      project_id: $('#edit-projecttask-project').val(),
      title: $('#edit-projecttask-title').val(),
      description: $('#edit-projecttask-description').val(),
      assigned_to: $('#edit-projecttask-assigned-to').val(),
      due_date: $('#edit-projecttask-due-date').val(),
      status: $('#edit-projecttask-status').val()
    };
    $.ajax({
      url: 'project_management/api/project_management_api.php?endpoint=tasks',
      method: 'PUT',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: function(res) {
        $('#editProjectTaskModal').modal('hide');
        $('#projectTasksTable').DataTable().ajax.reload();
      },
      error: function(xhr) {
        alert('Failed to update project task.');
      }
    });
  });
  // Project Task Delete Button Handler
  $(document).on('click', '#projectTasksTable .delete-projecttask-btn', function() {
    if (!confirm('Are you sure you want to delete this project task?')) return;
    const id = $(this).data('id');
    $.ajax({
      url: 'project_management/api/project_management_api.php?endpoint=tasks',
      method: 'DELETE',
      contentType: 'application/json',
      data: JSON.stringify({ id }),
      success: function(res) {
        $('#projectTasksTable').DataTable().ajax.reload();
      },
      error: function(xhr) {
        alert('Failed to delete project task.');
      }
    });
  });
  // ... existing code ...
