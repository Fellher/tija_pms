<?php
/**
 *
 *
 *
 * FIXME:
 * For now we assume that one can only be one of administrator, scheme member,etc.
 * We should probably allow one to log in as more than one of these.
 */
$isValidUser = false;
$updateSession['PersonID'] = false;
$isValidAdmin = false;
$isSuperAdmin =false;
$isTenantAdmin=false;
$isUnitAdmin=false;
$isUserAdmin=false;
$isAdmin=false;
$isSalesAdmin=false;
$isManager=false;
$isHRManager=false;
$isEntityAdmin=false;
$loggedInUser= '';
$hrManagerScope = array(
	'isHRManager' => false,
	'hasGlobalScope' => false,
	'entityIDs' => array(),
	'orgDataIDs' => array(),
	'scopes' => array()
);
if (isset($_SESSION['SessionID'])) {
	$sessionID = $_SESSION['SessionID'];
	$sessionDetails = Core::session_details($sessionID, $DBConn);
	if (isset($_SESSION['logedinUser']) && !EMPTY($_SESSION['logedinUser'])) {
		$activeUser= Utility::clean_string($_SESSION['logedinUser']);
	}
	if ($sessionDetails) {
		$loginTime = strtotime($sessionDetails->LoginTime);
		$updateSession = Core::update_session($sessionID, $DBConn);
		$userIDAuth= $sessionDetails->PersonID;
		$userDetails = Core::user (array("ID" =>$sessionDetails->PersonID), true, $DBConn);

		// var_dump($userDetails);
		$n = array();
		if ($userDetails->FirstName) {
			$n[] = $userDetails->FirstName;
		}
		if ($userDetails->Surname) {
			$n[] = $userDetails->Surname;
		}
		if ($userDetails->OtherNames) {
			$n[] = $userDetails->OtherNames;
		}
		$userNames = implode(' ', $n);
		$isValidUser = true;

		// Check for pending operational tasks (manual processing mode/ not for admins)
		if ($isValidUser && !$isValidAdmin) {
			require_once __DIR__ . '/../../classes/operationaltaskscheduler.php';
			try {
				$pendingTasksResult = OperationalTaskScheduler::checkPendingTasksForUser($userDetails->ID, $DBConn);
				// Store in session for notification display
				if ($pendingTasksResult['tasksReady'] > 0) {
					$_SESSION['pendingOperationalTasks'] = $pendingTasksResult['tasksReady'];
				}
			} catch (Exception $e) {
				// Silently fail - don't break login if operational tasks check fails
				error_log("Operational tasks check failed on login: " . $e->getMessage());
			}
		}

		$hrManagerScope = Employee::get_hr_manager_scope($userDetails->ID, $DBConn);
		if ($hrManagerScope['isHRManager']) {
			$isHRManager = true;
		}

		$userRow=Core::is_valid_admin($userIDAuth, $DBConn);
		if (Core::is_valid_admin($userIDAuth, $DBConn)) {
			$isValidAdmin = true;
			$adminID = $sessionDetails->PersonID;
			$loggedInUser= 'Admin';

		} else {

			$validAdmin = Core::app_administrators(array('userID'=>$userDetails->ID), false, $DBConn);
			// var_dump($validAdmin);
			if ($validAdmin) {
				$isAdmin = true;
				foreach ($validAdmin as $key => $adminData) {
					switch ($adminData->adminTypeID) {
						case 1:
							$isSuperAdmin= true;
							$loggedInUser= 'Super Admin';
						break;

						case 2:
							$isTenantAdmin= true;
							$loggedInUser= 'Tenant Admin';
						break;

						case 3:
							$isEntityAdmin= true;
							$loggedInUser= 'Entity Admin';
						break;

						case 4:
							$isUnitAdmin= true;
							$loggedInUser= 'Unit Admin';
						break;
						case 6:
							$isHRManager = true;
							$loggedInUser= 'HR Manager';

							if (!$hrManagerScope['isHRManager']) {
								$hrManagerScope['isHRManager'] = true;
								$hrManagerScope['hasGlobalScope'] = true;
							}

							$orgScopeID = isset($adminData->orgDataID) ? (int)$adminData->orgDataID : null;
							if ($orgScopeID) {
								if (!in_array($orgScopeID, $hrManagerScope['orgDataIDs'], true)) {
									$hrManagerScope['orgDataIDs'][] = $orgScopeID;
								}
								$hrManagerScope['scopes'][] = array(
									'entityID' => null,
									'orgDataID' => $orgScopeID,
									'global' => true
								);
							}
						break;
					}
				}
			}
		}



	}
}?>