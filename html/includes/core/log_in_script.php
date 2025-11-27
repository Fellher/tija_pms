<!-- <script type="text/javascript">
    var verifyCallback = function(response) {
            document.querySelector('.sign_in').disabled=false;
    };	
    var onloadCallback = function() {		
        grecaptcha.render('racg_recapture', {
            'sitekey' : '<?php echo $config['siteKey'] ?>',
                'callback' : verifyCallback
        });
    };
</script>
// ... existing code ... -->
<script>

	let recaptchaRendered = false; // Flag to check if reCAPTCHA is rendered
    let recaptchaSiteKey = '<?php echo $config['siteKey']; ?>';

	function onloadCallback() {
		if (!recaptchaRendered) {
			grecaptcha.render('racg_recapture', {
				'sitekey': recaptchaSiteKey, // Replace with your actual site key
				'callback': onRecaptchaSuccess
			});
			recaptchaRendered = true; // Set the flag to true after rendering
		}
	}

	function onRecaptchaSuccess(token) {
		// Handle successful reCAPTCHA verification
        document.querySelector('.sign_in').disabled=false;
	}
</script>
<!-- // ... existing code ... -->
<div class="container-lg">
    <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
        <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
            <div class="my-4 d-flex justify-content-center">
                <a href="index.html">
                    <img src="../assets/img/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
                    <img src="../assets/img/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
                </a>
            </div>
            <div class="card custom-card">
                <div class="card-body">
                    <form class="authentication-cover" action="<?php echo "{$base}php/scripts/core/sign_in.php" ?>" method="post">
                        <div class="aunthentication-cover-content">
                            <p class="h4 fw-bold mb-2 text-center">Sign in</p>
                            <!-- <div class="text-center">
                                <p class="fs-14 text-muted mt-3">Don't have an account yet? <a href="signup.html" class="text-primary fw-semibold">Sign up here</a></p>
                            </div> -->
                            <div class="d-grid align-items-center">
                                <button class="btn btn-outline-light border shadow-sm">
                                    <img src="../assets/img/authentication/social/1.png" class="w-4 h-4 me-2" alt="google-img">Sign in with Google
                                </button>
                            </div>
                            <div class="text-center my-3 authentication-barrier">
                                <span>OR</span>
                            </div>
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="signup-Email" class="form-label text-default op=8">Email address</label>
                                    <input type="text" name="email" class="form-control form-control-lg" id="signup-Email" placeholder="Email">
                                </div>
                                <div class="col-xl-12">
                                    <label  class="form-label text-default d-block">password
                                        <a href="<?php echo "{$base}html/?p=reset_password" ?>" class="float-end text-success">Forget password ?</a>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="password" class="form-control form-control-lg " id="signup-password" placeholder="password">
                                    </div>
                                </div>
                                <div class="col-10 mb-3 mx-auto">
                                    <div id="racg_recapture" class="mx-auto w-100"></div>
                                </div>
                                <div class="col-xl-12 d-grid mt-2">
                                    <input type="submit" value="Submit" class="btn btn-lg btn-primary sign_in" id="login-form-submit" name="login_submit" disabled />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>