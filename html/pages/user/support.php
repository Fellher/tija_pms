<?php 
if(!$isValidUser){
   Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
   include "includes/core/log_in_script.php";
   return;
}?>
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
        <h1 class="page-title fw-medium fs-24 mb-0">Tija Support</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
                    <li class="breadcrumb-item active d-inline-flex" aria-current="page">Support</li>
                </ol>
            </nav>
        </div>
    </div>