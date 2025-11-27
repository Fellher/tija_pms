<?php
/**
 * Employee Benefits API
 * Handles CRUD operations for employee benefit enrollments
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
        case 'save_benefit_enrollment':
            $benefitID = isset($_POST['benefitID']) && !empty($_POST['benefitID']) ?
                Utility::clean_string($_POST['benefitID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');
            $benefitTypeID = Utility::clean_string($_POST['benefitTypeID'] ?? '');

            if (empty($employeeID) || empty($benefitTypeID)) {
                throw new Exception('Employee ID and Benefit Type are required');
            }

            // Process dates
            $enrollmentDate = null;
            if (isset($_POST['enrollmentDate']) && !empty($_POST['enrollmentDate'])) {
                $date = trim($_POST['enrollmentDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $enrollmentDate = $date;
                }
            }

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

            if (!$enrollmentDate || !$effectiveDate) {
                throw new Exception('Enrollment date and effective date are required');
            }

            $data = [
                'employeeID' => $employeeID,
                'benefitTypeID' => $benefitTypeID,
                'enrollmentDate' => $enrollmentDate,
                'effectiveDate' => $effectiveDate,
                'endDate' => $endDate,
                'isActive' => isset($_POST['isActive']) && $_POST['isActive'] == 'Y' ? 'Y' : 'N',
                'coverageLevel' => Utility::clean_string($_POST['coverageLevel'] ?? 'individual'),
                'policyNumber' => Utility::clean_string($_POST['policyNumber'] ?? ''),
                'memberNumber' => Utility::clean_string($_POST['memberNumber'] ?? ''),
                'employerContribution' => floatval($_POST['employerContribution'] ?? 0),
                'employeeContribution' => floatval($_POST['employeeContribution'] ?? 0),
                'totalPremium' => floatval($_POST['totalPremium'] ?? 0),
                'contributionFrequency' => Utility::clean_string($_POST['contributionFrequency'] ?? 'monthly'),
                'dependentsCovered' => intval($_POST['dependentsCovered'] ?? 0),
                'dependentIDs' => Utility::clean_string($_POST['dependentIDs'] ?? ''),
                'providerName' => Utility::clean_string($_POST['providerName'] ?? ''),
                'providerContact' => Utility::clean_string($_POST['providerContact'] ?? ''),
                'providerPolicyNumber' => Utility::clean_string($_POST['providerPolicyNumber'] ?? ''),
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            if ($benefitID) {
                // Update existing
                $updateResult = $DBConn->update_table('tija_employee_benefits', $data, ['benefitID' => $benefitID]);
                if ($updateResult === false) {
                    $error = $DBConn->error ?? 'Unknown database error';
                    throw new Exception('Failed to update benefit enrollment: ' . $error);
                }
                $response['message'] = 'Benefit enrollment updated successfully';
            } else {
                // Create new
                $data['createdBy'] = $userDetails->ID;

                $insertResult = $DBConn->insert_data('tija_employee_benefits', $data);
                if ($insertResult === false) {
                    $error = $DBConn->error ?? 'Unknown database error';
                    throw new Exception('Failed to create benefit enrollment: ' . $error);
                }
                $response['message'] = 'Benefit enrollment created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_benefit_enrollment':
            $benefitID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$benefitID) {
                throw new Exception('Benefit ID is required');
            }

            $DBConn->query("SELECT eb.*, bt.benefitName, bt.benefitCode, bt.benefitCategory
                           FROM tija_employee_benefits eb
                           LEFT JOIN tija_benefit_types bt ON eb.benefitTypeID = bt.benefitTypeID
                           WHERE eb.benefitID = ? AND eb.Suspended = 'N'");
            $DBConn->bind(1, $benefitID);
            $DBConn->execute();
            $enrollment = $DBConn->single();

            if (!$enrollment) {
                throw new Exception('Benefit enrollment not found');
            }

            $response['success'] = true;
            $response['data'] = $enrollment;
            break;

        case 'delete_benefit_enrollment':
            $benefitID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$benefitID) {
                throw new Exception('Benefit ID is required');
            }

            // Soft delete
            if (!$DBConn->update_table('tija_employee_benefits',
                ['Suspended' => 'Y', 'isActive' => 'N'],
                ['benefitID' => $benefitID])) {
                throw new Exception('Failed to delete benefit enrollment');
            }

            $response['success'] = true;
            $response['message'] = 'Benefit enrollment deleted successfully';
            break;

        case 'toggle_benefit_status':
            $benefitID = isset($_POST['benefitID']) ? Utility::clean_string($_POST['benefitID']) : null;
            $newStatus = isset($_POST['status']) ? Utility::clean_string($_POST['status']) : null;

            if (!$benefitID || !$newStatus) {
                throw new Exception('Benefit ID and status are required');
            }

            $data = [
                'isActive' => $newStatus === 'Y' ? 'Y' : 'N',
                'updatedBy' => $userDetails->ID
            ];

            if (!$DBConn->update_table('tija_employee_benefits', $data, ['benefitID' => $benefitID])) {
                throw new Exception('Failed to update benefit status');
            }

            $response['success'] = true;
            $response['message'] = 'Benefit status updated successfully';
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
    $response['error_details'] = [
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'post_data' => $_POST
    ];
    error_log("Benefits API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
}

echo json_encode($response);
?>


