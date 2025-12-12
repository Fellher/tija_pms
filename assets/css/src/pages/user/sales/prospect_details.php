<?php
// CSS for prospect_details.php - Timeline styles
?>
<style>
/* ============================================================================
   TIMELINE STYLES - Prospect Journey Timeline
   ============================================================================ */

/* Timeline Container */
.timeline-container {
    position: relative;
    padding: 20px 0;
}

/* Timeline Milestone (Start/End Markers) */
.timeline-milestone {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    border-left: 4px solid #28a745;
}

.timeline-milestone-marker {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.timeline-milestone-marker i {
    font-size: 24px;
}

.timeline-milestone-content h6 {
    font-size: 1.1rem;
    color: #2c3e50;
}

.timeline-milestone-content p {
    margin: 0;
    font-size: 0.9rem;
}

/* Timeline Main Container */
.timeline {
    position: relative;
    padding-left: 0;
}

/* Timeline Item */
.timeline-item {
    position: relative;
    display: flex;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 23px;
    top: 48px;
    bottom: -16px;
    width: 2px;
    background: linear-gradient(180deg, #dee2e6 0%, #f8f9fa 100%);
}

/* Timeline Marker (Icon Circle) */
.timeline-marker {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    position: relative;
    z-index: 2;
}

.timeline-marker i {
    font-size: 20px;
}

/* Timeline Content */
.timeline-content {
    flex: 1;
    background: #ffffff;
    border-radius: 12px;
    padding: 1.25rem;
    border-left: 4px solid #007bff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.timeline-content:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.timeline-content h6 {
    font-size: 1.05rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.timeline-content .text-secondary {
    font-size: 0.95rem;
    line-height: 1.6;
    color: #6c757d;
}

/* Empty State */
.empty-state-icon {
    opacity: 0.5;
}

.empty-state-icon i {
    font-size: 64px;
}

/* Color Variants for Markers */
.bg-purple {
    background-color: #6f42c1 !important;
}

.bg-purple-transparent {
    background-color: rgba(111, 66, 193, 0.1) !important;
}

.text-purple {
    color: #6f42c1 !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .timeline-item {
        gap: 1rem;
    }

    .timeline-marker {
        width: 40px;
        height: 40px;
    }

    .timeline-marker i {
        font-size: 18px;
    }

    .timeline-milestone-marker {
        width: 40px;
        height: 40px;
    }

    .timeline-milestone-marker i {
        font-size: 20px;
    }

    .timeline-content {
        padding: 1rem;
    }

    .timeline-item:not(:last-child)::after {
        left: 19px;
    }
}

/* Badge Enhancements */
.badge.small {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

/* Metadata Section */
.timeline-content .border-top {
    border-color: #e9ecef !important;
}

.timeline-content .text-muted.small {
    font-size: 0.875rem;
}

.timeline-content .text-muted.small strong {
    font-weight: 600;
    color: #495057;
}

/* Empty State Cards */
.empty-state-icon + h5 {
    color: #2c3e50;
    font-weight: 600;
}

.card.border-0.bg-light {
    transition: all 0.3s ease;
}

.card.border-0.bg-light:hover {
    background-color: #e9ecef !important;
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.card.border-0.bg-light i {
    transition: transform 0.3s ease;
}

.card.border-0.bg-light:hover i {
    transform: scale(1.1);
}
</style>