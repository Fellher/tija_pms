<?php
/**
 * BAU Taxonomy Class
 *
 * Manages APQC Process Classification Framework taxonomy
 * Handles categories, process groups, processes, and activities
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 * @author     TIJA Development Team
 */

class BAUTaxonomy {

    /**
     * Get all categories
     *
     * @param array $whereArr Where conditions
     * @param bool $single Return single record
     * @param object $DBConn Database connection
     * @return array|false Categories or false
     */
    public static function getCategories($whereArr = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'categoryID', 'categoryCode', 'categoryName', 'categoryDescription',
            'displayOrder', 'isActive', 'DateAdded', 'LastUpdate',
            'LastUpdatedByID', 'Lapsed', 'Suspended'
        );

        $rows = $DBConn->retrieve_db_table_rows('tija_bau_categories', $cols, $whereArr);
        return ($single === true)
            ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false)
            : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get process groups by category
     *
     * @param int $categoryID Category ID
     * @param array $whereArr Additional where conditions
     * @param bool $single Return single record
     * @param object $DBConn Database connection
     * @return array|false Process groups or false
     */
    public static function getProcessGroups($categoryID, $whereArr = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $whereArr['categoryID'] = $categoryID;

        $cols = array(
            'processGroupID', 'categoryID', 'processGroupCode', 'processGroupName',
            'processGroupDescription', 'displayOrder', 'isActive', 'DateAdded',
            'LastUpdate', 'LastUpdatedByID', 'Lapsed', 'Suspended'
        );

        $rows = $DBConn->retrieve_db_table_rows('tija_bau_process_groups', $cols, $whereArr);
        return ($single === true)
            ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false)
            : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get processes by process group
     *
     * @param int $processGroupID Process group ID
     * @param array $whereArr Additional where conditions
     * @param bool $single Return single record
     * @param object $DBConn Database connection
     * @return array|false Processes or false
     */
    public static function getProcesses($processGroupID, $whereArr = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Only add processGroupID to whereArr if it's not null
        // This allows querying processes by other criteria without filtering by processGroupID
        if ($processGroupID !== null) {
            $whereArr['processGroupID'] = $processGroupID;
        }

        $cols = array(
            'processID', 'processGroupID', 'categoryID', 'processCode', 'processName',
            'processDescription', 'functionalArea', 'functionalAreaOwnerID',
            'isCustom', 'isActive', 'createdByID', 'DateAdded', 'LastUpdate',
            'LastUpdatedByID', 'Lapsed', 'Suspended'
        );

        $rows = $DBConn->retrieve_db_table_rows('tija_bau_processes', $cols, $whereArr);
        return ($single === true)
            ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false)
            : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get process by ID with full hierarchy
     *
     * @param int|string $processID Process ID or APQC code
     * @param object $DBConn Database connection
     * @return array|false Process with hierarchy or false
     */
    public static function getProcessByID($processID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Check if processID is numeric (ID) or string (APQC code)
        $whereArr = is_numeric($processID)
            ? ['processID' => $processID]
            : ['processCode' => $processID];

        // Handle null processGroupID by not adding it to whereArr
        // We need to query processes directly by processID or processCode
        $cols = array(
            'processID', 'processGroupID', 'categoryID', 'processCode', 'processName',
            'processDescription', 'functionalArea', 'functionalAreaID', 'functionalAreaOwnerID',
            'isCustom', 'isActive', 'createdByID', 'DateAdded', 'LastUpdate',
            'LastUpdatedByID', 'Lapsed', 'Suspended'
        );

        $rows = $DBConn->retrieve_db_table_rows('tija_bau_processes', $cols, $whereArr);

        // Handle different return types from retrieve_db_table_rows
        if (!$rows || $rows === false) {
            return false;
        }

        // If it's an array, get the first element
        if (is_array($rows)) {
            if (count($rows) === 0) {
                return false;
            }
            $process = $rows[0];
        } else {
            // Should be an object, but handle both
            $process = is_object($rows) ? $rows : false;
        }

        if (!$process) {
            return false;
        }

        // Handle both object and array access
        $processGroupID = is_object($process) ? ($process->processGroupID ?? null) : ($process['processGroupID'] ?? null);
        $procID = is_object($process) ? ($process->processID ?? null) : ($process['processID'] ?? null);

        if (!$processGroupID || !$procID) {
            return false;
        }

        // Get process group
        $processGroup = self::getProcessGroups($processGroupID, [], true, $DBConn);

        // Get category - handle case where processGroup might be false
        $category = false;
        if ($processGroup) {
            $categoryID = is_object($processGroup) ? ($processGroup->categoryID ?? null) : ($processGroup['categoryID'] ?? null);
            if ($categoryID) {
                $category = self::getCategories(['categoryID' => $categoryID], true, $DBConn);
            }
        }

        // Get activities
        $activities = self::getActivities($procID, [], false, $DBConn);

        return [
            'process' => $process,
            'processGroup' => $processGroup ?: null,
            'category' => $category ?: null,
            'activities' => $activities ?: []
        ];
    }

    /**
     * Get activities by process
     *
     * @param int $processID Process ID
     * @param array $whereArr Additional where conditions
     * @param bool $single Return single record
     * @param object $DBConn Database connection
     * @return array|false Activities or false
     */
    public static function getActivities($processID, $whereArr = [], $single = false, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $whereArr['processID'] = $processID;

        $cols = array(
            'activityID', 'processID', 'activityCode', 'activityName',
            'activityDescription', 'estimatedDuration', 'displayOrder',
            'isActive', 'DateAdded', 'LastUpdate', 'LastUpdatedByID',
            'Lapsed', 'Suspended'
        );

        $rows = $DBConn->retrieve_db_table_rows('tija_bau_activities', $cols, $whereArr);
        return ($single === true)
            ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false)
            : ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Search processes across taxonomy
     *
     * @param string $query Search query
     * @param array $filters Additional filters
     * @param object $DBConn Database connection
     * @return array|false Matching processes or false
     */
    public static function searchProcesses($query, $filters = [], $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $where = "WHERE (p.processName LIKE ? OR p.processCode LIKE ? OR p.processDescription LIKE ?)";
        $params = ["%{$query}%", "%{$query}%", "%{$query}%"];

        if (!empty($filters['functionalArea'])) {
            $where .= " AND p.functionalArea = ?";
            $params[] = $filters['functionalArea'];
        }

        if (!empty($filters['isActive'])) {
            $where .= " AND p.isActive = ?";
            $params[] = $filters['isActive'];
        }

        $sql = "SELECT p.*, pg.processGroupName, c.categoryName
                FROM tija_bau_processes p
                LEFT JOIN tija_bau_process_groups pg ON p.processGroupID = pg.processGroupID
                LEFT JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
                {$where}
                ORDER BY p.processCode";

        $stmt = $DBConn->prepare($sql);
        if ($stmt) {
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results ?: false;
        }

        return false;
    }

    /**
     * Create custom process
     *
     * @param array $data Process data
     * @param object $DBConn Database connection
     * @return int|false Process ID or false
     */
    public static function createCustomProcess($data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // If categoryID is not provided but processGroupID is, get categoryID from processGroup
        if (!isset($data['categoryID']) && isset($data['processGroupID'])) {
            // Get process group directly by processGroupID
            $cols = array('processGroupID', 'categoryID', 'processGroupCode', 'processGroupName');
            $processGroups = $DBConn->retrieve_db_table_rows('tija_bau_process_groups', $cols, ['processGroupID' => $data['processGroupID']]);
            if ($processGroups && count($processGroups) > 0) {
                $processGroup = $processGroups[0];
                $categoryID = is_object($processGroup) ? ($processGroup->categoryID ?? null) : ($processGroup['categoryID'] ?? null);
                if ($categoryID) {
                    $data['categoryID'] = $categoryID;
                }
            }
        }

        $cols = array(
            'processGroupID', 'categoryID', 'processCode', 'processName', 'processDescription',
            'functionalArea', 'functionalAreaOwnerID', 'isCustom', 'isActive',
            'createdByID'
        );

        $data['isCustom'] = 'Y';
        $data['isActive'] = $data['isActive'] ?? 'Y';

        return $DBConn->insert_db_table_row('tija_bau_processes', $cols, $data);
    }

    /**
     * Update process
     *
     * @param int $processID Process ID
     * @param array $data Update data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function updateProcess($processID, $data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // If processGroupID is being updated, sync categoryID from the process group
        if (isset($data['processGroupID']) && !isset($data['categoryID'])) {
            // Get process group directly by processGroupID
            $cols = array('processGroupID', 'categoryID', 'processGroupCode', 'processGroupName');
            $processGroups = $DBConn->retrieve_db_table_rows('tija_bau_process_groups', $cols, ['processGroupID' => $data['processGroupID']]);
            if ($processGroups && count($processGroups) > 0) {
                $processGroup = $processGroups[0];
                $categoryID = is_object($processGroup) ? ($processGroup->categoryID ?? null) : ($processGroup['categoryID'] ?? null);
                if ($categoryID) {
                    $data['categoryID'] = $categoryID;
                }
            }
        }

        // If only categoryID is being updated without processGroupID, validate it matches the processGroup's category
        if (isset($data['categoryID']) && !isset($data['processGroupID'])) {
            // Get current process to check processGroupID
            $process = self::getProcesses(null, ['processID' => $processID], true, $DBConn);
            if ($process) {
                $processGroupID = is_object($process) ? ($process->processGroupID ?? null) : ($process['processGroupID'] ?? null);
                if ($processGroupID) {
                    $cols = array('processGroupID', 'categoryID', 'processGroupCode', 'processGroupName');
                    $processGroups = $DBConn->retrieve_db_table_rows('tija_bau_process_groups', $cols, ['processGroupID' => $processGroupID]);
                    if ($processGroups && count($processGroups) > 0) {
                        $processGroup = $processGroups[0];
                        $processGroupCategoryID = is_object($processGroup) ? ($processGroup->categoryID ?? null) : ($processGroup['categoryID'] ?? null);
                        // Only update if the categoryID matches the processGroup's category
                        if ($processGroupCategoryID && $processGroupCategoryID != $data['categoryID']) {
                            // If categoryID doesn't match, we need to update processGroupID instead
                            // For now, we'll allow it but log a warning
                            error_log("Warning: Updating process categoryID without matching processGroupID for processID: {$processID}");
                        }
                    }
                }
            }
        }

        $whereArr = ['processID' => $processID];
        return $DBConn->update_table('tija_bau_processes', $data, $whereArr);
    }

    /**
     * Assign process owner (function head)
     *
     * @param int $processID Process ID
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function assignProcessOwner($processID, $employeeID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        return self::updateProcess($processID, ['functionalAreaOwnerID' => $employeeID], $DBConn);
    }

    /**
     * Create process (alias for createCustomProcess)
     *
     * @param array $data Process data
     * @param object $DBConn Database connection
     * @return int|false Process ID or false
     */
    public static function createProcess($data, $DBConn = null) {
        return self::createCustomProcess($data, $DBConn);
    }

    /**
     * Create activity
     *
     * @param array $data Activity data
     * @param object $DBConn Database connection
     * @return int|false Activity ID or false
     */
    public static function createActivity($data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'processID', 'activityCode', 'activityName', 'activityDescription',
            'functionalArea', 'isActive', 'createdByID'
        );

        $data['isActive'] = $data['isActive'] ?? 'Y';

        return $DBConn->insert_db_table_row('tija_bau_activities', $cols, $data);
    }

    /**
     * Update activity
     *
     * @param int $activityID Activity ID
     * @param array $data Update data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function updateActivity($activityID, $data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $whereArr = ['activityID' => $activityID];
        return $DBConn->update_table('tija_bau_activities', $data, $whereArr);
    }

    /**
     * Get activity by ID
     *
     * @param int $activityID Activity ID
     * @param object $DBConn Database connection
     * @return array|false Activity data or false
     */
    public static function getActivityByID($activityID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'activityID', 'processID', 'activityCode', 'activityName', 'activityDescription',
            'functionalArea', 'isActive', 'createdByID', 'DateAdded', 'LastUpdate'
        );

        return $DBConn->retrieve_db_table_rows('tija_bau_activities', $cols, ['activityID' => $activityID], true);
    }

    /**
     * Create category
     *
     * @param array $data Category data
     * @param object $DBConn Database connection
     * @return int|false Category ID or false
     */
    public static function createCategory($data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'categoryCode', 'categoryName', 'categoryDescription',
            'displayOrder', 'isActive'
        );

        $data['isActive'] = $data['isActive'] ?? 'Y';
        $data['displayOrder'] = $data['displayOrder'] ?? 0;

        return $DBConn->insert_db_table_row('tija_bau_categories', $cols, $data);
    }

    /**
     * Update category
     *
     * @param int $categoryID Category ID
     * @param array $data Update data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function updateCategory($categoryID, $data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $whereArr = ['categoryID' => $categoryID];
        return $DBConn->update_table('tija_bau_categories', $data, $whereArr);
    }

    /**
     * Create process group
     *
     * @param array $data Process group data
     * @param object $DBConn Database connection
     * @return int|false Process Group ID or false
     */
    public static function createProcessGroup($data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'categoryID', 'processGroupCode', 'processGroupName', 'processGroupDescription',
            'displayOrder', 'isActive'
        );

        $data['isActive'] = $data['isActive'] ?? 'Y';
        $data['displayOrder'] = $data['displayOrder'] ?? 0;

        return $DBConn->insert_db_table_row('tija_bau_process_groups', $cols, $data);
    }

    /**
     * Update process group
     *
     * @param int $processGroupID Process Group ID
     * @param array $data Update data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function updateProcessGroup($processGroupID, $data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $whereArr = ['processGroupID' => $processGroupID];
        return $DBConn->update_table('tija_bau_process_groups', $data, $whereArr);
    }

    /**
     * Get next available process group code for a category
     *
     * @param int $categoryID Category ID
     * @param object $DBConn Database connection
     * @return string Next available code
     */
    public static function getNextProcessGroupCode($categoryID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $category = self::getCategories(['categoryID' => $categoryID], true, $DBConn);
        if (!$category) {
            return '1.0';
        }

        $baseCode = $category['categoryCode'];
        $groups = self::getProcessGroups($categoryID, ['Suspended' => 'N'], false, $DBConn);

        if (!$groups || count($groups) === 0) {
            return $baseCode . '.1';
        }

        // Find highest number
        $maxNum = 0;
        foreach ($groups as $group) {
            $code = $group['processGroupCode'];
            if (preg_match('/^' . preg_quote($baseCode, '/') . '\.(\d+)$/', $code, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        return $baseCode . '.' . ($maxNum + 1);
    }

    /**
     * Get next available process code for a process group
     *
     * @param int $processGroupID Process Group ID
     * @param object $DBConn Database connection
     * @return string Next available code
     */
    public static function getNextProcessCode($processGroupID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Get process group directly
        $cols = array('processGroupID', 'processGroupCode', 'processGroupName');
        $processGroups = $DBConn->retrieve_db_table_rows('tija_bau_process_groups', $cols, ['processGroupID' => $processGroupID]);
        $processGroup = (is_array($processGroups) && count($processGroups) > 0) ? $processGroups[0] : false;

        if (!$processGroup) {
            return '1.0.1';
        }

        $baseCode = $processGroup['processGroupCode'];
        $processes = self::getProcesses($processGroupID, ['Suspended' => 'N'], false, $DBConn);

        if (!$processes || count($processes) === 0) {
            return $baseCode . '.1';
        }

        // Find highest number
        $maxNum = 0;
        foreach ($processes as $process) {
            $code = $process['processCode'];
            if (preg_match('/^' . preg_quote($baseCode, '/') . '\.(\d+)$/', $code, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        return $baseCode . '.' . ($maxNum + 1);
    }
}

