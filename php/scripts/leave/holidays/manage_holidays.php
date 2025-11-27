<?php
/**
 * Enhanced Holidays Management Handler
 * Handles CRUD operations with multi-jurisdiction support
 */

session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

// Get current user ID
$currentUserID = $userDetails->ID ?? null;

$DBConn->begin();
$errors = array();
$details = array();
$changes = array();
$success = "";

if ($isValidUser && $currentUserID || $isAdmin || $isValidAdmin || $isHRManager) {
   $holidayID = isset($_POST['holidayID']) && !empty($_POST['holidayID']) ? Utility::clean_string($_POST['holidayID']) : "";
   $holidayName = isset($_POST['holidayName']) && !empty($_POST['holidayName']) ? Utility::clean_string($_POST['holidayName']) : "";
   $holidayDate = isset($_POST['holidayDate']) && !empty($_POST['holidayDate']) ? Utility::clean_string($_POST['holidayDate']) : "";
   $holidayType = isset($_POST['holidayType']) && !empty($_POST['holidayType']) ? Utility::clean_string($_POST['holidayType']) : "";
   $countryID = isset($_POST['countryID']) && !empty($_POST['countryID']) ? Utility::clean_string($_POST['countryID']) : "";
   $repeatsAnnually = isset($_POST['repeatsAnnually']) && !empty($_POST['repeatsAnnually']) ? Utility::clean_string($_POST['repeatsAnnually']) : "N";

   // Enhanced jurisdiction fields
   $jurisdictionLevel = isset($_POST['jurisdictionLevel']) && !empty($_POST['jurisdictionLevel']) ? Utility::clean_string($_POST['jurisdictionLevel']) : "country";
   $regionID = isset($_POST['regionID']) && !empty($_POST['regionID']) ? Utility::clean_string($_POST['regionID']) : null;
   $cityID = isset($_POST['cityID']) && !empty($_POST['cityID']) ? Utility::clean_string($_POST['cityID']) : null;
   $entitySpecific = isset($_POST['entitySpecific']) && is_array($_POST['entitySpecific']) ? implode(',', $_POST['entitySpecific']) : null;

   // Applicability rules
   $applyToEmploymentTypes = isset($_POST['applyToEmploymentTypes']) && is_array($_POST['applyToEmploymentTypes']) ? implode(',', $_POST['applyToEmploymentTypes']) : 'all';
   $excludeBusinessUnits = isset($_POST['excludeBusinessUnits']) && is_array($_POST['excludeBusinessUnits']) ? implode(',', $_POST['excludeBusinessUnits']) : null;
   $affectsLeaveBalance = isset($_POST['affectsLeaveBalance']) && !empty($_POST['affectsLeaveBalance']) ? 'Y' : 'N';
   $holidayNotes = isset($_POST['holidayNotes']) && !empty($_POST['holidayNotes']) ? Utility::clean_string($_POST['holidayNotes']) : null;

   if (!$holidayID) {
      // CREATE NEW HOLIDAY
      $holidayDate ? $details['holidayDate'] = $holidayDate : $errors[] = "Please submit valid holiday date";
      $holidayName ? $details['holidayName'] = $holidayName : $errors[] = "Please submit valid holiday name";
      $holidayType ? $details['holidayType'] = $holidayType : $errors[] = "Please submit valid holiday type";
      $countryID ? $details['countryID'] = $countryID : $errors[] = "Please submit valid country";

      if (count($errors) === 0) {
         $details['repeatsAnnually'] = $repeatsAnnually;
         $details['jurisdictionLevel'] = $jurisdictionLevel;
         $details['regionID'] = $regionID;
         $details['cityID'] = $cityID;
         $details['entitySpecific'] = $entitySpecific;
         $details['applyToEmploymentTypes'] = $applyToEmploymentTypes;
         $details['excludeBusinessUnits'] = $excludeBusinessUnits;
         $details['affectsLeaveBalance'] = $affectsLeaveBalance;
         $details['holidayNotes'] = $holidayNotes;
         $details['CreateDate'] = $config['currentDateTimeFormated'];
         $details['CreatedByID'] = $currentUserID;
         $details['LastUpdate'] = $config['currentDateTimeFormated'];
         $details['LastUpdateByID'] = $currentUserID;

         // Validate jurisdiction rules
         $validationErrors = validateJurisdiction($details);
         if (!empty($validationErrors)) {
            $errors = array_merge($errors, $validationErrors);
         } else {
            if (!$DBConn->insert_data("tija_holidays", $details)) {
               $errors[] = "ERROR adding new holiday to the database";
            } else {
               $newHolidayID = $DBConn->lastInsertId();

               // Log the creation
               logHolidayAction($newHolidayID, 'created', $currentUserID, $DBConn);

               $success = "Holiday added successfully. Applies to " . getApplicableEmployeeCount($newHolidayID, $DBConn) . " employees.";
            }
         }
      }

   } else {
      // UPDATE EXISTING HOLIDAY
      $holidayDetails = Data::holidays(array("holidayID" => $holidayID), true, $DBConn);

      if ($holidayDetails) {
         $holidayName && ($holidayName !== $holidayDetails->holidayName) ? $changes['holidayName'] = $holidayName : "";
         $holidayDate && ($holidayDate !== $holidayDetails->holidayDate) ? $changes['holidayDate'] = $holidayDate : "";
         $holidayType && ($holidayType !== $holidayDetails->holidayType) ? $changes['holidayType'] = $holidayType : "";
         $countryID && ($countryID !== $holidayDetails->countryID) ? $changes['countryID'] = $countryID : "";
         $repeatsAnnually && ($repeatsAnnually !== $holidayDetails->repeatsAnnually) ? $changes['repeatsAnnually'] = $repeatsAnnually : "";

         // Enhanced fields
         $changes['jurisdictionLevel'] = $jurisdictionLevel;
         $changes['regionID'] = $regionID;
         $changes['cityID'] = $cityID;
         $changes['entitySpecific'] = $entitySpecific;
         $changes['applyToEmploymentTypes'] = $applyToEmploymentTypes;
         $changes['excludeBusinessUnits'] = $excludeBusinessUnits;
         $changes['affectsLeaveBalance'] = $affectsLeaveBalance;
         $changes['holidayNotes'] = $holidayNotes;

         if (count($errors) === 0) {
            if ($changes) {
               $changes['LastUpdate'] = $config['currentDateTimeFormated'];
               $changes['LastUpdateByID'] = $currentUserID;

               if (!$DBConn->update_table("tija_holidays", $changes, array("holidayID" => $holidayID))) {
                  $errors[] = "ERROR updating holiday details in the database";
               } else {
                  // Log the update
                  logHolidayAction($holidayID, 'updated', $currentUserID, $DBConn);

                  $success = "Holiday updated successfully";
               }
            } else {
               $success = "No changes made";
            }
         }
      } else {
         $errors[] = "Holiday not found";
      }
   }

} else {
   $errors[] = 'You need to log in as a valid user to manage holidays.';
}

