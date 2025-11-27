<?php
/**
 * Upload leave balances via CSV file
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

    if (!isset($_FILES['leaveBalanceFile']) || $_FILES['leaveBalanceFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Please provide a valid CSV file.');
    }

    $balanceDateInput = isset($_POST['balanceDate']) ? Utility::clean_string($_POST['balanceDate']) : date('Y-m-d');
    $balanceDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $balanceDateInput) ? $balanceDateInput : date('Y-m-d');

    $tmpFile = $_FILES['leaveBalanceFile']['tmp_name'];
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

    if ($payrollIndex === false) {
        fclose($handle);
        throw new Exception('The template must include a "Payroll Number" column.');
    }

    $leaveTypes = Leave::leave_types(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn);
    if (!$leaveTypes) {
        fclose($handle);
        throw new Exception('No leave types are configured for this entity.');
    }

    $leaveTypeColumnMap = array();
    foreach ($leaveTypes as $leaveType) {
        $label = trim(($leaveType->leaveTypeName ?? 'Leave Type')) . ' Balance';
        $normalizedLabel = normalize_header_key($label);

        foreach ($normalizedHeaders as $index => $headerKey) {
            if ($headerKey === $normalizedLabel) {
                $leaveTypeColumnMap[$leaveType->leaveTypeID] = $index;
                break;
            }
        }
    }

    if (empty($leaveTypeColumnMap)) {
        fclose($handle);
        throw new Exception('No leave type balance columns were found in the uploaded file.');
    }

    $uploadBatch = 'LB' . date('YmdHis');
    $uploadedBy = (int)($_SESSION['userID'] ?? 0);

    $rowNumber = 1;
    $matchedEmployees = 0;
    $balancesPersisted = 0;
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

        $matchedEmployees++;
        $rowHasUpdates = false;

        foreach ($leaveTypeColumnMap as $leaveTypeID => $columnIndex) {
            $valueRaw = $rowValues[$columnIndex] ?? '';
            if ($valueRaw === '') {
                continue;
            }

            $normalizedValue = str_replace(',', '', $valueRaw);
            if (!is_numeric($normalizedValue)) {
                $errors[] = "Row {$rowNumber}: Invalid numeric value '{$valueRaw}' for leave type ID {$leaveTypeID}.";
                continue;
            }

            $balanceDays = (float)$normalizedValue;

            try {
                Leave::save_manual_balance_entry(
                    (int)$employee->ID,
                    $entityID,
                    (int)$leaveTypeID,
                    $balanceDays,
                    $balanceDate,
                    $payrollNumber,
                    $uploadedBy,
                    $uploadBatch,
                    $DBConn
                );
                $balancesPersisted++;
                $rowHasUpdates = true;
            } catch (Exception $saveError) {
                $errors[] = "Row {$rowNumber}: {$saveError->getMessage()}";
            }
        }

        if (!$rowHasUpdates) {
            $skippedRows++;
        }
    }

    fclose($handle);

    if ($balancesPersisted === 0) {
        throw new Exception('No leave balances were updated. Please confirm the template contains numeric values.');
    }

    echo json_encode(array(
        'success' => true,
        'message' => 'Leave balances uploaded successfully.',
        'stats' => array(
            'rowsProcessed' => $rowNumber - 1,
            'employeesMatched' => $matchedEmployees,
            'balancesUpdated' => $balancesPersisted,
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

