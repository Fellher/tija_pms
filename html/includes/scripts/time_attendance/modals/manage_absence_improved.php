<div class="absence-form-improved">
	<div class="row g-3">
		<!-- Left Column - Absence Details -->
		<div class="col-md-6">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<h6 class="card-title mb-3 text-primary border-bottom pb-2">
						<i class="fa-solid fa-calendar-xmark me-2"></i>Absence Information
					</h6>

					<input type="hidden" name="absenceDate" value="<?php echo $dt->format('Y-m-d'); ?>">
					<input type="hidden" name="userID" value="<?php echo $userID ?>">

					<!-- Absence Name -->
					<div class="form-group mb-3">
						<label for="absenceName" class="form-label fw-semibold">
							<i class="fa-solid fa-tag me-1 text-muted"></i> Absence Name
						</label>
						<input
							type="text"
							class="form-control bg-light-blue"
							id="absenceName"
							name="absenceName"
							placeholder="e.g., Doctor's Appointment, Personal Leave"
							required
						>
						<div class="invalid-feedback">Please provide an absence name</div>
					</div>

					<!-- Absence Type -->
					<div class="form-group mb-3">
						<label for="absenceTypeID" class="form-label fw-semibold">
							<i class="fa-solid fa-list me-1 text-muted"></i> Absence Type
						</label>
						<select name="absenceTypeID" id="absenceTypeID" class="form-control bg-light-blue" required>
							<?php echo Form::populate_select_element_from_object($absenceTypes, 'absenceTypeID', 'absenceTypeName', '', '', 'Select Absence Type') ?>
						</select>
						<div class="invalid-feedback">Please select an absence type</div>
					</div>

					<!-- Affected Projects -->
					<div class="form-group mb-3">
						<label for="grp_option_abs" class="form-label fw-semibold">
							<i class="fa-solid fa-folder me-1 text-muted"></i> Affected Projects
						</label>
						<select
							name="projectID[]"
							id="grp_option_abs"
							class="form-control bg-light-blue"
							multiple
							required
						>
							<?php echo Form::populate_select_element_from_grouped_object($projectClientArray, 'projectID', 'projectName', '', '', 'Select Projects') ?>
						</select>
						<div class="invalid-feedback">Please select at least one affected project</div>
						<small class="form-text text-muted">
							<i class="fa-solid fa-info-circle me-1"></i>
							Select all projects affected by this absence
						</small>
					</div>

					<!-- Time Selection -->
					<div class="card bg-light-blue border-0 mb-3">
						<div class="card-body p-3">
							<label class="form-label fw-semibold mb-2">
								<i class="fa-solid fa-clock me-1 text-muted"></i> Time Period
							</label>

							<div class="row g-2">
								<div class="col-6">
									<label for="startTime" class="form-label small">Start Time</label>
									<input
										type="time"
										id="startTime"
										name="startTime"
										class="form-control bg-white"
										value="08:00"
										required
									>
									<div class="invalid-feedback">Required</div>
								</div>

								<div class="col-6">
									<label for="endTime" class="form-label small">End Time</label>
									<input
										type="time"
										id="endTime"
										name="endTime"
										class="form-control bg-white"
										value="17:00"
										required
									>
									<div class="invalid-feedback">Required</div>
								</div>
							</div>

							<!-- All Day Toggle -->
							<div class="form-check form-switch mt-3 mb-0">
								<input
									class="form-check-input"
									type="checkbox"
									id="allDay"
									name="allday"
									value="Y"
								>
								<label class="form-check-label" for="allDay">
									<i class="fa-solid fa-sun me-1"></i> All Day (8:00 - 17:00)
								</label>
							</div>

							<!-- Duration Display -->
							<div class="alert alert-info mt-2 mb-0 py-2 px-3" id="durationDisplay">
								<small>
									<i class="fa-solid fa-info-circle me-1"></i>
									Duration: <strong id="calculatedDuration">9:00 hours</strong>
								</small>
							</div>
						</div>
					</div>

					<!-- Quick Time Presets -->
					<div class="btn-group w-100 mb-2" role="group">
						<button type="button" class="btn btn-sm btn-outline-secondary time-preset" data-start="08:00" data-end="12:00">
							Morning<br><small class="text-muted">8-12</small>
						</button>
						<button type="button" class="btn btn-sm btn-outline-secondary time-preset" data-start="13:00" data-end="17:00">
							Afternoon<br><small class="text-muted">13-17</small>
						</button>
						<button type="button" class="btn btn-sm btn-outline-secondary time-preset" data-start="08:00" data-end="17:00">
							Full Day<br><small class="text-muted">8-17</small>
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Right Column - Description & Preview -->
		<div class="col-md-6">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<h6 class="card-title mb-3 text-primary border-bottom pb-2">
						<i class="fa-solid fa-file-lines me-2"></i>Description & Summary
					</h6>

					<!-- Description -->
					<div class="form-group mb-3">
						<label for="absenceDescription" class="form-label fw-semibold">
							<i class="fa-solid fa-comment me-1 text-muted"></i> Description
						</label>
						<textarea
							class="form-control bg-light-blue"
							name="absenceDescription"
							id="absenceDescription"
							rows="4"
							placeholder="Provide details about your absence..."
							required
						></textarea>
						<div class="form-text">
							<small id="absCharCount">0/300 characters</small>
						</div>
						<div class="invalid-feedback">Please provide a description</div>
					</div>

					<!-- Absence Summary Preview -->
					<div class="card bg-success-subtle border-success">
						<div class="card-header bg-success text-white py-2">
							<small class="fw-semibold">
								<i class="fa-solid fa-eye me-1"></i> Absence Summary
							</small>
						</div>
						<div class="card-body p-3">
							<div class="mb-2">
								<i class="fa-solid fa-calendar-day me-2 text-success"></i>
								<strong>Date:</strong>
								<span id="summaryDate"><?php echo $dt->format('l, F d, Y') ?></span>
							</div>
							<div class="mb-2">
								<i class="fa-solid fa-tag me-2 text-success"></i>
								<strong>Type:</strong>
								<span id="summaryType">Not selected</span>
							</div>
							<div class="mb-2">
								<i class="fa-solid fa-clock me-2 text-success"></i>
								<strong>Time:</strong>
								<span id="summaryTime">08:00 - 17:00</span>
							</div>
							<div class="mb-0">
								<i class="fa-solid fa-hourglass-half me-2 text-success"></i>
								<strong>Duration:</strong>
								<span id="summaryDuration">9:00 hours</span>
							</div>
						</div>
					</div>

					<!-- Help Text -->
					<div class="alert alert-light border mt-3 mb-0">
						<h6 class="alert-heading mb-2">
							<i class="fa-solid fa-lightbulb me-1 text-warning"></i> Tips
						</h6>
						<ul class="mb-0 small ps-3">
							<li>Use time presets for quick selection</li>
							<li>All day absence automatically sets 8:00-17:00</li>
							<li>Select all projects that will be affected</li>
							<li>Provide clear description for approval</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.absence-form-improved {
	padding: 0;
}

