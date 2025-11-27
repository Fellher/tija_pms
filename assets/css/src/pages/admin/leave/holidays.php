<style>
/* Enhanced Multi-Jurisdiction Holidays Styles */

/* Jurisdiction Badges */
.jurisdiction-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Holiday Cards with Level Indicators */
.holiday-card {
    transition: all 0.3s ease;
    border-left: 4px solid;
}

.holiday-card.country-level {
    border-left-color: #0052CC;
}

.holiday-card.region-level {
    border-left-color: #00875A;
}

.holiday-card.entity-level {
    border-left-color: #FF8B00;
}

.holiday-card.global-level {
    border-left-color: #6f42c1;
}

.holiday-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

/* Applicability Indicator */
.applicability-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #F4F5F7;
    border-radius: 8px;
    font-size: 0.875rem;
}

/* Past Holiday Styling */
.table-secondary {
    opacity: 0.7;
}

/* Stats Cards */
.card.border-0.shadow-sm {
    transition: all 0.3s ease;
}

.card.border-0.shadow-sm:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

/* Modal Sections */
.modal-body h6 {
    color: #172B4D;
    font-weight: 600;
}

.modal-body .border-bottom {
    border-color: #DFE1E6 !important;
}

/* Action Buttons */
.btn-group-sm .btn {
    transition: all 0.2s ease;
}

.btn-group-sm .btn:hover {
    transform: scale(1.05);
}

/* Form Sections */
.form-label.fw-semibold {
    color: #172B4D;
    margin-bottom: 0.5rem;
}

.form-text {
    font-size: 0.875rem;
    color: #5E6C84;
}

/* Badge color variants */
.bg-purple {
    background-color: #6f42c1 !important;
}

.text-purple {
    color: #6f42c1 !important;
}

.badge.bg-purple {
    background-color: #6f42c1 !important;
    color: white !important;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .jurisdiction-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
    }

    .applicability-indicator {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>

