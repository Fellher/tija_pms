<script type="text/javascript">
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
<?php $email = isset($_GET['email']) ? Utility::clean_string($_GET['email']) : ''; ?>
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
                        <form class="authentication-cover" action="<?php echo "{$base}php/scripts/core/password_reset_request.php" ?>" method="post">
                            <div class="aunthentication-cover-content">
                                <p class="h4 fw-semibold mb-2 text-center">Forgot password?</p>
                                <p class="mb-4 fw-normal text-center">Remember your password?  <a href="<?php echo "{$base}html/?p=sign_in" ?>" class="text-primary text-decoration-underline fw-semibold">Sign in here</a></p>
                                <div class="row gy-3">
                                    <div class="col-xl-12">
                                        <label for="reset-password" class="form-label text-default op-8">Email address</label>
                                        <div class="input-group">
                                            <input type="email" class="form-control form-control-lg" name="Email" id="reset-password" placeholder="Email" value="<?php echo $email ? $email: ""; ?>" required>
                                       </div>
                                    </div>
                                    <div class="col-10 mb-3 mx-auto">
                                    <div id="racg_recapture" class="mx-auto w-100"></div>
                                </div>
                                    <div class="col-xl-12 d-grid mt-3">
                                        <button  type="submit" class="btn btn-lg btn-primary">Send Reset Link</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
