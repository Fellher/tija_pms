<?php
/**
 * Project Plan Configuration
 * 
 * Centralized configuration system for project plan functionality.
 * This file contains all configuration settings, feature toggles,
 * and UI preferences for easy maintenance and updates.
 * 
 * @package    PMS
 * @subpackage Projects
 * @author     SKM Development Team
 * @version    3.0.0
 * @since      1.0.0
 * 
 * @var array $projectPlanConfig Global configuration array
 */

// This file is meant to be included, not accessed directly

/**
 * Project Plan Configuration Array
 * 
 * This configuration array controls all aspects of the project plan system:
 * - Feature toggles for enabling/disabling functionality
 * - Display preferences for UI components
 * - Validation rules and constraints
 * - Performance and caching settings
 * - Security and access control settings
 * 
 * @var array $projectPlanConfig
 * @since 3.0.0
 */
$projectPlanConfig = [
    
    // ========================================================================
    // FEATURE TOGGLES
    // ========================================================================
    'features' => [
        'phaseManagement' => true,           // Enable phase creation, editing, deletion
        'taskManagement' => true,            // Enable task management functionality
        'subtaskManagement' => true,         // Enable subtask/activity management
        'assigneeManagement' => true,        // Enable assignee management
        'timelineManagement' => true,        // Enable timeline and date management
        'progressTracking' => true,          // Enable progress tracking
        'dragDrop' => false,                 // Enable drag-and-drop functionality (future)
        'realTimeUpdates' => false,          // Enable real-time collaboration (future)
        'export' => true,                    // Enable data export functionality
        'import' => false,                   // Enable data import functionality
        'notifications' => true,             // Enable notification system
        'auditLog' => true,                  // Enable audit logging
    ],
    
    // ========================================================================
    // DISPLAY SETTINGS
    // ========================================================================
    'display' => [
        'theme' => 'light',                  // UI theme (light, dark, auto)
        'responsive' => true,                // Enable responsive design
        'animations' => true,                // Enable CSS animations
        'tooltips' => true,                 // Enable tooltips
        'modals' => true,                   // Enable modal dialogs
        'collapsiblePhases' => true,        // Enable phase collapse/expand
        'showPhaseTimeline' => true,        // Show phase timeline visualization
        'showTaskProgress' => true,         // Show task progress indicators
        'showAssigneeAvatars' => true,      // Show assignee avatars
        'showTaskWeighting' => true,        // Show task weighting information
        'showSubTasks' => true,             // Show subtask/activity details
        'showEmptyStates' => true,          // Show empty state messages
        'itemsPerPage' => 10,               // Default items per page
        'maxPhases' => 50,                  // Maximum phases per project
        'maxTasksPerPhase' => 100,          // Maximum tasks per phase
        'maxSubTasksPerTask' => 50,         // Maximum subtasks per task
    ],
    
    // ========================================================================
    // UI COMPONENT SETTINGS
    // ========================================================================
    'ui' => [
        'cardStyle' => 'modern',            // Card style (modern, classic, minimal)
        'buttonStyle' => 'rounded',         // Button style (rounded, square, pill)
        'colorScheme' => 'primary',         // Color scheme (primary, secondary, custom)
        'iconSet' => 'bootstrap',           // Icon set (bootstrap, fontawesome, custom)
        'fontSize' => 'medium',             // Font size (small, medium, large)
        'spacing' => 'comfortable',         // Spacing (compact, comfortable, spacious)
        'borderRadius' => 'medium',         // Border radius (none, small, medium, large)
        'shadows' => true,                  // Enable drop shadows
        'gradients' => true,                // Enable gradient backgrounds
    ],
    
    // ========================================================================
    // VALIDATION RULES
    // ========================================================================
    'validation' => [
        'dateValidation' => true,           // Enable date validation
        'requiredFields' => true,           // Enable required field validation
        'realTimeValidation' => true,       // Enable real-time validation
        'errorDisplay' => true,             // Enable error message display
        'clientSideValidation' => true,     // Enable client-side validation
        'serverSideValidation' => true,     // Enable server-side validation
        'maxPhaseNameLength' => 100,        // Maximum phase name length
        'maxTaskNameLength' => 200,         // Maximum task name length
        'maxSubTaskNameLength' => 150,      // Maximum subtask name length
        'maxDescriptionLength' => 1000,     // Maximum description length
        'minTaskDuration' => 1,             // Minimum task duration in days
        'maxTaskDuration' => 365,           // Maximum task duration in days
        'dateFormat' => 'Y-m-d',            // Date format for validation
        'timeFormat' => 'H:i:s',            // Time format for validation
    ],
    
    // ========================================================================
    // PERFORMANCE SETTINGS
    // ========================================================================
    'performance' => [
        'enableCaching' => true,            // Enable data caching
        'cacheTimeout' => 300,              // Cache timeout in seconds
        'lazyLoading' => true,              // Enable lazy loading
        'pagination' => true,               // Enable pagination
        'virtualScrolling' => false,        // Enable virtual scrolling (future)
        'debounceDelay' => 300,             // Debounce delay in milliseconds
        'maxConcurrentRequests' => 5,       // Maximum concurrent AJAX requests
        'enableCompression' => true,        // Enable response compression
        'minifyAssets' => true,             // Enable asset minification
    ],
    
    // ========================================================================
    // SECURITY SETTINGS
    // ========================================================================
    'security' => [
        'csrfProtection' => true,           // Enable CSRF protection
        'xssProtection' => true,            // Enable XSS protection
        'sqlInjectionProtection' => true,   // Enable SQL injection protection
        'inputSanitization' => true,        // Enable input sanitization
        'outputEscaping' => true,           // Enable output escaping
        'accessControl' => true,            // Enable access control
        'auditLogging' => true,             // Enable audit logging
        'sessionTimeout' => 3600,           // Session timeout in seconds
        'maxLoginAttempts' => 5,            // Maximum login attempts
        'passwordPolicy' => 'strong',       // Password policy (weak, medium, strong)
    ],
    
    // ========================================================================
    // API SETTINGS
    // ========================================================================
    'api' => [
        'enableREST' => true,               // Enable REST API
        'enableGraphQL' => false,           // Enable GraphQL API (future)
        'rateLimiting' => true,             // Enable rate limiting
        'maxRequestsPerMinute' => 60,       // Maximum requests per minute
        'apiVersion' => 'v1',               // API version
        'enableCORS' => true,               // Enable CORS
        'allowedOrigins' => ['*'],          // Allowed CORS origins
        'enableSwagger' => true,            // Enable Swagger documentation
    ],
    
    // ========================================================================
    // NOTIFICATION SETTINGS
    // ========================================================================
    'notifications' => [
        'enableEmail' => true,              // Enable email notifications
        'enablePush' => false,              // Enable push notifications (future)
        'enableSMS' => false,               // Enable SMS notifications (future)
        'enableInApp' => true,              // Enable in-app notifications
        'enableToast' => true,              // Enable toast notifications
        'enableSound' => false,             // Enable sound notifications
        'autoHideDelay' => 5000,            // Auto-hide delay in milliseconds
        'maxNotifications' => 10,           // Maximum visible notifications
    ],
    
    // ========================================================================
    // DEBUGGING AND LOGGING
    // ========================================================================
    'debug' => [
        'enableLogging' => true,            // Enable logging
        'logLevel' => 'info',               // Log level (debug, info, warning, error)
        'logToFile' => true,                // Log to file
        'logToDatabase' => true,            // Log to database
        'logToConsole' => false,            // Log to console (development only)
        'enableProfiling' => false,         // Enable performance profiling
        'enableTracing' => false,           // Enable request tracing
        'maxLogSize' => 10485760,           // Maximum log file size in bytes
        'logRotation' => true,              // Enable log rotation
    ],
    
    // ========================================================================
    // INTEGRATION SETTINGS
    // ========================================================================
    'integrations' => [
        'enableCalendar' => true,           // Enable calendar integration
        'enableEmail' => true,              // Enable email integration
        'enableSlack' => false,             // Enable Slack integration (future)
        'enableTeams' => false,             // Enable Microsoft Teams integration (future)
        'enableJira' => false,              // Enable Jira integration (future)
        'enableTrello' => false,            // Enable Trello integration (future)
        'enableGitHub' => false,            // Enable GitHub integration (future)
    ],
    
    // ========================================================================
    // CUSTOMIZATION SETTINGS
    // ========================================================================
    'customization' => [
        'allowThemeCustomization' => true,  // Allow theme customization
        'allowLayoutCustomization' => true, // Allow layout customization
        'allowWidgetCustomization' => true, // Allow widget customization
        'allowColorCustomization' => true,  // Allow color customization
        'allowFontCustomization' => true,   // Allow font customization
        'allowLogoCustomization' => true,   // Allow logo customization
        'allowBrandingCustomization' => true, // Allow branding customization
    ],
    
    // ========================================================================
    // EXPERIMENTAL FEATURES
    // ========================================================================
    'experimental' => [
        'enableAI' => false,                // Enable AI features (future)
        'enableML' => false,                // Enable machine learning (future)
        'enableBlockchain' => false,        // Enable blockchain features (future)
        'enableVR' => false,                // Enable VR features (future)
        'enableAR' => false,                // Enable AR features (future)
        'enableIoT' => false,               // Enable IoT integration (future)
    ],
];

