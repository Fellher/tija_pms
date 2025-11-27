<!-- Enhanced Styles -->
<style>
    /* ============================================================================
    ENHANCED LEAVE MANAGEMENT STYLES
    ============================================================================ */

    /* View Navigation */
    .nav-pills .nav-link {
        border-radius: 8px;
        margin-right: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .nav-pills .nav-link:hover {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
    }

    .nav-pills .nav-link.active {
        background-color: #007bff;
        color: white;
    }

    /* View Container Management */
    .view-container {
        display: none;
        width: 100%;
    }

    .view-container.active {
        display: block;
    }

    /* Metric Icons */
    .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Leave Balance Cards */
    .leave-balance-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        margin-bottom: 1rem;
    }

    .leave-balance-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    .leave-balance-card.priority-high {
        border-left: 4px solid #dc3545;
    }

    .leave-balance-card.priority-medium {
        border-left: 4px solid #ffc107;
    }

    .leave-balance-card.priority-low {
        border-left: 4px solid #28a745;
    }

    /* Leave Application Cards */
    .leave-application-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
    }

    .leave-application-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }

    /* Status Badges */
    .leave-status-badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
    }

    .status-pending { background-color: #fff3cd; color: #664d03; }
    .status-approved { background-color: #d1e7dd; color: #0f5132; }
    .status-rejected { background-color: #f8d7da; color: #842029; }
    .status-draft { background-color: #e9ecef; color: #495057; }

    /* Approval Workflow */
    .approval-workflow {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 1rem 0;
    }

    .approval-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
    }

    .approval-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 20px;
        right: -50%;
        width: 100%;
        height: 2px;
        background: #dee2e6;
        z-index: 1;
    }

    .approval-step.completed::after {
        background: #28a745;
    }

    .approval-step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e9ecef;
        color: #6c757d;
        margin-bottom: 0.5rem;
        z-index: 2;
        position: relative;
    }

    .approval-step.completed .approval-step-icon {
        background: #28a745;
        color: white;
    }

    .approval-step.current .approval-step-icon {
        background: #007bff;
        color: white;
    }

    /* Calendar Styling */
    .leave-calendar {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .calendar-day.has-leave {
        background-color: rgba(0, 123, 255, 0.1);
        border: 1px solid #007bff;
    }

    .calendar-day.holiday {
        background-color: rgba(255, 193, 7, 0.1);
        border: 1px solid #ffc107;
    }

    /* Project Clearance */
    .project-clearance-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }

    .clearance-status {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .clearance-status.pending {
        background: #fff3cd;
        color: #664d03;
    }

    .clearance-status.approved {
        background: #d1e7dd;
        color: #0f5132;
    }

    .clearance-status.required {
        background: #f8d7da;
        color: #842029;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .nav-pills {
            flex-direction: column;
            width: 100%;
        }

        .nav-pills .nav-link {
            margin-right: 0;
            margin-bottom: 0.5rem;
        }

        .approval-workflow {
            flex-direction: column;
            gap: 0.5rem;
        }

        .approval-step:not(:last-child)::after {
            display: none;
        }
    }

    /* Toast Notifications */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1055;
    }

    /* Loading States */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Form Enhancements */
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        opacity: 0.65;
        transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
    }

    /* File Upload Area */
    .file-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .file-upload-area:hover {
        border-color: #007bff;
        background-color: rgba(0, 123, 255, 0.05);
    }

    .file-upload-area.dragover {
        border-color: #007bff;
        background-color: rgba(0, 123, 255, 0.1);
    }

    /* Leave Type Cards */
    .leave-type-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .leave-type-card:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .leave-type-card.selected {
        border-color: #007bff;
        background-color: rgba(0, 123, 255, 0.05);
    }

    /* Holiday Calendar */
    .holiday-calendar {
        max-height: 400px;
        overflow-y: auto;
    }

    .holiday-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }

    .holiday-item:last-child {
        border-bottom: none;
    }

    .holiday-date {
        font-weight: 500;
        color: #495057;
    }

    .holiday-name {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .holiday-jurisdiction {
        background: #e9ecef;
        color: #495057;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
    }

    /* Dashboard Specific Styles */
    .leave-balance-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        height: 100%;
    }

    .leave-balance-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        border-color: #007bff;
    }

    .leave-type-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 123, 255, 0.1);
        border-radius: 50%;
    }

    .balance-main {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }

    .available-days {
        color: #007bff;
        line-height: 1;
    }

    .balance-label {
        font-size: 0.8rem;
    }

    .upcoming-leave-item {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .leave-days-badge {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .team-member-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef !important;
        transition: all 0.2s ease;
    }

    .team-member-card:hover {
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .date-range {
        font-size: 0.9rem;
    }

    .start-date {
        color: #495057;
    }

    .end-date {
        font-size: 0.8rem;
    }

    .member-stats {
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #dee2e6;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .leave-balance-card {
            margin-bottom: 1rem;
        }

        .team-member-card {
            margin-bottom: 1rem;
        }

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>