/**
 * Validate jurisdiction configuration
 */
function validateJurisdiction($data) {
   $errors = array();

   $level = $data['jurisdictionLevel'] ?? '';

   switch ($level) {
      case 'country':
         if (empty($data['countryID']) || $data['countryID'] === 'all') {
            $errors[] = "Please select a specific country for country-level jurisdiction";
         }
         break;

      case 'region':
         if (empty($data['countryID'])) {
            $errors[] = "Country is required for region-level jurisdiction";
         }
         if (empty($data['regionID'])) {
            $errors[] = "Please specify the region/state";
         }
         break;

      case 'city':
         if (empty($data['countryID'])) {
            $errors[] = "Country is required for city-level jurisdiction";
         }
         if (empty($data['cityID'])) {
            $errors[] = "Please specify the city";
         }
         break;

      case 'entity':
         if (empty($data['entitySpecific'])) {
            $errors[] = "Please select at least one entity";
         }
         break;
   }

   return $errors;
}

/**
 * Get count of employees applicable for this holiday
 */
function getApplicableEmployeeCount($holidayID, $DBConn) {
   $holiday = Data::holidays(['holidayID' => $holidayID], true, $DBConn);
   if (!$holiday) return 0;

   $applicableEmployees = getApplicableEmployees($holiday, $DBConn);
   return count($applicableEmployees);
}

