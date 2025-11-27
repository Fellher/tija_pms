<div class="expense-form-improved">
	<input type="hidden" name="userID" id="expenseUserID" value="<?php echo $userID ?>">

	<div class="row g-3">
		<!-- Left Column - Expense Details -->
		<div class="col-md-6">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<h6 class="card-title mb-3 text-primary border-bottom pb-2">
						<i class="fa-solid fa-wallet me-2"></i>Expense Details
					</h6>

					<!-- Project Selection -->
					<div class="form-group mb-3">
						<label for="expprojectID" class="form-label fw-semibold">
							<i class="fa-solid fa-folder me-1 text-muted"></i> Project / Case
						</label>
						<select
							name="projectID"
							id="expprojectID"
							class="form-control bg-light-blue"
							required
						>
							<?php echo Form::populate_select_element_from_grouped_object($projectArray, 'projectID', 'projectName', '', '', 'Select Project/Case') ?>
						</select>
						<div class="invalid-feedback">Please select a project</div>
					</div>

					<!-- Expense Type & Amount Row -->
					<div class="row g-2 mb-3">
						<div class="col-md-6">
							<label for="expenseTypeID" class="form-label fw-semibold">
								<i class="fa-solid fa-tags me-1 text-muted"></i> Expense Type
							</label>
							<select
								name="expenseTypeID"
								id="expenseTypeID"
								class="form-control bg-light-blue"
								required
							>
								<?php echo Form::populate_select_element_from_object($expenseTypes, 'expenseTypeID', 'typeName', '', '', 'Select Type') ?>
							</select>
							<div class="invalid-feedback">Please select expense type</div>
						</div>

						<div class="col-md-6">
							<label for="expenseAmount" class="form-label fw-semibold">
								<i class="fa-solid fa-money-bill me-1 text-muted"></i> Amount (KES)
							</label>
							<div class="input-group">
								<span class="input-group-text bg-light-blue">KES</span>
								<input
									type="number"
									class="form-control bg-light-blue"
									name="expenseAmount"
									id="expenseAmount"
									placeholder="0.00"
									step="0.01"
									min="0"
									required
								>
							</div>
							<div class="invalid-feedback">Please enter a valid amount</div>
						</div>
					</div>

					<!-- Date & Quick Amount Buttons -->
					<div class="form-group mb-3">
						<label for="expenseDate" class="form-label fw-semibold">
							<i class="fa-solid fa-calendar me-1 text-muted"></i> Expense Date
						</label>
						<input
							type="text"
							id="expenseDate"
							value="<?php echo date_format($dt,'Y-m-d') ?>"
							name="expenseDate"
							class="form-control bg-light-blue component-datepicker past-enabled"
							placeholder="YYYY-MM-DD"
							required
						>
						<div class="invalid-feedback">Please select a date</div>
					</div>

					<!-- Quick Amount Presets -->
					<div class="mb-3">
						<label class="form-label small text-muted">Quick Amount Presets:</label>
						<div class="btn-group w-100" role="group">
							<button type="button" class="btn btn-sm btn-outline-secondary amount-preset" data-amount="500">
								500
							</button>
							<button type="button" class="btn btn-sm btn-outline-secondary amount-preset" data-amount="1000">
								1,000
							</button>
							<button type="button" class="btn btn-sm btn-outline-secondary amount-preset" data-amount="2000">
								2,000
							</button>
							<button type="button" class="btn btn-sm btn-outline-secondary amount-preset" data-amount="5000">
								5,000
							</button>
						</div>
					</div>

					<!-- Document Upload -->
					<div class="form-group mb-3">
						<label for="expenseDocuments" class="form-label fw-semibold">
							<i class="fa-solid fa-file-invoice me-1 text-muted"></i> Supporting Documents
						</label>
						<div class="expense-upload-area border-2 border-dashed rounded p-3 text-center bg-light-blue">
							<input
								type="file"
								id="expenseDocuments"
								name="expenseDocuments[]"
								class="d-none"
								multiple
								accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.csv"
							>
							<label for="expenseDocuments" class="mb-0 w-100" style="cursor: pointer;">
								<i class="fa-solid fa-cloud-upload fs-3 text-primary"></i>
								<p class="mb-0 mt-2">
									<span class="text-primary fw-semibold">Upload receipts/invoices</span>
								</p>
								<small class="text-muted">PDF, Images, DOC (Max 10MB each)</small>
							</label>
						</div>
						<div id="expenseFileList" class="mt-2"></div>
						<small class="form-text text-info">
							<i class="fa-solid fa-info-circle me-1"></i>
							Attach receipts or invoices to support your expense claim
						</small>
					</div>
				</div>
			</div>
		</div>

		<!-- Right Column - Description & Summary -->
		<div class="col-md-6">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<h6 class="card-title mb-3 text-primary border-bottom pb-2">
						<i class="fa-solid fa-file-lines me-2"></i>Description & Summary
					</h6>

					<!-- Description -->
					<div class="form-group mb-3">
						<label for="expenseDescription" class="form-label fw-semibold">
							<i class="fa-solid fa-comment me-1 text-muted"></i> Expense Description
						</label>
						<textarea
							class="form-control bg-light-blue"
							name="expenseDescription"
							id="expenseDescription"
							rows="5"
							placeholder="Describe the expense purpose and details..."
							required
						></textarea>
						<div class="form-text">
							<small id="expCharCount">0/500 characters</small>
						</div>
						<div class="invalid-feedback">Please provide a description</div>
					</div>

					<!-- Expense Summary Card -->
					<div class="card bg-primary-subtle border-primary">
						<div class="card-header bg-primary text-white py-2">
							<small class="fw-semibold">
								<i class="fa-solid fa-receipt me-1"></i> Expense Summary
							</small>
						</div>
						<div class="card-body p-3">
							<div class="row mb-2">
								<div class="col-6">
									<small class="text-muted">Project:</small>
								</div>
								<div class="col-6 text-end">
									<strong id="summaryProject">Not selected</strong>
								</div>
							</div>
							<div class="row mb-2">
								<div class="col-6">
									<small class="text-muted">Type:</small>
								</div>
								<div class="col-6 text-end">
									<strong id="summaryExpenseType">Not selected</strong>
								</div>
							</div>
							<div class="row mb-2">
								<div class="col-6">
									<small class="text-muted">Date:</small>
								</div>
								<div class="col-6 text-end">
									<strong id="summaryExpenseDate"><?php echo $dt->format('d/m/Y') ?></strong>
								</div>
							</div>
							<hr class="my-2">
							<div class="row">
								<div class="col-6">
									<strong>Total Amount:</strong>
								</div>
								<div class="col-6 text-end">
									<h5 class="mb-0 text-primary">
										<strong id="summaryAmount">KES 0.00</strong>
									</h5>
								</div>
							</div>
						</div>
					</div>

					<!-- Common Expense Types Info -->
					<div class="alert alert-light border mt-3 mb-0">
						<h6 class="alert-heading mb-2">
							<i class="fa-solid fa-lightbulb me-1 text-warning"></i> Common Expenses
						</h6>
						<ul class="mb-0 small ps-3">
							<li>Travel & Transportation</li>
							<li>Meals & Entertainment</li>
							<li>Office Supplies</li>
							<li>Communication (Phone, Internet)</li>
							<li>Professional Services</li>
						</ul>
						<small class="text-muted mt-2 d-block">
							<i class="fa-solid fa-shield-check me-1"></i>
							All expenses require supporting documentation for approval
						</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.expense-form-improved {
	padding: 0;
}

