
<?php 
if($isValidUser){
	if ($isValidAdmin) {
		header("location:{$base}html/?s=core&ss=admin&p=home");
		# code...
	} else {
			header("location:{$base}html/?s=user&p=home");
	}
} else {
    
    include "includes/core/log_in_script.php";
    
}
// <!-- Page Header -->?>