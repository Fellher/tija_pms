<!-- Salary Category Add/Edit Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Add Salary Component Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" id="salaryComponentCategoryID" name="salaryComponentCategoryID">
                    <input type="hidden" id="categoryOrgDataID" name="orgDataID" value="<?= $employeeDetails->orgDataID ?? '' ?>">
                    <input type="hidden" id="categoryEntityID" name="entityID" value="<?= $employeeDetails->entityID ?? '' ?>">

                    <div class="mb-3">
                        <label class="form-label">Category Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="salaryComponentCategoryTitle" name="salaryComponentCategoryTitle"
                               placeholder="e.g., Allowances, Deductions" required>
                        <small class="text-muted">Enter title first - code will be auto-generated</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="categoryCode" name="categoryCode"
                                   placeholder="Auto-generated from title" required maxlength="50">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="enableManualCodeEdit('categoryCode')"
                                    title="Edit code manually">
                                <i class="ri-edit-line"></i>
                            </button>
                        </div>
                        <small class="text-muted" id="categoryCodeHint">
                            <i class="ri-magic-line"></i> Auto-generated (click edit to customize)
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="salaryComponentCategoryDescription" name="salaryComponentCategoryDescription"
                                  rows="3" placeholder="Brief description of this category"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="categoryType" name="categoryType" required>
                            <option value="">Select Type</option>
                            <option value="earning">Earning</option>
                            <option value="allowance">Allowance (for dynamic allowances)</option>
                            <option value="deduction">Deduction</option>
                            <option value="statutory">Statutory</option>
                            <option value="benefit">Benefit</option>
                            <option value="reimbursement">Reimbursement</option>
                        </select>
                        <small class="text-muted">Choose "Allowance" for dynamic allowances system</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="categorySortOrder" name="sortOrder"
                               min="0" value="0" placeholder="Display order">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

