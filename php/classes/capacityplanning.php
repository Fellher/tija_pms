<?php
/**
 * Capacity Planning Class
 *
 * Manages capacity planning and FTE calculations
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

class CapacityPlanning {

    const STANDARD_ANNUAL_HOURS = 2080; // 40 hours * 52 weeks

    /**
     * Calculate FTE from annual hours
     *
     * @param float $annualHours Annual hours
     * @return float FTE
     */
    public static function calculateFTE($annualHours) {
        return $annualHours / self::STANDARD_ANNUAL_HOURS;
    }

    /**
     * Calculate operational tax (total BAU hours)
     *
     * @param int $employeeID Employee ID
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param object $DBConn Database connection
     * @return float Total BAU hours
     */
    public static function calculateOperationalTax($employeeID, $startDate, $endDate, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $whereArr = [
            'employeeID' => $employeeID,
            'taskType' => 'operational',
            'taskDate' => ['BETWEEN', $startDate, $endDate],
            'Suspended' => 'N'
        ];

        $timeLogs = TimeAttendance::project_tasks_time_logs($whereArr, false, $DBConn);

        $totalHours = 0;
        if ($timeLogs) {
            foreach ($timeLogs as $log) {
                $hours = self::parseHours($log['taskDuration'] ?? $log['workHours'] ?? '0');
                $totalHours += $hours;
            }
        }

        return $totalHours;
    }

    /**
     * Parse hours from duration string
     *
     * @param string $duration Duration string
     * @return float Hours
     */
    private static function parseHours($duration) {
        // Parse "HH:MM:SS" or decimal hours
        if (strpos($duration, ':') !== false) {
            $parts = explode(':', $duration);
            $hours = (float)$parts[0];
            $minutes = (float)$parts[1] / 60;
            return $hours + $minutes;
        }
        return (float)$duration;
    }

    /**
     * Get available capacity
     *
     * @param int $employeeID Employee ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param object $DBConn Database connection
     * @return array Capacity breakdown
     */
    public static function getAvailableCapacity($employeeID, $startDate, $endDate, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $days = (strtotime($endDate) - strtotime($startDate)) / 86400;
        $totalHours = $days * 8; // Assuming 8 hours per day

        // Get PTO hours (would need to query leave system)
        $ptoHours = 0; // Placeholder

        // Get BAU hours
        $bauHours = self::calculateOperationalTax($employeeID, $startDate, $endDate, $DBConn);

        // Get project hours
        $projectHours = 0; // Placeholder - would query project time logs

        $available = $totalHours - $ptoHours - $bauHours - $projectHours;

        return [
            'totalHours' => $totalHours,
            'ptoHours' => $ptoHours,
            'bauHours' => $bauHours,
            'projectHours' => $projectHours,
            'availableHours' => max(0, $available),
            'utilization' => $totalHours > 0 ? (($totalHours - $available) / $totalHours) * 100 : 0
        ];
    }

    /**
     * Get capacity waterline
     *
     * @param int $employeeID Employee ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param object $DBConn Database connection
     * @return array Waterline breakdown
     */
    public static function getCapacityWaterline($employeeID, $startDate, $endDate, $DBConn = null) {
        $capacity = self::getAvailableCapacity($employeeID, $startDate, $endDate, $DBConn);

        return [
            'layer1_nonWorking' => $capacity['ptoHours'],
            'layer2_bau' => $capacity['bauHours'],
            'layer3_projects' => $capacity['projectHours'],
            'available' => $capacity['availableHours'],
            'total' => $capacity['totalHours']
        ];
    }

    /**
     * Create operational project (BAU bucket)
     *
     * @param array $data Project data
     * @param object $DBConn Database connection
     * @return int|false Project ID or false
     */
    public static function createOperationalProject($data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'projectCode', 'projectName', 'functionalArea', 'fiscalYear',
            'allocatedHours', 'functionalAreaOwnerID'
        );

        return $DBConn->insert_db_table_row('tija_operational_projects', $cols, $data);
    }

    /**
     * Allocate to operational project
     *
     * @param int $operationalProjectID Project ID
     * @param int $employeeID Employee ID
     * @param float $hours Hours to allocate
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function allocateToOperationalProject($operationalProjectID, $employeeID, $hours, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // This would typically create an allocation record
        // For now, just update the project's allocated hours
        $project = $DBConn->retrieve_db_table_rows(
            'tija_operational_projects',
            ['allocatedHours'],
            ['operationalProjectID' => $operationalProjectID],
            true
        );

        if ($project) {
            $newAllocated = ($project['allocatedHours'] ?? 0) + $hours;
            $fte = self::calculateFTE($newAllocated);

            return $DBConn->update_db_table_row(
                'tija_operational_projects',
                ['allocatedHours' => $newAllocated, 'fteRequirement' => $fte],
                ['operationalProjectID' => $operationalProjectID]
            );
        }

        return false;
    }
}

