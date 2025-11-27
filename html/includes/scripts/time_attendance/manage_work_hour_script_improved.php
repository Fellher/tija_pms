<div class="row workHourImproved">
	<div class="col-12">
		<!-- Quick Actions Bar -->
		<div class="quick-actions-bar mb-3 p-2 bg-light rounded">
			<div class="row align-items-center">
				<div class="col-md-6">
					<button type="button" class="btn btn-sm btn-outline-primary me-2" id="copyPreviousDayBtn" title="Copy from previous day">
						<i class="fa-solid fa-copy"></i> Copy Previous Day
					</button>
					<button type="button" class="btn btn-sm btn-outline-secondary me-2" id="useTemplateBtn" title="Use saved template">
						<i class="fa-solid fa-file-lines"></i> Use Template
					</button>
					<button type="button" class="btn btn-sm btn-outline-info" id="saveAsTemplateBtn" title="Save current entry as template">
						<i class="fa-solid fa-save"></i> Save as Template
					</button>
				</div>
				<div class="col-md-6 text-end">
					<span class="badge bg-info-subtle text-info fs-6">
						<i class="fa-solid fa-keyboard"></i> Tip: Use Tab to navigate, Ctrl+Enter to submit
					</span>
				</div>
			</div>
		</div>

		<!-- Main Entry Form -->
		<div class="row g-3">
			<!-- Left Column - Project & Task Selection -->
			<div class="col-lg-8">
					<div class="card shadow-sm h-100">
					<div class="card-body">
						<h6 class="card-title mb-3 text-primary">
							<i class="fa-solid fa-tasks me-2"></i>Task Selection
						</h6>

						<!-- Selection Mode Tabs -->
						<ul class="nav nav-tabs mb-3" id="selectionModeTabs" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link active" id="search-tab" data-bs-toggle="tab" data-bs-target="#search-mode" type="button" role="tab">
									<i class="fa-solid fa-search me-1"></i> Quick Search
								</button>
							</li>
							<!-- <li class="nav-item" role="presentation">
								<button class="nav-link" id="browse-tab" data-bs-toggle="tab" data-bs-target="#browse-mode" type="button" role="tab">
									<i class="fa-solid fa-sitemap me-1"></i> Browse & Select
								</button>
							</li> -->
						</ul>

						<div class="tab-content" id="selectionModeContent">
							<!-- SEARCH MODE -->
							<div class="tab-pane fade show active" id="search-mode" role="tabpanel">
								<div class="form-group mb-1 position-relative">
									<label for="projectTaskSearch" class="form-label fw-semibold">
										<i class="fa-solid fa-search me-1"></i> Search Project / Task
									</label>
									<input
										type="text"
										id="projectTaskSearch"
										class="form-control form-control-lg bg-light-blue border-2"
										placeholder="Start typing project name, client, or task..."
										autocomplete="off"
									>

									<!-- Search Results Dropdown -->
									<div id="searchResults" class="search-results-dropdown shadow-lg d-none">
										<div class="search-results-header p-2 bg-primary text-white">
											<small>Recent Tasks</small>
										</div>
										<div id="recentTasks" class="recent-tasks-list"></div>
										<div class="search-results-header p-2 bg-light">
											<small>Search Results</small>
										</div>
										<div id="searchResultsList" class="p-2">
											<div class="text-muted text-center py-3">
												<i class="fa-solid fa-search fs-4"></i>
												<p class="mb-0 mt-2">Start typing to search...</p>
											</div>
										</div>
									</div>
								</div>
								<!-- Dynamic spacer to push content down when results are showing -->
								<div id="searchResultsSpacer" class="search-results-spacer"></div>
							</div>

							<!-- BROWSE MODE -->
							<div class="tab-pane fade" id="browse-mode" role="tabpanel">
								<div class="hierarchical-selector">
									<!-- Step 1: Select Client -->
									<div class="form-group mb-3">
										<label for="browseClient" class="form-label fw-semibold">
											<span class="badge bg-primary me-1">1</span> Select Client
										</label>
										<select id="browseClient" class="form-control bg-light-blue">
											<option value="">-- Select Client --</option>
										</select>
									</div>

									<!-- Step 2: Select Project -->
									<div class="form-group mb-3">
										<label for="browseProject" class="form-label fw-semibold">
											<span class="badge bg-primary me-1">2</span> Select Project
										</label>
										<select id="browseProject" class="form-control bg-light-blue" disabled>
											<option value="">-- Select Client First --</option>
										</select>
									</div>

									<!-- Step 3: Select Phase -->
									<div class="form-group mb-3">
										<label for="browsePhase" class="form-label fw-semibold">
											<span class="badge bg-primary me-1">3</span> Select Phase
										</label>
										<select id="browsePhase" class="form-control bg-light-blue" disabled>
											<option value="">-- Select Project First --</option>
										</select>
									</div>

									<!-- Step 4: Select Task -->
									<div class="form-group mb-3">
										<label for="browseTask" class="form-label fw-semibold">
											<span class="badge bg-primary me-1">4</span> Select Task
										</label>
										<select id="browseTask" class="form-control bg-light-blue" disabled>
											<option value="">-- Select Phase First --</option>
										</select>
										<small class="form-text text-muted">
											<i class="fa-solid fa-info-circle me-1"></i>
											Only tasks assigned to you are shown
										</small>
									</div>

									<!-- Optional: Select Subtask -->
									<div class="form-group mb-3 d-none" id="subtaskGroup">
										<label for="browseSubtask" class="form-label fw-semibold">
											<span class="badge bg-secondary me-1">5</span> Select Subtask (Optional)
										</label>
										<select id="browseSubtask" class="form-control bg-light-blue" disabled>
											<option value="">-- Select Task First --</option>
										</select>
									</div>

									<!-- Confirm Selection Button -->
									<div class="text-end">
										<button type="button" class="btn btn-success" id="confirmBrowseSelection" disabled>
											<i class="fa-solid fa-check me-1"></i> Confirm Selection
										</button>
									</div>
								</div>
							</div>
						</div>

						<!-- Selected Task Display -->
						<div id="selectedTaskDisplay" class="selected-task-display d-none mb-3 p-3 bg-success-subtle border border-success rounded">
							<div class="d-flex justify-content-between align-items-start">
								<div>
									<h6 class="mb-1 text-success">
										<i class="fa-solid fa-check-circle me-1"></i>
										<span id="selectedProjectName"></span>
									</h6>
									<p class="mb-0 small">
										<span class="badge bg-primary me-1" id="selectedClientName"></span>
										<span class="badge bg-info me-1" id="selectedPhaseName"></span>
										<span class="badge bg-secondary" id="selectedTaskName"></span>
									</p>
								</div>
								<button type="button" class="btn btn-sm btn-danger" id="clearSelectionBtn">
									<i class="fa-solid fa-times"></i>
								</button>
							</div>
							<input type="hidden" name="clientID" id="hiddenClientID">
							<input type="hidden" name="projectID" id="hiddenProjectID">
							<input type="hidden" name="projectPhaseID" id="hiddenPhaseID">
							<input type="hidden" name="projectTaskID" id="hiddenTaskID">
						</div>

						<!-- Task Details Row -->
						<div class="row g-2">
							<div class="col-md-4">
								<label for="workTypeID" class="form-label fw-semibold">
									<i class="fa-solid fa-briefcase me-1"></i> Work Type
								</label>
								<select name="workTypeID" id="workTypeID" class="form-control bg-light-blue" required>
									<?php echo Form::populate_select_element_from_object($workType, 'workTypeID', 'workTypeName', '', '', 'Select Work Type') ?>
								</select>
							</div>

							<div class="col-md-4">
								<label for="taskDuration" class="form-label fw-semibold">
									<i class="fa-solid fa-clock me-1"></i> Duration
								</label>
								<div class="input-group">
									<input
										type="text"
										class="form-control bg-light-blue workHours"
										name="taskDuration"
										id="taskDuration"
										placeholder="HH:MM"
										pattern="[0-9]{1,2}:[0-5][0-9]"
										required
									>
									<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
										<i class="fa-solid fa-ellipsis-v"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end quick-time-select">
										<li><a class="dropdown-item" href="#" data-time="00:15">15 min</a></li>
										<li><a class="dropdown-item" href="#" data-time="00:30">30 min</a></li>
										<li><a class="dropdown-item" href="#" data-time="01:00">1 hour</a></li>
										<li><a class="dropdown-item" href="#" data-time="02:00">2 hours</a></li>
										<li><a class="dropdown-item" href="#" data-time="04:00">4 hours</a></li>
										<li><a class="dropdown-item" href="#" data-time="08:00">8 hours</a></li>
									</ul>
								</div>
								<div class="form-text text-danger d-none" id="durationError"></div>
							</div>

							<div class="col-md-4">
								<label for="taskStatusID" class="form-label fw-semibold">
									<i class="fa-solid fa-flag me-1"></i> Status
								</label>
								<select name="taskStatusID" id="taskStatusID" class="form-control bg-light-blue">
									<?php echo Form::populate_select_element_from_object($taskStatusList, 'taskStatusID', 'taskStatusName', '2', '', 'Select Status') ?>
								</select>
							</div>
						</div>

						<!-- Hidden Date Field -->
						<input type="hidden" name="taskDate" value="<?php echo date_format($dt,'Y-m-d') ?>">
					</div>
				</div>
			</div>

			<!-- Right Column - Description & Attachments -->
			<div class="col-lg-4">
				<div class="card shadow-sm h-100">
					<div class="card-body">
						<h6 class="card-title mb-3 text-primary">
							<i class="fa-solid fa-file-alt me-2"></i>Details
						</h6>

						<!-- Description -->
						<div class="form-group mb-3">
							<label for="taskNarrative" class="form-label fw-semibold">
								Description
							</label>
							<textarea
								class="form-control bg-light-blue"
								name="taskNarrative"
								id="taskNarrative"
								rows="4"
								placeholder="What did you work on?"
							></textarea>
							<div class="form-text">
								<small id="charCount">0/500 characters</small>
							</div>
						</div>

						<!-- File Attachments -->
						<div class="form-group">
							<label for="fileUpload" class="form-label fw-semibold">
								<i class="fa-solid fa-paperclip me-1"></i> Attachments
							</label>
							<div class="file-upload-area border-2 border-dashed rounded p-3 text-center bg-light-blue">
								<input
									type="file"
									id="fileUpload"
									name="fileAttachments[]"
									class="d-none"
									multiple
									accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.csv,.txt"
								>
								<label for="fileUpload" class="mb-0 w-100" style="cursor: pointer;">
									<i class="fa-solid fa-cloud-upload fs-2 text-primary"></i>
									<p class="mb-0 mt-2">
										<span class="text-primary fw-semibold">Click to upload</span> or drag and drop
									</p>
									<small class="text-muted">PDF, DOC, XLS, Images (Max 10MB)</small>
								</label>
							</div>
							<div id="fileList" class="file-list mt-2"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Recent Tasks Cache (hidden, for quick access) -->