/**
 * Get list of employees who should observe this holiday
 */
function getApplicableEmployees($holiday, $DBConn) {
   $whereConditions = ["d.Suspended = 'N'"];
   $params = [];

   // Apply jurisdiction filters
   if ($holiday->jurisdictionLevel === 'global') {
      // All employees
   } elseif ($holiday->jurisdictionLevel === 'country') {
      $whereConditions[] = "e.entityCountry = ?";
      $params[] = [$holiday->countryID, 's'];
   } elseif ($holiday->jurisdictionLevel === 'region') {
      $whereConditions[] = "e.entityCountry = ?";
      $params[] = [$holiday->countryID, 's'];
      $whereConditions[] = "(e.entityCity = ? OR e.entityRegion = ?)";
      $params[] = [$holiday->regionID, 's'];
      $params[] = [$holiday->regionID, 's'];
   } elseif ($holiday->jurisdictionLevel === 'entity') {
      $entities = explode(',', $holiday->entitySpecific ?? '');
      if (!empty($entities)) {
         $placeholders = implode(',', array_fill(0, count($entities), '?'));
         $whereConditions[] = "d.entityID IN ($placeholders)";
         foreach ($entities as $eid) {
            $params[] = [$eid, 's'];
         }
      }
   }

   // Apply employment type filters
   if (!empty($holiday->applyToEmploymentTypes) && $holiday->applyToEmploymentTypes !== 'all') {
      $types = explode(',', $holiday->applyToEmploymentTypes);
      $placeholders = implode(',', array_fill(0, count($types), '?'));
      $whereConditions[] = "d.employmentType IN ($placeholders)";
      foreach ($types as $type) {
         $params[] = [$type, 's'];
      }
   }

   // Exclude business units
   if (!empty($holiday->excludeBusinessUnits)) {
      $units = explode(',', $holiday->excludeBusinessUnits);
      $placeholders = implode(',', array_fill(0, count($units), '?'));
      $whereConditions[] = "d.businessUnitID NOT IN ($placeholders)";
      foreach ($units as $unit) {
         $params[] = [$unit, 's'];
      }
   }

   $whereClause = count($whereConditions) > 0 ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

   $sql = "SELECT
      u.ID,
      CONCAT(u.FirstName, ' ', u.Surname) as employeeName,
      e.entityName,
      e.entityCity,
      e.entityCountry,
      d.businessUnitID
   FROM people u
   JOIN user_details d ON u.ID = d.ID
   LEFT JOIN tija_entities e ON d.entityID = e.entityID
   $whereClause
   ORDER BY employeeName ASC";

   $employees = $DBConn->fetch_all_rows($sql, $params);
   return $employees ?: [];
}

/**
 * Log holiday actions for audit trail
 */
function logHolidayAction($holidayID, $action, $userID, $DBConn) {
   $logData = [
      'holidayID' => $holidayID,
      'action' => $action,
      'performedByID' => $userID,
      'performedAt' => date('Y-m-d H:i:s')
   ];

   // You can log to a holiday_audit_log table if it exists
   // $DBConn->insert_data('tija_holiday_audit_log', $logData);
}

// Handle response
if (count($errors) == 0) {
   $DBConn->commit();
   $messages = array(array('Text' => $success, 'Type' => 'success'));
} else {
   $DBConn->rollback();
   $messages = array_map(function($error) {
      return array('Text' => $error, 'Type' => 'danger');
   }, $errors);
}

$_SESSION['FlashMessages'] = serialize($messages);
$returnURL = Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=leave&p=holidays');
header("location:{$base}html/{$returnURL}");
?>