.expense-form-improved .bg-light-blue {
	background-color: rgba(var(--primary-rgb), 0.05);
	border-color: rgba(var(--primary-rgb), 0.2);
	transition: all 0.3s;
}

.expense-form-improved .bg-light-blue:focus {
	background-color: rgba(var(--primary-rgb), 0.08);
	border-color: var(--primary-color);
	box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.15);
}

.expense-upload-area {
	transition: all 0.3s;
}

.expense-upload-area:hover {
	background-color: var(--primary01);
	border-color: var(--primary-color);
}

.expense-upload-area.drag-over {
	background-color: var(--primary02);
	border-color: var(--primary-color);
	border-style: solid;
}

.amount-preset {
	font-size: 0.85rem;
	padding: 0.4rem 0.5rem;
}

.amount-preset.active {
	background-color: var(--primary-color);
	color: white;
	border-color: var(--primary-color);
}

.amount-preset:hover {
	background-color: var(--primary01);
	border-color: var(--primary-color);
}

.expense-file-item {
	display: flex;
	align-items: center;
	padding: 0.5rem;
	background: var(--gray-1);
	border-radius: 0.25rem;
	margin-bottom: 0.5rem;
	border-left: 3px solid var(--primary-color);
}

.expense-file-item .file-icon {
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

.expense-file-item .file-info {
	flex: 1;
}

.expense-file-item .file-name {
	font-size: 0.85rem;
	font-weight: 500;
}

.expense-file-item .file-size {
	font-size: 0.75rem;
	color: var(--text-muted);
}

.expense-file-item .remove-file {
	color: var(--danger-color);
	cursor: pointer;
	padding: 0.25rem 0.5rem;
}

.expense-file-item .remove-file:hover {
	background-color: rgba(var(--danger-rgb), 0.1);
	border-radius: 0.25rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
	'use strict';

	// Form elements
	const projectSelect = document.getElementById('expprojectID');
	const expenseTypeSelect = document.getElementById('expenseTypeID');
	const expenseAmountInput = document.getElementById('expenseAmount');
	const expenseDateInput = document.getElementById('expenseDate');
	const expenseDescriptionInput = document.getElementById('expenseDescription');
	const fileInput = document.getElementById('expenseDocuments');
	const fileList = document.getElementById('expenseFileList');
	const uploadArea = document.querySelector('.expense-upload-area');

	// Summary elements
	const summaryProject = document.getElementById('summaryProject');
	const summaryExpenseType = document.getElementById('summaryExpenseType');
	const summaryAmount = document.getElementById('summaryAmount');
	const summaryExpenseDate = document.getElementById('summaryExpenseDate');
	const expCharCount = document.getElementById('expCharCount');

	let uploadedFiles = [];

	// ========================================
	// Summary Updates
	// ========================================
	projectSelect.addEventListener('change', function() {
		const selectedText = this.options[this.selectedIndex].text;
		summaryProject.textContent = selectedText || 'Not selected';
	});

	expenseTypeSelect.addEventListener('change', function() {
		const selectedText = this.options[this.selectedIndex].text;
		summaryExpenseType.textContent = selectedText || 'Not selected';
	});

	expenseAmountInput.addEventListener('input', function() {
		const amount = parseFloat(this.value) || 0;
		summaryAmount.textContent = `KES ${formatCurrency(amount)}`;

		// Highlight preset if matches
		document.querySelectorAll('.amount-preset').forEach(btn => {
			const presetAmount = parseFloat(btn.getAttribute('data-amount'));
			if (presetAmount === amount) {
				btn.classList.add('active');
			} else {
				btn.classList.remove('active');
			}
		});
	});

	expenseDateInput.addEventListener('change', function() {
		if (this.value) {
			const date = new Date(this.value);
			summaryExpenseDate.textContent = date.toLocaleDateString('en-GB');
		}
	});

	// ========================================
	// Amount Presets
	// ========================================
	document.querySelectorAll('.amount-preset').forEach(button => {
		button.addEventListener('click', function() {
			const amount = this.getAttribute('data-amount');
			expenseAmountInput.value = amount;

			// Trigger input event to update summary
			expenseAmountInput.dispatchEvent(new Event('input'));

			// Visual feedback
			document.querySelectorAll('.amount-preset').forEach(btn => {
				btn.classList.remove('active');
			});
			this.classList.add('active');
		});
	});

	// ========================================
	// Character Counter
	// ========================================
	expenseDescriptionInput.addEventListener('input', function() {
		const count = this.value.length;
		expCharCount.textContent = `${count}/500 characters`;
		if (count > 500) {
			expCharCount.classList.add('text-danger');
			this.value = this.value.substring(0, 500);
		} else {
			expCharCount.classList.remove('text-danger');
		}
	});

	// ========================================
	// File Upload Handling
	// ========================================
	fileInput.addEventListener('change', function(e) {
		handleFiles(e.target.files);
	});

	// Drag and drop
	uploadArea.addEventListener('dragover', function(e) {
		e.preventDefault();
		this.classList.add('drag-over');
	});

	uploadArea.addEventListener('dragleave', function(e) {
		e.preventDefault();
		this.classList.remove('drag-over');
	});

	uploadArea.addEventListener('drop', function(e) {
		e.preventDefault();
		this.classList.remove('drag-over');
		handleFiles(e.dataTransfer.files);
	});

	function handleFiles(files) {
		Array.from(files).forEach(file => {
			// Validate file size
			if (file.size > 10 * 1024 * 1024) {
				alert(`File ${file.name} is too large. Maximum size is 10MB.`);
				return;
			}

		// Validate file type
		const allowedTypes = [
			'application/pdf',                                                                      // PDF
			'image/jpeg',                                                                           // JPEG
			'image/jpg',                                                                            // JPG
			'image/png',                                                                            // PNG
			'image/gif',                                                                            // GIF
			'image/webp',                                                                           // WebP
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',            // DOCX
			'application/msword',                                                                   // DOC
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',                  // XLSX
			'application/vnd.ms-excel',                                                             // XLS
			'text/csv',                                                                             // CSV
			'text/plain'                                                                            // TXT
		];
		if (!allowedTypes.includes(file.type)) {
			alert(`File ${file.name} (type: ${file.type}) is not a supported format.`);
			return;
		}

			uploadedFiles.push(file);
			displayFile(file);
		});
	}

	function displayFile(file) {
		const fileItem = document.createElement('div');
		fileItem.className = 'expense-file-item';

		const fileExt = file.name.split('.').pop().toLowerCase();
		let iconClass = 'fa-file';
		if (['jpg', 'jpeg', 'png'].includes(fileExt)) iconClass = 'fa-file-image';
		else if (fileExt === 'pdf') iconClass = 'fa-file-pdf';
		else if (['doc', 'docx'].includes(fileExt)) iconClass = 'fa-file-word';

		fileItem.innerHTML = `
			<div class="file-icon">
				<i class="fa-solid ${iconClass}"></i>
			</div>
			<div class="file-info">
				<div class="file-name">${escapeHtml(file.name)}</div>
				<div class="file-size">${formatFileSize(file.size)}</div>
			</div>
			<div class="remove-file" onclick="removeExpenseFile('${escapeHtml(file.name)}')">
				<i class="fa-solid fa-times"></i>
			</div>
		`;

		fileList.appendChild(fileItem);
	}

	window.removeExpenseFile = function(fileName) {
		uploadedFiles = uploadedFiles.filter(f => f.name !== fileName);
		updateFileList();
	};

	function updateFileList() {
		fileList.innerHTML = '';
		uploadedFiles.forEach(file => displayFile(file));
	}

	// ========================================
	// Form Validation
	// ========================================
	const form = document.querySelector('.expense-form-improved').closest('form');
	if (form) {
		form.addEventListener('submit', function(event) {
			let isValid = true;

			// Validate required fields
			[projectSelect, expenseTypeSelect, expenseAmountInput, expenseDateInput, expenseDescriptionInput].forEach(element => {
				if (!element.value || element.value.trim() === '') {
					element.classList.add('is-invalid');
					isValid = false;
				} else {
					element.classList.remove('is-invalid');
				}
			});

			// Validate amount is positive
			if (parseFloat(expenseAmountInput.value) <= 0) {
				expenseAmountInput.classList.add('is-invalid');
				alert('Expense amount must be greater than zero');
				isValid = false;
			}

			// Warn if no documents attached
			if (uploadedFiles.length === 0) {
				const confirmSubmit = confirm('No supporting documents attached. Are you sure you want to continue?');
				if (!confirmSubmit) {
					isValid = false;
				}
			}

			if (!isValid) {
				event.preventDefault();
				event.stopPropagation();
			}
		});

		// Real-time validation
		[projectSelect, expenseTypeSelect, expenseAmountInput, expenseDateInput, expenseDescriptionInput].forEach(element => {
			element.addEventListener('blur', function() {
				if (!this.value || this.value.trim() === '') {
					this.classList.add('is-invalid');
				} else {
					this.classList.remove('is-invalid');
				}
			});
		});
	}

	// ========================================
	// Utility Functions
	// ========================================
	function formatCurrency(amount) {
		return amount.toLocaleString('en-KE', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
	}

	function formatFileSize(bytes) {
		if (bytes === 0) return '0 Bytes';
		const k = 1024;
		const sizes = ['Bytes', 'KB', 'MB'];
		const i = Math.floor(Math.log(bytes) / Math.log(k));
		return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
	}

	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	// Initialize TomSelect for project dropdown (if available)
	if (typeof TomSelect !== 'undefined') {
		new TomSelect('#expprojectID', {
			placeholder: 'Select project or case',
			allowEmptyOption: false,
			sortField: {
				field: "text",
				direction: "asc"
			}
		});
	}
});
</script>

