<?php
/**
 * Sales Document Management Script
 * Handles upload, update, delete, and retrieval of sales documents
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

// Initialize response
$response = array(
    'success' => false,
    'message' => '',
    'data' => null
);

try {
    // Check if user is logged in
    if (!$isValidUser) {
        throw new Exception('User not authenticated. Please log in to continue.');
    }

    $userID = $userDetails->ID;
    $action = $_POST['action'] ?? 'upload';

    switch ($action) {
        case 'upload':
            handleDocumentUpload($userID, $DBConn);
            break;
        case 'update':
            handleDocumentUpdate($userID, $DBConn);
            break;
        case 'delete':
            handleDocumentDelete($userID, $DBConn);
            break;
        case 'get':
            handleGetDocument($userID, $DBConn);
            break;
        case 'approve':
            handleDocumentApproval($userID, $DBConn);
            break;
        default:
            throw new Exception('Invalid action specified.');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Sales Document Error [User: {$userID}]: " . $e->getMessage());
    echo json_encode($response);
    exit;
}

/**
 * Handle document upload
 */
function handleDocumentUpload($userID, $DBConn) {
    global $response, $config;

    try {
        // Validate required fields
        if (!isset($_POST['salesCaseID']) || empty($_POST['salesCaseID'])) {
            throw new Exception('Unable to upload document: Sales case information is missing. Please refresh the page and try again.');
        }

        if (!isset($_FILES['documentFile']) || $_FILES['documentFile']['error'] !== UPLOAD_ERR_OK) {
            $uploadError = $_FILES['documentFile']['error'] ?? UPLOAD_ERR_NO_FILE;
            $errorMessages = array(
                UPLOAD_ERR_INI_SIZE => 'The file is too large. Maximum file size is 50MB.',
                UPLOAD_ERR_FORM_SIZE => 'The file exceeds the maximum allowed size of 50MB.',
                UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was selected. Please choose a file to upload.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server error: Temporary folder is missing. Please contact support.',
                UPLOAD_ERR_CANT_WRITE => 'Server error: Unable to save file. Please contact support.',
                UPLOAD_ERR_EXTENSION => 'File upload was blocked by server security settings. Please contact support.'
            );
            $errorMsg = $errorMessages[$uploadError] ?? 'File upload failed. Please select a valid file and try again.';
            throw new Exception($errorMsg);
        }

        $salesCaseID = intval($_POST['salesCaseID']);
        $file = $_FILES['documentFile'];
        $documentName = isset($_POST['documentName']) ? Utility::clean_string($_POST['documentName']) : '';
        $documentCategory = isset($_POST['documentCategory']) ? Utility::clean_string($_POST['documentCategory']) : 'other';
        $documentType = isset($_POST['documentType']) ? Utility::clean_string($_POST['documentType']) : null;
        $description = isset($_POST['description']) ? Utility::clean_string($_POST['description']) : null;
        $proposalID = isset($_POST['proposalID']) && !empty($_POST['proposalID']) ? intval($_POST['proposalID']) : null;
        $expenseID = isset($_POST['expenseID']) && !empty($_POST['expenseID']) ? intval($_POST['expenseID']) : null;
        $isConfidential = isset($_POST['isConfidential']) && $_POST['isConfidential'] === 'Y' ? 'Y' : 'N';
        $isPublic = isset($_POST['isPublic']) && $_POST['isPublic'] === 'Y' ? 'Y' : 'N';
        $requiresApproval = isset($_POST['requiresApproval']) && $_POST['requiresApproval'] === 'Y' ? 'Y' : 'N';

        // Validate file size (50MB max)
        $maxSize = 50 * 1024 * 1024; // 50MB in bytes
        if ($file['size'] > $maxSize) {
            $fileSizeMB = round($file['size'] / 1024 / 1024, 2);
            throw new Exception("File size ({$fileSizeMB}MB) exceeds the maximum allowed size of 50MB. Please compress the file or choose a smaller file.");
        }

        // Allowed file types
        $allowedTypes = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif');
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedTypes)) {
            $allowedList = implode(', ', $allowedTypes);
            throw new Exception("File type '{$fileExtension}' is not allowed. Allowed file types: {$allowedList}");
        }

        // Use File class to handle upload
        $uploadResult = File::upload_file($file, 'sales_documents', $allowedTypes, $maxSize, $config, $DBConn);

        if (!$uploadResult || !isset($uploadResult['uploadedFilePaths'])) {
            throw new Exception('File upload failed. Please try again or contact support if the problem persists.');
        }

        $fileURL = $uploadResult['uploadedFilePaths'];
        $fileName = basename($fileURL);
        $fileOriginalName = $file['name'];
        $fileSize = $file['size'];
        $fileMimeType = $file['type'];

        // If document name not provided, use original filename
        if (empty($documentName)) {
            $documentName = pathinfo($fileOriginalName, PATHINFO_FILENAME);
        }

        // Prepare data for insertion
        $documentData = array(
            'salesCaseID' => $salesCaseID,
            'proposalID' => $proposalID,
            'documentName' => $documentName,
            'fileName' => $fileName,
            'fileOriginalName' => $fileOriginalName,
            'fileURL' => $fileURL,
            'fileType' => $fileExtension,
            'fileSize' => $fileSize,
            'fileMimeType' => $fileMimeType,
            'documentCategory' => $documentCategory,
            'documentType' => $documentType,
            'version' => '1.0',
            'uploadedBy' => $userID,
            'description' => $description,
            'expenseID' => $expenseID,
            'isConfidential' => $isConfidential,
            'isPublic' => $isPublic,
            'requiresApproval' => $requiresApproval,
            'approvalStatus' => $requiresApproval === 'Y' ? 'pending' : null,
            'downloadCount' => 0,
            'DateAdded' => 'NOW()',
            'Suspended' => 'N'
        );

        // Insert into database
        $result = $DBConn->insert_data('tija_sales_documents', $documentData);

        if ($result) {
            $documentID = $DBConn->lastInsertId();
            $response['success'] = true;
            $response['message'] = 'Document uploaded successfully';
            $response['data'] = array(
                'documentID' => $documentID,
                'documentName' => $documentName,
                'fileName' => $fileName,
                'fileURL' => $fileURL
            );
        } else {
            // Delete uploaded file if database insert fails
            $filePath = $config['DataDir'] . $fileURL;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            throw new Exception('File was uploaded but could not be saved to the database. Please try again or contact support if the problem persists.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Sales Document Upload Error [User: {$userID}, SalesCase: " . ($_POST['salesCaseID'] ?? 'N/A') . "]: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}

/**
 * Handle document update
 */
function handleDocumentUpdate($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['documentID']) || empty($_POST['documentID'])) {
            throw new Exception('Document ID is required for update.');
        }

        $documentID = intval($_POST['documentID']);
        $document = Sales::sales_document_by_id($documentID, $DBConn);

        if (!$document) {
            throw new Exception('Document not found.');
        }

        // Check permissions (user must be uploader, management, or finance)
        $isManagement = false;
        $isFinance = false;
        $isOwner = ($document->uploadedBy == $userID);

        // Check user role (enhance with actual role checking)
        if (isset($userDetails->permissionProfileName)) {
            $userRole = strtolower($userDetails->permissionProfileName);
            $isManagement = (strpos($userRole, 'management') !== false || strpos($userRole, 'manager') !== false || strpos($userRole, 'admin') !== false);
            $isFinance = (strpos($userRole, 'finance') !== false || strpos($userRole, 'accountant') !== false);
        }

        if (!$isOwner && !$isManagement && !$isFinance) {
            throw new Exception('You do not have permission to update this document.');
        }

        $changes = array();

        if (isset($_POST['documentName'])) {
            $changes['documentName'] = Utility::clean_string($_POST['documentName']);
        }
        if (isset($_POST['description'])) {
            $changes['description'] = Utility::clean_string($_POST['description']);
        }
        if (isset($_POST['documentCategory'])) {
            $changes['documentCategory'] = Utility::clean_string($_POST['documentCategory']);
        }
        if (isset($_POST['isConfidential'])) {
            $changes['isConfidential'] = $_POST['isConfidential'] === 'Y' ? 'Y' : 'N';
        }
        if (isset($_POST['isPublic'])) {
            $changes['isPublic'] = $_POST['isPublic'] === 'Y' ? 'Y' : 'N';
        }

        $changes['LastUpdatedByID'] = $userID;

        if (empty($changes)) {
            throw new Exception('No changes to update.');
        }

        $result = $DBConn->update_table('tija_sales_documents', $changes, array('documentID' => $documentID));

        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Document updated successfully';
        } else {
            throw new Exception('Failed to update document. Please try again.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Sales Document Update Error [User: {$userID}]: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}

/**
 * Handle document delete
 */
function handleDocumentDelete($userID, $DBConn) {
    global $response, $config;

    try {
        if (!isset($_POST['documentID']) || empty($_POST['documentID'])) {
            throw new Exception('Document ID is required for deletion.');
        }

        $documentID = intval($_POST['documentID']);
        $document = Sales::sales_document_by_id($documentID, $DBConn);

        if (!$document) {
            throw new Exception('Document not found.');
        }

        // Soft delete
        $result = $DBConn->update_table('tija_sales_documents',
            array('Suspended' => 'Y', 'LastUpdatedByID' => $userID),
            array('documentID' => $documentID)
        );

        if ($result) {
            // Optionally delete physical file (uncomment if needed)
            // $filePath = $config['DataDir'] . $document->fileURL;
            // if (file_exists($filePath)) {
            //     unlink($filePath);
            // }

            $response['success'] = true;
            $response['message'] = 'Document deleted successfully';
        } else {
            throw new Exception('Failed to delete document. Please try again.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Sales Document Delete Error [User: {$userID}]: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}

/**
 * Handle get document
 */
function handleGetDocument($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['documentID']) || empty($_POST['documentID'])) {
            throw new Exception('Document ID is required.');
        }

        $documentID = intval($_POST['documentID']);
        $document = Sales::sales_document_by_id($documentID, $DBConn);

        if (!$document) {
            throw new Exception('Document not found.');
        }

        $response['success'] = true;
        $response['data'] = $document;

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

/**
 * Handle document approval
 */
function handleDocumentApproval($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['documentID']) || empty($_POST['documentID'])) {
            throw new Exception('Document ID is required.');
        }

        if (!isset($_POST['approvalStatus']) || !in_array($_POST['approvalStatus'], array('approved', 'rejected'))) {
            throw new Exception('Invalid approval status.');
        }

        $documentID = intval($_POST['documentID']);
        $document = Sales::sales_document_by_id($documentID, $DBConn);

        if (!$document) {
            throw new Exception('Document not found.');
        }

        if ($document->requiresApproval !== 'Y') {
            throw new Exception('This document does not require approval.');
        }

        // Check if user has approval permissions (management/finance)
        $isManagement = false;
        $isFinance = false;

        if (isset($userDetails->permissionProfileName)) {
            $userRole = strtolower($userDetails->permissionProfileName);
            $isManagement = (strpos($userRole, 'management') !== false || strpos($userRole, 'manager') !== false || strpos($userRole, 'admin') !== false);
            $isFinance = (strpos($userRole, 'finance') !== false || strpos($userRole, 'accountant') !== false);
        }

        if (!$isManagement && !$isFinance) {
            throw new Exception('Only management and finance personnel can approve documents.');
        }

        $approvalStatus = $_POST['approvalStatus'];
        $approvalNotes = isset($_POST['approvalNotes']) ? Utility::clean_string($_POST['approvalNotes']) : null;

        $changes = array(
            'approvalStatus' => $approvalStatus,
            'approvedBy' => $userID,
            'approvedDate' => 'NOW()',
            'LastUpdatedByID' => $userID
        );

        if ($approvalNotes) {
            $changes['description'] = ($document->description ? $document->description . "\n\n" : '') .
                                      "Approval Notes: " . $approvalNotes;
        }

        $result = $DBConn->update_table('tija_sales_documents', $changes, array('documentID' => $documentID));

        if ($result) {
            $response['success'] = true;
            $response['message'] = "Document {$approvalStatus} successfully";
        } else {
            throw new Exception('Failed to update approval status. Please try again.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Sales Document Approval Error [User: {$userID}]: " . $e->getMessage());
    }

    echo json_encode($response);
    exit;
}

