<!-- Add/Edit Bank Account Modal -->
<div class="modal fade" id="bankAccountModal" tabindex="-1" aria-labelledby="bankAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bankAccountModalLabel">Add Bank Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bankAccountForm" onsubmit="saveBankAccount(event)">
                <div class="modal-body">
                    <input type="hidden" id="bankAccountID" name="bankAccountID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="row">
                        <!-- Bank Details -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Bank Details</h6>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="bankName" class="form-label">Bank Name <span class="text-danger">*</span></label>
                            <select class="form-select" id="bankName" name="bankName" required>
                                <option value="">-- Select Bank --</option>
                                <option value="KCB Bank">KCB Bank</option>
                                <option value="Equity Bank">Equity Bank</option>
                                <option value="Cooperative Bank">Cooperative Bank</option>
                                <option value="NCBA Bank">NCBA Bank</option>
                                <option value="Absa Bank">Absa Bank</option>
                                <option value="Standard Chartered">Standard Chartered</option>
                                <option value="Stanbic Bank">Stanbic Bank</option>
                                <option value="DTB Bank">DTB Bank</option>
                                <option value="I&M Bank">I&M Bank</option>
                                <option value="Family Bank">Family Bank</option>
                                <option value="CRDB Bank">CRDB Bank</option>
                                <option value="Other">Other (specify in notes)</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="bankCode" class="form-label">Bank Code</label>
                            <input type="text" class="form-control" id="bankCode" name="bankCode"
                                   placeholder="e.g., 01">
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="branchName" class="form-label">Branch Name</label>
                            <input type="text" class="form-control" id="branchName" name="branchName"
                                   placeholder="e.g., Westlands Branch">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="branchCode" class="form-label">Branch Code</label>
                            <input type="text" class="form-control" id="branchCode" name="branchCode">
                        </div>

                        <!-- Account Details -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Account Details</h6>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="accountNumber" class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="accountNumber" name="accountNumber" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="accountType" class="form-label">Account Type</label>
                            <select class="form-select" id="accountType" name="accountType">
                                <option value="salary">Salary Account</option>
                                <option value="savings">Savings Account</option>
                                <option value="checking">Checking Account</option>
                                <option value="current">Current Account</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="accountName" class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="accountName" name="accountName" required
                                   placeholder="Name as it appears on the account">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="KES">KES - Kenyan Shilling</option>
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="GBP">GBP - British Pound</option>
                            </select>
                        </div>

                        <!-- Allocation -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Salary Allocation</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="allocationPercentage" class="form-label">Allocation Percentage</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="allocationPercentage" name="allocationPercentage"
                                       min="0" max="100" step="0.01" value="100">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Percentage of salary to deposit in this account</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isPrimaryAccount" name="isPrimary" value="Y">
                                <label class="form-check-label" for="isPrimaryAccount">
                                    <strong>Set as Primary Account</strong>
                                </label>
                            </div>
                        </div>

                        <!-- International Details (Optional) -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">International Details (Optional)</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="swiftCode" class="form-label">SWIFT/BIC Code</label>
                            <input type="text" class="form-control" id="swiftCode" name="swiftCode"
                                   placeholder="For international transfers">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="iban" class="form-label">IBAN</label>
                            <input type="text" class="form-control" id="iban" name="iban"
                                   placeholder="International Bank Account Number">
                        </div>

                        <!-- Status & Dates -->
                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3">Status & Dates</h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="effectiveDate" class="form-label">Effective From</label>
                            <input type="text" class="form-control bank-datepicker" id="effectiveDate"
                                   name="effectiveDate" placeholder="Select date">
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isActiveAccount"
                                       name="isActive" value="Y" checked>
                                <label class="form-check-label" for="isActiveAccount">
                                    <strong>Account is Active</strong>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="bankNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="bankNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Bank Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

