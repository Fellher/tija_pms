<?php
/**
 * Expense Handler - AJAX Operations
 * Handles expense submission, updates, and retrieval
 * @package    Tija CRM
 * @subpackage Expense Management
 */

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Include necessary files
require_once '../../../php/includes.php';

// Get action
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';
$jsonInput = json_decode(file_get_contents('php://input'), true);

if ($jsonInput) {
    $action = isset($jsonInput['action']) ? Utility::clean_string($jsonInput['action']) : $action;
}

try {
    switch ($action) {
        case 'submit_expense':
            handleExpenseSubmission();
            break;
            
        case 'get_expense_details':
            handleGetExpenseDetails($jsonInput);
            break;
            
        case 'update_expense':
            handleExpenseUpdate($jsonInput);
            break;
            
        case 'approve_expense':
            handleExpenseApproval($jsonInput);
            break;
            
        case 'reject_expense':
            handleExpenseRejection($jsonInput);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function handleExpenseSubmission() {
    global $userDetails, $orgDataID, $entityID, $DBConn;
    
    // Validate required fields
    $requiredFields = ['expenseTypeID', 'expenseCategoryID', 'expenseDate', 'amount', 'description'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            return;
        }
    }
    
    // Generate expense number
    $expenseNumber = Expense::generate_expense_number($DBConn);
    
    // Prepare expense data
    $expenseData = array(
        'expenseNumber' => $expenseNumber,
        'employeeID' => $userDetails->ID,
        'expenseTypeID' => Utility::clean_string($_POST['expenseTypeID']),
        'expenseCategoryID' => Utility::clean_string($_POST['expenseCategoryID']),
        'expenseStatusID' => 1, // Draft
        'projectID' => !empty($_POST['projectID']) ? Utility::clean_string($_POST['projectID']) : null,
        'clientID' => !empty($_POST['clientID']) ? Utility::clean_string($_POST['clientID']) : null,
        'expenseDate' => Utility::clean_string($_POST['expenseDate']),
        'description' => Utility::clean_string($_POST['description']),
        'amount' => floatval($_POST['amount']),
        'currency' => 'KES',
        'receiptRequired' => 'Y',
        'receiptAttached' => isset($_FILES['receipt']) && $_FILES['receipt']['error'] === 0 ? 'Y' : 'N',
        'approvalRequired' => 'Y',
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'createdBy' => $userDetails->ID
    );
    
    // Insert expense
    $expenseID = $DBConn->insert_db_table_row('tija_expenses', $expenseData);
    
    if ($expenseID) {
        // Handle file upload
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === 0) {
            $uploadResult = handleFileUpload($expenseID, $_FILES['receipt']);
            if ($uploadResult['success']) {
                // Update expense with file path
                $DBConn->update_db_table_rows('tija_expenses', 
                    array('receiptPath' => $uploadResult['filePath']), 
                    array('expenseID' => $expenseID)
                );
            }
        }
        
        // Auto-submit if not urgent
        if (!isset($_POST['urgent'])) {
            $DBConn->update_db_table_rows('tija_expenses', 
                array('expenseStatusID' => 2), // Submitted
                array('expenseID' => $expenseID)
            );
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Expense submitted successfully',
            'expenseID' => $expenseID,
            'expenseNumber' => $expenseNumber
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create expense']);
    }
}

function handleGetExpenseDetails($input) {
    global $orgDataID, $entityID, $DBConn;
    
    if (!isset($input['expenseID'])) {
        echo json_encode(['success' => false, 'message' => 'Missing expense ID']);
        return;
    }
    
    $expenseID = Utility::clean_string($input['expenseID']);
    $expense = Expense::expenses_full(array('expenseID' => $expenseID, 'orgDataID' => $orgDataID, 'entityID' => $entityID), true, $DBConn);
    
    if ($expense) {
        $html = generateExpenseDetailsHTML($expense);
        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Expense not found']);
    }
}

function handleExpenseUpdate($input) {
    global $userDetails, $DBConn;
    
    if (!isset($input['expenseID'])) {
        echo json_encode(['success' => false, 'message' => 'Missing expense ID']);
        return;
    }
    
    $expenseID = Utility::clean_string($input['expenseID']);
    $updateData = array(
        'lastUpdatedBy' => $userDetails->ID,
        'lastUpdated' => date('Y-m-d H:i:s')
    );
    
    // Add fields to update
    if (isset($input['expenseTypeID'])) $updateData['expenseTypeID'] = Utility::clean_string($input['expenseTypeID']);
    if (isset($input['expenseCategoryID'])) $updateData['expenseCategoryID'] = Utility::clean_string($input['expenseCategoryID']);
    if (isset($input['expenseDate'])) $updateData['expenseDate'] = Utility::clean_string($input['expenseDate']);
    if (isset($input['amount'])) $updateData['amount'] = floatval($input['amount']);
    if (isset($input['description'])) $updateData['description'] = Utility::clean_string($input['description']);
    if (isset($input['projectID'])) $updateData['projectID'] = !empty($input['projectID']) ? Utility::clean_string($input['projectID']) : null;
    if (isset($input['clientID'])) $updateData['clientID'] = !empty($input['clientID']) ? Utility::clean_string($input['clientID']) : null;
    
    $result = $DBConn->update_db_table_rows('tija_expenses', $updateData, array('expenseID' => $expenseID));
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Expense updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update expense']);
    }
}

