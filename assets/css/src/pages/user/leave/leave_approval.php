<style>
/* ============================================================================
   LEAVE APPROVAL CENTER - ENTERPRISE EDITION STYLES
   ============================================================================ */

/* Statistics Cards */
.stats-card {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.stats-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* View Switcher */
.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}

.btn-group .btn:last-child {
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
}

/* Activity Timeline */
.activity-timeline {
    position: relative;
}

.activity-item {
    display: flex;
    padding: 1rem 0;
    border-bottom: 1px solid #e9ecef;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.activity-content {
    flex-grow: 1;
}

/* Leave Request Cards */
.leave-request-card {
    transition: all 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
}

.leave-request-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.leave-request-card.border-danger {
    border-left: 4px solid #dc3545 !important;
}

.leave-request-card.border-warning {
    border-left: 4px solid #ffc107 !important;
}

/* Avatar Styles */
.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.avatar-sm {
    width: 38px;
    height: 38px;
    font-size: 0.875rem;
}

.avatar-md {
    width: 48px;
    height: 48px;
    font-size: 1rem;
}

.avatar-rounded {
    border-radius: 50%;
}

.avatar-initials {
    font-weight: 600;
    color: #fff;
}

/* Table Enhancements */
.table-hover tbody tr {
    transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.05);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

thead.table-light th {
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    background-color: #f8f9fa !important;
}

/* Badge Styles */
.badge {
    padding: 0.375rem 0.75rem;
    font-weight: 500;
    border-radius: 6px;
}

/* Card Shadows */
.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
}

/* Calendar Specific Styles */
.fc .fc-daygrid-day {
    cursor: pointer;
}

.fc-event {
    cursor: pointer;
    padding: 2px 4px;
    margin: 1px 0;
    border-radius: 4px;
}

.fc-event:hover {
    opacity: 0.8;
}

.fc .fc-daygrid-day-number {
    padding: 4px;
    font-weight: 600;
}

/* Conflict Indicators */
.absence-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    z-index: 10;
}

/* Modal Enhancements */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-header {
    border-bottom: 1px solid #e9ecef;
    padding: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

/* Button Styles */
.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 6px;
}

.btn-success-light {
    background-color: rgba(40, 167, 69, 0.1);
    border-color: #28a745;
    color: #28a745;
}

.btn-success-light:hover {
    background-color: #28a745;
    color: white;
}

.btn-danger-light {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #dc3545;
}

.btn-danger-light:hover {
    background-color: #dc3545;
    color: white;
}

.btn-info-light {
    background-color: rgba(23, 162, 184, 0.1);
    border-color: #17a2b8;
    color: #17a2b8;
}

.btn-info-light:hover {
    background-color: #17a2b8;
    color: white;
}

.btn-warning-light {
    background-color: rgba(255, 193, 7, 0.1);
    border-color: #ffc107;
    color: #856404;
}

.btn-warning-light:hover {
    background-color: #ffc107;
    color: #212529;
}

.btn-primary-light {
    background-color: rgba(102, 126, 234, 0.1);
    border-color: #667eea;
    color: #667eea;
}

.btn-primary-light:hover {
    background-color: #667eea;
    color: white;
}

/* Transparent Badges */
.bg-primary-transparent {
    background-color: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

.bg-success-transparent {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.bg-danger-transparent {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.bg-warning-transparent {
    background-color: rgba(255, 193, 7, 0.1);
    color: #856404;
}

.bg-info-transparent {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.bg-secondary-transparent {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}

.bg-purple-transparent {
    background-color: rgba(111, 66, 193, 0.1);
    color: #6f42c1;
}

.text-purple {
    color: #6f42c1;
}

/* Page Header */
.page-header-breadcrumb {
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.page-title {
    font-weight: 500;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
    }

    .d-md-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }

    .ms-md-1 {
        margin-top: 1rem !important;
        margin-left: 0 !important;
    }

    .btn-group {
        width: 100%;
    }

    .btn-group .btn {
        flex: 1;
    }
}

/* Loading States */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}

/* Empty States */
.empty-state {
    padding: 3rem;
    text-align: center;
}

.empty-state i {
    font-size: 3rem;
    opacity: 0.5;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.5s ease-out;
}

.card:nth-child(1) { animation-delay: 0.1s; }
.card:nth-child(2) { animation-delay: 0.2s; }
.card:nth-child(3) { animation-delay: 0.3s; }
.card:nth-child(4) { animation-delay: 0.4s; }

/* Team Impact Indicator */
.team-impact-indicator {
    display: inline-block;
}

/* Urgency Indicators */
.table-warning {
    background-color: #fff3cd !important;
}

.border-danger {
    border-color: #dc3545 !important;
}

.border-warning {
    border-color: #ffc107 !important;
}

/* Smooth Transitions */
* {
    transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

/* Chart Container */
canvas {
    max-height: 300px;
}

/* Gap Utilities */
.gap-2 {
    gap: 0.5rem !important;
}

.gap-3 {
    gap: 1rem !important;
}

/* Flex Utilities */
.flex-wrap {
    flex-wrap: wrap;
}

.flex-fill {
    flex: 1 1 auto;
}

.flex-shrink-0 {
    flex-shrink: 0;
}

/* Border Radius Utilities */
.rounded-circle {
    border-radius: 50% !important;
}

.rounded-3 {
    border-radius: 0.75rem !important;
}

/* Text Utilities */
.text-uppercase {
    text-transform: uppercase;
}

.fw-semibold {
    font-weight: 600;
}

.fw-bold {
    font-weight: 700;
}

/* Team Member Cards */
.team-member-card {
    transition: all 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
}

.team-member-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.team-quick-stats {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 0.75rem;
}

.team-quick-stats .stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.team-quick-stats .stat-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.avatar-lg {
    width: 70px;
    height: 70px;
    font-size: 1.5rem;
}

/* Member Detail Modal */
#memberDetailModal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

#memberDetailModal .modal-xl {
    max-width: 1200px;
}

#memberDetailModal .card {
    border: 1px solid #e9ecef;
}

/* Status Badge Positioning */
.team-member-card .badge {
    font-size: 0.75rem;
}

/* Grid/Table View Toggle */
#cardView, #tableView {
    transition: opacity 0.3s ease;
}

/* Print Styles */
@media print {
    .btn-group,
    .btn,
    .modal {
        display: none !important;
    }

    .card {
        break-inside: avoid;
    }
}
</style>

