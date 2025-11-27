(function () {
	'use strict';

	/* To choose date */
	flatpickr('#date', {});
	flatpickr('.date', {});

	/* To choose date and time */
	flatpickr('#datetime', {
		enableTime: true,
		dateFormat: 'Y-m-d H:i',
	});

	/* For Human Friendly dates */
	flatpickr('.humanfrienndlydate', {
		altInput: true,
		altFormat: 'F j, Y',
		dateFormat: 'Y-m-d',
	});

	/* For Date Range Picker */
	flatpickr('.daterange', {
		mode: 'range',
		dateFormat: 'Y-m-d',
	});

	/* For Time Picker */
	flatpickr('#timepikcr', {
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
	});

	flatpickr('.timepicker', {
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
	});

	/* For Time Picker With 24hr Format */
	flatpickr('#timepickr1', {
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
		time_24hr: true,
		defaultDate: '00:00',
	});
	flatpickr('.timepicker24', {
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
		time_24hr: true,
	});

	flatpickr('#timepicker24hr', {
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
		time_24hr: true,
	});

	/* For Time Picker With Limits */
	flatpickr('#limittime', {
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
		minTime: '16:00',
		maxTime: '22:30',
	});

	/* For DateTimePicker with Limited Time Range */
	flatpickr('#limitdatetime', {
		enableTime: true,
		minTime: '16:00',
		maxTime: '22:00',
	});

	/* For Inline Calendar */
	flatpickr('#inlinecalendar', {
		inline: true,
	});

	/* For Date Pickr With Week Numbers */
	flatpickr('#weeknum', {
		weekNumbers: true,
	});

	/* For Inline Time */
	flatpickr('#inlinetime', {
		inline: true,
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
		time_24hr: true,
	});

	/* For Inline Time */
	flatpickr('#inlinetimePreTime', {
		inline: true,
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
		time_24hr: true,
		defaultDate: '00:00',
	});

	/* For Inline Time  class*/
	flatpickr('.inlinetime', {
		inline: true,
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
		time_24hr: true,
		defaultDate: '08:00',
	});

	/* For Preloading Time */
	flatpickr('#pretime', {
		enableTime: true,
		noCalendar: true,
		dateFormat: 'H:i',
		defaultDate: '13:45',
	});

	/* For Progress Business Development Expected Close Date - Future dates only */
	flatpickr('#progressExpectedCloseDate', {
		dateFormat: 'Y-m-d',
		altInput: true,
		altFormat: 'F j, Y',
		minDate: 'today',
		disableMobile: true,
		allowInput: false,
		clickOpens: true,
		onChange: function(selectedDates, dateStr, instance) {
			// Trigger validation if needed
			if (selectedDates.length > 0) {
				instance.element.classList.remove('is-invalid');
				instance.element.classList.add('is-valid');
			}
		}
	});
})();
