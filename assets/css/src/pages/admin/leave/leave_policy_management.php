<style>
/* Enhanced Enterprise-Grade Styles for Leave Policy Management */

:root {
    --enterprise-primary: #0052CC;
    --enterprise-secondary: #5E6C84;
    --enterprise-success: #00875A;
    --enterprise-danger: #DE350B;
    --enterprise-warning: #FF8B00;
    --enterprise-info: #0065FF;
    --enterprise-dark: #172B4D;
    --enterprise-light: #F4F5F7;
    --enterprise-border: #DFE1E6;
    --sidebar-bg: linear-gradient(180deg, #FFFFFF 0%, #F9FAFB 100%);
    --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.06);
    --card-shadow-hover: 0 10px 25px rgba(0, 0, 0, 0.08), 0 6px 10px rgba(0, 0, 0, 0.04);
}

/* Sidebar Navigation - Enterprise Style */
.enterprise-sidebar {
    background: var(--sidebar-bg);
    border-right: 1px solid var(--enterprise-border);
    min-height: calc(100vh - 100px);
    padding: 2rem 1rem;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.02);
}

.sidebar-header {
    padding: 1.25rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid var(--enterprise-primary);
    background: white;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
}

.sidebar-header h5 {
    color: var(--enterprise-dark);
    font-weight: 600;
    font-size: 1.125rem;
    margin: 0;
    display: flex;
    align-items: center;
}

.sidebar-header i {
    color: var(--enterprise-primary);
    font-size: 1.375rem;
}

.enterprise-sidebar .nav-link {
    color: var(--enterprise-secondary);
    padding: 0.875rem 1.25rem;
    border-radius: 10px;
    margin-bottom: 0.5rem;
    font-weight: 500;
    font-size: 0.9375rem;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    border: 1px solid transparent;
    position: relative;
}

.enterprise-sidebar .nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 0;
    background: var(--enterprise-primary);
    transition: height 0.3s ease;
}

.enterprise-sidebar .nav-link:hover {
    background: rgba(0, 82, 204, 0.08);
    color: var(--enterprise-primary);
    border-color: rgba(0, 82, 204, 0.2);
    transform: translateX(4px);
}

.enterprise-sidebar .nav-link:hover::before {
    height: 24px;
}

.enterprise-sidebar .nav-link.active {
    background: var(--enterprise-primary);
    color: white;
    box-shadow: 0 4px 12px rgba(0, 82, 204, 0.3);
}

.enterprise-sidebar .nav-link.active::before {
    height: 100%;
    background: white;
}

.enterprise-sidebar .nav-link i {
    width: 24px;
    font-size: 1.125rem;
}

/* Main Content Area */
.enterprise-main-content {
    padding: 2rem;
    background: #FAFBFC;
}

/* Page Header - Enterprise Style */
.enterprise-page-header {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    margin-bottom: 2rem;
    border-left: 5px solid var(--enterprise-primary);
    position: relative;
    overflow: hidden;
}

.enterprise-page-header::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(0, 82, 204, 0.05) 0%, transparent 70%);
    pointer-events: none;
}

