<?php
/**
 * Bulk Import Prospects API
 * Handles CSV/Excel file upload and prospect import
 */
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$response = array('success' => false, 'message' => '', 'data' => null);

// Check if user is logged in
if (!isset($userDetails->ID) || empty($userDetails->ID)) {
    $response['message'] = 'Unauthorized access. Please log in.';
    echo json_encode($response);
    exit;
}

$userID = $userDetails->ID;
$userDetails = Data::users(array('ID' => $userID), true, $DBConn);

if (!$userDetails) {
    $response['message'] = 'Invalid user session.';
    echo json_encode($response);
    exit;
}

// Get action
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';

try {
    switch ($action) {
        case 'upload':
            handleFileUpload($DBConn, $userDetails);
            break;

        case 'import':
            importProspects($DBConn, $userDetails);
            break;

        default:
            $response['message'] = 'Invalid action specified.';
            echo json_encode($response);
            exit;
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

/**
 * Handle file upload and parse CSV/Excel
 */
function handleFileUpload($DBConn, $userDetails) {
    global $response;

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'No file uploaded or upload error occurred.';
        echo json_encode($response);
        exit;
    }

    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validate file type
    $allowedExtensions = array('csv', 'xlsx', 'xls');
    if (!in_array($fileExt, $allowedExtensions)) {
        $response['message'] = 'Invalid file type. Only CSV and Excel files are allowed.';
        echo json_encode($response);
        exit;
    }

    // Validate file size (max 5MB)
    if ($fileSize > 5 * 1024 * 1024) {
        $response['message'] = 'File size exceeds 5MB limit.';
        echo json_encode($response);
        exit;
    }

    // Parse file
    $data = array();
    $headers = array();

    if ($fileExt === 'csv') {
        // Parse CSV
        if (($handle = fopen($fileTmpName, 'r')) !== FALSE) {
            $rowIndex = 0;
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if ($rowIndex === 0) {
                    // First row is headers
                    $headers = array_map('trim', $row);
                } else {
                    // Data rows
                    if (count($row) === count($headers)) {
                        $data[] = array_combine($headers, array_map('trim', $row));
                    }
                }
                $rowIndex++;
            }
            fclose($handle);
        }
    } else {
        // For Excel files, we would need a library like PHPExcel or PhpSpreadsheet
        // For now, we'll return an error message
        $response['message'] = 'Excel file support requires additional library. Please use CSV format.';
        echo json_encode($response);
        exit;
    }

    if (empty($data)) {
        $response['message'] = 'No data found in file.';
        echo json_encode($response);
        exit;
    }

    // Detect column mapping
    $columnMapping = detectColumnMapping($headers);

    // Store data in session for import step
    $_SESSION['import_data'] = $data;
    $_SESSION['import_headers'] = $headers;

    $response['success'] = true;
    $response['message'] = 'File uploaded and parsed successfully.';
    $response['data'] = array(
        'headers' => $headers,
        'rowCount' => count($data),
        'preview' => array_slice($data, 0, 5), // First 5 rows for preview
        'suggestedMapping' => $columnMapping
    );

    echo json_encode($response);
}

/**
 * Detect column mapping based on header names
 */
function detectColumnMapping($headers) {
    $mapping = array();

    $fieldPatterns = array(
        'salesProspectName' => array('name', 'prospect name', 'company name', 'client name'),
        'prospectEmail' => array('email', 'e-mail', 'email address'),
        'prospectPhone' => array('phone', 'telephone', 'mobile', 'contact number'),
        'prospectCaseName' => array('case', 'case name', 'opportunity', 'opportunity name'),
        'address' => array('address', 'location', 'street address'),
        'prospectWebsite' => array('website', 'url', 'web address'),
        'estimatedValue' => array('value', 'estimated value', 'deal value', 'amount'),
        'probability' => array('probability', 'win probability', 'chance'),
        'leadSourceID' => array('source', 'lead source', 'origin'),
        'industryID' => array('industry', 'sector'),
        'companySize' => array('size', 'company size', 'employees'),
        'expectedCloseDate' => array('close date', 'expected close', 'closing date'),
        'sourceDetails' => array('source details', 'notes', 'comments')
    );

    foreach ($headers as $header) {
        $headerLower = strtolower(trim($header));

        foreach ($fieldPatterns as $field => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($headerLower, $pattern) !== false) {
                    $mapping[$header] = $field;
                    break 2;
                }
            }
        }
    }

    return $mapping;
}

