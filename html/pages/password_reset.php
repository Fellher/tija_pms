
<?php


if (isset($_GET['t1']) && preg_match('/^[0-9a-f]+$/', $_GET['t1']) && isset($_GET['t2']) && preg_match('/^[0-9a-f]+$/', $_GET['t2'])) {
	$token1 = $_GET['t1'];
	$token2 = $_GET['t2']; 
	if (isset($_GET['ID']) && $_GET['ID'] !== '' ){
		$userID= Utility::clean_string($_GET['ID']);
		$applicantDetails=Core::user (['ID'=>$userID], true, $DBConn);		
	}
	if (isset($_GET['t1']) && preg_match('/^[0-9a-f]+$/', $_GET['t1']) && isset($_GET['t2']) && preg_match('/^[0-9a-f]+$/', $_GET['t2'])) {
		$token1 = $_GET['t1'];
		$token2 = $_GET['t2']; ?>

		<section id="content  " style="height: 100vh; width: 100vw; display: flex; justify-content: center; align-items: center;">
			<div class="content-wrap  pt-4" style="max-width: 600px; margin: auto;"> 
				<div class="container-fluid ">        

						<div class="">
							<div class="col-md-12 col-sm-12 p-sm-6 p-3 mx-auto shadow-lg">
								<form id="#AddAlumniMember" method="post" onsubmit="return checkForm(this);" action="<?php echo $base; ?>php/scripts/core/set_password.php">
									<fieldset>
										<div class="center">
												<legend>Set Password For Your Account</legend>
												<div>Please select a password for your account below:</div>
										</div>
										<input type="hidden" name="t1" value="<?php echo $_GET['t1']; ?>">
										<input type="hidden" name="t2" value="<?php echo $_GET['t2']; ?>">								 
										<div class="form-group">
											<label class="control-label" for="Email">Email Address</label>
											<div class="controls">
												<input type="email" id="Email" name="Email" placeholder="Email Address" value="<?php echo $applicantDetails->Email; ?>" class="form-control required" required readOnly >
											</div>
										</div>
	
										<div class="form-group">
											<label class="control-label" for="Password">Password:</label>
											<div class="controls">
												<input type="password" id="Password" name="Password" placeholder="Password" class="form-control required" required >
											</div>
										</div>

										<div class="form-group  ">
											<label class="control-label" for="PasswordConfirm">Confirm Password:</label>
											<div class="controls">
												<input type="password" id="PasswordConfirm" name="PasswordConfirm" placeholder="Password" class="form-control required" onkeyup="checkPasswordMatch();" required>
											</div>
										</div>

										<div style="clear: both; height: 10px;"></div>
										<div class="registrationFormAlert" id="divCheckPasswordMatch"></div>
										<div class="form-group">
											<label class="checkbox" style="margin-left=20px">
											<input onchange="this.setCustomValidity(validity.valueMissing ? 'Please indicate that you have read, understood and agree to <?php echo $config['siteName']; ?>  Terms and conditions for use of this platform' : '');" id="field_terms"  type="checkbox" name="terms" required value="1"> I have read, understood and agree to <?php echo $config['siteName'] ?> Terms & Condition for the use of this platform .
										</label>
										<script type="text/javascript"> document.getElementById("field_terms").setCustomValidity("Please indicate that you have read, understood and agree to <?php echo $config['siteName']; ?>  Terms & Conditions for the use of this platform"); 
										</script>
										<p class="text-end">
											<button id="completeRegistration" class="btn btn-primary" type="submit" ><i class="fa fa-arrow-right"></i>&nbsp;Complete Registration</button>
										</p>
										</div>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</section>


		<script type="text/javascript">

			const password = document.getElementById('Password'),
				passwordconfirm = document.getElementById('PasswordConfirm');

				passwordconfirm.addEventListener('keyup', checkPasswordMatch);

			function checkPasswordMatch () {
				let divCheckPasswordMatch = document.getElementById('divCheckPasswordMatch'),
					completeRegistration = document.getElementById('completeRegistration');
				if (password.value !== passwordconfirm.value) {
					divCheckPasswordMatch.innerHTML = `<span class"text-danger"> Passwords do not match </span>`;
					divCheckPasswordMatch.classList.add('alert');
					divCheckPasswordMatch.classList.add('alert-danger');
					divCheckPasswordMatch.setAttribute('role', "alert");
					completeRegistration.setAttribute("disabled", "");
				} else {
					divCheckPasswordMatch.innerHTML =`<span class= "text-success"> Passwords match </span>` ;
					// divCheckPasswordMatch.classList.add('alert');
					divCheckPasswordMatch.classList.add('alert-success');
					divCheckPasswordMatch.classList.remove('alert-danger');
					completeRegistration.removeAttribute("disabled");
				}
			}
			// function checkPasswordMatch() {
			// 			var password = $("#Password").val();
			// 			var confirmPassword = $("#PasswordConfirm").val();

			// 			if (password != confirmPassword) {
			// 				$("#divCheckPasswordMatch").html("Passwords do not match!").addClass('text-danger').removeClass('text-success');
			// 			} else {
			// 				$("#divCheckPasswordMatch").html("Passwords match.").addClass('text-success').removeClass('text-danger');
			// 				$("#completeRegistration").removeAttr("disabled");
			// 			}						
			// 	}
		</script>
		<?php
	
	} else {
		Alert::error('Please click on the link you received in the email.');
	}
} else {
	
	$email = isset($_GET['email']) ? $_GET['email'] : '';
	$valid = isset($_GET['valid']) ? $_GET['valid'] : '';
	if ($valid === 'true' && $email !== '') {
		$applicantDetails=Core::user (['Email'=>$email], true, $DBConn);
		// var_dump($applicantDetails);
	}
	
	?>
	<div class="container-lg">
        <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
            <div class="col-xxl-45col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                <div class="my-4 d-flex justify-content-center">
                    <a href="index.html">
                        <img src="../assets/img/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
                        <img src="../assets/img/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
                    </a>
                </div>
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="authentication-cover">
                            <div class="aunthentication-cover-content">
                                <p class="h4 fw-semibold mb-2 text-center">Password Reset</p>
                                <p class="mb-4 fw-normal text-center fs-18">Your password request link has been sent to your email address.</p>
                                <p class="mb-4 fw-normal text-center fs-18">Please check your email for the link to reset your password.</p>
                                
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

	<?php
	Alert::error('Please click on the link you received in the email.');
}
?>