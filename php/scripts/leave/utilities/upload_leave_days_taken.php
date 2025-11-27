<?php
/**
 * Upload leave days taken via CSV file
 * Synchronizes leave applications to tija_leave_applications table
 */

session_start();
$base = '../../../../';
require_once $base . 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser || (!$isAdmin && !$isValidAdmin && !$isHRManager)) {
    http_response_code(403);
    echo json_encode(array(
        'success' => false,
        'message' => 'Access denied'
    ));
    exit;
}

function normalize_header_key($value) {
    $value = strtolower(trim((string)$value));
    $value = preg_replace('/[^a-z0-9]+/', '_', $value);
    return trim($value, '_');
}

try {
    $defaultEntityID = (int)($userDetails->entityID ?? ($_SESSION['entityID'] ?? 1));
    $entityID = isset($_POST['entityID']) && $_POST['entityID'] !== ''
        ? (int)Utility::clean_string($_POST['entityID'])
        : $defaultEntityID;

    if ($isHRManager && !$isAdmin && !$isValidAdmin) {
        $entityID = $defaultEntityID;
    }

    if ($entityID <= 0) {
        throw new Exception('Invalid entity selected.');
    }

    if (!isset($_FILES['leaveDaysFile']) || $_FILES['leaveDaysFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Please provide a valid CSV file.');
    }

    $tmpFile = $_FILES['leaveDaysFile']['tmp_name'];
    $handle = fopen($tmpFile, 'r');
    if (!$handle) {
        throw new Exception('Unable to read uploaded file.');
    }

    $headerRow = fgetcsv($handle);
    if (!$headerRow) {
        fclose($handle);
        throw new Exception('Uploaded file is empty.');
    }

    $normalizedHeaders = array();
    foreach ($headerRow as $index => $headerLabel) {
        $normalizedHeaders[$index] = normalize_header_key($headerLabel);
    }

    $payrollIndex = array_search('payroll_number', $normalizedHeaders, true);
    if ($payrollIndex === false) {
        $payrollIndex = array_search('payroll_no', $normalizedHeaders, true);
    }
    if ($payrollIndex === false) {
        $payrollIndex = array_search('payrollnumber', $normalizedHeaders, true);
    }

    $leaveTypeIndex = array_search('leave_type', $normalizedHeaders, true);
    $startDateIndex = array_search('start_date', $normalizedHeaders, true);
    $endDateIndex = array_search('end_date', $normalizedHeaders, true);
    $daysTakenIndex = array_search('days_taken', $normalizedHeaders, true);
    $statusIndex = array_search('status', $normalizedHeaders, true);
    $commentsIndex = array_search('comments', $normalizedHeaders, true);

    if ($payrollIndex === false) {
        fclose($handle);
        throw new Exception('The template must include a "Payroll Number" column.');
    }

    if ($leaveTypeIndex === false) {
        fclose($handle);
        throw new Exception('The template must include a "Leave Type" column.');
    }

    if ($startDateIndex === false || $endDateIndex === false) {
        fclose($handle);
        throw new Exception('The template must include "Start Date" and "End Date" columns.');
    }

    $leaveTypes = Leave::leave_types(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn);
    if (!$leaveTypes) {
        fclose($handle);
        throw new Exception('No leave types are configured for this entity.');
    }

    $leaveTypeNameMap = array();
    foreach ($leaveTypes as $leaveType) {
        $typeName = strtolower(trim($leaveType->leaveTypeName ?? ''));
        $leaveTypeNameMap[$typeName] = $leaveType->leaveTypeID;
    }

    $currentPeriod = Leave::get_current_leave_period($entityID, $DBConn);
    $defaultPeriodID = $currentPeriod ? $currentPeriod->leavePeriodID : null;

    if (!$defaultPeriodID) {
        fclose($handle);
        throw new Exception('No active leave period found for this entity. Please configure leave periods first.');
    }

    $uploadedBy = (int)($_SESSION['userID'] ?? 0);
    $rowNumber = 1;
    $matchedEmployees = 0;
    $applicationsCreated = 0;
    $applicationsUpdated = 0;
    $skippedRows = 0;
    $errors = array();

    while (($row = fgetcsv($handle)) !== false) {
        $rowNumber++;
        $rowValues = array();
        foreach ($row as $idx => $value) {
            $rowValues[$idx] = trim((string)$value);
        }

        $payrollNumber = $rowValues[$payrollIndex] ?? '';
        $payrollNumber = trim($payrollNumber);

        if ($payrollNumber === '') {
            $skippedRows++;
            continue;
        }

        $employee = Employee::employees(
            array('payrollNo' => $payrollNumber, 'entityID' => $entityID),
            true,
            $DBConn
        );

        if (!$employee) {
            $errors[] = "Row {$rowNumber}: Payroll number {$payrollNumber} does not match any active employee.";
            $skippedRows++;
            continue;
        }

        $leaveTypeName = trim($rowValues[$leaveTypeIndex] ?? '');
        if ($leaveTypeName === '') {
            $errors[] = "Row {$rowNumber}: Leave type is required.";
            $skippedRows++;
            continue;
        }

        $leaveTypeNameLower = strtolower($leaveTypeName);
        $leaveTypeID = $leaveTypeNameMap[$leaveTypeNameLower] ?? null;

        if (!$leaveTypeID) {
            $errors[] = "Row {$rowNumber}: Leave type '{$leaveTypeName}' not found. Available types: " . implode(', ', array_keys($leaveTypeNameMap));
            $skippedRows++;
            continue;
        }

        $startDate = trim($rowValues[$startDateIndex] ?? '');
        $endDate = trim($rowValues[$endDateIndex] ?? '');

        if ($startDate === '' || $endDate === '') {
            $errors[] = "Row {$rowNumber}: Both start date and end date are required.";
            $skippedRows++;
            continue;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $errors[] = "Row {$rowNumber}: Invalid start date format. Use YYYY-MM-DD.";
            $skippedRows++;
            continue;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $errors[] = "Row {$rowNumber}: Invalid end date format. Use YYYY-MM-DD.";
            $skippedRows++;
            continue;
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            $errors[] = "Row {$rowNumber}: Start date cannot be after end date.";
            $skippedRows++;
            continue;
        }

        $daysTakenRaw = $rowValues[$daysTakenIndex] ?? '';
        $daysTaken = null;
        if ($daysTakenRaw !== '') {
            $normalizedDays = str_replace(',', '', $daysTakenRaw);
            if (!is_numeric($normalizedDays)) {
                $errors[] = "Row {$rowNumber}: Invalid numeric value '{$daysTakenRaw}' for days taken.";
                $skippedRows++;
                continue;
            }
            $daysTaken = (float)$normalizedDays;
        } else {
            $daysTaken = Leave::calculate_working_days($startDate, $endDate, $entityID, $DBConn);
        }

        $statusID = 4;
        $statusRaw = trim($rowValues[$statusIndex] ?? '');
        if ($statusRaw !== '') {
            $statusID = (int)$statusRaw;
            if (!in_array($statusID, array(1, 3, 4))) {
                $statusID = 4;
            }
        }

        $comments = trim($rowValues[$commentsIndex] ?? '');
        if ($comments === '') {
            $comments = 'Imported via CSV upload';
        }

        $entitlement = Leave::resolve_entitlement_for_employee(
            (int)$employee->ID,
            $leaveTypeID,
            $entityID,
            $DBConn
        );

        if (!$entitlement) {
            $errors[] = "Row {$rowNumber}: No leave entitlement found for employee and leave type.";
            $skippedRows++;
            continue;
        }

        $leavePeriodID = $defaultPeriodID;
        $periodSql = "
            SELECT leavePeriodID
            FROM tija_leave_periods
            WHERE entityID = ?
              AND leavePeriodStartDate <= ?
              AND leavePeriodEndDate >= ?
              AND Lapsed = 'N'
              AND Suspended = 'N'
            ORDER BY leavePeriodStartDate DESC
            LIMIT 1
        ";
        $periodRows = $DBConn->fetch_all_rows(
            $periodSql,
            array(
                array($entityID, 'i'),
                array($startDate, 's'),
                array($endDate, 's')
            )
        );
        if ($periodRows && count($periodRows) > 0) {
            $leavePeriodID = (int)($periodRows[0]->leavePeriodID ?? $defaultPeriodID);
        }

        $matchedEmployees++;

        $checkExistingSql = "
            SELECT leaveApplicationID
            FROM tija_leave_applications
            WHERE employeeID = ?
              AND leaveTypeID = ?
              AND startDate = ?
              AND endDate = ?
              AND Lapsed = 'N'
              AND Suspended = 'N'
            LIMIT 1
        ";
        $existingRows = $DBConn->fetch_all_rows(
            $checkExistingSql,
            array(
                array((int)$employee->ID, 'i'),
                array($leaveTypeID, 'i'),
                array($startDate, 's'),
                array($endDate, 's')
            )
        );

        if ($existingRows && count($existingRows) > 0) {
            $applicationID = (int)($existingRows[0]->leaveApplicationID ?? 0);
            if ($applicationID > 0) {
                $updateData = array(
                    'noOfDays' => $daysTaken,
                    'leaveStatusID' => $statusID,
                    'leaveComments' => $comments,
                    'LastUpdate' => date('Y-m-d H:i:s'),
                    'LastUpdateByID' => $uploadedBy,
                    'modifiedBy' => $uploadedBy,
                    'modifiedDate' => date('Y-m-d H:i:s')
                );
                $updateResult = $DBConn->update_table(
                    'tija_leave_applications',
                    $updateData,
                    array('leaveApplicationID' => $applicationID)
                );
                if ($updateResult) {
                    $applicationsUpdated++;
                } else {
                    $errors[] = "Row {$rowNumber}: Failed to update existing leave application.";
                }
            }
        } else {
            $leaveData = array(
                'leaveTypeID' => $leaveTypeID,
                'leaveEntitlementID' => (int)($entitlement->leaveEntitlementID ?? 0),
                'employeeID' => (int)$employee->ID,
                'orgDataID' => (int)($employee->orgDataID ?? 1),
                'entityID' => $entityID,
                'leavePeriodID' => $leavePeriodID,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'noOfDays' => $daysTaken,
                'leaveComments' => $comments,
                'leaveStatusID' => $statusID,
                'dateApplied' => date('Y-m-d H:i:s'),
                'appliedByID' => (int)$employee->ID,
                'createdBy' => $uploadedBy,
                'createdDate' => date('Y-m-d H:i:s'),
                'DateAdded' => date('Y-m-d H:i:s'),
                'Lapsed' => 'N',
                'Suspended' => 'N'
            );

            $insertResult = $DBConn->insert_data('tija_leave_applications', $leaveData);
            if ($insertResult) {
                $applicationsCreated++;
            } else {
                $errors[] = "Row {$rowNumber}: Failed to create leave application.";
            }
        }
    }

    fclose($handle);

    if ($applicationsCreated === 0 && $applicationsUpdated === 0) {
        throw new Exception('No leave applications were created or updated. Please check your data and try again.');
    }

    echo json_encode(array(
        'success' => true,
        'message' => 'Leave days taken uploaded successfully.',
        'stats' => array(
            'rowsProcessed' => $rowNumber - 1,
            'employeesMatched' => $matchedEmployees,
            'applicationsCreated' => $applicationsCreated,
            'applicationsUpdated' => $applicationsUpdated,
            'skippedRows' => $skippedRows,
        ),
        'errors' => $errors
    ));
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}