/**
 * Import prospects from uploaded data
 */
function importProspects($DBConn, $userDetails) {
    global $response;

    // Get data from session
    if (!isset($_SESSION['import_data']) || !isset($_SESSION['import_headers'])) {
        $response['message'] = 'No import data found. Please upload a file first.';
        echo json_encode($response);
        exit;
    }

    $data = $_SESSION['import_data'];
    $headers = $_SESSION['import_headers'];

    // Get column mapping from POST
    if (!isset($_POST['columnMapping']) || empty($_POST['columnMapping'])) {
        $response['message'] = 'Column mapping is required.';
        echo json_encode($response);
        exit;
    }

    $columnMapping = json_decode($_POST['columnMapping'], true);

    // Validate required fields are mapped
    $requiredFields = array('salesProspectName', 'prospectEmail');
    foreach ($requiredFields as $field) {
        if (!in_array($field, $columnMapping)) {
            $response['message'] = "Required field '{$field}' must be mapped.";
            echo json_encode($response);
            exit;
        }
    }

    // Get default values
    $defaultBusinessUnitID = isset($_POST['defaultBusinessUnitID']) ? (int)$_POST['defaultBusinessUnitID'] : null;
    $defaultLeadSourceID = isset($_POST['defaultLeadSourceID']) ? (int)$_POST['defaultLeadSourceID'] : null;

    if (!$defaultBusinessUnitID || !$defaultLeadSourceID) {
        $response['message'] = 'Default Business Unit and Lead Source are required.';
        echo json_encode($response);
        exit;
    }

    // Import data
    $successCount = 0;
    $errorCount = 0;
    $errors = array();
    $duplicates = array();

    foreach ($data as $rowIndex => $row) {
        try {
            // Map columns
            $prospectData = array(
                'orgDataID' => $userDetails->orgDataID,
                'entityID' => $userDetails->entityID,
                'businessUnitID' => $defaultBusinessUnitID,
                'leadSourceID' => $defaultLeadSourceID,
                'LastUpdateByID' => $userDetails->ID
            );

            foreach ($columnMapping as $csvColumn => $dbField) {
                if (isset($row[$csvColumn]) && !empty($row[$csvColumn])) {
                    $prospectData[$dbField] = Utility::clean_string($row[$csvColumn]);
                }
            }

            // Validate required fields
            if (empty($prospectData['salesProspectName']) || empty($prospectData['prospectEmail'])) {
                $errors[] = "Row " . ($rowIndex + 2) . ": Missing required fields";
                $errorCount++;
                continue;
            }

            // Check for duplicates
            $existingProspect = Sales::sales_prospects(array(
                'prospectEmail' => $prospectData['prospectEmail'],
                'orgDataID' => $userDetails->orgDataID
            ), true, $DBConn);

            if ($existingProspect) {
                $duplicates[] = "Row " . ($rowIndex + 2) . ": Email already exists - " . $prospectData['prospectEmail'];
                $errorCount++;
                continue;
            }

            // Insert prospect
            $sql = "INSERT INTO tija_sales_prospects (" . implode(', ', array_keys($prospectData)) . ")
                    VALUES (" . implode(', ', array_fill(0, count($prospectData), '?')) . ")";

            $params = array();
            foreach ($prospectData as $value) {
                $params[] = array($value, is_numeric($value) ? 'i' : 's');
            }

            $result = $DBConn->execute_query($sql, $params);

            if ($result) {
                $prospectID = $DBConn->get_last_insert_id();
                // Calculate lead score
                Sales::calculate_lead_score($prospectID, $DBConn);
                $successCount++;
            } else {
                $errors[] = "Row " . ($rowIndex + 2) . ": Failed to insert";
                $errorCount++;
            }

        } catch (Exception $e) {
            $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
            $errorCount++;
        }
    }

    // Clear session data
    unset($_SESSION['import_data']);
    unset($_SESSION['import_headers']);

    $response['success'] = true;
    $response['message'] = "Import completed. {$successCount} prospects imported successfully.";
    $response['data'] = array(
        'successCount' => $successCount,
        'errorCount' => $errorCount,
        'totalRows' => count($data),
        'errors' => $errors,
        'duplicates' => $duplicates
    );

    echo json_encode($response);
}
?>
