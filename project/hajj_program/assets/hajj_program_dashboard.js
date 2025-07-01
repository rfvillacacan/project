        // Global variables
        let currentProjectId = null;
        const domains = ['Prep', 'GRC', 'SD', 'SecOPS', 'OT', 'IS'];

        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadProjects();
            loadDomainStats();
            setupEventListeners();
        });

        // Setup event listeners
        function setupEventListeners() {
            // Domain cards click
            document.querySelectorAll('.domain-card').forEach(card => {
                card.addEventListener('click', function() {
                    const domain = this.dataset.domain;
                    loadProjects(domain);
                });
            });

            // Save project button
            document.getElementById('saveProjectBtn').addEventListener('click', saveProject);
        }

        // Load projects
        async function loadProjects(domain = null) {
            try {
                const url = domain 
                    ? `api/hajj_program_api.php?endpoint=projects&domain=${domain}`
                    : 'api/hajj_program_api.php?endpoint=projects';
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.projects) {
                    displayProjects(data.projects);
                }
            } catch (error) {
                console.error('Error loading projects:', error);
            }
        }

        // Display projects in table
        function displayProjects(projects) {
            const tbody = document.getElementById('projectsTableBody');
            tbody.innerHTML = '';
            
            projects.forEach(project => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${project.project_name}</td>
                    <td>${project.domain}</td>
                    <td><span class="badge bg-${getStatusColor(project.status)}">${project.status}</span></td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: ${project.progress}%">
                                ${project.progress}%
                            </div>
                        </div>
                    </td>
                    <td>${formatDate(project.due_date)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewProject(${project.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="editProject(${project.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Load domain statistics
        async function loadDomainStats() {
            try {
                const response = await fetch('api/hajj_program_api.php?endpoint=projects');
                const data = await response.json();
                
                if (data.projects) {
                    updateDomainCards(data.projects);
                }
            } catch (error) {
                console.error('Error loading domain stats:', error);
            }
        }

        // Update domain cards with statistics
        function updateDomainCards(projects) {
            domains.forEach(domain => {
                const domainProjects = projects.filter(p => p.domain === domain);
                const totalProgress = domainProjects.reduce((sum, p) => sum + p.progress, 0);
                const avgProgress = domainProjects.length ? Math.round(totalProgress / domainProjects.length) : 0;
                
                const card = document.querySelector(`.domain-card[data-domain="${domain}"]`);
                if (card) {
                    const progressBar = card.querySelector('.progress-bar');
                    const progressText = card.querySelector('small');
                    
                    progressBar.style.width = `${avgProgress}%`;
                    progressText.textContent = `${avgProgress}% Complete`;
                }
            });
        }

        // Save new project
        async function saveProject() {
            const form = document.getElementById('newProjectForm');
            const formData = new FormData(form);
            const projectData = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('api/hajj_program_api.php?endpoint=projects', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(projectData)
                });
                
                const data = await response.json();
                if (data.id) {
                    bootstrap.Modal.getInstance(document.getElementById('newProjectModal')).hide();
                    form.reset();
                    loadProjects();
                    loadDomainStats();
                }
            } catch (error) {
                console.error('Error saving project:', error);
            }
        }

        // Helper functions
        function getStatusColor(status) {
            const colors = {
                'Not Started': 'secondary',
                'In Progress': 'primary',
                'Completed': 'success',
                'On Hold': 'warning'
            };
            return colors[status] || 'secondary';
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        }

        // View project details
        async function viewProject(id) {
            currentProjectId = id;
            try {
                const [projectResponse, milestonesResponse, risksResponse, activitiesResponse] = await Promise.all([
                    fetch(`api/hajj_program_api.php?endpoint=projects&id=${id}`),
                    fetch(`api/hajj_program_api.php?endpoint=milestones&project_id=${id}`),
                    fetch(`api/hajj_program_api.php?endpoint=risks&project_id=${id}`),
                    fetch(`api/hajj_program_api.php?endpoint=activities&project_id=${id}`)
                ]);

                const [projectData, milestonesData, risksData, activitiesData] = await Promise.all([
                    projectResponse.json(),
                    milestonesResponse.json(),
                    risksResponse.json(),
                    activitiesResponse.json()
                ]);

                // Update timeline
                displayTimeline(milestonesData.milestones);
                
                // Update risks
                displayRisks(risksData.risks);
                
                // Update activities
                displayActivities(activitiesData.activities);
            } catch (error) {
                console.error('Error loading project details:', error);
            }
        }

        // Display timeline
        function displayTimeline(milestones) {
            const container = document.getElementById('timelineContent');
            container.innerHTML = '';
            
            milestones.forEach(milestone => {
                const item = document.createElement('div');
                item.className = 'timeline-item';
                item.innerHTML = `
                    <h6>${milestone.milestone_name}</h6>
                    <p class="text-muted">${milestone.description}</p>
                    <div class="d-flex justify-content-between">
                        <small>Due: ${formatDate(milestone.due_date)}</small>
                        <span class="badge bg-${getStatusColor(milestone.status)}">${milestone.status}</span>
                    </div>
                `;
                container.appendChild(item);
            });
        }

        // Display risks
        function displayRisks(risks) {
            const container = document.getElementById('risksContent');
            container.innerHTML = '';
            
            risks.forEach(risk => {
                const item = document.createElement('div');
                item.className = `card mb-2 risk-card risk-${risk.impact.toLowerCase()}`;
                item.innerHTML = `
                    <div class="card-body">
                        <h6 class="card-title">${risk.risk_description}</h6>
                        <p class="card-text">${risk.mitigation_plan || 'No mitigation plan'}</p>
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-${getRiskColor(risk.impact)}">${risk.impact}</span>
                            <small>Due: ${formatDate(risk.due_date)}</small>
                        </div>
                    </div>
                `;
                container.appendChild(item);
            });
        }

        // Display activities
        function displayActivities(activities) {
            const container = document.getElementById('activitiesContent');
            container.innerHTML = '';
            
            activities.forEach(activity => {
                const item = document.createElement('div');
                item.className = 'timeline-item';
                item.innerHTML = `
                    <h6>${activity.activity_type}</h6>
                    <p class="text-muted">${activity.description}</p>
                    <small>By ${activity.created_by} on ${formatDate(activity.created_at)}</small>
                `;
                container.appendChild(item);
            });
        }

        // Helper function for risk colors
        function getRiskColor(impact) {
            const colors = {
                'Critical': 'danger',
                'High': 'warning',
                'Medium': 'info',
                'Low': 'success'
            };
            return colors[impact] || 'secondary';
        }

        // Edit project
        async function editProject(id) {
            try {
                const response = await fetch(`api/hajj_program_api.php?endpoint=projects&id=${id}`);
                const data = await response.json();
                
                if (data.project) {
                    const project = data.project;
                    
                    // Populate form fields
                    document.getElementById('editProjectId').value = project.id;
                    document.getElementById('editProjectName').value = project.project_name;
                    document.getElementById('editProjectDomain').value = project.domain;
                    document.getElementById('editProjectDescription').value = project.description;
                    document.getElementById('editProjectStartDate').value = project.start_date;
                    document.getElementById('editProjectDueDate').value = project.due_date;
                    document.getElementById('editProjectStatus').value = project.status;
                    document.getElementById('editProjectProgress').value = project.progress;
                    document.getElementById('progressValue').textContent = `${project.progress}%`;
                    document.getElementById('editProjectPriority').value = project.priority;
                    document.getElementById('editProjectAssignedTo').value = project.assigned_to;
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('editProjectModal'));
                    modal.show();
                }
            } catch (error) {
                console.error('Error loading project:', error);
                alert('Error loading project details');
            }
        }

        // Update project
        async function updateProject() {
            const form = document.getElementById('editProjectForm');
            const formData = new FormData(form);
            const projectData = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('api/hajj_program_api.php?endpoint=projects', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(projectData)
                });
                
                const data = await response.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editProjectModal')).hide();
                    loadProjects();
                    loadDomainStats();
                } else {
                    alert('Error updating project: ' + data.message);
                }
            } catch (error) {
                console.error('Error updating project:', error);
                alert('Error updating project');
            }
        }

        // Delete project
        async function deleteProject() {
            const projectId = document.getElementById('editProjectId').value;
            
            if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
                try {
                    const response = await fetch(`api/hajj_program_api.php?endpoint=projects&id=${projectId}`, {
                        method: 'DELETE'
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editProjectModal')).hide();
                        loadProjects();
                        loadDomainStats();
                    } else {
                        alert('Error deleting project: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error deleting project:', error);
                    alert('Error deleting project');
                }
            }
        }

        // Update progress value display
        document.getElementById('editProjectProgress').addEventListener('input', function() {
            document.getElementById('progressValue').textContent = `${this.value}%`;
        });

        // Add event listeners for edit project modal
        document.getElementById('updateProjectBtn').addEventListener('click', updateProject);
        document.getElementById('deleteProjectBtn').addEventListener('click', deleteProject);
