# Project Plan System - Modular Architecture

## Overview

The Project Plan System has been completely refactored using a modular architecture to improve maintainability, scalability, and ease of updates. The system is now organized into distinct layers with clear separation of concerns.

## Architecture

### 1. Configuration Layer (`config/`)

- **File**: `project_plan_config.php`
- **Purpose**: Centralized configuration management
- **Features**:
  - Feature toggles for easy enable/disable of functionality
  - UI settings and theme configuration
  - Validation rules and constraints
  - Display options and preferences

### 2. Data Layer (`data/`)

- **File**: `project_plan_data.php`
- **Purpose**: Data preparation and validation
- **Features**:
  - Data loading and preparation functions
  - Input validation and sanitization
  - Error handling and logging
  - Data transformation utilities

### 3. Business Logic Layer (`logic/`)

- **File**: `project_plan_logic.php`
- **Purpose**: Business rules and calculations
- **Features**:
  - Progress calculations
  - Timeline management
  - Resource allocation
  - Validation rules
  - Project summary generation

### 4. Presentation Layer (`presentation/`)

- **File**: `project_plan_presentation.php`
- **Purpose**: UI generation and formatting
- **Features**:
  - HTML template generation
  - UI component rendering
  - Data formatting for display
  - Responsive design handling

### 5. JavaScript Layer (`js/`)

- **File**: `project_plan_manager.js`
- **Purpose**: Client-side functionality
- **Features**:
  - State management
  - Event handling
  - API communication
  - UI updates and animations

## File Structure

```
html/includes/scripts/projects/
├── project_plan.php                 # Main entry point
├── project_plan_backup.php          # Backup of original file
├── project_plan_modular.php         # Modular version
├── config/
│   └── project_plan_config.php      # Configuration layer
├── data/
│   └── project_plan_data.php        # Data layer
├── logic/
│   └── project_plan_logic.php       # Business logic layer
├── presentation/
│   └── project_plan_presentation.php # Presentation layer
├── js/
│   └── project_plan_manager.js      # JavaScript layer
└── README.md                        # This documentation
```

## Key Features

### 1. Modular Architecture

- **Separation of Concerns**: Each layer has a specific responsibility
- **Loose Coupling**: Components can be updated independently
- **High Cohesion**: Related functionality is grouped together
- **Easy Testing**: Each layer can be tested in isolation

### 2. Configuration Management

- **Centralized Settings**: All configuration in one place
- **Feature Toggles**: Easy enable/disable of functionality
- **Environment Specific**: Different settings for different environments
- **Runtime Updates**: Configuration can be updated without code changes

### 3. Data Management

- **Validation**: Comprehensive input validation
- **Sanitization**: XSS and SQL injection prevention
- **Error Handling**: Robust error handling and logging
- **Caching**: Performance optimization through caching

### 4. Business Logic

- **Progress Calculations**: Automatic progress calculation
- **Timeline Management**: Project timeline tracking
- **Resource Allocation**: Resource management and tracking
- **Validation Rules**: Business rule validation

### 5. Presentation

- **Responsive Design**: Works on all device sizes
- **Accessibility**: WCAG 2.1 AA compliance
- **Performance**: Optimized for fast loading
- **User Experience**: Intuitive and user-friendly interface

### 6. JavaScript Functionality

- **State Management**: Centralized state management
- **Event Handling**: Efficient event handling
- **API Communication**: AJAX communication with backend
- **UI Updates**: Real-time UI updates

## Usage

### 1. Basic Usage

```php
// Include the main file
include 'project_plan.php';
```

### 2. Configuration

```php
// Get configuration
$config = getProjectPlanConfig();

// Modify configuration
$config['features']['phaseManagement'] = true;
$config['ui']['theme'] = 'dark';
```

### 3. Data Access

```php
// Get project data
$planData = prepareProjectPlanData($teamMembers, $projectDetails, $DBConn);

// Access specific data
$phases = $planData['phases'];
$tasks = $planData['tasks'];
```

### 4. Business Logic

```php
// Get business logic manager
$logic = getProjectPlanLogic($config);

// Calculate progress
$progress = $logic->calculatePhaseProgress($phaseData);

// Validate data
$validation = $logic->validatePhaseData($phaseData);
```

### 5. Presentation

```php
// Get presentation manager
$presentation = getProjectPlanPresentation($config);

// Render components
echo $presentation->renderProjectPlanHeader($projectData);
echo $presentation->renderPhaseList($phases);
```

## Configuration Options

### Feature Toggles

```php
$config['features'] = [
    'phaseManagement' => true,      // Enable phase management
    'taskManagement' => true,       // Enable task management
    'subtaskManagement' => true,    // Enable subtask management
    'assigneeManagement' => true,   // Enable assignee management
    'timelineManagement' => true,   // Enable timeline management
    'progressTracking' => true      // Enable progress tracking
];
```

### UI Settings

```php
$config['ui'] = [
    'theme' => 'light',             // Theme: light, dark
    'responsive' => true,           // Enable responsive design
    'animations' => true,           // Enable animations
    'tooltips' => true,             // Enable tooltips
    'modals' => true,               // Enable modals
    'collapsiblePhases' => true     // Enable collapsible phases
];
```

