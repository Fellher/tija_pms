<?php
/**
 * Goal Integration Class
 *
 * Integrates Goals module with job_roles, orgchart, and organization structure
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

class GoalIntegration {

    /**
     * Get job role mapping for goal templates
     *
     * Maps job roles to functional domains for template suggestions
     *
     * @param int $jobTitleID Job Title ID
     * @param object $DBConn Database connection
     * @return string|false Functional domain or false
     */
    public static function getFunctionalDomainFromJobRole($jobTitleID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'data.php';

        // Get job title details
        $jobTitle = Data::job_titles(array('jobTitleID' => $jobTitleID, 'Lapsed' => 'N'), true, $DBConn);
        if (!$jobTitle) {
            return false;
        }

        // Get job category to determine functional domain
        if (isset($jobTitle->jobCategoryID)) {
            $jobCategory = Data::job_categories(array('jobCategoryID' => $jobTitle->jobCategoryID, 'Lapsed' => 'N'), true, $DBConn);
            if ($jobCategory) {
                // Map job category to functional domain
                $domainMapping = array(
                    'Sales' => 'Sales',
                    'Marketing' => 'Marketing',
                    'IT' => 'IT',
                    'HR' => 'HR',
                    'Finance' => 'Finance',
                    'Operations' => 'Operations',
                    'Engineering' => 'Engineering',
                    'Management' => 'Management'
                );

                $categoryName = $jobCategory->jobCategoryName ?? '';
                foreach ($domainMapping as $key => $domain) {
                    if (stripos($categoryName, $key) !== false) {
                        return $domain;
                    }
                }
            }
        }

        // Fallback: check job title name
        $titleName = $jobTitle->jobTitle ?? '';
        if (stripos($titleName, 'Sales') !== false) return 'Sales';
        if (stripos($titleName, 'IT') !== false || stripos($titleName, 'Tech') !== false) return 'IT';
        if (stripos($titleName, 'HR') !== false || stripos($titleName, 'Human') !== false) return 'HR';
        if (stripos($titleName, 'Finance') !== false || stripos($titleName, 'Account') !== false) return 'Finance';
        if (stripos($titleName, 'Manager') !== false || stripos($titleName, 'Director') !== false) return 'Management';

        return false;
    }

    /**
     * Get competency level from job role
     *
     * Maps job band/level to competency level for template filtering
     *
     * @param int $jobBandID Job Band ID
     * @param object $DBConn Database connection
     * @return string|false Competency level or false
     */
    public static function getCompetencyLevelFromJobRole($jobBandID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'data.php';

        // Get job band details
        $jobBand = Data::job_bands(array('jobBandID' => $jobBandID, 'Lapsed' => 'N'), true, $DBConn);
        if (!$jobBand) {
            return false;
        }

        $bandName = strtolower($jobBand->jobBandName ?? '');

        // Map job band to competency level
        if (stripos($bandName, 'junior') !== false || stripos($bandName, 'entry') !== false) {
            return 'Junior';
        } elseif (stripos($bandName, 'senior') !== false || stripos($bandName, 'lead') !== false) {
            return 'Senior';
        } elseif (stripos($bandName, 'principal') !== false || stripos($bandName, 'expert') !== false) {
            return 'Principal';
        } elseif (stripos($bandName, 'executive') !== false || stripos($bandName, 'director') !== false || stripos($bandName, 'vp') !== false) {
            return 'Executive';
        }

        return 'Senior'; // Default
    }

    /**
     * Get organization structure for goal cascading
     *
     * Retrieves org chart positions for cascade target selection
     *
     * @param int $entityID Entity ID
     * @param object $DBConn Database connection
     * @return array|false Org chart positions or false
     */
    public static function getOrgChartForCascading($entityID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'data.php';

        // Get current org chart for entity
        $orgCharts = Data::org_charts(array('entityID' => $entityID, 'isCurrent' => 'Y', 'Lapsed' => 'N'), false, $DBConn);
        if (!$orgCharts || count($orgCharts) === 0) {
            return false;
        }

        $orgChart = $orgCharts[0];
        $orgChartID = $orgChart->orgChartID;

        // Get position assignments
        $positions = Data::org_chart_position_assignments(
            array('orgChartID' => $orgChartID, 'Lapsed' => 'N', 'Suspended' => 'N'),
            false,
            $DBConn
        );

        return $positions ?: false;
    }

    /**
     * Get employees by org chart position
     *
     * Retrieves employees assigned to specific org chart positions
     *
     * @param int $positionID Position ID
     * @param object $DBConn Database connection
     * @return array|false Employees array or false
     */
    public static function getEmployeesByOrgPosition($positionID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'employee.php';
        require_once 'data.php';

        // Get position assignments
        $assignments = Data::org_chart_position_assignments(
            array('positionID' => $positionID, 'Lapsed' => 'N', 'Suspended' => 'N'),
            false,
            $DBConn
        );

        if (!$assignments) {
            return false;
        }

        $employees = array();
        foreach ($assignments as $assignment) {
            if (isset($assignment->orgDataID)) {
                // Get employee by orgDataID
                $employee = Employee::employees(array('orgDataID' => $assignment->orgDataID, 'Valid' => 'Y'), true, $DBConn);
                if ($employee) {
                    $employees[] = $employee;
                }
            }
        }

        return count($employees) > 0 ? $employees : false;
    }

    /**
     * Enhanced template suggestions with job role integration
     *
     * Uses job roles and org structure to suggest relevant templates
     *
     * @param int $userID User ID
     * @param object $DBConn Database connection
     * @return array|false Suggested templates or false
     */
    public static function suggestTemplatesWithJobRole($userID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goallibrary.php';
        require_once 'employee.php';

        // Get user details
        $user = Employee::employees(array('ID' => $userID, 'Valid' => 'Y'), true, $DBConn);
        if (!$user) {
            return false;
        }

        $filters = array('isActive' => 'Y');

        // Get functional domain from job role
        if (isset($user->jobTitleID)) {
            $functionalDomain = self::getFunctionalDomainFromJobRole($user->jobTitleID, $DBConn);
            if ($functionalDomain) {
                $filters['functionalDomain'] = $functionalDomain;
            }
        }

        // Get competency level from job band
        if (isset($user->jobBandID)) {
            $competencyLevel = self::getCompetencyLevelFromJobRole($user->jobBandID, $DBConn);
            if ($competencyLevel) {
                $filters['competencyLevel'] = $competencyLevel;
            }
        }

        // Get templates
        $templates = GoalLibrary::getTemplates($filters, $DBConn);

        // Sort by relevance (usage count + match score)
        if ($templates) {
            usort($templates, function($a, $b) use ($filters) {
                $scoreA = ($a->usageCount ?? 0);
                $scoreB = ($b->usageCount ?? 0);

                // Boost score if functional domain matches
                if (isset($filters['functionalDomain']) && $a->functionalDomain === $filters['functionalDomain']) {
                    $scoreA += 100;
                }
                if (isset($filters['functionalDomain']) && $b->functionalDomain === $filters['functionalDomain']) {
                    $scoreB += 100;
                }

                // Boost score if competency level matches
                if (isset($filters['competencyLevel']) && $a->competencyLevel === $filters['competencyLevel']) {
                    $scoreA += 50;
                }
                if (isset($filters['competencyLevel']) && $b->competencyLevel === $filters['competencyLevel']) {
                    $scoreB += 50;
                }

                return $scoreB - $scoreA;
            });
        }

        return $templates;
    }

    /**
     * Get cascade targets from org chart
     *
     * Uses org chart structure to determine cascade targets
     *
     * @param int $entityID Entity ID
     * @param int $positionID Position ID (optional - for specific position)
     * @param object $DBConn Database connection
     * @return array|false Target entities/users or false
     */
    public static function getCascadeTargetsFromOrgChart($entityID, $positionID = null, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goalhierarchy.php';

        // Get org chart positions
        $positions = self::getOrgChartForCascading($entityID, $DBConn);
        if (!$positions) {
            return false;
        }

        $targets = array();

        foreach ($positions as $position) {
            // If specific position requested, filter
            if ($positionID && $position->positionID != $positionID) {
                continue;
            }

            // Get employees in this position
            $employees = self::getEmployeesByOrgPosition($position->positionID, $DBConn);
            if ($employees) {
                foreach ($employees as $employee) {
                    $targets[] = array(
                        'type' => 'User',
                        'id' => $employee->ID,
                        'name' => ($employee->FirstName ?? '') . ' ' . ($employee->Surname ?? ''),
                        'position' => $position->positionTitle ?? '',
                        'positionID' => $position->positionID
                    );
                }
            }

            // Also include child positions for cascading
            if (isset($position->positionParentID) && $position->positionParentID) {
                // This would be used for hierarchical cascading
            }
        }

        return count($targets) > 0 ? $targets : false;
    }
}

