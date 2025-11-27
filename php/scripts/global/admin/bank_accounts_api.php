<?php
/**
 * Bank Accounts API
 * Handles CRUD operations for employee bank accounts
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    if (!$isValidUser) {
        throw new Exception('You must be logged in to perform this action');
    }

    $action = isset($_GET['action']) ? Utility::clean_string($_GET['action']) :
              (isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '');

    if (!$action) {
        throw new Exception('No action specified');
    }

    $DBConn->begin();

    switch ($action) {
        case 'save_bank_account':
            $bankAccountID = isset($_POST['bankAccountID']) && !empty($_POST['bankAccountID']) ?
                Utility::clean_string($_POST['bankAccountID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            // Process dates
            $effectiveDate = null;
            if (isset($_POST['effectiveDate']) && !empty($_POST['effectiveDate'])) {
                $date = trim($_POST['effectiveDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $effectiveDate = $date;
                }
            }

            $endDate = null;
            if (isset($_POST['endDate']) && !empty($_POST['endDate'])) {
                $date = trim($_POST['endDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $endDate = $date;
                }
            }

            $data = [
                'employeeID' => $employeeID,
                'bankName' => Utility::clean_string($_POST['bankName'] ?? ''),
                'bankCode' => Utility::clean_string($_POST['bankCode'] ?? ''),
                'branchName' => Utility::clean_string($_POST['branchName'] ?? ''),
                'branchCode' => Utility::clean_string($_POST['branchCode'] ?? ''),
                'accountNumber' => Utility::clean_string($_POST['accountNumber'] ?? ''),
                'accountName' => Utility::clean_string($_POST['accountName'] ?? ''),
                'accountType' => Utility::clean_string($_POST['accountType'] ?? 'salary'),
                'currency' => Utility::clean_string($_POST['currency'] ?? 'KES'),
                'isPrimary' => isset($_POST['isPrimary']) && $_POST['isPrimary'] == 'Y' ? 'Y' : 'N',
                'allocationPercentage' => floatval($_POST['allocationPercentage'] ?? 100),
                'swiftCode' => Utility::clean_string($_POST['swiftCode'] ?? ''),
                'iban' => Utility::clean_string($_POST['iban'] ?? ''),
                'sortCode' => Utility::clean_string($_POST['sortCode'] ?? ''),
                'isActive' => isset($_POST['isActive']) && $_POST['isActive'] == 'Y' ? 'Y' : 'N',
                'effectiveDate' => $effectiveDate,
                'endDate' => $endDate,
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            // Validate required fields
            if (empty($data['bankName']) || empty($data['accountNumber']) || empty($data['accountName'])) {
                throw new Exception('Bank name, account number, and account name are required');
            }

            // If setting as primary, unset other primary accounts
            if ($data['isPrimary'] == 'Y') {
                $DBConn->update_table('tija_employee_bank_accounts',
                    ['isPrimary' => 'N'],
                    ['employeeID' => $employeeID]);
            }

            if ($bankAccountID) {
                // Update existing
                $updateResult = $DBConn->update_table('tija_employee_bank_accounts', $data, ['bankAccountID' => $bankAccountID]);
                if ($updateResult === false) {
                    $error = $DBConn->error ?? 'Unknown database error';
                    throw new Exception('Failed to update bank account: ' . $error);
                }
                $response['message'] = 'Bank account updated successfully';
            } else {
                // Create new
                $data['createdBy'] = $userDetails->ID;

                $insertResult = $DBConn->insert_data('tija_employee_bank_accounts', $data);
                if ($insertResult === false) {
                    $error = $DBConn->error ?? 'Unknown database error';
                    throw new Exception('Failed to create bank account: ' . $error);
                }
                $response['message'] = 'Bank account created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_bank_account':
            $bankAccountID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$bankAccountID) {
                throw new Exception('Bank account ID is required');
            }

            $DBConn->query("SELECT * FROM tija_employee_bank_accounts WHERE bankAccountID = ? AND Suspended = 'N'");
            $DBConn->bind(1, $bankAccountID);
            $DBConn->execute();
            $account = $DBConn->single();

            if (!$account) {
                throw new Exception('Bank account not found');
            }

            $response['success'] = true;
            $response['data'] = $account;
            break;

        case 'delete_bank_account':
            $bankAccountID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$bankAccountID) {
                throw new Exception('Bank account ID is required');
            }

            // Soft delete
            if (!$DBConn->update_table('tija_employee_bank_accounts',
                ['Suspended' => 'Y', 'isActive' => 'N'],
                ['bankAccountID' => $bankAccountID])) {
                throw new Exception('Failed to delete bank account');
            }

            $response['success'] = true;
            $response['message'] = 'Bank account deleted successfully';
            break;

        case 'verify_bank_account':
            $bankAccountID = isset($_POST['bankAccountID']) ? Utility::clean_string($_POST['bankAccountID']) : null;
            if (!$bankAccountID) {
                throw new Exception('Bank account ID is required');
            }

            $data = [
                'isVerified' => 'Y',
                'verifiedDate' => date('Y-m-d'),
                'verifiedBy' => $userDetails->ID,
                'updatedBy' => $userDetails->ID
            ];

            if (!$DBConn->update_table('tija_employee_bank_accounts', $data, ['bankAccountID' => $bankAccountID])) {
                throw new Exception('Failed to verify bank account');
            }

            $response['success'] = true;
            $response['message'] = 'Bank account verified successfully';
            break;

        default:
            throw new Exception('Invalid action specified');
    }

    if ($response['success']) {
        $DBConn->commit();
    } else {
        $DBConn->rollback();
    }

} catch (Exception $e) {
    $DBConn->rollback();
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>