### Display Options

```php
$config['display'] = [
    'showPhaseTimeline' => true,    // Show phase timeline
    'showTaskProgress' => true,     // Show task progress
    'showAssigneeAvatars' => true,  // Show assignee avatars
    'showTaskWeighting' => true,    // Show task weighting
    'showSubTasks' => true,         // Show subtasks
    'enableDragDrop' => false,      // Enable drag and drop
    'itemsPerPage' => 10            // Items per page
];
```

## API Reference

### Configuration Functions

- `getProjectPlanConfig()` - Get configuration array
- `setProjectPlanConfig($config)` - Set configuration array

### Data Functions

- `prepareProjectPlanData($teamMembers, $projectDetails, $DBConn)` - Prepare project data
- `validateProjectData($data)` - Validate project data
- `sanitizeProjectData($data)` - Sanitize project data

### Business Logic Functions

- `getProjectPlanLogic($config)` - Get business logic manager
- `calculatePhaseProgress($phaseData)` - Calculate phase progress
- `calculateTaskProgress($taskData)` - Calculate task progress
- `validatePhaseData($phaseData)` - Validate phase data
- `validateTaskData($taskData)` - Validate task data

### Presentation Functions

- `getProjectPlanPresentation($config)` - Get presentation manager
- `renderProjectPlanHeader($projectData)` - Render project header
- `renderPhaseList($phases)` - Render phases list
- `renderTaskCard($task)` - Render task card
- `renderSubtaskCard($subtask)` - Render subtask card

## Security Considerations

### 1. Input Validation

- All user inputs are validated using business rules
- SQL injection prevention through prepared statements
- XSS prevention through output escaping

### 2. Access Control

- User permission validation
- Project access control
- Role-based access control

### 3. Data Sanitization

- Input sanitization using Utility::clean_string()
- Output escaping using htmlspecialchars()
- CSRF protection for form submissions

## Performance Optimizations

### 1. Caching

- Configuration caching
- Data caching
- Template caching

### 2. Lazy Loading

- Lazy loading of project components
- Lazy loading of images and assets
- Lazy loading of JavaScript modules

### 3. Database Optimization

- Efficient queries
- Query result caching
- Database connection pooling

### 4. Frontend Optimization

- Minified CSS and JavaScript
- Compressed images
- CDN for static assets

## Browser Support

- **Chrome**: 90+
- **Firefox**: 88+
- **Safari**: 14+
- **Edge**: 90+

## Mobile Support

- **Responsive Design**: Works on all screen sizes
- **Touch Interactions**: Touch-friendly interface
- **Performance**: Optimized for mobile devices
- **Offline Support**: Basic offline functionality

## Accessibility

- **WCAG 2.1 AA Compliance**: Meets accessibility standards
- **Keyboard Navigation**: Full keyboard support
- **Screen Reader Support**: Compatible with screen readers
- **High Contrast Mode**: Support for high contrast
- **Reduced Motion**: Respects user preferences

## Testing

### 1. Unit Testing

- Each layer can be tested independently
- Mock objects for dependencies
- Test coverage for all functions

### 2. Integration Testing

- Test layer interactions
- Test API endpoints
- Test database operations

### 3. End-to-End Testing

- Test complete user workflows
- Test cross-browser compatibility
- Test mobile responsiveness

## Maintenance

### 1. Regular Updates

- Keep dependencies updated
- Apply security patches
- Update documentation

### 2. Performance Monitoring

- Monitor page load times
- Monitor database performance
- Monitor user experience

### 3. Error Monitoring

- Log errors and exceptions
- Monitor error rates
- Fix issues promptly

## Troubleshooting

### Common Issues

1. **Configuration Not Loading**

   - Check file permissions
   - Verify file paths
   - Check PHP error logs

2. **Data Not Displaying**

   - Check database connection
   - Verify data preparation
   - Check validation rules

3. **JavaScript Errors**

   - Check browser console
   - Verify JavaScript files
   - Check for conflicts

4. **Performance Issues**
   - Check database queries
   - Monitor memory usage
   - Check for caching issues

### Debug Mode

Enable debug mode in configuration:

```php
$config['debug'] = true;
```

This will provide additional logging and error information.

## Contributing

### 1. Code Style

- Follow PSR-12 coding standards
- Use meaningful variable names
- Add comprehensive comments

### 2. Documentation

- Update documentation for changes
- Add examples for new features
- Keep README up to date

### 3. Testing

- Add tests for new features
- Ensure all tests pass
- Maintain test coverage

## Version History

### Version 3.0.0 (Current)

- Complete modular architecture refactor
- Improved performance and maintainability
- Enhanced user experience
- Better accessibility support
- Comprehensive documentation

### Version 2.0.0

- Initial refactoring
- Basic modular structure
- Improved code organization

### Version 1.0.0

- Original implementation
- Monolithic structure
- Basic functionality

## Support

For support and questions:

- **Email**: support@skm.co.ke
- **Documentation**: This README file
- **Code Comments**: Inline documentation in code
- **Issue Tracking**: GitHub issues (if applicable)

## License

This project is proprietary software owned by SKM Development Team.
All rights reserved.
