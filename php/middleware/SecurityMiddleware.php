<?php
/**
 * Security Middleware
 * Enterprise-level security layer for application-wide protection
 *
 * @package Tija Practice Management System
 * @subpackage Security
 * @version 1.0
 */

class SecurityMiddleware {

    /**
     * Generate CSRF Token
     *
     * @return string CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) ||
            (time() - $_SESSION['csrf_token_time']) > 3600) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF Token
     *
     * @param string $token Token to validate
     * @return bool True if valid
     */
    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        // Check token age (expire after 1 hour)
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get CSRF Token HTML Input
     *
     * @return string HTML input field
     */
    public static function csrfTokenField() {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate POST Request with CSRF
     *
     * @throws Exception If CSRF validation fails
     */
    public static function validatePOSTRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!self::validateCSRFToken($token)) {
                throw new Exception("CSRF token validation failed");
            }
        }
    }

    /**
     * Sanitize Input Data
     *
     * @param mixed $data Data to sanitize
     * @param string $type Data type (string, int, float, email, url)
     * @return mixed Sanitized data
     */
    public static function sanitizeInput($data, $type = 'string') {
        switch ($type) {
            case 'int':
                return filter_var($data, FILTER_VALIDATE_INT);

            case 'float':
                return filter_var($data, FILTER_VALIDATE_FLOAT);

            case 'email':
                return filter_var($data, FILTER_VALIDATE_EMAIL);

            case 'url':
                return filter_var($data, FILTER_VALIDATE_URL);

            case 'html':
                return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            case 'string':
            default:
                return trim(strip_tags($data));
        }
    }

    /**
     * Validate File Upload
     *
     * @param array $file $_FILES array element
     * @param array $options Validation options
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateFileUpload($file, $options = []) {
        $defaults = [
            'maxSize' => 5 * 1024 * 1024, // 5MB
            'allowedTypes' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'allowedMimes' => ['image/jpeg', 'image/png', 'application/pdf',
                             'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                             'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        ];

        $options = array_merge($defaults, $options);

        // Check if file was uploaded
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['valid' => false, 'message' => 'Invalid file upload'];
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'File upload failed with error code: ' . $file['error']];
        }

        // Check file size
        if ($file['size'] > $options['maxSize']) {
            return ['valid' => false, 'message' => 'File size exceeds maximum allowed (' . ($options['maxSize'] / 1024 / 1024) . 'MB)'];
        }

        // Check file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $options['allowedTypes'])) {
            return ['valid' => false, 'message' => 'File type not allowed. Allowed types: ' . implode(', ', $options['allowedTypes'])];
        }

        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $options['allowedMimes'])) {
            return ['valid' => false, 'message' => 'Invalid file MIME type'];
        }

        return ['valid' => true, 'message' => 'File validation passed'];
    }

    /**
     * Rate Limiting
     *
     * @param string $action Action identifier
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $timeWindow Time window in seconds
     * @return bool True if allowed, false if rate limit exceeded
     */
    public static function checkRateLimit($action, $maxAttempts = 10, $timeWindow = 60) {
        $key = 'rate_limit_' . $action . '_' . ($_SESSION['ID'] ?? 'guest');

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
            return true;
        }

        $data = $_SESSION[$key];

        // Check if time window has passed
        if (time() - $data['first_attempt'] > $timeWindow) {
            // Reset counter
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
            return true;
        }

        // Increment attempts
        $_SESSION[$key]['attempts']++;

        // Check if limit exceeded
        if ($_SESSION[$key]['attempts'] > $maxAttempts) {
            return false;
        }

        return true;
    }

    /**
     * Log Security Event
     *
     * @param string $event Event type
     * @param string $message Event message
     * @param string $severity Severity level (info, warning, critical)
     */
    public static function logSecurityEvent($event, $message, $severity = 'info') {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'message' => $message,
            'severity' => $severity,
            'user_id' => $_SESSION['ID'] ?? 'guest',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        error_log("SECURITY [{$severity}]: " . json_encode($logEntry));

        // For critical events, also write to database
        if ($severity === 'critical') {
            // Write to database audit log
            global $DBConn;
            if ($DBConn) {
                try {
                    $DBConn->insert_data('tija_security_log', $logEntry);
                } catch (Exception $e) {
                    error_log("Failed to write security log to database: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Check Project Access Permission
     *
     * @param int $projectID Project ID
     * @param int $userID User ID
     * @param string $permission Permission type (view, edit, delete, manage_team, manage_billing)
     * @return bool True if authorized
     */
    public static function checkProjectPermission($projectID, $userID, $permission = 'view') {
        global $DBConn;

        // Admins have full access
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) {
            return true;
        }

        // Check if user is project manager
        $project = Projects::project_full(['projectID' => $projectID], true, $DBConn);
        if ($project && $project->projectManagerID == $userID) {
            return true;
        }

        // Check if user is team member
        $teamMember = ProjectTeam::team_members([
            'projectID' => $projectID,
            'userID' => $userID,
            'Suspended' => 'N'
        ], true, $DBConn);

        if ($teamMember) {
            // Different permissions based on role
            switch ($permission) {
                case 'view':
                    return true; // All team members can view

                case 'edit':
                    return in_array($teamMember->role, ['manager', 'lead', 'admin']);

                case 'delete':
                case 'manage_team':
                case 'manage_billing':
                    return in_array($teamMember->role, ['manager', 'admin']);

                default:
                    return false;
            }
        }

        return false;
    }

    /**
     * Sanitize Array Recursively
     *
     * @param array $array Array to sanitize
     * @return array Sanitized array
     */
    public static function sanitizeArray($array) {
        $sanitized = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = self::sanitizeInput($value);
            }
        }
        return $sanitized;
    }

    /**
     * Generate Secure Random Token
     *
     * @param int $length Token length
     * @return string Secure random token
     */
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    /**
     * Validate Session
     *
     * @param object|null $userDetails Optional user details object
     * @return bool True if session is valid
     */
    public static function validateSession($userDetails = null) {
        // Check multiple possible session ID variables (backward compatibility)
        $sessionValid = false;

        if (isset($_SESSION['ID']) && !empty($_SESSION['ID'])) {
            $sessionValid = true;
        } elseif (isset($_SESSION['SESS_USER_ID']) && !empty($_SESSION['SESS_USER_ID'])) {
            $sessionValid = true;
        } elseif ($userDetails && isset($userDetails->ID) && !empty($userDetails->ID)) {
            $sessionValid = true;
        }

        if (!$sessionValid) {
            return false;
        }

        // Check session timeout (30 minutes) - optional
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            // Don't destroy session, just warn
            error_log("Session timeout warning for user " . ($_SESSION['ID'] ?? $_SESSION['SESS_USER_ID'] ?? 'unknown'));
            // session_destroy();
            // return false;
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();

        // Check session hijacking prevention - make it less strict
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }

        // Only log suspicious activity, don't block (too strict for development)
        if ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            self::logSecurityEvent('session_user_agent_change', 'User agent changed during session', 'info');
            // Don't destroy session - update it instead
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }

        return true;
    }
}
?>

