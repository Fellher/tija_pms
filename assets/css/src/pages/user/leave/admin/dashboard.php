<style>
/* Leave Admin Dashboard Styles */

/* Admin Dashboard Specific Styles */
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
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
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

/* Quick Action Cards */
.card {
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-radius: 12px 12px 0 0 !important;
    padding: 1rem 1.5rem;
}

.card-body {
    padding: 1.5rem;
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

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar-nav {
        margin-bottom: 1rem;
    }
    
    .stats-card .card-body {
        padding: 1rem;
    }
    
    .stats-number {
        font-size: 1.5rem;
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
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

/* Icon Styles */
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
</style>