<?php
/**
 * Goal Permissions Class
 *
 * Handles RBAC for goals module
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

class GoalPermissions {

    /**
     * Check if user can view global goals
     *
     * @param int $userID User ID
     * @param object $DBConn Database connection
     * @return bool
     */
    public static function canViewGlobal($userID, $DBConn = null) {
        global $isAdmin, $isValidAdmin;

        if (!$DBConn) {
            global $DBConn;
        }

        // Global admins can view
        if (isset($isAdmin) && $isAdmin) {
            return true;
        }
        if (isset($isValidAdmin) && $isValidAdmin) {
            return true;
        }

        // Check for specific permission (would integrate with permission system)
        return false;
    }

    /**
     * Check if user can create goals
     *
     * @param int $userID User ID
     * @param object $DBConn Database connection
     * @return bool
     */
    public static function canCreate($userID, $DBConn = null) {
        global $isValidUser;

        // Any valid user can create their own goals
        return isset($isValidUser) && $isValidUser;
    }

    /**
     * Check if user can edit goal
     *
     * @param string $goalUUID Goal UUID
     * @param int $userID User ID
     * @param object $DBConn Database connection
     * @return bool
     */
    public static function canEdit($goalUUID, $userID, $DBConn = null) {
        global $isAdmin, $isValidAdmin;

        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        $goal = Goal::getGoal($goalUUID, $DBConn);
        if (!$goal) {
            return false;
        }

        // Admins can edit
        if (isset($isAdmin) && $isAdmin) {
            return true;
        }
        if (isset($isValidAdmin) && $isValidAdmin) {
            return true;
        }

        // Owner can edit their own goals
        if ($goal->ownerUserID == $userID) {
            return true;
        }

        // Manager can edit subordinate goals
        require_once 'employee.php';
        $employee = Employee::employees(array('ID' => $goal->ownerUserID), true, $DBConn);
        if ($employee && $employee->supervisorID == $userID) {
            return true;
        }

        // Matrix manager can edit assigned goals
        require_once 'goalmatrix.php';
        $matrixAssignment = GoalMatrix::getMatrixAssignment($goalUUID, $goal->ownerUserID, $DBConn);
        if ($matrixAssignment && $matrixAssignment->matrixManagerID == $userID) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can delete goal
     *
     * @param string $goalUUID Goal UUID
     * @param int $userID User ID
     * @param object $DBConn Database connection
     * @return bool
     */
    public static function canDelete($goalUUID, $userID, $DBConn = null) {
        global $isAdmin, $isValidAdmin;

        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        $goal = Goal::getGoal($goalUUID, $DBConn);
        if (!$goal) {
            return false;
        }

        // Critical goals require L+2 approval (manager's manager)
        if ($goal->propriety === 'Critical') {
            // Check if user is L+2 (manager's manager)
            require_once 'employee.php';
            $employee = Employee::employees(array('ID' => $goal->ownerUserID), true, $DBConn);
            if ($employee && $employee->supervisorID) {
                $manager = Employee::employees(array('ID' => $employee->supervisorID), true, $DBConn);
                if ($manager && $manager->supervisorID == $userID) {
                    return true; // L+2 approval
                }
            }
            return false;
        }

        // Admins can delete
        if (isset($isAdmin) && $isAdmin) {
            return true;
        }

        // Owner can delete their own non-critical goals
        if ($goal->ownerUserID == $userID) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can evaluate goal
     *
     * @param string $goalUUID Goal UUID
     * @param int $userID User ID
     * @param object $DBConn Database connection
     * @return bool
     */
    public static function canEvaluate($goalUUID, $userID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        $goal = Goal::getGoal($goalUUID, $DBConn);
        if (!$goal) {
            return false;
        }

        // Self-evaluation
        if ($goal->ownerUserID == $userID) {
            return true;
        }

        // Manager evaluation
        require_once 'employee.php';
        $employee = Employee::employees(array('ID' => $goal->ownerUserID), true, $DBConn);
        if ($employee && $employee->supervisorID == $userID) {
            return true;
        }

        // Matrix manager evaluation
        require_once 'goalmatrix.php';
        $matrixAssignment = GoalMatrix::getMatrixAssignment($goalUUID, $goal->ownerUserID, $DBConn);
        if ($matrixAssignment && $matrixAssignment->matrixManagerID == $userID) {
            return true;
        }

        // Check if user is assigned as peer evaluator (would need evaluator assignment table)
        // For now, return false for peers - would need explicit assignment

        return false;
    }

    /**
     * Check if user can cascade goals
     *
     * @param int $userID User ID
     * @param object $DBConn Database connection
     * @return bool
     */
    public static function canCascade($userID, $DBConn = null) {
        global $isAdmin, $isValidAdmin;

        // Admins can cascade
        if (isset($isAdmin) && $isAdmin) {
            return true;
        }
        if (isset($isValidAdmin) && $isValidAdmin) {
            return true;
        }

        // Entity managers can cascade to their entities
        // Would need to check entity ownership
        return false;
    }

    /**
     * Check if user can manage goal library
     *
     * @param int $userID User ID
     * @param object $DBConn Database connection
     * @return bool
     */
    public static function canManageLibrary($userID, $DBConn = null) {
        global $isAdmin, $isValidAdmin, $isHRManager;

        // Admins and HR managers can manage library
        if (isset($isAdmin) && $isAdmin) {
            return true;
        }
        if (isset($isValidAdmin) && $isValidAdmin) {
            return true;
        }
        if (isset($isHRManager) && $isHRManager) {
            return true;
        }

        return false;
    }
}