/**
 * Get Configuration Value
 * 
 * Retrieves a configuration value using dot notation.
 * Supports nested array access and default values.
 * 
 * @param string $key Configuration key in dot notation (e.g., 'features.phaseManagement')
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value or default
 * @since 3.0.0
 */
function getProjectPlanConfig($key = null, $default = null) {
    global $projectPlanConfig;
    
    if ($key === null) {
        return $projectPlanConfig;
    }
    
    $keys = explode('.', $key);
    $value = $projectPlanConfig;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * Set Configuration Value
 * 
 * Sets a configuration value using dot notation.
 * Creates nested arrays as needed.
 * 
 * @param string $key Configuration key in dot notation
 * @param mixed $value Value to set
 * @return bool True on success, false on failure
 * @since 3.0.0
 */
function setProjectPlanConfig($key, $value) {
    global $projectPlanConfig;
    
    $keys = explode('.', $key);
    // var_dump($config);
    $config = &$projectPlanConfig;
   
    
    foreach ($keys as $k) {
        if (!isset($config[$k]) || !is_array($config[$k])) {
            $config[$k] = [];
        }
        $config = &$config[$k];
    }
    
    $config = $value;
    return true;
}
/**
 * Check if Feature is Enabled
 * 
 * Convenience function to check if a feature is enabled.
 * 
 * @param string $feature Feature name
 * @return bool True if enabled, false otherwise
 * @since 3.0.0
 */
function isFeatureEnabled($feature) {
    return getProjectPlanConfig("features.{$feature}", false);
}

/**
 * Check if Display Option is Enabled
 * 
 * Convenience function to check if a display option is enabled.
 * 
 * @param string $option Display option name
 * @return bool True if enabled, false otherwise
 * @since 3.0.0
 */
function isDisplayEnabled($option) {
    return getProjectPlanConfig("display.{$option}", false);
}

/**
 * Get UI Setting
 * 
 * Convenience function to get a UI setting value.
 * 
 * @param string $setting UI setting name
 * @param mixed $default Default value
 * @return mixed UI setting value or default
 * @since 3.0.0
 */
function getUISetting($setting, $default = null) {
    return getProjectPlanConfig("ui.{$setting}", $default);
}

/**
 * Validate Configuration
 * 
 * Validates the configuration array for required keys and valid values.
 * 
 * @return array Validation results with errors and warnings
 * @since 3.0.0
 */
function validateProjectPlanConfig() {
    global $projectPlanConfig;
    
    $errors = [];
    $warnings = [];
    
    // Check required sections
    $requiredSections = ['features', 'display', 'ui', 'validation', 'performance', 'security'];
    foreach ($requiredSections as $section) {
        if (!isset($projectPlanConfig[$section])) {
            $errors[] = "Missing required configuration section: {$section}";
        }
    }
    
    // Validate feature toggles
    if (isset($projectPlanConfig['features'])) {
        $requiredFeatures = ['phaseManagement', 'taskManagement', 'subtaskManagement'];
        foreach ($requiredFeatures as $feature) {
            if (!isset($projectPlanConfig['features'][$feature])) {
                $warnings[] = "Missing feature toggle: {$feature}";
            }
        }
    }
    
    // Validate display settings
    if (isset($projectPlanConfig['display'])) {
        if (!isset($projectPlanConfig['display']['itemsPerPage']) || 
            !is_numeric($projectPlanConfig['display']['itemsPerPage']) ||
            $projectPlanConfig['display']['itemsPerPage'] <= 0) {
            $errors[] = "Invalid itemsPerPage setting";
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'warnings' => $warnings
    ];
}

// Validate configuration on load
$configValidation = validateProjectPlanConfig();
if (!$configValidation['valid']) {
    error_log('Project Plan Configuration Validation Failed: ' . implode(', ', $configValidation['errors']));
}

// Log warnings if any
if (!empty($configValidation['warnings'])) {
    error_log('Project Plan Configuration Warnings: ' . implode(', ', $configValidation['warnings']));
}

/**
 * Global Config Compatibility Functions
 * ====================================
 * These functions provide compatibility with the global config system
 * and allow the project plan config to work seamlessly with the main config.
 */

/**
 * Get Configuration Value (Global Compatible)
 * 
 * This function works with both the local project plan config and the global config.
 * It first checks the global config, then falls back to the local config.
 * 
 * @param string $key Configuration key in dot notation
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value or default
 */
function getProjectPlanConfigGlobal($key = null, $default = null) {
    // First try to get from global config if available
    if (function_exists('getConfig')) {
        $globalValue = getConfig("projectPlan.{$key}", null);
        if ($globalValue !== null) {
            return $globalValue;
        }
    }
    
    // Fall back to local config
    return getProjectPlanConfig($key, $default);
}

/**
 * Set Configuration Value (Global Compatible)
 * 
 * This function sets values in both the global config and local config.
 * 
 * @param string $key Configuration key in dot notation
 * @param mixed $value Value to set
 * @return bool True on success, false on failure
 */
function setProjectPlanConfigGlobal($key, $value) {
    $success = true;
    
    // Set in global config if available
    if (function_exists('setConfig')) {
        $success = setConfig("projectPlan.{$key}", $value) && $success;
    }
    
    // Also set in local config
    $success = setProjectPlanConfig($key, $value) && $success;
    
    return $success;
}

/**
 * Check if Feature is Enabled (Global Compatible)
 * 
 * This function checks both global and local config for feature status.
 * 
 * @param string $feature Feature name
 * @return bool True if enabled, false otherwise
 */
function isFeatureEnabledGlobal($feature) {
    // First try global config
    if (function_exists('isProjectPlanFeatureEnabled')) {
        $globalValue = isProjectPlanFeatureEnabled($feature);
        if ($globalValue !== false) {
            return $globalValue;
        }
    }
    
    // Fall back to local config
    return isFeatureEnabled($feature);
}

/**
 * Get UI Setting (Global Compatible)
 * 
 * This function gets UI settings from both global and local config.
 * 
 * @param string $setting UI setting name
 * @param mixed $default Default value
 * @return mixed UI setting value or default
 */
function getUISettingGlobal($setting, $default = null) {
    // First try global config
    if (function_exists('getProjectPlanUISetting')) {
        $globalValue = getProjectPlanUISetting($setting, null);
        if ($globalValue !== null) {
            return $globalValue;
        }
    }
    
    // Fall back to local config
    return getUISetting($setting, $default);
}

/**
 * Get Display Setting (Global Compatible)
 * 
 * This function gets display settings from both global and local config.
 * 
 * @param string $setting Display setting name
 * @param mixed $default Default value
 * @return mixed Display setting value or default
 */
function getDisplaySettingGlobal($setting, $default = null) {
    // First try global config
    if (function_exists('getProjectPlanDisplaySetting')) {
        $globalValue = getProjectPlanDisplaySetting($setting, null);
        if ($globalValue !== null) {
            return $globalValue;
        }
    }
    
    // Fall back to local config
    return isDisplayEnabled($setting) ? true : $default;
}
