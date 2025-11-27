<?php
class Alert {

	public static function show ($msg, $type, $dismissable, $classes ='') {
		$classNames ='';
		if (isset($classes) && is_array($classes) ) {			 
			$classNames=implode(' ', array_filter($classes, function($n){ return $n ? true : false; }));			 
		}
		// var_dump($classNames);
		?>
		<div class="alert <?php echo "alert-{$type} alert-dismissible fade show {$classNames} ";?>" role="alert">
			<?php echo $msg;
			echo $dismissable ? "<button type='button' class='btn-close {$classNames}' data-bs-dismiss='alert' aria-label='Close'></button>" : '';?>
		</div>
		<?php
	}

	public static function error ($msg, $dismissable=false, $classes="") {
		Alert::show($msg, 'danger', $dismissable, $classes);
	}

	public static function warning ($msg, $dismissable=false,  $classes="") {
		Alert::show($msg, 'warning', $dismissable, $classes);
	} 

	public static function success ($msg, $closeable=false,  $classes="") {
		Alert::show($msg, 'success', $closeable, $classes);
	}

	public static function info ($msg, $closeable=false,  $classes="") {
		Alert::show($msg, 'info', $closeable, $classes);
	}

	 public static function danger ($msg, $closeable=false,  $classes="") {
		Alert::show($msg, 'danger', $closeable, $classes);
	}
	 public static function privilege_error () {
		Alert::error('You do not have sufficient privileges to perform that action.', false);
	}

	public static function print_error_list ($errors, $closeable=false,  $classes="") {
		$msg = '';
		$msg .= '<ul class="indent">';
		foreach ($errors as $key=>$error) {
			$msg .= "<li>{$error}</li>";
		}
		$msg .= '</ul>';
		Alert::error($msg, $closeable);
	}
}?>