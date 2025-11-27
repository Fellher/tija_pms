<!-- Salary Component Add/Edit Modal -->
<div class="modal fade" id="componentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="componentModalTitle">Add Salary Component</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="componentForm">
                <div class="modal-body">
                    <input type="hidden" id="salaryComponentID" name="salaryComponentID">
                    <input type="hidden" id="componentOrgDataID" name="orgDataID" value="<?= $employeeDetails->orgDataID ?? '' ?>">
                    <input type="hidden" id="componentEntityID" name="entityID" value="<?= $employeeDetails->entityID ?? '' ?>">

                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Component Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="salaryComponentTitle" name="salaryComponentTitle"
                                   placeholder="e.g., Housing Allowance" required>
                            <small class="text-muted">Enter title first - code will be auto-generated</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Component Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="componentCode" name="componentCode"
                                       placeholder="Auto-generated from title" required>
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="enableManualCodeEdit('componentCode')"
                                        title="Edit code manually">
                                    <i class="ri-edit-line"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="componentCodeHint">
                                <i class="ri-magic-line"></i> Auto-generated (click edit to customize)
                            </small>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="salaryComponentDescription" name="salaryComponentDescription"
                                      rows="2" placeholder="Brief description of this component"></textarea>
                        </div>

                        <!-- Category and Type -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Classification</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="salaryComponentCategoryID" name="salaryComponentCategoryID" required>
                                <option value="">Select Category</option>
                                <?php if ($categories): foreach ($categories as $cat): ?>
                                <option value="<?= $cat->salaryComponentCategoryID ?>">
                                    <?= htmlspecialchars($cat->salaryComponentCategoryTitle) ?>
                                </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="salaryComponentType" name="salaryComponentType" required>
                                <option value="">Select Type</option>
                                <option value="earning">Earning (Added to salary)</option>
                                <option value="deduction">Deduction (Subtracted from salary)</option>
                            </select>
                        </div>

                        <!-- Value Configuration -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Value Configuration</h6>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Value Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="salaryComponentValueType" name="salaryComponentValueType" required>
                                <option value="">Select Type</option>
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage</option>
                                <option value="formula">Formula (Advanced)</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Default Value</label>
                            <input type="number" class="form-control" id="defaultValue" name="defaultValue"
                                   step="0.01" min="0" value="0.00">
                            <small class="text-muted">Amount or percentage</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Apply To</label>
                            <select class="form-select" id="applyTo" name="applyTo">
                                <option value="basic_salary">Basic Salary</option>
                                <option value="gross_salary">Gross Salary</option>
                                <option value="taxable_income">Taxable Income</option>
                                <option value="net_salary">Net Salary</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Value (Optional)</label>
                            <input type="number" class="form-control" id="minimumValue" name="minimumValue"
                                   step="0.01" min="0" placeholder="Leave blank for no minimum">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maximum Value (Optional)</label>
                            <input type="number" class="form-control" id="maximumValue" name="maximumValue"
                                   step="0.01" min="0" placeholder="Leave blank for no maximum">
                        </div>

                        <!-- Flags and Options -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Options</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isStatutory" name="isStatutory" value="Y">
                                <label class="form-check-label" for="isStatutory">
                                    Statutory Deduction
                                </label>
                                <small class="d-block text-muted">e.g., PAYE, NHIF, NSSF</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isMandatory" name="isMandatory" value="Y">
                                <label class="form-check-label" for="isMandatory">
                                    Mandatory for All Employees
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isTaxable" name="isTaxable" value="Y" checked>
                                <label class="form-check-label" for="isTaxable">
                                    Taxable Income
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isVisible" name="isVisible" value="Y" checked>
                                <label class="form-check-label" for="isVisible">
                                    Visible on Payslip
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isProrated" name="isProrated" value="Y">
                                <label class="form-check-label" for="isProrated">
                                    Prorate for Partial Months
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sortOrder" name="sortOrder"
                                   min="0" value="0" placeholder="Display order on payslip">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes"
                                      rows="2" placeholder="Internal notes or special instructions"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Component
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

