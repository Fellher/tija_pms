<?php
/**
 * Employee Profile Extended Management Class
 *
 * This class contains extended methods for contact details, emergency contacts,
 * next of kin, dependants, reporting structure, qualifications, bank details,
 * and benefits management.
 *
 * @package    Tija CRM
 * @subpackage Employee Management
 * @version    1.0
 * @created    2025-10-15
 */

class EmployeeProfileExtended extends EmployeeProfile {

    // ===================================================================
    // 5. CONTACT DETAILS METHODS
    // ===================================================================

    /**
     * Get employee contact details
     */
    public static function get_contact_details($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_contact_details WHERE Suspended = 'N'";

        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause;

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);

        if (!$result) {
            return null;
        }

        if ($single) {
            return $result->fetch_object();
        }

        $contacts = [];
        while ($row = $result->fetch_object()) {
            $contacts[] = $row;
        }

        return empty($contacts) ? null : $contacts;
    }

    /**
     * Save contact details
     */
    public static function save_contact_details($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $employeeID = $data['employeeID'] ?? null;

        if (!$employeeID) {
            return ['success' => false, 'message' => 'Employee ID is required'];
        }

        $existing = self::get_contact_details(['employeeID' => $employeeID], true, $DBConn);

        $fields = [
            'primaryPhoneNumber', 'secondaryPhoneNumber', 'personalEmail', 'workEmail',
            'currentAddress', 'currentCity', 'currentState', 'currentPostalCode', 'currentCountry',
            'permanentAddress', 'permanentCity', 'permanentState', 'permanentPostalCode',
            'permanentCountry', 'sameAsCurrentAddress', 'linkedInProfile', 'facebookProfile',
            'twitterProfile', 'otherSocialMedia', 'updatedBy', 'Suspended'
        ];

        if ($existing) {
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            if (empty($updates)) {
                return ['success' => false, 'message' => 'No data to update'];
            }

            $sql = "UPDATE tija_employee_contact_details
                    SET " . implode(', ', $updates) . "
                    WHERE employeeID = " . (int)$employeeID;

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Contact details updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update contact details'];
            }
        } else {
            $data['employeeID'] = $employeeID;
            $insertFields = ['employeeID'];
            $insertValues = [(int)$employeeID];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            $sql = "INSERT INTO tija_employee_contact_details
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Contact details created successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to create contact details'];
            }
        }
    }

    // ===================================================================
    // 6. EMERGENCY CONTACTS METHODS
    // ===================================================================

    /**
     * Get emergency contacts
     */
    public static function get_emergency_contacts($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_emergency_contacts WHERE Suspended = 'N'";

        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause;
        $sql .= " ORDER BY isPrimary DESC, sortOrder ASC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);

        if (!$result) {
            return null;
        }

        if ($single) {
            return $result->fetch_object();
        }

        $contacts = [];
        while ($row = $result->fetch_object()) {
            $contacts[] = $row;
        }

        return empty($contacts) ? null : $contacts;
    }

    /**
     * Get emergency contacts full
     */
    public static function get_emergency_contacts_full($whereArr = [], $single = false, $DBConn = null) {
        $where = '';
        $params = [];
        $rows = array();
        $emergencyContacts = array("emergencyContactID", "DateAdded", "employeeID", "contactName", "relationship", "primaryPhoneNumber", "secondaryPhoneNumber", "workPhoneNumber", "emailAddress", "address", "city", "county", "postalCode", "country", "isPrimary", "sortOrder", "contactPriority", "occupation", "employer", "nationalID", "bloodType", "medicalConditions", "authorizedToCollectSalary", "authorizedForMedicalDecisions", "notes", "photoPath", "createdBy", "updatedBy", "updatedAt", "Lapsed", "Suspended");

        $employeeArr = array("ID", "DateAdded", "FirstName", "Surname", "OtherNames", "userInitials", "Email", "profile_image",);
        if (count($whereArr) > 0) {

            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                // Check if the column is in the emergency contacts array
                if (in_array($col, $emergencyContacts)) {
                    $where .= "ec.{$col} = ?";
                } elseif (in_array($col, $employeeArr)) {
                    $where .= "u.{$col} = ?";
                }
                $params[] = array($val, 's');
                $i++;
            }
        }


        $sql = "SELECT ec.*,
        u.ID, CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname) AS employeeName,
        CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname,  ' (', u.userInitials, ')') AS employeeNameWithInitials,
        CONCAT(SUBSTRING(u.FirstName, 1, 1), SUBSTRING(u.Surname, 1, 1)) AS employeeInitials
        FROM tija_employee_emergency_contacts ec
        LEFT JOIN people u ON ec.employeeID = u.ID
        {$where}
        ORDER BY ec.isPrimary DESC, ec.sortOrder ASC";
        $rows = $DBConn->fetch_all_rows($sql,$params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Save emergency contact
     */
    public static function save_emergency_contact($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $emergencyContactID = $data['emergencyContactID'] ?? null;

        $fields = [
            'employeeID', 'contactName', 'relationship', 'primaryPhoneNumber',
            'secondaryPhoneNumber', 'emailAddress', 'address', 'city', 'country',
            'isPrimary', 'sortOrder', 'notes', 'createdBy', 'updatedBy', 'Suspended'
        ];

        if ($emergencyContactID) {
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            $sql = "UPDATE tija_employee_emergency_contacts
                    SET " . implode(', ', $updates) . "
                    WHERE emergencyContactID = " . (int)$emergencyContactID;

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Emergency contact updated successfully'];
            }
        } else {
            $insertFields = [];
            $insertValues = [];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            $sql = "INSERT INTO tija_employee_emergency_contacts
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Emergency contact created successfully', 'id' => $DBConn->insert_id];
            }
        }

        return ['success' => false, 'message' => 'Failed to save emergency contact'];
    }

    /**
     * Delete emergency contact
     */
    public static function delete_emergency_contact($emergencyContactID, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $sql = "UPDATE tija_employee_emergency_contacts
                SET Suspended = 'Y'
                WHERE emergencyContactID = " . (int)$emergencyContactID;

        if ($DBConn->query($sql)) {
            return ['success' => true, 'message' => 'Emergency contact deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete emergency contact'];
    }

    // ===================================================================
    // 7. NEXT OF KIN METHODS
    // ===================================================================

    /**
     * Get next of kin records
     */
    public static function get_next_of_kin($whereArr, $single, $DBConn) {
        $params = array();
        $where = '';
        $rows = array();
        $nextOfKin = array('nextOfKinID', 'DateAdded', 'employeeID', 'fullName', 'relationship', 'dateOfBirth', 'gender', 'nationalID', 'phoneNumber', 'alternativePhone', 'emailAddress', 'address', 'city', 'county', 'country', 'allocationPercentage', 'isPrimary', 'sortOrder', 'occupation', 'employer', 'notes', 'createdBy', 'updatedBy', 'updatedAt', 'Lapsed', 'Suspended');

        // Always filter by Suspended = 'N'
        $where = "WHERE Suspended = 'N'";

        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                $where .= " AND ";

                if (in_array($col, $nextOfKin)) {
                    $where .= "`{$col}` = ?";
                } else {
                    // If the column is not found, skip it
                    continue;
                }
                $params[] = array($val, 's');
                $i++;
            }
        }

        $sql = "SELECT * FROM tija_employee_next_of_kin
                {$where}
                ORDER BY isPrimary DESC, sortOrder ASC";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        return($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Save next of kin
     */
    public static function save_next_of_kin($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $nextOfKinID = $data['nextOfKinID'] ?? null;

        $fields = [
            'employeeID', 'fullName', 'relationship', 'dateOfBirth', 'nationalID',
            'phoneNumber', 'emailAddress', 'address', 'city', 'country', 'isPrimary',
            'allocationPercentage', 'sortOrder', 'notes', 'createdBy', 'updatedBy', 'Suspended'
        ];

        if ($nextOfKinID) {
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            $sql = "UPDATE tija_employee_next_of_kin
                    SET " . implode(', ', $updates) . "
                    WHERE nextOfKinID = " . (int)$nextOfKinID;

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Next of kin updated successfully'];
            }
        } else {
            $insertFields = [];
            $insertValues = [];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            $sql = "INSERT INTO tija_employee_next_of_kin
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Next of kin created successfully', 'id' => $DBConn->insert_id];
            }
        }

        return ['success' => false, 'message' => 'Failed to save next of kin'];
    }

    /**
     * Delete next of kin
     */
    public static function delete_next_of_kin($nextOfKinID, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $sql = "UPDATE tija_employee_next_of_kin
                SET Suspended = 'Y'
                WHERE nextOfKinID = " . (int)$nextOfKinID;

        if ($DBConn->query($sql)) {
            return ['success' => true, 'message' => 'Next of kin deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete next of kin'];
    }

    // ===================================================================
    // 8. DEPENDANTS METHODS
    // ===================================================================

    /**
     * Get dependants
     */
    public static function get_dependants($whereArr, $single, $DBConn) {
        $params = array();
        $where = '';
        $rows = array();
        $dependants = array('dependantID', 'DateAdded', 'employeeID', 'fullName', 'relationship', 'dateOfBirth', 'gender', 'nationalID', 'isBeneficiary', 'isStudent', 'isDisabled', 'isDependentForTax', 'schoolName', 'grade', 'studentID', 'bloodType', 'medicalConditions', 'insuranceMemberNumber', 'phoneNumber', 'emailAddress', 'photoPath', 'notes', 'createdBy', 'updatedBy', 'updatedAt', 'Lapsed', 'Suspended');

        // Always filter by Suspended = 'N'
        $where = "WHERE Suspended = 'N'";

        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                $where .= " AND ";

                if (in_array($col, $dependants)) {
                    $where .= "`{$col}` = ?";
                } else {
                    // If the column is not found, skip it
                    continue;
                }
                $params[] = array($val, 's');
                $i++;
            }
        }

        $sql = "SELECT *,
                TIMESTAMPDIFF(YEAR, dateOfBirth, CURDATE()) as age
                FROM tija_employee_dependants
                {$where}
                ORDER BY dateOfBirth ASC";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        return($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Save dependant
     */
    public static function save_dependant($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $dependantID = $data['dependantID'] ?? null;

        $fields = [
            'employeeID', 'fullName', 'relationship', 'dateOfBirth', 'gender', 'nationalID',
            'birthCertificateNumber', 'isStudent', 'schoolName', 'hasDisability', 'disabilityDetails',
            'medicalConditions', 'isBeneficiary', 'benefitStartDate', 'benefitEndDate',
            'allocationPercentage', 'phoneNumber', 'emailAddress', 'address', 'notes',
            'createdBy', 'updatedBy', 'Suspended'
        ];

        if ($dependantID) {
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            $sql = "UPDATE tija_employee_dependants
                    SET " . implode(', ', $updates) . "
                    WHERE dependantID = " . (int)$dependantID;

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Dependant updated successfully'];
            }
        } else {
            $insertFields = [];
            $insertValues = [];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            $sql = "INSERT INTO tija_employee_dependants
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Dependant created successfully', 'id' => $DBConn->insert_id];
            }
        }

        return ['success' => false, 'message' => 'Failed to save dependant'];
    }

    /**
     * Delete dependant
     */
    public static function delete_dependant($dependantID, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $sql = "UPDATE tija_employee_dependants
                SET Suspended = 'Y'
                WHERE dependantID = " . (int)$dependantID;

        if ($DBConn->query($sql)) {
            return ['success' => true, 'message' => 'Dependant deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete dependant'];
    }

    // ===================================================================
    // 9. WORK EXPERIENCE METHODS
    // ===================================================================
    /**
     * Get work experience full
     */
    public static function get_work_experience_full($whereArr = [], $single = false, $DBConn = null) {
        $where = '';
        $params = [];
        $rows = array();
        $workExperience = array('workExperienceID', 'employeeID', 'companyName', 'jobTitle', 'industry', 'employmentType', 'startDate', 'endDate', 'isCurrentEmployer', 'responsibilities', 'achievements', 'reasonForLeaving', 'supervisorName', 'supervisorContact', 'canContact', 'location', 'country', 'monthlyGrossSalary', 'currency', 'sortOrder', 'notes', 'createdBy', 'updatedBy', 'updatedAt', 'Lapsed', 'Suspended');

        $employeeArr = array('ID', 'DateAdded', 'FirstName', 'Surname', 'OtherNames', 'userInitials', 'Email', 'profile_image');
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                if (in_array($col, $workExperience)) {
                    $where .= "e.{$col} = ?";
                } elseif (in_array($col, $employeeArr)) {
                    $where .= "u.{$col} = ?";
                }
                $params[] = array($val, 's');
                $i++;
            }
        }
        $sql = "SELECT e.*,
        u.ID, CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname) AS employeeName,
        CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname,  ' (', u.userInitials, ')') AS employeeNameWithInitials,
        CONCAT(SUBSTRING(u.FirstName, 1, 1), SUBSTRING(u.Surname, 1, 1)) AS employeeInitials
        FROM tija_employee_work_experience e
        LEFT JOIN people u ON e.employeeID = u.ID
        {$where}
        ORDER BY e.startDate DESC";
        $rows = $DBConn->fetch_all_rows($sql, $params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get work experience
     */
    public static function get_work_experience($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_work_experience WHERE Suspended = 'N'";

        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause;
        $sql .= " ORDER BY startDate DESC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);

        if (!$result) {
            return null;
        }

        if ($single) {
            return $result->fetch_object();
        }

        $experience = [];
        while ($row = $result->fetch_object()) {
            $experience[] = $row;
        }

        return empty($experience) ? null : $experience;
    }

    /**
     * Save work experience
     */
    public static function save_work_experience($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $workExperienceID = $data['workExperienceID'] ?? null;

        $fields = [
            'employeeID', 'companyName', 'jobTitle', 'industry', 'employmentType',
            'startDate', 'endDate', 'isCurrentEmployer', 'responsibilities', 'achievements',
            'reasonForLeaving', 'supervisorName', 'supervisorContact', 'canContact',
            'location', 'country', 'monthlyGrossSalary', 'currency', 'sortOrder',
            'createdBy', 'updatedBy', 'Suspended'
        ];

        if ($workExperienceID) {
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            $sql = "UPDATE tija_employee_work_experience
                    SET " . implode(', ', $updates) . "
                    WHERE workExperienceID = " . (int)$workExperienceID;

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Work experience updated successfully'];
            }
        } else {
            $insertFields = [];
            $insertValues = [];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            $sql = "INSERT INTO tija_employee_work_experience
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Work experience created successfully', 'id' => $DBConn->insert_id];
            }
        }

        return ['success' => false, 'message' => 'Failed to save work experience'];
    }

    /**
     * Delete work experience
     */
    public static function delete_work_experience($workExperienceID, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $sql = "UPDATE tija_employee_work_experience
                SET Suspended = 'Y'
                WHERE workExperienceID = " . (int)$workExperienceID;

        if ($DBConn->query($sql)) {
            return ['success' => true, 'message' => 'Work experience deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete work experience'];
    }

    // ===================================================================
    // 10. EDUCATION METHODS
    // ===================================================================

    /**
     * Get education records full
     */
    public static function get_education_full($whereArr = [], $single = false, $DBConn = null) {
        $where = '';
        $params = [];
        $rows = array();
        $education = array('educationID', 'employeeID', 'institutionName', 'educationLevel', 'fieldOfStudy', 'degreeTitle', 'grade', 'startDate', 'completionDate', 'isCompleted', 'certificateNumber', 'location', 'country', 'attachmentPath', 'verificationStatus', 'verifiedBy', 'verificationDate', 'sortOrder', 'notes', 'createdBy', 'updatedBy', 'updatedAt', 'Lapsed', 'Suspended');
        $employeeArr = array('ID', 'DateAdded', 'FirstName', 'Surname', 'OtherNames', 'userInitials', 'Email', 'profile_image');
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                if (in_array($col, $education)) {
                    $where .= "e.{$col} = ?";
                } elseif (in_array($col, $employeeArr)) {
                    $where .= "u.{$col} = ?";
                }
                $params[] = array($val, 's');
                $i++;
            }
        }
        $sql = "SELECT e.*,
        u.ID, CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname) AS employeeName,
        CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname,  ' (', u.userInitials, ')') AS employeeNameWithInitials,
        CONCAT(SUBSTRING(u.FirstName, 1, 1), SUBSTRING(u.Surname, 1, 1)) AS employeeInitials
        FROM tija_employee_education e
        LEFT JOIN people u ON e.employeeID = u.ID
        {$where}
        ORDER BY e.completionDate DESC";
        $rows = $DBConn->fetch_all_rows($sql, $params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get education records
     */
    public static function get_education($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_education WHERE Suspended = 'N'";

        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause;
        $sql .= " ORDER BY completionDate DESC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);

        if (!$result) {
            return null;
        }

        if ($single) {
            return $result->fetch_object();
        }

        $education = [];
        while ($row = $result->fetch_object()) {
            $education[] = $row;
        }

        return empty($education) ? null : $education;
    }

    /**
     * Save education record
     */
    public static function save_education($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $educationID = $data['educationID'] ?? null;

        $fields = [
            'employeeID', 'institutionName', 'educationLevel', 'fieldOfStudy', 'degreeTitle',
            'grade', 'startDate', 'completionDate', 'isCompleted', 'certificateNumber',
            'location', 'country', 'attachmentPath', 'verificationStatus', 'verifiedBy',
            'verificationDate', 'sortOrder', 'notes', 'createdBy', 'updatedBy', 'Suspended'
        ];

        if ($educationID) {
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            $sql = "UPDATE tija_employee_education
                    SET " . implode(', ', $updates) . "
                    WHERE educationID = " . (int)$educationID;

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Education record updated successfully'];
            }
        } else {
            $insertFields = [];
            $insertValues = [];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            $sql = "INSERT INTO tija_employee_education
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Education record created successfully', 'id' => $DBConn->insert_id];
            }
        }

        return ['success' => false, 'message' => 'Failed to save education record'];
    }

    /**
     * Delete education record
     */
    public static function delete_education($educationID, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $sql = "UPDATE tija_employee_education
                SET Suspended = 'Y'
                WHERE educationID = " . (int)$educationID;

        if ($DBConn->query($sql)) {
            return ['success' => true, 'message' => 'Education record deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete education record'];
    }

    // ===================================================================
    // SKILLS, LICENSES, CERTIFICATIONS, BANK DETAILS, BENEFITS
    // (Similar CRUD methods follow the same pattern)
    // ===================================================================

    /**
     * Get employee skills
     */
    public static function get_skills($whereArr = [], $single = false, $DBConn = null) {
        $where = '';
        $params = [];
        $rows = array();
        $skills = array('skillID', 'employeeID', 'skillName', 'proficiencyLevel', 'yearsOfExperience', 'sortOrder', 'notes', 'createdBy', 'updatedBy', 'updatedAt', 'Lapsed', 'Suspended');
        $employeeArr = array('ID', 'DateAdded', 'FirstName', 'Surname', 'OtherNames', 'userInitials', 'Email', 'profile_image');
        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                if (in_array($col, $skills)) {
                    $where .= "e.{$col} = ?";
                } elseif (in_array($col, $employeeArr)) {
                    $where .= "u.{$col} = ?";
                }
                $params[] = array($val, 's');
                $i++;
            }
        }
        $sql = "SELECT e.*,
        u.ID, CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname) AS employeeName,
        CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname,  ' (', u.userInitials, ')') AS employeeNameWithInitials,
        CONCAT(SUBSTRING(u.FirstName, 1, 1), SUBSTRING(u.Surname, 1, 1)) AS employeeInitials
        FROM tija_employee_skills e
        LEFT JOIN people u ON e.employeeID = u.ID
        {$where}
        ORDER BY e.proficiencyLevel DESC, e.skillName ASC";
        $rows = $DBConn->fetch_all_rows($sql, $params);
        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get bank details
     */
    public static function get_bank_details($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_bank_details WHERE Suspended = 'N'";
        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause . " ORDER BY isPrimary DESC, sortOrder ASC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);
        if (!$result) return null;

        if ($single) {
            return $result->fetch_object();
        }

        $banks = [];
        while ($row = $result->fetch_object()) {
            $banks[] = $row;
        }

        return empty($banks) ? null : $banks;
    }

    /**
     * Save bank details
     */
    public static function save_bank_details($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $bankDetailID = $data['bankDetailID'] ?? null;

        $fields = [
            'employeeID', 'bankName', 'bankCode', 'branchName', 'branchCode', 'accountNumber',
            'accountName', 'accountType', 'swiftCode', 'iban', 'currency', 'isPrimary',
            'isActiveForSalary', 'salaryAllocationPercentage', 'sortOrder', 'verificationStatus',
            'verifiedBy', 'verificationDate', 'notes', 'createdBy', 'updatedBy', 'Suspended'
        ];

        if ($bankDetailID) {
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            $sql = "UPDATE tija_employee_bank_details
                    SET " . implode(', ', $updates) . "
                    WHERE bankDetailID = " . (int)$bankDetailID;

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Bank details updated successfully'];
            }
        } else {
            $insertFields = [];
            $insertValues = [];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            $sql = "INSERT INTO tija_employee_bank_details
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Bank details created successfully', 'id' => $DBConn->insert_id];
            }
        }

        return ['success' => false, 'message' => 'Failed to save bank details'];
    }

    /**
     * Get employee benefits
     */
    public static function get_benefits($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_benefits WHERE Suspended = 'N'";
        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause . " ORDER BY isActive DESC, benefitType ASC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);
        if (!$result) return null;

        if ($single) {
            return $result->fetch_object();
        }

        $benefits = [];
        while ($row = $result->fetch_object()) {
            $benefits[] = $row;
        }

        return empty($benefits) ? null : $benefits;
    }

    /**
     * Get employee benefits full (with benefit type details)
     */
    public static function get_benefits_full($whereArr = [], $single = false, $DBConn = null) {
        $where = '';
        $params = [];
        $rows = array();
        $benefits = array('benefitID', 'DateAdded', 'employeeID', 'benefitTypeID', 'enrollmentDate', 'effectiveDate', 'endDate', 'isActive', 'coverageLevel', 'policyNumber', 'memberNumber', 'employerContribution', 'employeeContribution', 'totalPremium', 'contributionFrequency', 'dependentsCovered', 'dependentIDs', 'providerName', 'providerContact', 'providerPolicyNumber', 'notes', 'createdBy', 'updatedBy', 'updatedAt', 'Lapsed', 'Suspended');
        $benefitTypes = array('benefitTypeID', 'benefitName', 'benefitCode', 'benefitCategory', 'description', 'providerName', 'providerContact');
        $employeeArr = array('ID', 'DateAdded', 'FirstName', 'Surname', 'OtherNames', 'userInitials', 'Email', 'profile_image');

        if (count($whereArr) > 0) {
            $i = 0;
            foreach ($whereArr as $col => $val) {
                if ($where == '') {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }
                if (in_array($col, $benefits)) {
                    $where .= "eb.{$col} = ?";
                } elseif (in_array($col, $employeeArr)) {
                    $where .= "u.{$col} = ?";
                } else {
                    // If the column is not found, skip it
                    continue;
                }
                $params[] = array($val, 's');
                $i++;
            }
        }

        $sql = "SELECT eb.*, bt.benefitName, bt.benefitCode, bt.benefitCategory, bt.description,
        u.ID, CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname) AS employeeName,
        CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname,  ' (', u.userInitials, ')') AS employeeNameWithInitials,
        CONCAT(SUBSTRING(u.FirstName, 1, 1), SUBSTRING(u.Surname, 1, 1)) AS employeeInitials
        FROM tija_employee_benefits eb
        LEFT JOIN tija_benefit_types bt ON eb.benefitTypeID = bt.benefitTypeID
        LEFT JOIN people u ON eb.employeeID = u.ID
        {$where}
        ORDER BY bt.benefitCategory, bt.benefitName";

        $rows = $DBConn->fetch_all_rows($sql, $params);

        return($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Save benefit details
     */
    public static function save_benefit($data, $DBConn = null) {
        if (!$DBConn) {
            return ['success' => false, 'message' => 'Database connection required'];
        }

        $benefitID = $data['benefitID'] ?? null;

        $fields = [
            'employeeID', 'benefitType', 'benefitName', 'providerName', 'policyNumber',
            'membershipNumber', 'coverageAmount', 'employeeContribution', 'employerContribution',
            'contributionFrequency', 'coverageStartDate', 'coverageEndDate', 'isActive',
            'beneficiaries', 'attachmentPath', 'notes', 'createdBy', 'updatedBy', 'Suspended'
        ];

        if ($benefitID) {
            $updates = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $value = self::escape_value($data[$field], $DBConn);
                    $updates[] = "`$field` = $value";
                }
            }

            $sql = "UPDATE tija_employee_benefits
                    SET " . implode(', ', $updates) . "
                    WHERE benefitID = " . (int)$benefitID;

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Benefit updated successfully'];
            }
        } else {
            $insertFields = [];
            $insertValues = [];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertFields[] = "`$field`";
                    $insertValues[] = self::escape_value($data[$field], $DBConn);
                }
            }

            $sql = "INSERT INTO tija_employee_benefits
                    (" . implode(', ', $insertFields) . ")
                    VALUES (" . implode(', ', $insertValues) . ")";

            if ($DBConn->query($sql)) {
                return ['success' => true, 'message' => 'Benefit created successfully', 'id' => $DBConn->insert_id];
            }
        }

        return ['success' => false, 'message' => 'Failed to save benefit'];
    }

    /**
     * Get extended personal details
     */
    public static function get_extended_personal($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_extended_personal WHERE Suspended = 'N'";
        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause;

        if ($single) {
            $sql .= " LIMIT 1";
        }

        // Use mysqlConnect methods correctly
        $DBConn->query($sql);
        $DBConn->execute();

        if ($single) {
            return $DBConn->single();
        }

        return $DBConn->resultSet();
    }
    /**
     * Get employee addresses full
     */
    public static function get_addresses_full($whereArr = [], $single = false, $DBConn = null) {
        $where = '';
        $params = [];
        $addresses = array("addressID", "employeeID", "addressType", "addressLine1", "addressLine2", "city", "county", "postalCode", "country", "isPrimary", "Suspended", "Lapsed", "createdBy", "createdDate", "updatedBy", "updatedDate");
        $employeeArr = array("ID", "DateAdded", "FirstName", "Surname", "OtherNames", "userInitials", "Email", "profile_image", );
        $rows = array();
        $i = 0;
        foreach ($whereArr as $col => $val) {
            if ($where == '') {
                $where = "WHERE ";
            } else {
                $where .= " AND ";
            }
            if (in_array($col, $addresses)) {
                $where .= "`$col` = ?";

            } else {
                // If the column is not found in the addresses array, you can choose to skip it or handle it differently
                continue;
            }
            $params[] = array($val, 's');
            $i++;
        }
        $sql = "SELECT ad.*,
        u.ID, CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname) AS employeeName,
        CONCAT(u.FirstName, IF(u.OtherNames IS NOT NULL, CONCAT(' ', u.OtherNames), ''), ' ', u.Surname,  ' (', u.userInitials, ')') AS employeeNameWithInitials,
        CONCAT(SUBSTRING(u.FirstName, 1, 1), SUBSTRING(u.Surname, 1, 1)) AS employeeInitials
        FROM tija_employee_addresses ad
        LEFT JOIN people u ON ad.employeeID = u.ID
        $where ORDER BY ad.isPrimary DESC, ad.addressType ASC";

        $rows = $DBConn->fetch_all_rows($sql,$params);

        return($single === true) ? ((is_array($rows) && count($rows)=== 1) ? $rows[0] : false) : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }
    /**
     * Get employee addresses
     */
    public static function get_addresses($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_addresses WHERE Suspended = 'N'";
        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause . " ORDER BY isPrimary DESC, addressType ASC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);
        if (!$result) return null;

        if ($single) {
            return $result->fetch_object();
        }

        $addresses = [];
        while ($row = $result->fetch_object()) {
            $addresses[] = $row;
        }

        return empty($addresses) ? null : $addresses;
    }

    /**
     * Get employee allowances
     */
    public static function get_allowances($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_allowances WHERE Suspended = 'N'";
        $whereClause = self::build_where_clause($params);
        $sql .= $whereClause . " ORDER BY effectiveDate DESC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);
        if (!$result) return null;

        if ($single) {
            return $result->fetch_object();
        }

        $allowances = [];
        while ($row = $result->fetch_object()) {
            $allowances[] = $row;
        }

        return empty($allowances) ? null : $allowances;
    }

    /**
     * Get salary history
     */
    public static function get_salary_history($params = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $sql = "SELECT * FROM tija_employee_salary_history";
        $whereClause = self::build_where_clause($params);
        $sql .= " WHERE 1=1" . $whereClause . " ORDER BY effectiveDate DESC";

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $result = $DBConn->query($sql);
        if (!$result) return null;

        if ($single) {
            return $result->fetch_object();
        }

        $history = [];
        while ($row = $result->fetch_object()) {
            $history[] = $row;
        }

        return empty($history) ? null : $history;
    }

    /**
     * Get comprehensive employee profile
     * Returns all employee data in one call
     */
    public static function get_comprehensive_profile($employeeID, $DBConn = null) {
        if (!$DBConn) {
            return null;
        }

        $profile = [];

        $profile['personal'] = self::get_personal_details(['employeeID' => $employeeID], true, $DBConn);
        $profile['employment'] = self::get_employment_details(['employeeID' => $employeeID], true, $DBConn);
        $profile['jobHistory'] = self::get_job_history(['employeeID' => $employeeID], false, $DBConn);
        $profile['compensation'] = self::get_compensation(['employeeID' => $employeeID, 'isCurrent' => 'Y'], true, $DBConn);
        $profile['contacts'] = self::get_contact_details_full(['employeeID' => $employeeID], true, $DBConn);
        $profile['emergencyContacts'] = self::get_emergency_contacts_full(['employeeID' => $employeeID], false, $DBConn);
        $profile['nextOfKin'] = self::get_next_of_kin(['employeeID' => $employeeID], false, $DBConn);
        $profile['dependants'] = self::get_dependants(['employeeID' => $employeeID], false, $DBConn);
        $profile['workExperience'] = self::get_work_experience(['employeeID' => $employeeID], false, $DBConn);
        $profile['education'] = self::get_education(['employeeID' => $employeeID], false, $DBConn);
        $profile['skills'] = self::get_skills(['employeeID' => $employeeID], false, $DBConn);
        $profile['bankDetails'] = self::get_bank_details(['employeeID' => $employeeID], false, $DBConn);
        $profile['benefits'] = self::get_benefits_full(['employeeID' => $employeeID], false, $DBConn);

        return $profile;
    }
}

