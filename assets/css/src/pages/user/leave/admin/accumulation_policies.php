<style>
/* Accumulation Policies Page Styles */

/* Policy Cards */
.policy-card {
    transition: transform 0.2s ease-in-out;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
}

.policy-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Rule Items */
.rule-item {
    border-left: 4px solid #0d6efd;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.rule-item:hover {
    background: #e9ecef;
    border-left-color: #0056b3;
}

/* Badges */
.accrual-type-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.priority-badge {
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    color: white;
    font-weight: 500;
}

/* Sidebar Navigation */
.sidebar-nav {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    border: 1px solid #e9ecef;
}

.sidebar-nav .nav-link {
    color: #6c757d;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.sidebar-nav .nav-link.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.sidebar-nav .nav-link:hover {
    background: #e9ecef;
    color: #0d6efd;
    border-color: #dee2e6;
}

.sidebar-nav .nav-link i {
    width: 20px;
    text-align: center;
}

/* Statistics Cards */
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    border: none;
    overflow: hidden;
}

.stats-card .card-body {
    padding: 1.5rem;
}

.stats-card .stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

/* Form Sections */
.form-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
}

.section-title {
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

/* Form Controls */
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Buttons */
.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Tables */
.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

/* Alerts */
.alert {
    border-radius: 8px;
    border: none;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-success {
    background-color: #d1edff;
    color: #0c5460;
}

/* Loading States */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar-nav {
        margin-bottom: 1rem;
    }
    
    .policy-card {
        margin-bottom: 1rem;
    }
    
    .form-section {
        padding: 1rem;
    }
    
    .stats-card .card-body {
        padding: 1rem;
    }
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-in {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(-20px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Tooltip Styles */
.tooltip-inner {
    background-color: #333;
    color: white;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

/* Modal Styles */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-header {
    border-bottom: 1px solid #e9ecef;
    border-radius: 12px 12px 0 0;
}

.modal-footer {
    border-top: 1px solid #e9ecef;
    border-radius: 0 0 12px 12px;
}

/* Card Enhancements */
.card {
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-radius: 12px 12px 0 0 !important;
}

/* Status Indicators */
.status-active {
    color: #28a745;
    font-weight: 500;
}

.status-inactive {
    color: #dc3545;
    font-weight: 500;
}

.status-pending {
    color: #ffc107;
    font-weight: 500;
}

/* Icon Styles */
.icon-lg {
    font-size: 1.5rem;
}

.icon-xl {
    font-size: 2rem;
}

/* Text Utilities */
.text-muted-light {
    color: #6c757d !important;
}

.text-primary-dark {
    color: #0056b3 !important;
}

/* Background Utilities */
.bg-light-primary {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.bg-light-success {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.bg-light-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-light-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
}
</style>