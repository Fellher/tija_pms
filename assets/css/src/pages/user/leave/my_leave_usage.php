<style>
/* ============================================================================
   MY LEAVE USAGE - ANALYTICS DASHBOARD STYLES
   ============================================================================ */

/* Statistics Cards */
.stats-card {
    transition: all 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
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

.bg-primary-transparent {
    background-color: rgba(102, 126, 234, 0.1);
}

.bg-success-transparent {
    background-color: rgba(40, 167, 69, 0.1);
}

.bg-warning-transparent {
    background-color: rgba(255, 193, 7, 0.1);
}

.bg-info-transparent {
    background-color: rgba(23, 162, 184, 0.1);
}

.bg-danger-transparent {
    background-color: rgba(220, 53, 69, 0.1);
}

.bg-secondary-transparent {
    background-color: rgba(108, 117, 125, 0.1);
}

.bg-purple-transparent {
    background-color: rgba(111, 66, 193, 0.1);
}

.text-purple {
    color: #6f42c1;
}

/* Leave Type Indicator */
.leave-type-indicator {
    width: 4px;
    height: 40px;
    border-radius: 2px;
}

/* Progress Bars */
.progress-stack {
    min-width: 100px;
}

.progress {
    background-color: #e9ecef;
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Table Enhancements */
.table-hover tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.05);
    cursor: default;
}

thead.table-light th {
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    background-color: #f8f9fa !important;
}

/* Insight Cards */
.insight-item {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
}

.insight-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.insight-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

/* Stats Row */
.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.stat-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

/* Usage Circle (SVG) */
.usage-circle {
    position: relative;
    display: inline-block;
}

.usage-circle svg {
    transform: rotate(-90deg);
}

/* Comparison Boxes */
.comparison-box {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 12px;
    height: 100%;
}

.comparison-stats .stat-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
}

.comparison-stats .stat-item:last-child {
    border-bottom: none;
}

/* Button Styles */
.btn-primary-ghost {
    background-color: transparent;
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
    transition: all 0.3s ease;
}

.btn-primary-ghost:hover {
    background-color: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Gradient Card */
.bg-gradient {
    position: relative;
    overflow: hidden;
}

.bg-gradient::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: gradient-rotate 15s linear infinite;
}

@keyframes gradient-rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Period Selector */
.form-select-sm {
    border-radius: 8px;
    border-color: #dee2e6;
    padding: 0.375rem 2rem 0.375rem 0.75rem;
}

.form-select-sm:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        font-size: 1.5rem !important;
    }

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

    .comparison-box {
        margin-bottom: 1rem;
    }

    .table-responsive {
        font-size: 0.875rem;
    }
}

/* Loading States */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.spinner-border-sm {
    width: 1.5rem;
    height: 1.5rem;
}

/* Toast Container */
.toast-container {
    z-index: 1055;
}

/* Badge Enhancements */
.badge {
    padding: 0.375rem 0.75rem;
    font-weight: 500;
    border-radius: 6px;
}

/* Card Shadows */
.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
}

/* Smooth Transitions */
* {
    transition: background-color 0.2s ease, color 0.2s ease;
}

/* Chart Container */
canvas {
    max-height: 300px;
}

/* Empty State */
.empty-state {
    padding: 3rem;
    text-align: center;
}

.empty-state i {
    font-size: 3rem;
    opacity: 0.5;
}

/* Animation for cards */
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
</style>