.absence-form-improved .bg-light-blue {
	background-color: rgba(var(--primary-rgb), 0.05);
	border-color: rgba(var(--primary-rgb), 0.2);
	transition: all 0.3s;
}

.absence-form-improved .bg-light-blue:focus {
	background-color: rgba(var(--primary-rgb), 0.08);
	border-color: var(--primary-color);
	box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.15);
}

.time-preset {
	padding: 0.5rem 0.25rem;
	font-size: 0.75rem;
	line-height: 1.2;
}

.time-preset.active {
	background-color: var(--primary-color);
	color: white;
	border-color: var(--primary-color);
}

.time-preset:hover {
	background-color: var(--primary01);
	border-color: var(--primary-color);
}

.card-title {
	font-size: 1rem;
	font-weight: 600;
}

input[type="time"] {
	padding: 0.5rem;
}

.form-check-input:checked {
	background-color: var(--success-color);
	border-color: var(--success-color);
}

#durationDisplay {
	font-size: 0.85rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
	'use strict';

	// Form elements
	const absenceNameInput = document.getElementById('absenceName');
	const absenceTypeSelect = document.getElementById('absenceTypeID');
	const startTimeInput = document.getElementById('startTime');
	const endTimeInput = document.getElementById('endTime');
	const allDayCheckbox = document.getElementById('allDay');
	const absenceDescription = document.getElementById('absenceDescription');
	const projectSelect = document.getElementById('grp_option_abs');

	// Summary elements
	const summaryType = document.getElementById('summaryType');
	const summaryTime = document.getElementById('summaryTime');
	const summaryDuration = document.getElementById('summaryDuration');
	const calculatedDuration = document.getElementById('calculatedDuration');
	const absCharCount = document.getElementById('absCharCount');

	// Initialize TomSelect for project selection
	if (projectSelect) {
		new TomSelect('#grp_option_abs', {
			plugins: ['remove_button'],
			maxItems: null,
			placeholder: 'Select affected projects',
			allowEmptyOption: false,
			sortField: {
				field: "text",
				direction: "asc"
			}
		});
	}

	// ========================================
	// Time Calculation & Validation
	// ========================================
	function calculateDuration() {
		const start = startTimeInput.value;
		const end = endTimeInput.value;

		if (!start || !end) return;

		const startMinutes = timeToMinutes(start);
		const endMinutes = timeToMinutes(end);

		let diff = endMinutes - startMinutes;
		if (diff < 0) {
			// End time is on next day
			diff += 24 * 60;
		}

		const hours = Math.floor(diff / 60);
		const minutes = diff % 60;
		const duration = `${hours}:${String(minutes).padStart(2, '0')} hours`;

		calculatedDuration.textContent = duration;
		summaryDuration.textContent = duration;
		summaryTime.textContent = `${start} - ${end}`;
	}

	function timeToMinutes(time) {
		const [hours, minutes] = time.split(':').map(Number);
		return hours * 60 + minutes;
	}

	// Event listeners for time changes
	startTimeInput.addEventListener('change', calculateDuration);
	endTimeInput.addEventListener('change', calculateDuration);

	// ========================================
	// All Day Toggle
	// ========================================
	allDayCheckbox.addEventListener('change', function() {
		if (this.checked) {
			startTimeInput.value = '08:00';
			endTimeInput.value = '17:00';
			startTimeInput.setAttribute('readonly', true);
			endTimeInput.setAttribute('readonly', true);

			// Deactivate all time presets
			document.querySelectorAll('.time-preset').forEach(btn => {
				btn.classList.remove('active');
			});
		} else {
			startTimeInput.removeAttribute('readonly');
			endTimeInput.removeAttribute('readonly');
		}
		calculateDuration();
	});

	// ========================================
	// Time Presets
	// ========================================
	document.querySelectorAll('.time-preset').forEach(button => {
		button.addEventListener('click', function() {
			// Uncheck all day
			allDayCheckbox.checked = false;
			startTimeInput.removeAttribute('readonly');
			endTimeInput.removeAttribute('readonly');

			// Set times
			startTimeInput.value = this.getAttribute('data-start');
			endTimeInput.value = this.getAttribute('data-end');

			// Visual feedback
			document.querySelectorAll('.time-preset').forEach(btn => {
				btn.classList.remove('active');
			});
			this.classList.add('active');

			calculateDuration();
		});
	});

	// ========================================
	// Summary Updates
	// ========================================
	absenceTypeSelect.addEventListener('change', function() {
		const selectedText = this.options[this.selectedIndex].text;
		summaryType.textContent = selectedText || 'Not selected';
	});

	// ========================================
	// Character Counter
	// ========================================
	absenceDescription.addEventListener('input', function() {
		const count = this.value.length;
		absCharCount.textContent = `${count}/300 characters`;
		if (count > 300) {
			absCharCount.classList.add('text-danger');
			this.value = this.value.substring(0, 300);
		} else {
			absCharCount.classList.remove('text-danger');
		}
	});

	// ========================================
	// Form Validation
	// ========================================
	const form = document.querySelector('.absence-form-improved').closest('form');
	if (form) {
		form.addEventListener('submit', function(event) {
			let isValid = true;

			// Validate required fields
			[absenceNameInput, absenceTypeSelect, startTimeInput, endTimeInput, absenceDescription].forEach(element => {
				if (element.value.trim() === '' || element.value === '') {
					element.classList.add('is-invalid');
					isValid = false;
				} else {
					element.classList.remove('is-invalid');
				}
			});

			// Validate time range
			const start = timeToMinutes(startTimeInput.value);
			const end = timeToMinutes(endTimeInput.value);

			if (end <= start && end !== 0) {
				alert('End time must be after start time');
				endTimeInput.classList.add('is-invalid');
				isValid = false;
			}

			if (!isValid) {
				event.preventDefault();
				event.stopPropagation();
			}
		});

		// Real-time validation
		[absenceNameInput, absenceTypeSelect, startTimeInput, endTimeInput, absenceDescription].forEach(element => {
			element.addEventListener('blur', function() {
				if (this.value.trim() === '') {
					this.classList.add('is-invalid');
				} else {
					this.classList.remove('is-invalid');
				}
			});
		});
	}

	// Initialize duration on load
	calculateDuration();
});
</script>

