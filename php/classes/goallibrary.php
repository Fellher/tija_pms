<?php
/**
 * Goal Library Class
 *
 * Manages goal templates and library
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

class GoalLibrary {

    /**
     * Unified templates accessor
     *
     * @param array  $whereArr Filter criteria
     * @param bool   $single   When true returns a single record (or false), when false returns an array (or false)
     * @param object $DBConn   Database connection
     * @return mixed           Object, array of objects, or false
     */
    public static function templates($whereArr = array(), $single = false, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Ensure core flags unless explicitly overridden
        if (!isset($whereArr['Lapsed'])) {
            $whereArr['Lapsed'] = 'N';
        }
        if (!isset($whereArr['Suspended'])) {
            $whereArr['Suspended'] = 'N';
        }

        $cols = array(
            'libraryID', 'templateCode', 'templateName', 'templateDescription',
            'goalType', 'variables', 'defaultKPIs', 'jurisdictionDeny',
            'suggestedWeight', 'functionalDomain', 'competencyLevel',
            'strategicPillar', 'timeHorizon', 'jurisdictionScope',
            'broaderConceptID', 'narrowerConceptIDs', 'relatedConceptIDs',
            'isActive', 'usageCount', 'DateAdded', 'LastUpdate', 'LastUpdatedByID'
        );

        $row = $DBConn->retrieve_db_table_rows('tija_goal_library', $cols, $whereArr, $single);

        if (!$row) {
            return false;
        }

        // Normalise to array of rows for processing
        $rows = $single ? array($row) : $row;

        foreach ($rows as &$template) {
            if (is_array($template)) {
                $template = (object) $template;
            }

            if (isset($template->variables) && $template->variables) {
                $template->variables = json_decode($template->variables, true);
            }
            if (isset($template->defaultKPIs) && $template->defaultKPIs) {
                $template->defaultKPIs = json_decode($template->defaultKPIs, true);
            }
            if (isset($template->jurisdictionDeny) && $template->jurisdictionDeny) {
                $template->jurisdictionDeny = json_decode($template->jurisdictionDeny, true);
            }
            if (isset($template->narrowerConceptIDs) && $template->narrowerConceptIDs) {
                $template->narrowerConceptIDs = json_decode($template->narrowerConceptIDs, true);
            }
            if (isset($template->relatedConceptIDs) && $template->relatedConceptIDs) {
                $template->relatedConceptIDs = json_decode($template->relatedConceptIDs, true);
            }
        }

        return $single ? $rows[0] : $rows;
    }

    /**
     * Get templates with filters
     *
     * @param array $filters Filter criteria
     * @param object $DBConn Database connection
     * @return array|false Templates array or false
     */
    public static function getTemplates($filters = array(), $DBConn = null) {
        // Backwards-compatible wrapper using unified templates() method
        return self::templates($filters, false, $DBConn);
    }

    /**
     * Get single template
     *
     * @param int $libraryID Library ID
     * @param object $DBConn Database connection
     * @return object|false Template data or false
     */
    public static function getTemplate($libraryID, $DBConn = null) {
        // Backwards-compatible wrapper using unified templates() method
        return self::templates(array('libraryID' => $libraryID), true, $DBConn);
    }

    /**
     * Create new template
     *
     * @param array $data Template data
     * @param object $DBConn Database connection
     * @return int|false Library ID or false
     */
    public static function createTemplate($data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $templateData = array(
            'templateCode' => $data['templateCode'] ?? '',
            'templateName' => $data['templateName'] ?? '',
            'templateDescription' => $data['templateDescription'] ?? null,
            'goalType' => $data['goalType'] ?? 'Strategic',
            'variables' => isset($data['variables']) ? json_encode($data['variables']) : null,
            'defaultKPIs' => isset($data['defaultKPIs']) ? json_encode($data['defaultKPIs']) : null,
            'jurisdictionDeny' => isset($data['jurisdictionDeny']) ? json_encode($data['jurisdictionDeny']) : null,
            'suggestedWeight' => $data['suggestedWeight'] ?? 0.2500,
            'functionalDomain' => $data['functionalDomain'] ?? null,
            'competencyLevel' => $data['competencyLevel'] ?? 'All',
            'strategicPillar' => $data['strategicPillar'] ?? null,
            'timeHorizon' => $data['timeHorizon'] ?? 'Annual',
            'jurisdictionScope' => $data['jurisdictionScope'] ?? null,
            'broaderConceptID' => $data['broaderConceptID'] ?? null,
            'narrowerConceptIDs' => isset($data['narrowerConceptIDs']) ? json_encode($data['narrowerConceptIDs']) : null,
            'relatedConceptIDs' => isset($data['relatedConceptIDs']) ? json_encode($data['relatedConceptIDs']) : null,
            'isActive' => $data['isActive'] ?? 'Y',
            'LastUpdatedByID' => $data['LastUpdatedByID'] ?? null
        );

        $result = $DBConn->insert_data('tija_goal_library', $templateData);

        if ($result) {
            $libraryID = $DBConn->lastInsertId();
            // Increment usage count
            self::incrementUsageCount($libraryID, $DBConn);
            return $libraryID;
        }

        return false;
    }

    /**
     * Update existing template
     *
     * @param int   $libraryID Library ID
     * @param array $data      Template data
     * @param object $DBConn   Database connection
     * @return bool Success
     */
    public static function updateTemplate($libraryID, $data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        if (!$libraryID) {
            return false;
        }

        $templateData = array(
            'templateCode' => $data['templateCode'] ?? '',
            'templateName' => $data['templateName'] ?? '',
            'templateDescription' => $data['templateDescription'] ?? null,
            'goalType' => $data['goalType'] ?? 'Strategic',
            'variables' => isset($data['variables']) ? json_encode($data['variables']) : null,
            'defaultKPIs' => isset($data['defaultKPIs']) ? json_encode($data['defaultKPIs']) : null,
            'jurisdictionDeny' => isset($data['jurisdictionDeny']) ? json_encode($data['jurisdictionDeny']) : null,
            'suggestedWeight' => $data['suggestedWeight'] ?? 0.2500,
            'functionalDomain' => $data['functionalDomain'] ?? null,
            'competencyLevel' => $data['competencyLevel'] ?? 'All',
            'strategicPillar' => $data['strategicPillar'] ?? null,
            'timeHorizon' => $data['timeHorizon'] ?? 'Annual',
            'jurisdictionScope' => $data['jurisdictionScope'] ?? null,
            'broaderConceptID' => $data['broaderConceptID'] ?? null,
            'narrowerConceptIDs' => isset($data['narrowerConceptIDs']) ? json_encode($data['narrowerConceptIDs']) : null,
            'relatedConceptIDs' => isset($data['relatedConceptIDs']) ? json_encode($data['relatedConceptIDs']) : null,
            'isActive' => $data['isActive'] ?? 'Y',
            'LastUpdatedByID' => $data['LastUpdatedByID'] ?? null
        );

        return $DBConn->update_table('tija_goal_library', $templateData, array('libraryID' => $libraryID));
    }

    /**
     * Instantiate goal from template
     *
     * @param int $libraryID Library ID
     * @param array $variables Variable values to fill in template
     * @param object $DBConn Database connection
     * @return string|false Goal UUID or false
     */
    public static function instantiateTemplate($libraryID, $variables, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $template = self::getTemplate($libraryID, $DBConn);
        if (!$template || $template->isActive !== 'Y') {
            return false;
        }

        require_once 'goal.php';

        // Build goal title from template
        $goalTitle = $template->templateName;
        if ($template->variables && is_array($template->variables)) {
            foreach ($template->variables as $var) {
                if (isset($variables[$var])) {
                    $goalTitle = str_replace('[' . $var . ']', $variables[$var], $goalTitle);
                }
            }
        }

        // Prepare goal data
        $goalData = array(
            'libraryRefID' => $libraryID,
            'goalType' => $template->goalType,
            'goalTitle' => $goalTitle,
            'goalDescription' => $template->templateDescription,
            'weight' => $template->suggestedWeight,
            'visibility' => 'Private',
            'status' => 'Draft'
        );

        // Add type-specific data
        if ($template->goalType === 'OKR' && isset($variables['objective'])) {
            $goalData['okrData'] = array(
                'objective' => $variables['objective'],
                'keyResults' => $variables['keyResults'] ?? array()
            );
        } elseif ($template->goalType === 'KPI' && isset($variables['kpiName'])) {
            $goalData['kpiData'] = array(
                'kpiName' => $variables['kpiName'],
                'kpiDescription' => $variables['kpiDescription'] ?? null,
                'targetValue' => $variables['targetValue'] ?? 0,
                'unit' => $variables['unit'] ?? null,
                'currencyCode' => $variables['currencyCode'] ?? null
            );
        }

        // Create goal
        $goalUUID = Goal::createGoal($goalData, $DBConn);

        if ($goalUUID) {
            // Increment template usage
            self::incrementUsageCount($libraryID, $DBConn);
        }

        return $goalUUID;
    }

    /**
     * Suggest templates for user
     *
     * @param int $userID User ID
     * @param array $context Context data (jobTitleID, departmentID, etc.)
     * @param object $DBConn Database connection
     * @return array|false Suggested templates or false
     */
    public static function suggestTemplates($userID, $context = array(), $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'employee.php';

        // Get user details
        $user = Employee::employees(array('ID' => $userID), true, $DBConn);
        if (!$user) {
            return false;
        }

        $filters = array('isActive' => 'Y');

        // Filter by job family/functional domain if available
        if (isset($user->jobTitleID)) {
            require_once 'goalintegration.php';
            $functionalDomain = GoalIntegration::getFunctionalDomainFromJobRole($user->jobTitleID, $DBConn);
            if ($functionalDomain) {
                $filters['functionalDomain'] = $functionalDomain;
            }
        }

        // Filter by competency level based on job band
        if (isset($user->jobBandID)) {
            require_once 'goalintegration.php';
            $competencyLevel = GoalIntegration::getCompetencyLevelFromJobRole($user->jobBandID, $DBConn);
            if ($competencyLevel) {
                $filters['competencyLevel'] = $competencyLevel;
            }
        }

        // Get templates
        $templates = self::getTemplates($filters, $DBConn);

        // Sort by usage count (most popular first)
        if ($templates) {
            usort($templates, function($a, $b) {
                return ($b->usageCount ?? 0) - ($a->usageCount ?? 0);
            });
        }

        return $templates;
    }

    /**
     * Get taxonomy tree (SKOS broader/narrower)
     *
     * @param object $DBConn Database connection
     * @return array Taxonomy tree
     */
    public static function getTaxonomyTree($DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'libraryID', 'templateCode', 'templateName', 'broaderConceptID',
            'narrowerConceptIDs', 'relatedConceptIDs'
        );

        $where = array('Lapsed' => 'N', 'Suspended' => 'N', 'isActive' => 'Y');
        $templates = $DBConn->retrieve_db_table_rows('tija_goal_library', $cols, $where, false);

        // Build tree structure
        $tree = array();
        $indexed = array();

        // Index all templates
        foreach ($templates as $template) {
            $indexed[$template->libraryID] = $template;
            if ($template->narrowerConceptIDs) {
                $template->narrowerConceptIDs = json_decode($template->narrowerConceptIDs, true);
            }
        }

        // Build tree (top-level concepts have no broader concept)
        foreach ($indexed as $id => $template) {
            if (!$template->broaderConceptID) {
                $tree[] = self::buildTreeNode($id, $indexed);
            }
        }

        return $tree;
    }

    /**
     * Build tree node recursively
     *
     * @param int $libraryID Library ID
     * @param array $indexed Indexed templates
     * @return array Tree node
     */
    private static function buildTreeNode($libraryID, $indexed) {
        $template = $indexed[$libraryID];
        $node = array(
            'libraryID' => $template->libraryID,
            'templateCode' => $template->templateCode,
            'templateName' => $template->templateName,
            'children' => array()
        );

        if ($template->narrowerConceptIDs && is_array($template->narrowerConceptIDs)) {
            foreach ($template->narrowerConceptIDs as $childID) {
                if (isset($indexed[$childID])) {
                    $node['children'][] = self::buildTreeNode($childID, $indexed);
                }
            }
        }

        return $node;
    }

    /**
     * Increment usage count
     *
     * @param int $libraryID Library ID
     * @param object $DBConn Database connection
     * @return bool Success
     */
    private static function incrementUsageCount($libraryID, $DBConn) {
        $current = $DBConn->retrieve_db_table_rows(
            'tija_goal_library',
            array('usageCount'),
            array('libraryID' => $libraryID),
            true
        );

        if ($current) {
            $newCount = ($current->usageCount ?? 0) + 1;
            return $DBConn->update_table(
                'tija_goal_library',
                array('usageCount' => $newCount),
                array('libraryID' => $libraryID)
            );
        }

        return false;
    }
}