<div id="recentTasksCache" class="d-none" data-recent='<?php echo json_encode(array()) ?>'></div>

<style>
	/* Custom styles for improved time entry */
	.workHourImproved {
		font-size: 0.9rem;
	}

	.quick-actions-bar {
		border-left: 4px solid var(--primary-color);
	}

	.search-results-dropdown {
		position: absolute;
		top: 100%;
		left: 0;
		right: 0;
		z-index: 1050;
		max-height: 350px;
		overflow-y: auto;
		background: white;
		border: 1px solid var(--default-border);
		border-radius: 0.375rem;
		margin-top: 0.25rem;
		box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
	}

	/* Search results spacer */
	.search-results-spacer {
		height: 0;
		transition: height 0.3s ease;
	}

	.search-results-spacer.active {
		height: 350px;
	}

	/* Project header in search results */
	.search-result-project-header {
		position: sticky;
		top: 0;
		background: white;
		z-index: 10;
	}

	.search-result-project-header .bg-light {
		background-color: rgba(var(--primary-rgb), 0.05) !important;
		border-left: 4px solid var(--primary-color);
	}

	.search-result-project-header strong {
		font-size: 0.9rem;
		color: var(--default-text-color);
	}

	/* Task items in search results */
	.search-result-task-item {
		cursor: pointer;
		transition: all 0.2s;
		border-bottom: 1px solid var(--default-border);
	}

	.search-result-task-item:hover {
		background-color: var(--primary01);
	}

	.search-result-task-item:hover .task-item-content {
		transform: translateX(5px);
	}

	.task-item-content {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 0.75rem 1rem;
		transition: transform 0.2s;
	}

	/* Status-based left border */
	.task-item-content.border-start-danger {
		border-left: 4px solid var(--danger-color);
		background-color: rgba(var(--danger-rgb), 0.02);
	}

	.task-item-content.border-start-warning {
		border-left: 4px solid var(--warning-color);
		background-color: rgba(var(--warning-rgb), 0.02);
	}

	.task-item-content.border-start-info {
		border-left: 4px solid var(--info-color);
		background-color: rgba(var(--info-rgb), 0.02);
	}

	.task-main-info {
		flex: 1;
	}

	.task-main-info h6 {
		margin-bottom: 0.5rem;
		font-size: 0.95rem;
		font-weight: 600;
		color: var(--default-text-color);
	}

	.task-meta {
		display: flex;
		flex-wrap: wrap;
		gap: 0.25rem;
	}

	.task-meta .badge {
		font-size: 0.75rem;
		padding: 0.25rem 0.5rem;
		font-weight: 500;
	}

	.task-actions {
		padding-left: 1rem;
		display: flex;
		align-items: center;
	}

	.task-actions i {
		font-size: 1.2rem;
		transition: transform 0.2s;
	}

	.search-result-task-item:hover .task-actions i {
		transform: translateX(5px);
	}

	/* Old simple search result item styles (for fallback) */
	.search-results-dropdown .search-result-item {
		padding: 0.75rem 1rem;
		border-bottom: 1px solid var(--default-border);
		cursor: pointer;
		transition: all 0.2s;
	}

	.search-results-dropdown .search-result-item:hover {
		background-color: var(--primary01);
		border-left: 3px solid var(--primary-color);
	}

	.search-result-item h6 {
		margin-bottom: 0.25rem;
		font-size: 0.9rem;
	}

	.search-result-item small {
		color: var(--text-muted);
	}

	.selected-task-display {
		animation: slideDown 0.3s ease-out;
	}

	@keyframes slideDown {
		from {
			opacity: 0;
			transform: translateY(-10px);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	.file-upload-area {
		transition: all 0.3s;
	}

	.file-upload-area:hover {
		background-color: var(--primary01);
		border-color: var(--primary-color);
	}

	.file-upload-area.drag-over {
		background-color: var(--primary02);
		border-color: var(--primary-color);
		border-style: solid;
	}

	.file-item {
		display: flex;
		align-items: center;
		padding: 0.5rem;
		background: var(--gray-1);
		border-radius: 0.25rem;
		margin-bottom: 0.5rem;
	}

	.file-item .file-icon {
		width: 32px;
		height: 32px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: var(--primary-color);
		color: white;
		border-radius: 0.25rem;
		margin-right: 0.75rem;
	}

	.file-item .file-name {
		flex: 1;
		font-size: 0.85rem;
	}

	.file-item .file-size {
		font-size: 0.75rem;
		color: var(--text-muted);
		margin-right: 0.5rem;
	}

	.file-item .remove-file {
		color: var(--danger-color);
		cursor: pointer;
		padding: 0.25rem 0.5rem;
	}

	.bg-light-blue {
		background-color: rgba(var(--primary-rgb), 0.05);
		border-color: rgba(var(--primary-rgb), 0.2);
	}

	.bg-light-blue:focus {
		background-color: rgba(var(--primary-rgb), 0.08);
		border-color: var(--primary-color);
		box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.15);
	}

	.quick-time-select .dropdown-item {
		padding: 0.5rem 1rem;
		cursor: pointer;
	}

	.quick-time-select .dropdown-item:hover {
		background-color: var(--primary01);
	}

	/* Hierarchical Selector Styles */
	.hierarchical-selector {
		padding: 0.5rem 0;
	}

	.hierarchical-selector .form-group {
		position: relative;
	}

	.hierarchical-selector select:disabled {
		background-color: rgba(0, 0, 0, 0.05);
		cursor: not-allowed;
		opacity: 0.6;
	}

	.hierarchical-selector select:not(:disabled) {
		border-color: var(--primary-color);
		box-shadow: 0 0 0 0.1rem rgba(var(--primary-rgb), 0.1);
	}

	.hierarchical-selector select:not(:disabled):hover {
		background-color: var(--primary01);
		border-color: var(--primary-color);
	}

	.hierarchical-selector .badge {
		font-size: 0.75rem;
		width: 20px;
		height: 20px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
	}

	.hierarchical-selector select option {
		padding: 0.5rem;
	}

	.hierarchical-selector #confirmBrowseSelection:disabled {
		opacity: 0.5;
		cursor: not-allowed;
	}

	.hierarchical-selector #confirmBrowseSelection:not(:disabled) {
		animation: pulse 2s ease-in-out infinite;
	}

	@keyframes pulse {
		0%, 100% {
			transform: scale(1);
		}
		50% {
			transform: scale(1.05);
		}
	}

	/* Tab styles enhancement */
	.nav-tabs {
		border-bottom: 2px solid var(--default-border);
	}

	.nav-tabs .nav-link {
		border: none;
		border-bottom: 3px solid transparent;
		color: var(--text-muted);
		font-weight: 500;
		padding: 0.75rem 1rem;
		transition: all 0.3s;
	}

	.nav-tabs .nav-link:hover {
		border-color: rgba(var(--primary-rgb), 0.3);
		color: var(--primary-color);
	}

	.nav-tabs .nav-link.active {
		border-color: var(--primary-color);
		color: var(--primary-color);
		background: transparent;
	}

	.tab-content {
		min-height: auto;
	}

	#search-mode {
		position: relative;
	}

	#browse-mode {
		padding-top: 0.5rem;
	}

	/* Mobile adjustments for search results */
	@media (max-width: 767.98px) {
		.search-results-dropdown {
			max-height: 300px;
		}

		.search-results-spacer.active {
			height: 300px;
		}

		.tab-content {
			padding: 0;
		}

		.task-meta {
			flex-direction: column;
			align-items: flex-start;
		}

		.task-meta .badge {
			font-size: 0.7rem;
			padding: 0.2rem 0.4rem;
		}

		.task-main-info h6 {
			font-size: 0.85rem;
		}

		.search-result-project-header {
			font-size: 0.85rem;
		}
	}
