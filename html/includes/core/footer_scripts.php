

 <?php
if(!$isValidUser){?>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"  async defer></script>
    <?php
} else {?>

<!-- Chartjs Chart JS -->
<script src="<?php echo $base ?>assets/libs/chart.js/chart.min.js"></script>

<!-- CRM-Dashboard -->
<!-- <script src="<?php echo $base ?>assets/js/index.js"></script> -->

<?php


} ?>
<!-- Popper JS -->
<script src="<?php echo $base ?>assets/libs/@popperjs/core/umd/popper.min.js"></script>

<!-- Bootstrap JS -->
<script src="<?php echo $base ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Your code here
document.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
    }
});
});
</script>


<!-- Scroll To Top -->
<div class="scrollToTop">
    <span class="arrow"><i class="ri-arrow-up-s-fill fs-20"></i></span>
</div>
<div id="responsive-overlay"></div>
<!-- Scroll To Top -->
<?php
if($isValidUser){ ?>
 <!-- Apex Charts JS -->
 <script src="<?php echo $base ?>assets/libs/apexcharts/apexcharts.min.js"></script>


<!-- Defaultmenu JS -->
<script src="<?php echo $base ?>assets/js/defaultmenu.min.js"></script>

<!-- Node Waves JS-->
<script src="<?php echo $base ?>assets/libs/node-waves/waves.min.js"></script>
   <!-- Bootstrap JS -->
   <!-- <script src="<?php echo $base ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script> -->

<!-- Sticky JS -->
<script src="<?php echo $base ?>assets/js/sticky.js"></script>

<!-- Simplebar JS -->
<script src="<?php echo $base ?>assets/libs/simplebar/simplebar.min.js"></script>
<script src="<?php echo $base ?>assets/js/simplebar.js"></script>

<!-- Color Picker JS -->
<script src="<?php echo $base ?>assets/libs/@simonwep/pickr/pickr.es5.min.js"></script>

 <!-- Date & Time Picker JS -->
<script src="<?= "{$base}assets/libs/flatpickr/flatpickr.min.js"?>"></script>
<script src="<?= "{$base}assets/js/date&time_pickers.js"?>"></script>
 <!-- Tom Select JS -->
 <script src="<?= $base ?>assets/libs/tom-select/js/tom-select.complete.min.js"></script>
<!-- <script src="<?= $base ?>assets/js/tom-select.js"></script> -->

  <!-- Jquery Cdn -->
<!-- <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script> -->

<!-- Select2 Cdn -->
<!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->

<!-- Internal Select-2.js -->
<!-- <script src="<?php echo $base ?>assets/js/select2.js"></script> -->





<!-- Toast Notifications System (load before custom.js) -->
<script src="<?php echo $base ?>assets/js/toast-notifications.js?v=<?= time(); ?>"></script>

<!-- Event Delegation System (load before custom.js) -->
<script src="<?php echo $base ?>assets/js/event-delegation.js?v=<?= time(); ?>"></script>

<!-- Custom JS -->

<script src="<?php echo $base ?>assets/js/custom.js"></script>

<!-- Time Attendance Shortcuts & Accessibility -->
<script src="<?php echo $base ?>assets/js/time_attendance_shortcuts.js?v=<?=time();?>"></script>

<script src="<?php echo $base ?>assets/js/custom-switcher.min.js"></script>

    <script>
        function validateTimeInput(element) {
			let value = element.value;
			if (value.includes('.')) {
				let decimalHours = parseFloat(value);
				let hours = Math.floor(decimalHours);
				let minutes = Math.round((decimalHours - hours) * 60);
				let formattedTime = `${hours < 10 ? '0' : ''}${hours}:${minutes < 10 ? '0' : ''}${minutes}`;
				element.value = formattedTime;
			}
			else {
				let timeParts = value.split(':');
				if (timeParts.length === 2) {
					let hours = parseInt(timeParts[0]);
					let minutes = parseInt(timeParts[1]);
					if (minutes > 59) {
						hours += Math.floor(minutes / 60);
						minutes = minutes % 60;
					element.nextElementSibling.textContent = "Invalid time format. Please enter time in HH:MM format. The extra minutes will be converted to hours";
					}
					if (isNaN(hours) || isNaN(minutes)) {
						element.value = '';
					} else {
						element.value = `${hours < 10 ? '0' : ''}${hours}:${minutes < 10 ? '0' : ''}${minutes}`;
					}
				} else {
					element.value = '';
				}
			}
		}



        tinymce.init({
            selector: 'textarea.borderless',
            height: 300,
            plugins: 'advlist autolink link image lists charmap preview',
            menubar: false,
            resize: true,
        });
        tinymce.init({
            selector: 'textarea.borderless-mini',

            height: 150,
            plugins: 'advlist autolink link image lists charmap preview',
            menubar: false,
            resize: true,
        });

        tinymce.init({
            selector: 'textarea.borderless-md',

            height: 225,
            plugins: 'advlist autolink link image lists charmap preview',
            menubar: false,
            resize: true,
        });
    </script>

    <?php
}?>