<?php header("Content-Type: application/javascript"); ?>
    // Get allowed tables from backend (sync with dashboard6.php logic)
    const REPORTING_TABLES = <?php
      //$showOnly = ['tbl_isprojects', 'daily_tasks', 'service_progress'];
      $showOnly = ['tbl_isprojects', 'daily_tasks'];
      echo json_encode($showOnly);
    ?>;
    // --- Reporting Page JS ---
    document.addEventListener('DOMContentLoaded', function() {
      // Populate the table dropdown
      function populateReportTableDropdown() {
        const select = document.getElementById('report-table-select');
        if (!select || !REPORTING_TABLES) return;
        select.innerHTML = '';
        REPORTING_TABLES.forEach(tbl => {
          const opt = document.createElement('option');
          opt.value = tbl;
          opt.textContent = tbl;
          select.appendChild(opt);
        });
      }
      populateReportTableDropdown();
      document.getElementById('report-table-select').addEventListener('change', loadReportDataAndSummary);
      // --- Main logic to load data and summary ---
      function loadReportDataAndSummary() {
        const tableName = document.getElementById('report-table-select').value;
        if (!tableName) return;
        document.getElementById('report-summary-cards').innerHTML = '<div class="col-12 text-center text-secondary">Loading summary...</div>';
        document.getElementById('report-filters').innerHTML = '';
        const table = document.getElementById('report-table');
        // Destroy DataTable if exists before changing table structure
        if ($.fn.DataTable.isDataTable('#report-table')) {
          $('#report-table').DataTable().clear().destroy();
        }
        table.innerHTML = '<thead><tr><th>Loading...</th></tr></thead><tbody></tbody>';
        fetch(`reporting_api.php?table=${encodeURIComponent(tableName)}`)
          .then(resp => resp.json())
          .then(json => {
            if (!json || !json.columns || !json.data) {
              table.innerHTML = '<thead><tr><th>Error loading data</th></tr></thead><tbody></tbody>';
              return;
            }
            // Rebuild thead and tbody
            let thead = '<tr>' + json.columns.map(col => `<th>${col}</th>`).join('') + '</tr>';
            let tbody = json.data.map(row => {
              return '<tr>' + json.columns.map(col => `<td>${row[col] !== null ? row[col] : ''}</td>`).join('') + '</tr>';
            }).join('');
            table.innerHTML = `<thead>${thead}</thead><tbody>${tbody}</tbody>`;
            // Re-initialize DataTable
            $('#report-table').DataTable({
              responsive: true,
              paging: true,
              searching: true,
              ordering: true,
              info: true,
              autoWidth: false,
              lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
              language: { search: "Filter:" },
              dom: 'Bfrtip',
              buttons: [
                { extend: 'pdf', className: 'btn btn-outline-light', text: '<i class="fas fa-file-pdf"></i> PDF' },
                { extend: 'excel', className: 'btn btn-outline-success', text: '<i class="fas fa-file-excel"></i> Excel' },
                { extend: 'print', className: 'btn btn-outline-info', text: '<i class="fas fa-print"></i> Print' }
              ]
            });
            setTimeout(() => {
              const dtBtns = document.querySelector('#report-table_wrapper .dt-buttons');
              const exportBtns = document.getElementById('report-export-btns');
              if (dtBtns && exportBtns) {
                exportBtns.innerHTML = '';
                exportBtns.appendChild(dtBtns);
              }
            }, 200);
            // Prepare filters if columns exist
            let filtersHtml = '';
            if (json.columns.some(c => c.toLowerCase().includes('date'))) {
              filtersHtml += `<div><label>Date Range: <input type="date" id="report-filter-date-from"> to <input type="date" id="report-filter-date-to"></label></div>`;
            }
            if (json.columns.some(c => c.toLowerCase().includes('status'))) {
              const statusCol = json.columns.find(c => c.toLowerCase().includes('status'));
              const statuses = [...new Set(json.data.map(row => row[statusCol]).filter(Boolean))];
              filtersHtml += `<div><label>Status: <select id="report-filter-status"><option value="">All</option>${statuses.map(s => `<option value="${s}">${s}</option>`).join('')}</select></label></div>`;
            }
            document.getElementById('report-filters').innerHTML = filtersHtml;
            if (document.getElementById('report-filter-date-from') && document.getElementById('report-filter-date-to')) {
              document.getElementById('report-filter-date-from').addEventListener('change', reloadWithFilters);
              document.getElementById('report-filter-date-to').addEventListener('change', reloadWithFilters);
            }
            if (document.getElementById('report-filter-status')) {
              document.getElementById('report-filter-status').addEventListener('change', reloadWithFilters);
            }
            function reloadWithFilters() {
              const dateFrom = document.getElementById('report-filter-date-from')?.value;
              const dateTo = document.getElementById('report-filter-date-to')?.value;
              const status = document.getElementById('report-filter-status')?.value;
              let url = `reporting_api.php?table=${encodeURIComponent(tableName)}`;
              if (dateFrom && dateTo) url += `&date_from=${encodeURIComponent(dateFrom)}&date_to=${encodeURIComponent(dateTo)}`;
              if (status) url += `&status=${encodeURIComponent(status)}`;
              // Destroy DataTable before changing table structure
              if ($.fn.DataTable.isDataTable('#report-table')) {
                $('#report-table').DataTable().clear().destroy();
              }
              table.innerHTML = '<thead><tr><th>Loading...</th></tr></thead><tbody></tbody>';
              fetch(url)
                .then(resp => resp.json())
                .then(json => {
                  if (!json || !json.columns || !json.data) {
                    table.innerHTML = '<thead><tr><th>Error loading data</th></tr></thead><tbody></tbody>';
                    return;
                  }
                  let thead = '<tr>' + json.columns.map(col => `<th>${col}</th>`).join('') + '</tr>';
                  let tbody = json.data.map(row => {
                    return '<tr>' + json.columns.map(col => `<td>${row[col] !== null ? row[col] : ''}</td>`).join('') + '</tr>';
                  }).join('');
                  table.innerHTML = `<thead>${thead}</thead><tbody>${tbody}</tbody>`;
                  $('#report-table').DataTable({
                    responsive: true,
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    autoWidth: false,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                    language: { search: "Filter:" },
                    dom: 'Bfrtip',
                    buttons: [
                      { extend: 'pdf', className: 'btn btn-outline-light', text: '<i class="fas fa-file-pdf"></i> PDF' },
                      { extend: 'excel', className: 'btn btn-outline-success', text: '<i class="fas fa-file-excel"></i> Excel' },
                      { extend: 'print', className: 'btn btn-outline-info', text: '<i class="fas fa-print"></i> Print' }
                    ]
                  });
                  setTimeout(() => {
                    const dtBtns = document.querySelector('#report-table_wrapper .dt-buttons');
                    const exportBtns = document.getElementById('report-export-btns');
                    if (dtBtns && exportBtns) {
                      exportBtns.innerHTML = '';
                      exportBtns.appendChild(dtBtns);
                    }
                  }, 200);
                });
              loadReportSummary(tableName, dateFrom, dateTo, status);
            }
            loadReportSummary(tableName);
          });
      }
      function loadReportSummary(tableName, dateFrom, dateTo, status) {
        let url = `reporting_api.php?table=${encodeURIComponent(tableName)}&action=summary`;
        if (dateFrom && dateTo) url += `&date_from=${encodeURIComponent(dateFrom)}&date_to=${encodeURIComponent(dateTo)}`;
        if (status) url += `&status=${encodeURIComponent(status)}`;
        fetch(url)
          .then(resp => resp.json())
          .then(summary => {
            let html = '';
            if (summary && typeof summary === 'object') {
              html += '<div class="row g-4 mb-2">';
              html += `<div class="col-6 col-md-2"><div class="summary-card p-3 text-center"><div class="summary-title">Total</div><div class="summary-value" style="font-size:2rem; font-weight:700; color:#fff;">${summary.total ?? 0}</div></div></div>`;
              ['completed', 'in progress', 'pending', 'closed', 'open'].forEach(stat => {
                if (typeof summary[stat] !== 'undefined') {
                  let color = stat === 'completed' ? '#198754' : stat === 'in progress' ? '#ffc107' : stat === 'pending' ? '#dc3545' : '#adb5bd';
                  html += `<div class="col-6 col-md-2"><div class="summary-card p-3 text-center"><div class="summary-title" style="text-transform:capitalize;">${stat}</div><div class="summary-value" style="font-size:2rem; font-weight:700; color:${color};">${summary[stat]}</div></div></div>`;
                }
              });
              html += '</div>';
            } else {
              html = '<div class="col-12 text-center text-secondary">No summary data.</div>';
            }
            document.getElementById('report-summary-cards').innerHTML = html;
          });
      }
      // Initial load
      loadReportDataAndSummary();
    });