</style>


<script>
	document.addEventListener('DOMContentLoaded', function() {
		'use strict';

		// Prevent Enter key from submitting form
		document.addEventListener('keydown', function(event) {
			if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
				event.preventDefault();
			}

			// Ctrl+Enter to submit form
			if (event.ctrlKey && event.key === 'Enter') {
				document.querySelector('form').submit();
			}
		});

		// ========================================
		// Project/Task Search Functionality
		// ========================================
		const searchInput = document.getElementById('projectTaskSearch');
		const searchResults = document.getElementById('searchResults');
		const searchResultsList = document.getElementById('searchResultsList');
		const selectedTaskDisplay = document.getElementById('selectedTaskDisplay');

		let searchTimeout;
		let allProjectData = <?php echo json_encode($validUserProjects); ?>;
		let userAssignedTasksData = typeof userAssignedTasks !== 'undefined' ? userAssignedTasks : [];

		// Debug: Check what data we have
		console.log('=== SEARCH DATA DEBUG ===');
		console.log('All Project Data count:', allProjectData?.length);
		console.log('All Project Data sample:', allProjectData?.[0]);
		console.log('User Assigned Tasks Data count:', userAssignedTasksData?.length);
		console.log('User Assigned Tasks Data sample:', userAssignedTasksData?.[0]);

		// If userAssignedTasksData is empty, use allProjectData and enrich it
		if (!userAssignedTasksData || userAssignedTasksData.length === 0) {
			console.log('⚠️ Using allProjectData as fallback');
			userAssignedTasksData = allProjectData.filter(item => {
				// Filter items that have task information
				return item.projectTaskID && item.projectTaskName;
			});
			console.log('✅ Enriched Task Data count:', userAssignedTasksData.length);
			console.log('✅ Enriched Task Data sample:', userAssignedTasksData?.[0]);
		}

		console.log('=== END DEBUG ===');

		// Search input handler
		searchInput.addEventListener('input', function() {
			clearTimeout(searchTimeout);
			const query = this.value.trim().toLowerCase();

			if (query.length < 2) {
				hideSearchResults();
				return;
			}

			searchTimeout = setTimeout(() => {
				performSearch(query);
			}, 300);
		});

		// Focus and blur handlers for search
		searchInput.addEventListener('focus', function() {
			if (this.value.length >= 2) {
				showSearchResults();
			}
		});

		document.addEventListener('click', function(e) {
			if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
				hideSearchResults();
			}
		});

		// Helper functions to show/hide search results with spacer
		function showSearchResults() {
			searchResults.classList.remove('d-none');
			const spacer = document.getElementById('searchResultsSpacer');
			if (spacer) {
				spacer.classList.add('active');
			}
		}

		function hideSearchResults() {
			searchResults.classList.add('d-none');
			const spacer = document.getElementById('searchResultsSpacer');
			if (spacer) {
				spacer.classList.remove('active');
			}
		}

		function performSearch(query) {
			searchResultsList.innerHTML = '';

			console.log('Performing search for:', query);
			console.log('Searching in data:', userAssignedTasksData);

			// Search in assigned tasks data for more detailed results
			const taskResults = userAssignedTasksData.filter(task => {
				const matches = task.projectName?.toLowerCase().includes(query) ||
						task.clientName?.toLowerCase().includes(query) ||
						task.projectTaskName?.toLowerCase().includes(query) ||
						task.projectPhaseName?.toLowerCase().includes(query) ||
						(task.projectCode && task.projectCode.toLowerCase().includes(query));

				if (matches) {
					console.log('Match found:', task);
				}
				return matches;
			});

			console.log('Task Results:', taskResults);

			if (taskResults.length === 0) {
				searchResultsList.innerHTML = `
					<div class="text-center text-muted py-3">
						<i class="fa-solid fa-search fs-4"></i>
						<p class="mb-0 mt-2">No matching tasks found</p>
						<small class="text-muted">Try searching by client, project, or task name</small>
						<div class="mt-2"><small class="text-muted">Available data: ${userAssignedTasksData.length} tasks</small></div>
					</div>
				`;
			} else {
				// Group results by client and project
				const groupedResults = groupSearchResults(taskResults, query);
				console.log('Grouped Results:', groupedResults);
				displayGroupedResults(groupedResults);
			}

			showSearchResults();
		}

		function groupSearchResults(tasks, query) {
			const grouped = {};
			const queryLower = query.toLowerCase();

			// Determine if search is by client or project
			const isClientSearch = tasks.some(t => t.clientName?.toLowerCase().includes(queryLower));

			tasks.forEach(task => {
				const clientKey = task.clientID;
				const projectKey = task.projectID;

				if (!grouped[clientKey]) {
					grouped[clientKey] = {
						clientID: task.clientID,
						clientName: task.clientName,
						projects: {}
					};
				}

				if (!grouped[clientKey].projects[projectKey]) {
					grouped[clientKey].projects[projectKey] = {
						projectID: task.projectID,
						projectName: task.projectName,
						projectCode: task.projectCode,
						tasks: []
					};
				}

				grouped[clientKey].projects[projectKey].tasks.push(task);
			});

			return { grouped, isClientSearch };
		}

		function displayGroupedResults(data) {
			const { grouped, isClientSearch } = data;

			Object.values(grouped).forEach(client => {
				Object.values(client.projects).forEach(project => {
					// Create project header
					const projectHeader = document.createElement('div');
					projectHeader.className = 'search-result-project-header';
					projectHeader.innerHTML = `
						<div class="d-flex align-items-center justify-content-between py-2 px-3 bg-light border-bottom">
							<div>
								<i class="fa-solid fa-building me-2 text-primary"></i>
								<strong>${escapeHtml(client.clientName)}</strong>
								<i class="fa-solid fa-chevron-right mx-2 text-muted"></i>
								<span class="text-primary">${escapeHtml(project.projectName)}</span>
								${project.projectCode ? `<span class="badge bg-secondary ms-2">${escapeHtml(project.projectCode)}</span>` : ''}
							</div>
							<small class="text-muted">${project.tasks.length} task${project.tasks.length !== 1 ? 's' : ''}</small>
						</div>
					`;
					searchResultsList.appendChild(projectHeader);

					// Create task items
					project.tasks.forEach(task => {
						const taskItem = createTaskSearchResultItem(task, client.clientName, project.projectName);
						searchResultsList.appendChild(taskItem);
					});
				});
			});
		}

		function createTaskSearchResultItem(task, clientName, projectName) {
			const div = document.createElement('div');
			div.className = 'search-result-task-item';

			console.log('Creating task item for:', task);

			// Determine task status badge
			const today = new Date().toISOString().split('T')[0];
			const dueDate = task.taskDue || task.taskEnd || task.taskDeadline;
			let statusBadge = '';
			let statusClass = '';

			if (dueDate) {
				if (dueDate < today) {
					statusBadge = '<span class="badge bg-danger ms-2"><i class="fa-solid fa-exclamation-triangle"></i> Overdue</span>';
					statusClass = 'border-start-danger';
				} else if (dueDate === today) {
					statusBadge = '<span class="badge bg-warning ms-2"><i class="fa-solid fa-clock"></i> Due Today</span>';
					statusClass = 'border-start-warning';
				} else {
					statusBadge = '<span class="badge bg-info ms-2"><i class="fa-solid fa-calendar"></i> Upcoming</span>';
					statusClass = 'border-start-info';
				}
			}

			// Format due date
			const dueDateDisplay = dueDate ? formatDate(dueDate) : 'No due date';

			// Get task name
			const taskName = task.projectTaskName || task.taskName || 'Unnamed Task';
			const phaseName = task.projectPhaseName || task.phaseName || 'General';
			const statusName = task.taskStatusName || task.statusName || '';

			div.innerHTML = `
				<div class="task-item-content ${statusClass}">
					<div class="task-main-info">
						<h6 class="mb-1">
							<i class="fa-solid fa-tasks me-1 text-primary"></i>
							${escapeHtml(taskName)}
							${statusBadge}
						</h6>
						<div class="task-meta">
							<span class="badge bg-primary-subtle text-primary me-1">
								<i class="fa-solid fa-layer-group me-1"></i>
								${escapeHtml(phaseName)}
							</span>
							<span class="badge bg-info-subtle text-info me-1">
								<i class="fa-solid fa-calendar-days me-1"></i>
								Due: ${dueDateDisplay}
							</span>
							${statusName ? `<span class="badge bg-secondary-subtle text-secondary">
								<i class="fa-solid fa-flag me-1"></i>
								${escapeHtml(statusName)}
							</span>` : ''}
						</div>
					</div>
					<div class="task-actions">
						<i class="fa-solid fa-chevron-right text-muted"></i>
					</div>
				</div>
			`;

			div.addEventListener('click', function() {
				console.log('Task clicked:', task);
				selectTaskFromSearch(task, clientName, projectName);
			});

			return div;
		}

		function selectTaskFromSearch(task, clientName, projectName) {
			const selectedProject = {
				clientID: task.clientID,
				clientName: clientName,
				projectID: task.projectID,
				projectName: projectName,
				projectPhaseID: task.projectPhaseID,
				projectPhaseName: task.projectPhaseName || 'N/A',
				projectTaskID: task.projectTaskID,
				projectTaskName: task.projectTaskName
			};

			selectProject(selectedProject);
			hideSearchResults();
		}

		function formatDate(dateString) {
			if (!dateString) return 'No date';
			const date = new Date(dateString);
			const today = new Date();
			const diffTime = date - today;
			const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

			const options = { month: 'short', day: 'numeric', year: 'numeric' };
			const formatted = date.toLocaleDateString('en-US', options);

			if (diffDays < 0) {
				return `${formatted} (${Math.abs(diffDays)}d ago)`;
			} else if (diffDays === 0) {
				return `${formatted} (Today)`;
			} else if (diffDays === 1) {
				return `${formatted} (Tomorrow)`;
			} else if (diffDays <= 7) {
				return `${formatted} (${diffDays}d)`;
			} else {
				return formatted;
			}
		}

		function createSearchResultItem(project) {
			const div = document.createElement('div');
			div.className = 'search-result-item';
			div.innerHTML = `
				<h6 class="mb-1">
					<i class="fa-solid fa-folder-open me-1 text-primary"></i>
					${escapeHtml(project.projectName)}
				</h6>
				<small>
					<span class="badge bg-primary-subtle text-primary me-1">${escapeHtml(project.clientName)}</span>
					<span class="text-muted">${project.projectCode || ''}</span>
				</small>
			`;

			div.addEventListener('click', function() {
				selectProject(project);
				hideSearchResults();
			});

			return div;
		}

		function selectProject(project) {
			// Display selected task
			document.getElementById('selectedProjectName').textContent = project.projectName;
			document.getElementById('selectedClientName').textContent = project.clientName;
			document.getElementById('selectedPhaseName').textContent = project.projectPhaseName || 'N/A';
			document.getElementById('selectedTaskName').textContent = project.projectTaskName || 'General';

			// Set hidden fields
			document.getElementById('hiddenClientID').value = project.clientID || '';
			document.getElementById('hiddenProjectID').value = project.projectID;
			document.getElementById('hiddenPhaseID').value = project.projectPhaseID || '';
			document.getElementById('hiddenTaskID').value = project.projectTaskID || '';

			// Show selected display, clear search
			selectedTaskDisplay.classList.remove('d-none');
			searchInput.value = '';

			// Save to recent tasks
			saveToRecentTasks(project);

			// Focus on duration input
			document.getElementById('taskDuration').focus();
		}

		// Clear selection
		document.getElementById('clearSelectionBtn').addEventListener('click', function() {
			selectedTaskDisplay.classList.add('d-none');
			document.getElementById('hiddenClientID').value = '';
			document.getElementById('hiddenProjectID').value = '';
			document.getElementById('hiddenPhaseID').value = '';
			document.getElementById('hiddenTaskID').value = '';
			searchInput.focus();
		});

		// ========================================
		// Quick Time Selection
		// ========================================
		document.querySelectorAll('.quick-time-select .dropdown-item').forEach(item => {
			item.addEventListener('click', function(e) {
				e.preventDefault();
				const time = this.getAttribute('data-time');
				document.getElementById('taskDuration').value = time;
				validateTimeInput(document.getElementById('taskDuration'));
			});
		});

		// ========================================
		// Time Input Validation
		// ========================================
		const timeInput = document.getElementById('taskDuration');
		timeInput.addEventListener('blur', function() {
			validateTimeInput(this);
		});

		function validateTimeInput(element) {
			let value = element.value.trim();
			const errorDiv = document.getElementById('durationError');

			if (!value) {
				errorDiv.classList.add('d-none');
				return;
			}

			// Convert decimal to HH:MM
			if (value.includes('.')) {
				let decimalHours = parseFloat(value);
				let hours = Math.floor(decimalHours);
				let minutes = Math.round((decimalHours - hours) * 60);
				value = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
			}

			// Validate HH:MM format
			const timeParts = value.split(':');
			if (timeParts.length === 2) {
				let hours = parseInt(timeParts[0]);
				let minutes = parseInt(timeParts[1]);

				if (isNaN(hours) || isNaN(minutes)) {
					errorDiv.textContent = 'Invalid time format. Use HH:MM';
					errorDiv.classList.remove('d-none');
					return;
				}

				// Handle minutes overflow
				if (minutes > 59) {
					hours += Math.floor(minutes / 60);
					minutes = minutes % 60;
				}

				element.value = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
				errorDiv.classList.add('d-none');
			} else {
				errorDiv.textContent = 'Invalid time format. Use HH:MM';
				errorDiv.classList.remove('d-none');
			}
		}

		// ========================================
		// File Upload Handling
		// ========================================
		const fileUpload = document.getElementById('fileUpload');
		const fileList = document.getElementById('fileList');
		const fileUploadArea = document.querySelector('.file-upload-area');
		let uploadedFiles = [];

		fileUpload.addEventListener('change', function(e) {
			handleFiles(e.target.files);
		});

		// Drag and drop
		fileUploadArea.addEventListener('dragover', function(e) {
			e.preventDefault();
			this.classList.add('drag-over');
		});

		fileUploadArea.addEventListener('dragleave', function(e) {
			e.preventDefault();
			this.classList.remove('drag-over');
		});

		fileUploadArea.addEventListener('drop', function(e) {
			e.preventDefault();
			this.classList.remove('drag-over');
			handleFiles(e.dataTransfer.files);
		});

		function handleFiles(files) {
			Array.from(files).forEach(file => {
				if (file.size > 10 * 1024 * 1024) {
					alert(`File ${file.name} is too large. Maximum size is 10MB.`);
					return;
				}

				uploadedFiles.push(file);
				displayFile(file);
			});
		}

		function displayFile(file) {
			const fileItem = document.createElement('div');
			fileItem.className = 'file-item';

			const fileExt = file.name.split('.').pop().toLowerCase();
			let iconClass = 'fa-file';
			if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) iconClass = 'fa-file-image';
			else if (['pdf'].includes(fileExt)) iconClass = 'fa-file-pdf';
			else if (['doc', 'docx'].includes(fileExt)) iconClass = 'fa-file-word';
			else if (['xls', 'xlsx'].includes(fileExt)) iconClass = 'fa-file-excel';

			fileItem.innerHTML = `
				<div class="file-icon">
					<i class="fa-solid ${iconClass}"></i>
				</div>
				<div class="file-name">${escapeHtml(file.name)}</div>
				<div class="file-size">${formatFileSize(file.size)}</div>
				<div class="remove-file" onclick="removeFile('${escapeHtml(file.name)}')">
					<i class="fa-solid fa-times"></i>
				</div>
			`;

			fileList.appendChild(fileItem);
		}

		window.removeFile = function(fileName) {
			uploadedFiles = uploadedFiles.filter(f => f.name !== fileName);
			updateFileList();
		};

		function updateFileList() {
			fileList.innerHTML = '';
			uploadedFiles.forEach(file => displayFile(file));
		}

		function formatFileSize(bytes) {
			if (bytes === 0) return '0 Bytes';
			const k = 1024;
			const sizes = ['Bytes', 'KB', 'MB'];
			const i = Math.floor(Math.log(bytes) / Math.log(k));
			return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
		}

		// ========================================
		// Character Counter
		// ========================================
		const taskNarrative = document.getElementById('taskNarrative');
		const charCount = document.getElementById('charCount');

		taskNarrative.addEventListener('input', function() {
			const count = this.value.length;
			charCount.textContent = `${count}/500 characters`;
			if (count > 500) {
				charCount.classList.add('text-danger');
			} else {
				charCount.classList.remove('text-danger');
			}
		});

		// ========================================
		// Recent Tasks Management
		// ========================================
		function saveToRecentTasks(project) {
			let recent = JSON.parse(localStorage.getItem('recentTasks') || '[]');
			recent = recent.filter(p => p.projectID !== project.projectID);
			recent.unshift(project);
			recent = recent.slice(0, 5); // Keep only 5 recent
			localStorage.setItem('recentTasks', JSON.stringify(recent));
			loadRecentTasks();
		}

		function loadRecentTasks() {
			const recent = JSON.parse(localStorage.getItem('recentTasks') || '[]');
			const recentTasksList = document.getElementById('recentTasks');
			recentTasksList.innerHTML = '';

			if (recent.length === 0) {
				recentTasksList.innerHTML = '<div class="text-center text-muted p-2"><small>No recent tasks</small></div>';
				return;
			}

			recent.forEach(project => {
				const item = createSearchResultItem(project);
				recentTasksList.appendChild(item);
			});
		}

		// ========================================
		// HIERARCHICAL BROWSE MODE
		// ========================================
		const browseClient = document.getElementById('browseClient');
		const browseProject = document.getElementById('browseProject');
		const browsePhase = document.getElementById('browsePhase');
		const browseTask = document.getElementById('browseTask');
		const browseSubtask = document.getElementById('browseSubtask');
		const confirmBrowseBtn = document.getElementById('confirmBrowseSelection');
		const subtaskGroup = document.getElementById('subtaskGroup');

		// Prepare hierarchical data structure
		// Use userAssignedTasksData which contains the complete hierarchy (client->project->phase->task)
		let hierarchicalData = prepareHierarchicalData(userAssignedTasksData);

		console.log('=== BROWSE & SELECT DATA DEBUG ===');
		console.log('Input data for hierarchical structure:', userAssignedTasksData);
		console.log('Built hierarchical data:', hierarchicalData);
		console.log('Clients count:', Object.keys(hierarchicalData.clients).length);
		console.log('Projects count:', Object.keys(hierarchicalData.projects).length);
		console.log('Phases count:', Object.keys(hierarchicalData.phases).length);
		console.log('Tasks count:', Object.keys(hierarchicalData.tasks).length);
		console.log('=== END BROWSE DEBUG ===');

		function prepareHierarchicalData(projects) {
			const data = {
				clients: {},
				projects: {},
				phases: {},
				tasks: {},
				subtasks: {}
			};

			projects.forEach(project => {
				// Group by client
				if (!data.clients[project.clientID]) {
					data.clients[project.clientID] = {
						clientID: project.clientID,
						clientName: project.clientName,
						projects: []
					};
				}

				// Add project to client
				if (!data.projects[project.projectID]) {
					data.projects[project.projectID] = {
						projectID: project.projectID,
						projectName: project.projectName,
						projectCode: project.projectCode || '',
						clientID: project.clientID,
						clientName: project.clientName,
						phases: []
					};
					data.clients[project.clientID].projects.push(project.projectID);
				}

				// Add phase to project
				if (project.projectPhaseID && !data.phases[project.projectPhaseID]) {
					data.phases[project.projectPhaseID] = {
						projectPhaseID: project.projectPhaseID,
						projectPhaseName: project.projectPhaseName,
						projectID: project.projectID,
						tasks: []
					};
					if (!data.projects[project.projectID].phases.includes(project.projectPhaseID)) {
						data.projects[project.projectID].phases.push(project.projectPhaseID);
					}
				}

				// Add task to phase
				if (project.projectTaskID && project.projectPhaseID) {
					if (!data.tasks[project.projectTaskID]) {
						data.tasks[project.projectTaskID] = {
							projectTaskID: project.projectTaskID,
							projectTaskName: project.projectTaskName,
							projectPhaseID: project.projectPhaseID,
							projectID: project.projectID,
							clientID: project.clientID,
							subtasks: []
						};
						if (!data.phases[project.projectPhaseID].tasks.includes(project.projectTaskID)) {
							data.phases[project.projectPhaseID].tasks.push(project.projectTaskID);
						}
					}
				}
			});

			return data;
		}

		// Populate clients on load
		function populateClients() {
			browseClient.innerHTML = '<option value="">-- Select Client --</option>';
			Object.values(hierarchicalData.clients).forEach(client => {
				const option = document.createElement('option');
				option.value = client.clientID;
				option.textContent = client.clientName;
				browseClient.appendChild(option);
			});
		}

		// Client change handler
		browseClient.addEventListener('change', function() {
			const clientID = this.value;
			browseProject.disabled = !clientID;
			browsePhase.disabled = true;
			browseTask.disabled = true;
			browseSubtask.disabled = true;
			confirmBrowseBtn.disabled = true;

			browseProject.innerHTML = '<option value="">-- Select Project --</option>';
			browsePhase.innerHTML = '<option value="">-- Select Phase --</option>';
			browseTask.innerHTML = '<option value="">-- Select Task --</option>';

			if (clientID) {
				const client = hierarchicalData.clients[clientID];
				client.projects.forEach(projectID => {
					const project = hierarchicalData.projects[projectID];
					const option = document.createElement('option');
					option.value = project.projectID;
					option.textContent = `${project.projectName} ${project.projectCode ? '(' + project.projectCode + ')' : ''}`;
					option.dataset.project = JSON.stringify(project);
					browseProject.appendChild(option);
				});
			}
		});

		// Project change handler
		browseProject.addEventListener('change', function() {
			const projectID = this.value;
			browsePhase.disabled = !projectID;
			browseTask.disabled = true;
			browseSubtask.disabled = true;
			confirmBrowseBtn.disabled = true;

			browsePhase.innerHTML = '<option value="">-- Select Phase --</option>';
			browseTask.innerHTML = '<option value="">-- Select Task --</option>';

			if (projectID) {
				const project = hierarchicalData.projects[projectID];

				if (project.phases.length === 0) {
					browsePhase.innerHTML = '<option value="">No phases available</option>';
					browsePhase.disabled = true;
				} else {
					project.phases.forEach(phaseID => {
						const phase = hierarchicalData.phases[phaseID];
						if (phase) {
							const option = document.createElement('option');
							option.value = phase.projectPhaseID;
							option.textContent = phase.projectPhaseName;
							option.dataset.phase = JSON.stringify(phase);
							browsePhase.appendChild(option);
						}
					});
				}
			}
		});

		// Phase change handler
		browsePhase.addEventListener('change', function() {
			const phaseID = this.value;
			browseTask.disabled = !phaseID;
			browseSubtask.disabled = true;
			confirmBrowseBtn.disabled = true;

			browseTask.innerHTML = '<option value="">-- Select Task --</option>';

			if (phaseID) {
				const phase = hierarchicalData.phases[phaseID];

				if (phase.tasks.length === 0) {
					browseTask.innerHTML = '<option value="">No tasks assigned to you</option>';
					browseTask.disabled = true;
				} else {
					phase.tasks.forEach(taskID => {
						const task = hierarchicalData.tasks[taskID];
						if (task) {
							const option = document.createElement('option');
							option.value = task.projectTaskID;
							option.textContent = task.projectTaskName;
							option.dataset.task = JSON.stringify(task);
							browseTask.appendChild(option);
						}
					});
				}
			}
		});

		// Task change handler
		browseTask.addEventListener('change', function() {
			const taskID = this.value;
			confirmBrowseBtn.disabled = !taskID;

			// Check if subtasks exist (you can add subtask logic here)
			// For now, just enable the confirm button
			if (taskID) {
				const task = hierarchicalData.tasks[taskID];
				// If task has subtasks, show subtask dropdown
				if (task && task.subtasks && task.subtasks.length > 0) {
					subtaskGroup.classList.remove('d-none');
					browseSubtask.disabled = false;
					populateSubtasks(task.subtasks);
				} else {
					subtaskGroup.classList.add('d-none');
				}
			}
		});

		function populateSubtasks(subtasks) {
			browseSubtask.innerHTML = '<option value="">-- None (Optional) --</option>';
			subtasks.forEach(subtask => {
				const option = document.createElement('option');
				option.value = subtask.subtaskID;
				option.textContent = subtask.subtaskName;
				browseSubtask.appendChild(option);
			});
		}

		// Confirm browse selection
		confirmBrowseBtn.addEventListener('click', function() {
			const clientID = browseClient.value;
			const projectID = browseProject.value;
			const phaseID = browsePhase.value;
			const taskID = browseTask.value;

			if (!projectID || !taskID) {
				alert('Please select at least a project and task');
				return;
			}

			// Get selected data
			const clientName = browseClient.options[browseClient.selectedIndex].text;
			const projectName = browseProject.options[browseProject.selectedIndex].text;
			const phaseName = phaseID ? browsePhase.options[browsePhase.selectedIndex].text : 'N/A';
			const taskName = browseTask.options[browseTask.selectedIndex].text;

			// Create project object
			const selectedProject = {
				clientID: clientID,
				clientName: clientName,
				projectID: projectID,
				projectName: projectName.replace(/\s*\([^)]*\)/, ''), // Remove code from name
				projectPhaseID: phaseID,
				projectPhaseName: phaseName,
				projectTaskID: taskID,
				projectTaskName: taskName
			};

			// Select the project (reuse the existing function)
			selectProject(selectedProject);

			// Switch back to search tab to show selection
			const searchTab = document.getElementById('search-tab');
			if (searchTab) {
				const tab = new bootstrap.Tab(searchTab);
				tab.show();
			}
		});

		// Initialize browse mode
		populateClients();

		// ========================================
		// Quick Actions
		// ========================================
		document.getElementById('copyPreviousDayBtn').addEventListener('click', function() {
			// This would require AJAX call to fetch previous day's entries
			alert('Copy from previous day feature - requires backend implementation');
		});

		document.getElementById('useTemplateBtn').addEventListener('click', function() {
			// Load saved templates
			alert('Use template feature - requires backend implementation');
		});

		document.getElementById('saveAsTemplateBtn').addEventListener('click', function() {
			// Save current entry as template
			alert('Save as template feature - requires backend implementation');
		});

		// ========================================
		// Utility Functions
		// ========================================
		function escapeHtml(text) {
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		}

		// Initialize
		loadRecentTasks();
	});
</script>

