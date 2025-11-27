/* Leave Policy Management Page Styles */

/* Policy Cards */
.policy-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    background: white;
}

.policy-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: #0d6efd;
}

.policy-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem;
    border-bottom: none;
}

.policy-card-body {
    padding: 1.5rem;
}

/* Leave Type Cards */
.leave-type-card {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    background: white;
    transition: all 0.2s ease;
}

.leave-type-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
}

.leave-type-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f3f4;
}

.leave-type-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.leave-type-code {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Status Badges */
.status-badge {
    font-size: 0.75rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 500;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-suspended {
    background: #f8d7da;
    color: #721c24;
}

.status-lapsed {
    background: #fff3cd;
    color: #856404;
}

/* Sidebar Navigation */
.sidebar-nav {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.sidebar-nav .nav-link {
    color: #6c757d;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    text-decoration: none;
}

.sidebar-nav .nav-link.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.sidebar-nav .nav-link:hover {
    background: #e9ecef;
    color: #0d6efd;
    border-color: #dee2e6;
    transform: translateX(2px);
}

.sidebar-nav .nav-link i {
    width: 20px;
    text-align: center;
    margin-right: 0.5rem;
}

/* Statistics Cards */
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    border: none;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.stats-card .card-body {
    padding: 1.5rem;
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stats-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

/* Form Sections */
.form-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.section-title {
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.75rem;
    margin-bottom: 1.5rem;
    font-weight: 600;
    font-size: 1.1rem;
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

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
}

/* Buttons */
.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.btn-outline-primary {
    border-color: #0d6efd;
    color: #0d6efd;
    border-radius: 8px;
    font-weight: 500;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
    transform: translateY(-1px);
}

.btn-outline-danger {
    border-color: #dc3545;
    color: #dc3545;
    border-radius: 8px;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    transform: translateY(-1px);
}

.btn-outline-success {
    border-color: #198754;
    color: #198754;
    border-radius: 8px;
}

.btn-outline-success:hover {
    background-color: #198754;
    border-color: #198754;
    transform: translateY(-1px);
}

/* Tables */
.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

/* Alerts */
.alert {
    border-radius: 10px;
    border: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-success {
    background-color: #d1edff;
    color: #0c5460;
}

.alert-warning {
    background-color: #fff3cd;
    color: #856404;
}

.alert-info {
    background-color: #d1ecf1;
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
    border-radius: 12px;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Search and Filter */
.search-container {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.search-input {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}

.search-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
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
    
    .leave-type-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.4s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
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

.scale-in {
    animation: scaleIn 0.3s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
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
    background: #f8f9fa;
}

.modal-footer {
    border-top: 1px solid #e9ecef;
    border-radius: 0 0 12px 12px;
    background: #f8f9fa;
}

/* Card Enhancements */
.card {
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-radius: 12px 12px 0 0 !important;
}

/* Icon Styles */
.icon-lg {
    font-size: 1.5rem;
}

.icon-xl {
    font-size: 2rem;
}

.icon-primary {
    color: #0d6efd;
}

.icon-success {
    color: #198754;
}

.icon-warning {
    color: #ffc107;
}

.icon-danger {
    color: #dc3545;
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
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-light-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-light-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.action-btn {
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.action-btn:hover {
    transform: translateY(-1px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.empty-state-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state-title {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: #495057;
}

.empty-state-description {
    margin-bottom: 1.5rem;
}

/* Progress Bars */
.progress {
    height: 8px;
    border-radius: 4px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 4px;
    transition: width 0.6s ease;
}

/* Badge Styles */
.badge {
    font-size: 0.75rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 500;
}

.badge-primary {
    background-color: #0d6efd;
    color: white;
}

.badge-success {
    background-color: #198754;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #000;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}

.badge-info {
    background-color: #0dcaf0;
    color: #000;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}
