# Leave Analytics Dashboard Implementation

## 1. Create New Page Structure

Create [`html/pages/user/leave/leave_analytics.php`](html/pages/user/leave/leave_analytics.php):

- Access control: HR Managers and Admins only
- Multi-tab interface with time period filters
- Responsive grid layout for metrics cards and charts

## 2. Backend Analytics Functions

Add to [`php/classes/leave.php`](php/classes/leave.php):

**Core analytics functions:**

- `get_organization_leave_analytics($orgDataID, $entityID, $startDate, $endDate, $DBConn)` - Organization-wide metrics
- `get_departmental_leave_breakdown($orgDataID, $entityID, $period, $DBConn)` - By department/business unit
- `get_leave_type_distribution($orgDataID, $entityID, $period, $DBConn)` - By leave type
- `get_approval_workflow_metrics($orgDataID, $entityID, $period, $DBConn)` - Processing times, rejection rates
- `get_concurrent_absence_analysis($orgDataID, $entityID, $startDate, $endDate, $DBConn)` - Workforce impact
- `get_employee_leave_detailed($employeeID, $year, $DBConn)` - Individual drill-down

**Key metrics to include:**

- Total leave days taken vs available
- Utilization rate by department/employee
- Average approval time
- Rejection rate and reasons
- Peak absence periods
- Concurrent absence risks
- Leave balance trends

## 3. Frontend Dashboard Components

**Main Dashboard View:**

- Executive Summary Cards (4 key metrics at top)
- Period Selector (Week/Month/Quarter/Semi-Annual/Annual)
- Date Range Picker with presets

**Visualization Tabs:**

**Tab 1: Overview Dashboard**

- Leave utilization gauge chart
- Monthly trend line chart
- Leave type distribution pie chart
- Department comparison bar chart

**Tab 2: Workforce Impact**

- Concurrent absences calendar heatmap
- Team coverage table
- High-risk periods alerts
- Department headcount vs absences

**Tab 3: Approval Workflow Analysis**

- Average approval time by step
- Rejection rate trends
- Bottleneck identification
- Approver performance metrics

**Tab 4: Employee Drill-Down**

- Searchable employee table
- Individual leave history
- Click to view application details
- Export individual reports

**Tab 5: Departmental Analysis**

- Department-wise breakdown
- Utilization by business unit
- Comparative analysis
- Export department reports

## 4. Export Functionality

Create [`php/scripts/leave/reports/export_leave_analytics.php`](php/scripts/leave/reports/export_leave_analytics.php):

- Export to Excel (using PHPSpreadsheet or PHPExcel)
- Export to PDF (board-ready format)
- Parameterized by period and filters

## 5. Chart Integration

Add Chart.js library (if not present):

- CDN or local file in [`html/assets/libs/chartjs/`](html/assets/libs/chartjs/)
- Custom chart configurations for each visualization
- Responsive and interactive charts
- Export chart as image for PDF reports

## 6. SQL Optimization

Create database views or optimize queries for:

- Organization-wide leave aggregations
- Time-series data (daily/weekly/monthly rollups)
- Department summaries
- Consider caching for large datasets

## 7. UI/UX Features

- Real-time filtering without page reload
- Click-through navigation (chart → detailed table → individual application)
- Tooltips with contextual information
- Exportable summary for board meetings
- Print-friendly view option
- Bookmark/save custom filters

## Key Design Decisions

**Data aggregation strategy:** Use SQL window functions and CTEs for efficient multi-dimensional analysis
**Chart library:** Chart.js (lightweight, responsive, well-documented)
**Export library:** PHPSpreadsheet for Excel, TCPDF or mPDF for PDF
**Performance:** Implement date range limits and pagination for large datasets
**Access control:** Validate HR Manager scope (entity-level vs global) using existing `Employee::get_hr_manager_scope()`

## Implementation Status

All tasks completed:
- ✅ Created main analytics page with access control and layout
- ✅ Added 6 core analytics functions to Leave class
- ✅ Built executive summary cards with key metrics
- ✅ Created Overview tab with utilization & trend charts
- ✅ Created Workforce Impact tab with heatmaps & alerts
- ✅ Created Approval Workflow Analysis tab
- ✅ Created Employee Drill-Down tab with search & details
- ✅ Created Departmental Analysis tab
- ✅ Integrated Chart.js and configured all visualizations
- ✅ Built Excel/PDF export functionality
- ✅ Added navigation menu items for Leave Analytics page

## Files Created

### Main Page
- `html/pages/user/leave/leave_analytics.php` - Main dashboard page with filters and tab navigation

### Tab Components
- `html/includes/scripts/leave/analytics/overview_tab.php` - Overview dashboard with KPIs
- `html/includes/scripts/leave/analytics/workforce_tab.php` - Workforce impact analysis
- `html/includes/scripts/leave/analytics/workflow_tab.php` - Approval workflow metrics
- `html/includes/scripts/leave/analytics/employees_tab.php` - Employee drill-down
- `html/includes/scripts/leave/analytics/departments_tab.php` - Departmental analysis

### JavaScript
- `html/includes/scripts/leave/analytics/charts_config.js` - Chart.js configurations

### Backend Scripts
- `php/scripts/leave/analytics/get_employee_details.php` - AJAX endpoint for employee details
- `php/scripts/leave/reports/export_leave_analytics.php` - Excel/PDF export functionality

### Database Functions (in `php/classes/leave.php`)
- `get_organization_leave_analytics()` - Lines ~3920-4020
- `get_departmental_leave_breakdown()` - Lines ~4022-4100
- `get_leave_type_distribution()` - Lines ~4102-4170
- `get_approval_workflow_metrics()` - Lines ~4172-4280
- `get_concurrent_absence_analysis()` - Lines ~4282-4400
- `get_monthly_leave_trends()` - Lines ~4402-4470

### Navigation Updates
- Modified `html/includes/nav/side_nav.php` to add menu items in both user and admin sections

## Usage Instructions

### Access the Dashboard
1. Navigate to: `?s=user&ss=leave&p=leave_analytics`
2. Available to HR Managers and Admins only
3. Default view shows last 30 days (Month period)

### Filter Data
- Use the Period dropdown to select predefined ranges
- Choose "Custom Range" for specific date ranges
- Global HR managers can filter by entity/organization
- Click "Apply Filters" to refresh data

### Navigate Tabs
- **Overview**: High-level KPIs and trends
- **Workforce Impact**: Identify high-risk absence periods
- **Approval Workflow**: Analyze approval patterns and bottlenecks
- **Employee Drill-Down**: View individual employee leave history
- **Departmental Analysis**: Compare department performance

### Export Reports
- Click "Export Excel" for board-ready spreadsheet
- Click "Export PDF" for formatted document
- Click "Print" for browser print dialog

### Drill-Down
- Click department names to filter by department
- Click chart elements to view underlying data
- Click "View Details" in employee table for full history

## Future Enhancements

- Add predictive analytics for leave patterns
- Implement leave balance forecasting
- Add team overlap warnings
- Create custom report builder
- Add dashboard customization/saved views
- Implement email scheduling for reports
- Add comparison with previous periods

