<!-- Page Header -->
<?php

//  var_dump($isValidAdmin);
if (!$isAdmin) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true,
        array('fst-italic', 'text-center', 'font-18'));
        include "includes/core/log_in_script.php";
    return;
}?>


<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Setup Dashboard</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                <li class="breadcrumb-item active d-inline-flex" aria-current="page">Setup Dashboard</li>
            </ol>
        </nav>
    </div>
</div>