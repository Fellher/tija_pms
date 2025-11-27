<style>
/* Enterprise Leave Types Management Styles */
.leave-type-table-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.table-header-enterprise {
    background: linear-gradient(135deg, #0052CC 0%, #0065FF 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-bottom: none;
}

.table-header-enterprise h4 {
    margin: 0;
    font-weight: 600;
    font-size: 1.375rem;
}

.btn-add-enterprise {
    background: white;
    color: #0052CC;
    border: 2px solid white;
    padding: 0.5rem 1.5rem;
    border-radius: 25px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-add-enterprise:hover {
    background: #0052CC;
    color: white;
    border-color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
}

.enterprise-table {
    margin: 0;
}

.enterprise-table thead th {
    background: #F4F5F7;
    color: #172B4D;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8125rem;
    letter-spacing: 0.5px;
    padding: 1rem 1.5rem;
    border-bottom: 2px solid #DFE1E6;
}

.enterprise-table tbody td {
    padding: 1.25rem 1.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #F4F5F7;
}

.enterprise-table tbody tr {
    transition: all 0.2s ease;
}

.enterprise-table tbody tr:hover {
    background: #F9FAFB;
    transform: scale(1.002);
}

.leave-type-code-badge {
    display: inline-flex;
    padding: 0.375rem 0.875rem;
    background: linear-gradient(135deg, #0052CC 0%, #0065FF 100%);
    color: white;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

.leave-type-name {
    font-weight: 600;
    color: #172B4D;
    font-size: 1rem;
}

.action-btn {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
}

.btn-edit-enterprise {
    background: #E3F2FD;
    color: #0052CC;
    border-color: #0052CC;
}

.btn-edit-enterprise:hover {
    background: #0052CC;
    color: white;
}

.btn-delete-enterprise {
    background: #FFEBE6;
    color: #DE350B;
    border-color: #DE350B;
}

.btn-delete-enterprise:hover {
    background: #DE350B;
    color: white;
}

.empty-state {
    padding: 4rem 2rem;
    text-align: center;
}

.empty-state-icon {
    font-size: 5rem;
    color: #DFE1E6;
    margin-bottom: 1.5rem;
}

.page-header-enterprise {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    margin-bottom: 2rem;
    border-left: 5px solid #0052CC;
}
</style>