.enterprise-page-header h2 {
    color: var(--enterprise-dark);
    font-weight: 600;
    font-size: 1.875rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.enterprise-page-header .subtitle {
    color: var(--enterprise-secondary);
    font-size: 1rem;
    font-weight: 400;
}

.page-header-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

/* Help Button - Top Right */
.help-button-inline {
    background: white;
    border: 2px solid var(--enterprise-primary);
    color: var(--enterprise-primary);
    padding: 0.625rem 1.25rem;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.help-button-inline:hover {
    background: var(--enterprise-primary);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 82, 204, 0.3);
}

.help-button-inline i {
    font-size: 1.125rem;
}

/* Alert Enhancements */
.alert {
    border: none;
    border-left: 5px solid;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
    padding: 1.25rem 1.5rem;
}

.alert-danger {
    border-left-color: var(--enterprise-danger);
    background: #FFEBE6;
    color: #DE350B;
}

.alert-success {
    border-left-color: var(--enterprise-success);
    background: #E3FCEF;
    color: #006644;
}

.alert i {
    font-size: 1.25rem;
}

/* Action Buttons */
.btn-enterprise {
    padding: 0.75rem 1.75rem;
    font-weight: 500;
    border-radius: 10px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    border: none;
}

.btn-enterprise:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.btn-primary {
    background: var(--enterprise-primary);
}

.btn-primary:hover {
    background: #0043A8;
}

/* Cards */
.enterprise-card {
    background: white;
    border-radius: 12px;
    box-shadow: var(--card-shadow);
    transition: all 0.3s ease;
    border: 1px solid var(--enterprise-border);
}

.enterprise-card:hover {
    box-shadow: var(--card-shadow-hover);
}

/* Help Modal Styling */
.help-modal .modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.help-modal .modal-header {
    background: linear-gradient(135deg, var(--enterprise-primary) 0%, #0043A8 100%);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 2rem 2.5rem;
    border-bottom: none;
}

.help-modal .modal-title {
    font-weight: 600;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.help-modal .modal-body {
    padding: 2.5rem;
    max-height: 70vh;
    overflow-y: auto;
}

.help-step {
    padding: 1.5rem;
    margin-bottom: 1.25rem;
    border-radius: 12px;
    background: var(--enterprise-light);
    border-left: 4px solid var(--enterprise-primary);
    transition: all 0.3s ease;
}

.help-step:hover {
    background: #E9ECEF;
    transform: translateX(4px);
    box-shadow: var(--card-shadow);
}

.help-step-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--enterprise-primary) 0%, #0065FF 100%);
    color: white;
    border-radius: 12px;
    font-weight: 700;
    margin-right: 1rem;
    box-shadow: 0 4px 8px rgba(0, 82, 204, 0.2);
}

.help-category {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--enterprise-border);
    box-shadow: var(--card-shadow);
}

.help-category-title {
    color: var(--enterprise-primary);
    font-weight: 600;
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid var(--enterprise-primary);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.feature-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: rgba(0, 82, 204, 0.1);
    color: var(--enterprise-primary);
    border-radius: 12px;
}

.tip-box {
    background: linear-gradient(135deg, #FFF9E6 0%, #FFF4D1 100%);
    border-left: 4px solid var(--enterprise-warning);
    padding: 1.25rem 1.5rem;
    border-radius: 12px;
    margin: 1.5rem 0;
}

.tip-box i {
    color: var(--enterprise-warning);
    font-size: 1.25rem;
    margin-right: 0.75rem;
}

/* Floating Help Button */
.help-button-float {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--enterprise-primary) 0%, #0065FF 100%);
    color: white;
    border: none;
    border-radius: 50%;
    box-shadow: 0 8px 24px rgba(0, 82, 204, 0.4);
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
}

.help-button-float:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 32px rgba(0, 82, 204, 0.5);
    background: linear-gradient(135deg, #0043A8 0%, #0052CC 100%);
}

.help-button-float:active {
    transform: scale(0.95);
}

.help-button-float i {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* Policy Configuration Specific Styles */
.stats-card {
    transition: all 0.3s ease;
    border: none;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
}

.stats-icon {
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50% !important;
}

.stats-icon i {
    font-size: 2rem !important;
}

.policy-card {
    transition: all 0.3s ease;
}

.policy-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.config-stepper {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.config-stepper .step {
    position: relative;
    opacity: 0.5;
    cursor: pointer;
    transition: all 0.3s ease;
}

.config-stepper .step.active,
.config-stepper .step.completed {
    opacity: 1;
}

.config-stepper .step-icon {
    width: 50px;
    height: 50px;
    margin: 0 auto 0.5rem;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #6c757d;
}

.config-stepper .step.active .step-icon {
    background: linear-gradient(135deg, var(--enterprise-primary) 0%, var(--enterprise-info) 100%);
    color: white;
}

.config-stepper .step.completed .step-icon {
    background: var(--enterprise-success);
    color: white;
}

.config-stepper .step-label {
    font-size: 0.875rem;
    font-weight: 500;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #0052CC 0%, #0065FF 100%);
}

.bg-purple {
    background: linear-gradient(135deg, #6f42c1 0%, #8b5cf6 100%);
}

.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.checklist-item {
    font-size: 0.875rem;
}

.quick-stats .stat-value {
    font-size: 1.25rem;
}

.quick-stats .stat-label {
    font-size: 0.75rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .enterprise-sidebar {
        border-right: none;
        border-bottom: 1px solid var(--enterprise-border);
        min-height: auto;
        padding: 1rem;
    }

    .enterprise-page-header {
        padding: 1.5rem;
    }

    .help-category {
        padding: 1.5rem;
    }

    .help-button-float {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
        bottom: 20px;
        right: 20px;
    }

    .config-stepper {
        padding: 1rem;
    }

    .config-stepper .step-label {
        font-size: 0.75rem;
    }
}
</style>

