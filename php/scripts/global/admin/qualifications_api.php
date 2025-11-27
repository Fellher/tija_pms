<?php
/**
 * Qualifications API - Education, Experience, Skills, Certifications, Licenses
 * Unified CRUD operations for all qualification types
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
        // ========================================
        // EDUCATION OPERATIONS
        // ========================================

        case 'save_education':
            $educationID = isset($_POST['educationID']) && !empty($_POST['educationID']) ?
                Utility::clean_string($_POST['educationID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            // Process dates
            $startDate = null;
            if (isset($_POST['startDate']) && !empty($_POST['startDate'])) {
                $date = trim($_POST['startDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $startDate = $date;
            }

            $completionDate = null;
            if (isset($_POST['completionDate']) && !empty($_POST['completionDate'])) {
                $date = trim($_POST['completionDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $completionDate = $date;
            }

            $data = [
                'employeeID' => $employeeID,
                'institutionName' => Utility::clean_string($_POST['institutionName'] ?? ''),
                'institutionType' => Utility::clean_string($_POST['institutionType'] ?? 'university'),
                'institutionCountry' => Utility::clean_string($_POST['institutionCountry'] ?? 'Kenya'),
                'qualificationLevel' => Utility::clean_string($_POST['qualificationLevel'] ?? ''),
                'qualificationTitle' => Utility::clean_string($_POST['qualificationTitle'] ?? ''),
                'fieldOfStudy' => Utility::clean_string($_POST['fieldOfStudy'] ?? ''),
                'grade' => Utility::clean_string($_POST['grade'] ?? ''),
                'startDate' => $startDate,
                'completionDate' => $completionDate,
                'isCompleted' => isset($_POST['isCompleted']) && $_POST['isCompleted'] == 'Y' ? 'Y' : 'N',
                'certificateNumber' => Utility::clean_string($_POST['certificateNumber'] ?? ''),
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            if (empty($data['institutionName']) || empty($data['qualificationLevel']) || empty($data['qualificationTitle'])) {
                throw new Exception('Institution name, qualification level, and title are required');
            }

            if ($educationID) {
                if (!$DBConn->update_table('tija_employee_education', $data, ['educationID' => $educationID])) {
                    throw new Exception('Failed to update education record');
                }
                $response['message'] = 'Education record updated successfully';
            } else {
                $data['createdBy'] = $userDetails->ID;
                if (!$DBConn->insert_data('tija_employee_education', $data)) {
                    throw new Exception('Failed to create education record');
                }
                $response['message'] = 'Education record created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_education':
            $educationID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$educationID) throw new Exception('Education ID required');

            $DBConn->query("SELECT * FROM tija_employee_education WHERE educationID = ? AND Suspended = 'N'");
            $DBConn->bind(1, $educationID);
            $DBConn->execute();
            $education = $DBConn->single();

            if (!$education) throw new Exception('Education record not found');

            $response['success'] = true;
            $response['data'] = $education;
            break;

        case 'delete_education':
            $educationID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$educationID) throw new Exception('Education ID required');

            if (!$DBConn->update_table('tija_employee_education', ['Suspended' => 'Y'], ['educationID' => $educationID])) {
                throw new Exception('Failed to delete education record');
            }

            $response['success'] = true;
            $response['message'] = 'Education record deleted successfully';
            break;

        // ========================================
        // WORK EXPERIENCE OPERATIONS
        // ========================================

        case 'save_experience':
            $experienceID = isset($_POST['experienceID']) && !empty($_POST['experienceID']) ?
                Utility::clean_string($_POST['experienceID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            // Process dates
            $startDate = null;
            if (isset($_POST['startDate']) && !empty($_POST['startDate'])) {
                $date = trim($_POST['startDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $startDate = $date;
            }

            $endDate = null;
            if (isset($_POST['endDate']) && !empty($_POST['endDate'])) {
                $date = trim($_POST['endDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $endDate = $date;
            }

            $data = [
                'employeeID' => $employeeID,
                'companyName' => Utility::clean_string($_POST['companyName'] ?? ''),
                'companyIndustry' => Utility::clean_string($_POST['companyIndustry'] ?? ''),
                'companyLocation' => Utility::clean_string($_POST['companyLocation'] ?? ''),
                'jobTitle' => Utility::clean_string($_POST['jobTitle'] ?? ''),
                'department' => Utility::clean_string($_POST['department'] ?? ''),
                'employmentType' => Utility::clean_string($_POST['employmentType'] ?? 'full_time'),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'isCurrent' => isset($_POST['isCurrent']) && $_POST['isCurrent'] == 'Y' ? 'Y' : 'N',
                'responsibilities' => Utility::clean_string($_POST['responsibilities'] ?? ''),
                'achievements' => Utility::clean_string($_POST['achievements'] ?? ''),
                'reasonForLeaving' => Utility::clean_string($_POST['reasonForLeaving'] ?? ''),
                'supervisorName' => Utility::clean_string($_POST['supervisorName'] ?? ''),
                'supervisorContact' => Utility::clean_string($_POST['supervisorContact'] ?? ''),
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            if (empty($data['companyName']) || empty($data['jobTitle'])) {
                throw new Exception('Company name and job title are required');
            }

            if ($experienceID) {
                if (!$DBConn->update_table('tija_employee_work_experience', $data, ['experienceID' => $experienceID])) {
                    throw new Exception('Failed to update experience record');
                }
                $response['message'] = 'Experience record updated successfully';
            } else {
                $data['createdBy'] = $userDetails->ID;
                if (!$DBConn->insert_data('tija_employee_work_experience', $data)) {
                    throw new Exception('Failed to create experience record');
                }
                $response['message'] = 'Experience record created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_experience':
            $experienceID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$experienceID) throw new Exception('Experience ID required');

            $DBConn->query("SELECT * FROM tija_employee_work_experience WHERE experienceID = ? AND Suspended = 'N'");
            $DBConn->bind(1, $experienceID);
            $DBConn->execute();
            $experience = $DBConn->single();

            if (!$experience) throw new Exception('Experience record not found');

            $response['success'] = true;
            $response['data'] = $experience;
            break;

        case 'delete_experience':
            $experienceID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$experienceID) throw new Exception('Experience ID required');

            if (!$DBConn->update_table('tija_employee_work_experience', ['Suspended' => 'Y'], ['experienceID' => $experienceID])) {
                throw new Exception('Failed to delete experience record');
            }

            $response['success'] = true;
            $response['message'] = 'Experience record deleted successfully';
            break;

        // ========================================
        // SKILLS OPERATIONS
        // ========================================

        case 'save_skill':
            $skillID = isset($_POST['skillID']) && !empty($_POST['skillID']) ?
                Utility::clean_string($_POST['skillID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            $lastUsedDate = null;
            if (isset($_POST['lastUsedDate']) && !empty($_POST['lastUsedDate'])) {
                $date = trim($_POST['lastUsedDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $lastUsedDate = $date;
            }

            $data = [
                'employeeID' => $employeeID,
                'skillName' => Utility::clean_string($_POST['skillName'] ?? ''),
                'skillCategory' => Utility::clean_string($_POST['skillCategory'] ?? ''),
                'proficiencyLevel' => Utility::clean_string($_POST['proficiencyLevel'] ?? 'intermediate'),
                'yearsOfExperience' => intval($_POST['yearsOfExperience'] ?? 0),
                'isCertified' => isset($_POST['isCertified']) && $_POST['isCertified'] == 'Y' ? 'Y' : 'N',
                'certificationName' => Utility::clean_string($_POST['certificationName'] ?? ''),
                'lastUsedDate' => $lastUsedDate,
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            if (empty($data['skillName'])) {
                throw new Exception('Skill name is required');
            }

            if ($skillID) {
                if (!$DBConn->update_table('tija_employee_skills', $data, ['skillID' => $skillID])) {
                    throw new Exception('Failed to update skill');
                }
                $response['message'] = 'Skill updated successfully';
            } else {
                $data['createdBy'] = $userDetails->ID;
                if (!$DBConn->insert_data('tija_employee_skills', $data)) {
                    throw new Exception('Failed to create skill');
                }
                $response['message'] = 'Skill created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_skill':
            $skillID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$skillID) throw new Exception('Skill ID required');

            $DBConn->query("SELECT * FROM tija_employee_skills WHERE skillID = ? AND Suspended = 'N'");
            $DBConn->bind(1, $skillID);
            $DBConn->execute();
            $skill = $DBConn->single();

            if (!$skill) throw new Exception('Skill not found');

            $response['success'] = true;
            $response['data'] = $skill;
            break;

        case 'delete_skill':
            $skillID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$skillID) throw new Exception('Skill ID required');

            if (!$DBConn->update_table('tija_employee_skills', ['Suspended' => 'Y'], ['skillID' => $skillID])) {
                throw new Exception('Failed to delete skill');
            }

            $response['success'] = true;
            $response['message'] = 'Skill deleted successfully';
            break;

        // ========================================
        // CERTIFICATION OPERATIONS
        // ========================================

        case 'save_certification':
            $certificationID = isset($_POST['certificationID']) && !empty($_POST['certificationID']) ?
                Utility::clean_string($_POST['certificationID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            // Process dates
            $issueDate = null;
            if (isset($_POST['issueDate']) && !empty($_POST['issueDate'])) {
                $date = trim($_POST['issueDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $issueDate = $date;
            }

            $expiryDate = null;
            if (isset($_POST['expiryDate']) && !empty($_POST['expiryDate'])) {
                $date = trim($_POST['expiryDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $expiryDate = $date;
            }

            $data = [
                'employeeID' => $employeeID,
                'certificationName' => Utility::clean_string($_POST['certificationName'] ?? ''),
                'issuingOrganization' => Utility::clean_string($_POST['issuingOrganization'] ?? ''),
                'certificationNumber' => Utility::clean_string($_POST['certificationNumber'] ?? ''),
                'issueDate' => $issueDate,
                'expiryDate' => $expiryDate,
                'doesNotExpire' => isset($_POST['doesNotExpire']) && $_POST['doesNotExpire'] == 'Y' ? 'Y' : 'N',
                'verificationURL' => Utility::clean_string($_POST['verificationURL'] ?? ''),
                'credentialID' => Utility::clean_string($_POST['credentialID'] ?? ''),
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            if (empty($data['certificationName']) || empty($data['issuingOrganization'])) {
                throw new Exception('Certification name and issuing organization are required');
            }

            if ($certificationID) {
                if (!$DBConn->update_table('tija_employee_certifications', $data, ['certificationID' => $certificationID])) {
                    throw new Exception('Failed to update certification');
                }
                $response['message'] = 'Certification updated successfully';
            } else {
                $data['createdBy'] = $userDetails->ID;
                if (!$DBConn->insert_data('tija_employee_certifications', $data)) {
                    throw new Exception('Failed to create certification');
                }
                $response['message'] = 'Certification created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_certification':
            $certificationID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$certificationID) throw new Exception('Certification ID required');

            $DBConn->query("SELECT * FROM tija_employee_certifications WHERE certificationID = ? AND Suspended = 'N'");
            $DBConn->bind(1, $certificationID);
            $DBConn->execute();
            $cert = $DBConn->single();

            if (!$cert) throw new Exception('Certification not found');

            $response['success'] = true;
            $response['data'] = $cert;
            break;

        case 'delete_certification':
            $certificationID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$certificationID) throw new Exception('Certification ID required');

            if (!$DBConn->update_table('tija_employee_certifications', ['Suspended' => 'Y'], ['certificationID' => $certificationID])) {
                throw new Exception('Failed to delete certification');
            }

            $response['success'] = true;
            $response['message'] = 'Certification deleted successfully';
            break;

        // ========================================
        // LICENSE OPERATIONS
        // ========================================

        case 'save_license':
            $licenseID = isset($_POST['licenseID']) && !empty($_POST['licenseID']) ?
                Utility::clean_string($_POST['licenseID']) : null;
            $employeeID = Utility::clean_string($_POST['employeeID'] ?? '');

            if (empty($employeeID)) {
                throw new Exception('Employee ID is required');
            }

            // Process dates
            $issueDate = null;
            if (isset($_POST['issueDate']) && !empty($_POST['issueDate'])) {
                $date = trim($_POST['issueDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $issueDate = $date;
            }

            $expiryDate = null;
            if (isset($_POST['expiryDate']) && !empty($_POST['expiryDate'])) {
                $date = trim($_POST['expiryDate']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $expiryDate = $date;
            }

            $data = [
                'employeeID' => $employeeID,
                'licenseName' => Utility::clean_string($_POST['licenseName'] ?? ''),
                'licenseNumber' => Utility::clean_string($_POST['licenseNumber'] ?? ''),
                'licenseCategory' => Utility::clean_string($_POST['licenseCategory'] ?? ''),
                'issuingAuthority' => Utility::clean_string($_POST['issuingAuthority'] ?? ''),
                'issuingCountry' => Utility::clean_string($_POST['issuingCountry'] ?? 'Kenya'),
                'issueDate' => $issueDate,
                'expiryDate' => $expiryDate,
                'doesNotExpire' => isset($_POST['doesNotExpire']) && $_POST['doesNotExpire'] == 'Y' ? 'Y' : 'N',
                'isActive' => isset($_POST['isActive']) && $_POST['isActive'] == 'Y' ? 'Y' : 'N',
                'restrictions' => Utility::clean_string($_POST['restrictions'] ?? ''),
                'notes' => Utility::clean_string($_POST['notes'] ?? ''),
                'updatedBy' => $userDetails->ID,
                'Suspended' => 'N',
                'Lapsed' => 'N'
            ];

            if (empty($data['licenseName']) || empty($data['licenseNumber']) || empty($data['issuingAuthority'])) {
                throw new Exception('License name, number, and issuing authority are required');
            }

            if ($licenseID) {
                if (!$DBConn->update_table('tija_employee_licenses', $data, ['licenseID' => $licenseID])) {
                    throw new Exception('Failed to update license');
                }
                $response['message'] = 'License updated successfully';
            } else {
                $data['createdBy'] = $userDetails->ID;
                if (!$DBConn->insert_data('tija_employee_licenses', $data)) {
                    throw new Exception('Failed to create license');
                }
                $response['message'] = 'License created successfully';
            }

            $response['success'] = true;
            break;

        case 'get_license':
            $licenseID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$licenseID) throw new Exception('License ID required');

            $DBConn->query("SELECT * FROM tija_employee_licenses WHERE licenseID = ? AND Suspended = 'N'");
            $DBConn->bind(1, $licenseID);
            $DBConn->execute();
            $license = $DBConn->single();

            if (!$license) throw new Exception('License not found');

            $response['success'] = true;
            $response['data'] = $license;
            break;

        case 'delete_license':
            $licenseID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : null;
            if (!$licenseID) throw new Exception('License ID required');

            if (!$DBConn->update_table('tija_employee_licenses', ['Suspended' => 'Y'], ['licenseID' => $licenseID])) {
                throw new Exception('Failed to delete license');
            }

            $response['success'] = true;
            $response['message'] = 'License deleted successfully';
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

