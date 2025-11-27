<?php
/**
 * Core class - Pho class fro authentication
 *
 * @author 		Felix Mauncho
 * @copyright 	2023 Felixmauncho
 * @email 		felixmauncho@sbsl.co.ke
 * ======================================
 * */

	class Core {
		// public static function user ( $whereArr, $single, $DBConn) {
		// 	$cols = array('ID', 'Email', 'FirstName', 'Surname', 'OtherNames', 'profile_image', 'NeedsToChangePassword', 'Valid', 'active');
		// 	$rows = $DBConn->retrieve_db_table_rows ('people', $cols, $whereArr);
		// 	if ($single === true) {
		// 		return(count($rows) ===1) ? $rows[0] : false;
		// 	} else {
		// 		return(count($rows) >0) ? $rows : false;
		// 	}
		// }

		public static function user($whereArr, $single, $DBConn) {
			$params= array();
			$where= '';
			$rows=array();
			if (count($whereArr) > 0) {
				$i = 0;
				foreach ($whereArr as $col => $val) {
					if ($where == '') {
						$where = "WHERE ";
					} else {
						$where .= " AND ";
					}
					$where .= "u.{$col} = ?";
					$params[] = array($val, 's');
					$i++;
				}
			}
			$sql = "SELECT  u.ID, u.DateAdded, u.FirstName, u.Surname, u.OtherNames, u.Email, u.profile_image, u.Valid, u.userInitials, u.isEmployee,
			CONCAT(u.Surname, ' ', u.FirstName) as userFullName,  u.NeedsToChangePassword, u.active, CONCAT(u.Surname, ' ', u.FirstName, ' (', u.Email, ')') as userFullNameEmail
			FROM people u

			{$where}
			ORDER BY u.FirstName ASC";
			$rows = $DBConn->fetch_all_rows($sql,$params);
			return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
		}

		public static function app_administrators($whereArr, $single, $DBConn) {
			$params= array();
			$where= '';
			$rows=array();
			if (count($whereArr) > 0) {
				$i = 0;
				foreach ($whereArr as $col => $val) {
					if ($where == '') {
						$where = "WHERE ";
					} else {
						$where .= " AND ";
					}
					$where .= "a.{$col} = ?";
					$params[] = array($val, 's');
					$i++;
				}
			}
			$sql = "SELECT  a.*, u.ID, u.FirstName, u.Surname, u.OtherNames, u.Email, u.profile_image, u.Valid, u.userInitials, u.isEmployee AS isEmployeeAdmin, at.adminTypeName, at.adminCode, o.orgName, o.orgDataID
			FROM tija_administrators a
			LEFT JOIN tija_admin_types at ON a.adminTypeID = at.adminTypeID
			LEFT JOIN people u ON a.userID = u.ID
			LEFT JOIN tija_organisation_data o ON a.orgDataID = o.orgDataID
			{$where}
			ORDER BY u.FirstName ASC";
			$rows = $DBConn->fetch_all_rows($sql,$params);
			return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
		}

		/*User full name concantation
		**********************************************************************/
		public static function user_name_object($object) {
	      	if ($object && is_object($object)) {
	        	$firstName= isset($object->FirstName) ? strtolower($object->FirstName) : '';
	        	$surName = isset($object->Surname) ? strtolower($object->Surname) : '';
	        	$otherName= isset($object->OtherNames) ? strtolower($object->OtherNames) : '';
				$userName=implode(' ', array_filter(array($firstName,$otherName, $surName), function($n){ return $n ? true : false; }));
	        	return $userName ? ucwords($userName) : false;
	      	}
	      	return false;
	    }
		public static function user_name_initials($object) {
	      if ($object && is_object($object)) {
	        	$firstName= isset($object->FirstName) ? strtolower($object->FirstName) : '';
	        	$surName = isset($object->Surname) ? strtolower($object->Surname) : '';
	        	$otherName= isset($object->OtherNames) ? strtolower($object->OtherNames) : '';
			}
	        	return $firstName && $surName ? strtoupper(substr($firstName, 0, 1).substr($surName, 0, 1)) : false;
	    }

	    public static function user_name($ID, $DBConn) {
	      	if ($ID && $ID!== '') {
	        	$userDetails= Core::user(array('ID'=>$ID), true, $DBConn);
					if($userDetails){
						$firstName= isset($userDetails->FirstName) ? strtolower($userDetails->FirstName) : '';
						$surName = isset($userDetails->Surname) ? strtolower($userDetails->Surname) : '';
						$otherName= isset($userDetails->OtherNames) ? strtolower($userDetails->OtherNames) : '';
						if ($userDetails) {
								$userFullName=implode(' ', array_filter(array($firstName,$otherName, $surName), function($n){ return $n ? true : false; }));
						}
						return $userFullName ? ucwords($userFullName) : false;
					}
	      	}
	      	return false;
	    }

		 public static function get_user_name_initials ($ID, $DBConn, $initialsCount=2) {
			if ($ID && $ID!== '') {
				$userDetails= Core::user(array('ID'=>$ID), true, $DBConn);
				if($userDetails){
					$firstName= isset($userDetails->FirstName) ? strtolower($userDetails->FirstName) : '';
					$surName = isset($userDetails->Surname) ? strtolower($userDetails->Surname) : '';
					$otherName= isset($userDetails->OtherNames) ? strtolower($userDetails->OtherNames) : '';
					if ($userDetails) {
						$userFullName=implode(' ', array_filter(array($firstName,$otherName, $surName), function($n){ return $n ? true : false; }));
					}
					// get initials
					if($initialsCount > 0){
						$initialsArr = array();
						if($initialsCount = 2){
							$initialsArr[] = strtoupper(substr($firstName, 0, 1).substr($surName, 0, 1));
						} else {
							$names = explode(' ', $userFullName);
							foreach ($names as $name) {
								$initialsArr[] = strtoupper(substr($name, 0, 1));
							}

						}

						// var_dump($initialsArr);
						$initials= implode('', $initialsArr);
					}

					return $firstName && $initials ? ['name'=>ucwords($userFullName), 'initials'=>$initials, 'ID'=> $ID] : false;
				}

			}

		 }

		public static function add_registration_tokens ($personID, $DBConn) {
		 	if ($personID) {
				$added = false;
				$n = 0;
				while (!$added && $n < 8) {
					$token1 = bin2hex(openssl_random_pseudo_bytes(32));
					$token2 = bin2hex(openssl_random_pseudo_bytes(32));
					$insert = array('PersonID'=>$personID, 'DateAdded'=>'NOW()','Token1'=>$token1,'Token2'=>$token2,'PasswordSet'=>'n');
					$added = $DBConn->insert_data('registration_tokens', $insert);
					if($added){
						$ID= $DBConn->lastInsertId();
					}
					$n++;
				}
				return $added ? array($token1, $token2, $ID) : false;
		 	}
		}

		public  static function tokens($whereArr, $single, $DBConn) {
			$cols = array('ID', 'PersonID', 'DateAdded', 'PasswordSet', 'DatePasswordSet', 'Token1', 'Token2');
			$rows = $DBConn->retrieve_db_table_rows ('registration_tokens', $cols, $whereArr);
			return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
		}

		public static function registration_token_details($email, $token1, $token2, $DBConn) {
		 	if ($token1 && $token2) {
				$where = '';
				$params = array();
				$where .= ($where == '' ? '' : ' AND ') . 'rt.Token1 = ? AND rt.Token2 = ?';
				$params[] = array($token1, 's');
				$params[] = array($token2, 's');
				$query = "SELECT rt.ID, rt.PersonID, p.Email, rt.DateAdded, rt.PasswordSet, rt.DatePasswordSet, p.NeedsToChangePassword
						 	FROM registration_tokens rt
						 	LEFT JOIN people p ON rt.PersonID = p.ID
						 	WHERE {$where};";
				$cols = array('ID', 'PersonID', 'Email', 'DateAdded','PasswordSet', 'DatePasswordSet', 'NeedsToChangePassword');
				$rows = $DBConn->fetch_all_rows($query, $params);

				var_dump($rows);
				if (count($rows) == 1) {
					if ($email) {
					 	return $rows[0]->Email == $email ? $rows[0] : false;
					} else {
				 		return $rows[0];
					}
				}
		 	}
		 	return false;
		}

		public static function token_verification( $token1, $token2, $DBConn) {
		 	if ($token1 && $token2) {
				$where = '';
				$params = array();
				$where .= ($where == '' ? '' : ' AND ') . 'rt.Token1 = ? AND rt.Token2 = ?';
				$params[] = array($token1, 's');
				$params[] = array($token2, 's');
				$query = "SELECT rt.ID, rt.PersonID,  rt.DateAdded, rt.PasswordSet, rt.DatePasswordSet, rt.Valid
						 FROM registration_tokens rt
						 WHERE {$where};";
				$cols = array('ID', 'PersonID', 'DateAdded','PasswordSet', 'DatePasswordSet', 'valid');
				$rows = $DBConn->fetch_all_rows($DBConn->query($query, $params), $cols);
				return count($rows)==1 ? $rows[0] : false;
		 	}
		 	return false;
		}

		public static function complete_registration ($registrationDetails, $password, $DBConn) {
		 	if (is_object($registrationDetails)) {
				$updatePassword = true;
				if ($password && Core::set_password($registrationDetails->PersonID, $password, false, $DBConn)) {
					$updateArray = array('PasswordSet'=>'y', 'DatePasswordSet'=>'NOW()');
					$where = array('ID'=>$registrationDetails->ID);
					$updatePassword = $DBConn->update_table('registration_tokens', $updateArray, $where);
				}
				return $DBConn->update_table('people', array('Valid'=>'y', 'NeedsToChangePassword'=>'n'), array('ID'=>$registrationDetails->PersonID));
		 	}
		 	return false;
		}

		public static function set_password ($personID, $password, $needsToChangePassword, $DBConn) {
		 	if ($personID && $password) {
				$salt = mt_rand();
				$hash = crypt($password, '$6$rounds=1024$'. $salt . '$'); /* sha512 encryption. */
				$updateArray = array('Password'=>$hash, 'NeedsToChangePassword'=>$needsToChangePassword ? 'y' : 'n');
				$where = array('ID'=>$personID);
				return $DBConn->update_table('people', $updateArray, $where);
		 	}
		 	return false;
		}

		public static function updatePassword_token ($registrationDetails, $password, $passwordSet){
			if ($password && $passwordSet) {
				$updateArray = array('PasswordSet'=>'y', 'DatePasswordSet'=>'NOW()');
				$where = array('ID'=>$registrationDetails->ID);
				$updatePassword = $DBConn->update_table('registration_tokens', $updateArray, $where);
			}
			return $updatePassword;
		}



		public static function validate_login ($email, $password, $DBConn) {
		 	if ($email && $password) {
				$params = array();
				$params[] = array($email, 's');
				$query = "SELECT p.ID, p.Password, p.NeedsToChangePassword FROM people p
							WHERE p.Email = ?;";
				$cols = array('PersonID', 'Password', 'NeedsToChangePassword');
				$DBConn->query($query);
				foreach ($params as $value) {
					$DBConn->bind("1", $value[0] );
				}
				$rows = $DBConn->resultSet();
				// var_dump($rows);
				if (count($rows) == 1) {
					$data= $rows[0];

					$pws = crypt($password, $data->Password);
					 var_dump($pws);
					var_dump($data->Password);
					if (crypt($password, $data->Password) == $data->Password) {

						$returnArr =array('PersonID'=>$data->ID,  'NeedsToChangePassword'=>$data->NeedsToChangePassword);

						var_dump($returnArr);
						return $returnArr;
					}
				}
		 	}
		 	return false;
		}

		public static function active_sessions ($personID, $DBConn) {
		 	if ($personID) {
				$params = array();
				$params[] = array($personID, 's');
				$query = "SELECT SessIDStr
							FROM login_sessions
							WHERE PersonID = ? AND ISNULL(LogoutTime);";
				$cols = array('SessIDStr');
			 	$DBConn->query($query);
				foreach ($params as $value) {
					$DBConn->bind("1", $value[0]);
					$DBConn->bind("2", null);
				}
				$rows= $DBConn->resultSetArr();
				if (count($rows) > 0) {
					return array_map(function($val) { return $val['SessIDStr']; }, $rows);
				}
		 	}
		 	return false;
		}

		public static function create_new_session ($personID, $check, $time, $DBConn) {
		 if ($personID) {
			$sessionID = false;
			$i = 0;
			do {
				$time = new DateTime();
				$sessionID = bin2hex(openssl_random_pseudo_bytes(32));
				$insertArray = array('PersonID'=>$personID, 'LoginTime'=>$time->format('Y-m-d H:i:s'), 'SessIDStr'=>$sessionID, 'CheckStr'=>$check, 'LastActionTime'=>$time->format('Y-m-d H:i:s'));
				if (!$DBConn->insert_data('login_sessions', $insertArray)) {
				 	$sessionID = false;
				}
				$i++;
			} while (!$sessionID && $i < 16);
				return $sessionID;
		 	}
		 	return false;
		}

		public static function update_session ($sessionID, $DBConn) {
		 	if ($sessionID) {
				$updateArray = array('LastActionTime'=>strftime("%Y-%m-%d %H:%M:%S", time()));
				$where = array('SessIDStr'=>$sessionID);
				return $DBConn->update_table('login_sessions', $updateArray, $where);
		 	}
		 	return false;
		}

		public static function end_session ($sessionID, $DBConn) {
		 	if ($sessionID) {
				$updateArray = array('LogoutTime'=>strftime("%Y-%m-%d %H:%M:%S", time()));
				$where = array('SessIDStr'=>$sessionID);
				return $DBConn->update_table('login_sessions', $updateArray, $where);
		 	}
		 	return false;
		}

		public static function end_active_sessions ($personID, $DBConn) {
		 	$activeSessions = Core::active_sessions($personID, $DBConn);
		 	if ($activeSessions) {
				$errorBool = false;
				foreach ($activeSessions as $index => $sessIDStr) {
					$endSession = Core::end_session($sessIDStr, $DBConn);
					if (!$endSession) {
				 		$errorBool = true;
					}
				}
				return !$errorBool;
		 	} else {
				return true;
		 	}
		}

		public static function session_details($sessIDStr, $DBConn) {
		 	if ($sessIDStr) {
				$cols = array('PersonID', 'CheckStr', 'LoginTime');
				$where = array('SessIDStr'=>$sessIDStr);
				$rows = $DBConn->retrieve_db_table_rows ('login_sessions', $cols, $where);
				if (count($rows) == 1) {
					return $rows[0];
				}
		 	}
		 	return false;
		}

		public static function is_valid_admin ($personID, $DBConn) {
			if ($personID) {
				$params = array();
				$params[] = array($personID, 's');
				$query = "SELECT ID
							FROM administrators
							WHERE ID = ?;";
				$cols = array('ID');
				$DBConn->query($query);
				$DBConn->bind("1", $personID );
				$rows= $DBConn->resultSet();
				return (count($rows) == 1) ? true : null;
			}
			return false;
		}

		 public static function is_valid_user_admin ($personID, $DBConn) {
			if ($personID) {
				$params = array();
				$params[] = array($personID, 's');
				$query = "SELECT adminID, userID
							FROM  tija_administrators
							WHERE userID = ?;";
				$cols = array('adminID','userID');
				// $DBConn->fetch_all_rows($query, $params);
				$DBConn->query($query);
				$DBConn->bind("1", $personID );
				$rows= $DBConn->resultSet();
				return (count($rows) == 1) ? true : null;
			}
			return false;
		}

		/*======================================
		login Redirect
		=======================================*/
		public static function login_redirect ($personID, $DBConn) {
			if (Core::is_valid_admin($personID, $DBConn)) {
				$isValidAdmin = true;
				$adminID = $personID;
				$toURL= "s=core&ss=admin&p=home";
			} elseif (Core::is_valid_user_admin($personID, $DBConn)) {
				$adminDetails=Core::org_admins(array('userID'=>$personID), true,  $DBConn);
				if ($adminDetails) {
					foreach ($adminDetails as $key => $admin) {
						if ((int)$admin->adminTypeID=== 1 ) {
							$isSuperAdmin= true;
						}
						if ((int)$admin->adminTypeID=== 2) {
							$isSystemAdmin = true;
						}

						if ((int)$admin->adminTypeID=== 3) {
							$isEntityAdmin = true;
						}

						if ((int)$admin->adminTypeID=== 4) {
							$isUnitAdmin = true;
						}

						if ((int)$admin->adminTypeID=== 5) {
							$isTeamAdmin = true;
						}

						$toURL= "s=user&p=home";
					}
				}
			} else {
				$toURL='s=user&p=home';
			}
			return $toURL ? $toURL : false;
		}

		public static function org_admins($whereArr, $single, $DBConn) {
			$cols = array('adminID', 'userID', 'adminTypeID', 'orgDataID', 'entityID', 'unitTypeID', 'unitID', 'DateAdded', 'LastUpdate', 'Lapsed', 'Suspended');
			$rows = $DBConn->retrieve_db_table_rows ('tija_administrators', $cols, $whereArr);
			return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
		}


		public static function organisation_admins($whereArr, $single, $DBConn) {
			$dataArr = array();
			$where = '';
			$params = array();
			if (count($whereArr) > 0) {
				$i = 0;
				foreach ($whereArr as $col => $val) {
					if ($where == '') {
						$where = "WHERE ";
					} else {
						$where .= " AND ";
					}
					$where .= "a.{$col} = ?";
					$params[] = array($val, 's');
					$i++;
				}
			}


			$query = "SELECT a.*, u.unitName, e.entityName, o.orgName, t.unitTypeName, at.adminTypeName,
			CONCAT(p.FirstName, ' ', p.Surname) AS AdminName, p.OtherNames, p.Email, p.profile_image
			FROM tija_administrators a
			LEFT JOIN tija_units u ON a.unitID = u.unitID
			LEFT JOIN tija_entities e ON a.entityID = e.entityID
			LEFT JOIN tija_organisation_data o ON a.orgDataID = o.orgDataID
			LEFT JOIN tija_unit_types t ON a.unitTypeID = t.unitTypeID
			LEFT JOIN tija_admin_types at ON a.adminTypeID = at.adminTypeID
			LEFT JOIN people p ON a.userID = p.ID
			{$where}
			ORDER BY a.adminID DESC";

			$rows = $DBConn->fetch_all_rows($query, $params);

			return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
		}

		public static function send_email_php_mailer($details, $config, $DBConn) {

			var_dump($details);
			$errors = array();
			$email = $details['Email'];
			$name = $details['Name'];
			$subject = $details['Subject'];
			$emailBody = $details['Body'];
			$emailNoHtml = $details['BodyNoHtml'];

			try {
				$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
				$mail->isSMTP();                                            // Send using SMTP
				$mail->Host       = $config['emailHost'];                   // Set the SMTP server to send through
				$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
				$mail->Username   = $config['siteEmail'];                   // SMTP username
				$mail->Password   = $config['emailPWS'];                    // SMTP password
				$mail->SMTPSecure =PHPMailer::ENCRYPTION_SMTPS; //PHPMailer::ENCRYPTION_STARTTLS;			// Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
				$mail->Port       =$config['emailPort'];                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

				$mail->setFrom($config['siteEmail'], $config['siteName']);
				$mail->addAddress($email, $name);     								// Add a recipient
				$mail->addReplyTo($config['siteEmail'], $config['siteName']);
				$mail->addBCC($config['secondaryEmail'], $config['siteName']);
				// Attachments
				//$mail->addAttachment('/var/tmp/file.tar.gz');         		// Add attachments
				//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    		// Optional name

				$mail->isHTML(true);                                 			// Set email format to HTML
				$mail->Subject = $subject;
				$mail->Body    = $emailBody;
				// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
				  $mail->AltBody    = $emailNoHtml; 									// optional, comment out and test

				  $mail->send();
			} catch (Exception $e) {
				var_dump($e);
				$errors[]= "Unable to send reset Email. Error. {$mail->ErrorInfo}";

			}
			return $errors ? $errors : true;
		}
	}?>
