<?php

class Form {

	public static function validate_email ($email) {
		$isValid = true;
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex) {
			$isValid = false;
		} else {
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64) {
				 // local part length exceeded
				 $isValid = false;
			} else if ($domainLen < 1 || $domainLen > 255) {
				 // domain part length exceeded
				 $isValid = false;
			} else if ($local[0] == '.' || $local[$localLen-1] == '.') {
				 // local part starts or ends with '.'
				 $isValid = false;
			} else if (preg_match('/\\.\\./', $local)) {
				 // local part has two consecutive dots
				 $isValid = false;
			} else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
				 // character not valid in domain part
				 $isValid = false;
			} else if (preg_match('/\\.\\./', $domain)) {
				 // domain part has two consecutive dots
				 $isValid = false;
			} else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
				 // character not valid in local part unless local part is quoted
				 if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
						$isValid = false;
				 }
			}
			/*if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
				 // domain not found in DNS
				 $isValid = false;
			}*/
		}
		return $isValid ? $email : false;
	}

	public static function populate_select_element_from_array($array, $valueColumn, $textColumn, $selectedID, $selectedText, $blankText='Select:') {
		$resultString = "";
	
		if (count($array) == 0) {
			return '<option value="">Nothing to select</option>';
		} else {
			$returnString = "<option value=\"\">{$blankText}</option>";
			foreach ($array as $row) {
				$selected = ($row[$valueColumn] == $selectedID || $row[$textColumn] == $selectedText) ? "SELECTED" : "";
				$returnString .= "<option value=\"".$row[$valueColumn]."\" $selected>".ucfirst($row[$textColumn])."</option>";
			}
			return $returnString;
		}
	}

	public static function populate_select_element_from_object($array, $valueColumn, $textColumn, $selectedID, $selectedText, $blankText="Select:") {
		$resultString = "";
		if (!empty($array)) {
			if (count($array) == 0) { 
			  return "<option value=\"\">Nothing to select</option>";
			} else {
				$returnString = "<option value=\"\">{$blankText}</option>";
			  	foreach ($array as $row) {
					$selected = ($row->$valueColumn == $selectedID || Utility::clean_string($row->$textColumn) == $selectedText) ? "SELECTED" : "";
					$returnString .= "<option value=\"".$row->$valueColumn."\" $selected>".Utility::clean_string(ucfirst($row->$textColumn))."</option>";
			  	}
			  return $returnString;
			}			
		} else {
			  return "<option value=\"\">Nothing to select</option>";
		}	
	}

	public static function populate_select_element_from_object_multi($array, $valueColumn, $textColumn, $selectArr, $selectedText, $blankText="Select:") {
		$resultString = "";		
		if (!empty($array)) {
			if (count($array) == 0) { 
			  return "<option value=\"\">Nothing to select</option>";
			} else {
				$returnString = "<option value=\"\">{$blankText}</option>";
				foreach ($array as $row) {
					$selected ="";					
					if ($selectArr) {						
						foreach ($selectArr as $key => $selectedID) {
							if ($selectedID == $row->$valueColumn) {
								$selected =  "SELECTED";
							}							
						}
					}				
					$returnString .= "<option value=\"".$row->$valueColumn."\" $selected>".ucfirst($row->$textColumn)."</option>";
			  	}
			  	return $returnString;
			}			
		} else {
			  return "<option value=\"\">Nothing to select</option>";
		}
	}



	public static function populate_select_element_from_grouped_array($array, $valueColumn, $textColumn,  $selectedID, $selectedText, $blankText='Select:') {
		$resultString = "";
		if (count($array) == 0) { 
			return "<option value=\"\">Nothing to select</option>";
		} else {
			$returnString = "<option value=\"\">Select:</option>";
			foreach ($array as $groupName => $options) {
				
				$returnString .= "<optgroup label='{$groupName}'>";
				foreach ($options as $option) {
					$selected = ($option[$valueColumn] == $selectedID || $option[$textColumn] == $selectedText) ? "SELECTED" : "";
					$returnString .= "<option value=\"".$option[$valueColumn]."\" $selected>".ucfirst($option[$textColumn])."</option>";
				}
				$returnString .= "</optgroup>";
			}
			return $returnString;
		}
	}

	public static function populate_select_element_from_grouped_object($array, $valueColumn, $textColumn,  $selectedID, $selectedText, $blankText='Select:') {
		$resultString = "";
		if (count($array) == 0) { 
			return "<option value=\"\">Nothing to select</option>";
		} else {
			$returnString = "<option value=\"\">{$blankText}:</option>";
			foreach ($array as $groupName => $options) {
				$returnString .= "<optgroup label='{$groupName}'>";
				foreach ($options as $option) {
					// var_dump($option);
					$selected = ($option->$valueColumn == $selectedID || $option->$textColumn == $selectedText) ? "SELECTED" : "";
					// var_dump($selected);
					$returnString .= "<option value=\"".$option->$valueColumn."\" $selected>".ucfirst($option->$textColumn)."</option>";
				}
				$returnString .= "</optgroup>";
			}
			return $returnString;
		}
	}
}?>