function handleExpenseApproval($input) {
    global $userDetails, $DBConn;
    
    if (!isset($input['expenseID'])) {
        echo json_encode(['success' => false, 'message' => 'Missing expense ID']);
        return;
    }
    
    $expenseID = Utility::clean_string($input['expenseID']);
    $approvalNotes = isset($input['approvalNotes']) ? Utility::clean_string($input['approvalNotes']) : '';
    
    $updateData = array(
        'expenseStatusID' => 4, // Approved
        'approvedBy' => $userDetails->ID,
        'approvalDate' => date('Y-m-d H:i:s'),
        'approvalNotes' => $approvalNotes,
        'lastUpdatedBy' => $userDetails->ID,
        'lastUpdated' => date('Y-m-d H:i:s')
    );
    
    $result = $DBConn->update_db_table_rows('tija_expenses', $updateData, array('expenseID' => $expenseID));
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Expense approved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to approve expense']);
    }
}

function handleExpenseRejection($input) {
    global $userDetails, $DBConn;
    
    if (!isset($input['expenseID'])) {
        echo json_encode(['success' => false, 'message' => 'Missing expense ID']);
        return;
    }
    
    $expenseID = Utility::clean_string($input['expenseID']);
    $rejectionReason = isset($input['rejectionReason']) ? Utility::clean_string($input['rejectionReason']) : '';
    
    $updateData = array(
        'expenseStatusID' => 5, // Rejected
        'rejectedBy' => $userDetails->ID,
        'rejectionDate' => date('Y-m-d H:i:s'),
        'rejectionReason' => $rejectionReason,
        'lastUpdatedBy' => $userDetails->ID,
        'lastUpdated' => date('Y-m-d H:i:s')
    );
    
    $result = $DBConn->update_db_table_rows('tija_expenses', $updateData, array('expenseID' => $expenseID));
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Expense rejected']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reject expense']);
    }
}

function handleFileUpload($expenseID, $file) {
    $uploadDir = '../../../data/uploaded_files/expenses/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $fileName = 'expense_' . $expenseID . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['success' => true, 'filePath' => $filePath];
    } else {
        return ['success' => false, 'message' => 'File upload failed'];
    }
}

function generateExpenseDetailsHTML($expense) {
    $html = '
    <div class="row">
        <div class="col-md-6">
            <h6>Expense Information</h6>
            <table class="table table-sm">
                <tr><td><strong>Expense Number:</strong></td><td>' . htmlspecialchars($expense->expenseNumber) . '</td></tr>
                <tr><td><strong>Type:</strong></td><td>' . htmlspecialchars($expense->expenseTypeName) . '</td></tr>
                <tr><td><strong>Category:</strong></td><td>' . htmlspecialchars($expense->expenseCategoryName) . '</td></tr>
                <tr><td><strong>Amount:</strong></td><td>KES ' . number_format($expense->amount, 2) . '</td></tr>
                <tr><td><strong>Date:</strong></td><td>' . date('M d, Y', strtotime($expense->expenseDate)) . '</td></tr>
                <tr><td><strong>Status:</strong></td><td>
                    <span class="badge" style="background-color: ' . $expense->expenseStatusColor . '">
                        ' . htmlspecialchars($expense->expenseStatusName) . '
                    </span>
                </td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6>Additional Information</h6>
            <table class="table table-sm">
                <tr><td><strong>Project:</strong></td><td>' . ($expense->projectName ? htmlspecialchars($expense->projectName) : 'N/A') . '</td></tr>
                <tr><td><strong>Client:</strong></td><td>' . ($expense->clientName ? htmlspecialchars($expense->clientName) : 'N/A') . '</td></tr>
                <tr><td><strong>Submitted:</strong></td><td>' . date('M d, Y H:i', strtotime($expense->submissionDate)) . '</td></tr>
                <tr><td><strong>Receipt:</strong></td><td>' . ($expense->receiptAttached === 'Y' ? 'Attached' : 'Not Attached') . '</td></tr>
            </table>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <h6>Description</h6>
            <p class="border p-3 rounded">' . htmlspecialchars($expense->description) . '</p>
        </div>
    </div>';
    
    if ($expense->approvalNotes) {
        $html .= '
        <div class="row mt-3">
            <div class="col-12">
                <h6>Approval Notes</h6>
                <p class="border p-3 rounded bg-light">' . htmlspecialchars($expense->approvalNotes) . '</p>
            </div>
        </div>';
    }
    
    if ($expense->rejectionReason) {
        $html .= '
        <div class="row mt-3">
            <div class="col-12">
                <h6>Rejection Reason</h6>
                <p class="border p-3 rounded bg-danger-subtle text-danger">' . htmlspecialchars($expense->rejectionReason) . '</p>
            </div>
        </div>';
    }
    
    return $html;
}
?>
