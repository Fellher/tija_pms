<?php
// Modal for managing Business Units
echo Utility::form_modal_header("manageBusinessUnitModal", "organisation/manage_business_unit.php", "Manage Business Unit", array('modal-md', 'modal-dialog-centered', "modal-lg"), $base);
?>
<div id="business_unit_form" class="managebusinessUnits">
    <div class="row g-3">
        <input type="hidden" id="businessUnitID" name="businessUnitID">
        <input type="hidden" id="bu_orgDataID" name="orgDataID" value="<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
        <input type="hidden" id="bu_entityID" name="entityID" value="<?= $entityID ?>">

        <div class="col-md-12">
            <label for="businessUnitName" class="form-label mb-0">Business Unit Name</label>
            <input type="text" id="businessUnitName" name="businessUnitName" class="form-control form-control-sm" placeholder="e.g., Tax Advisory, Projects, Commercial" required>
            <small class="text-muted">Examples: Projects, Reporting, Tax, Tija, Product Lines, Cost Centers</small>
        </div>

        <div class="col-md-6">
            <label for="bu_unitTypeID" class="form-label mb-0">Business Unit Type</label>
            <select id="bu_unitTypeID" name="unitTypeID" class="form-select form-control-sm" required>
                <option value="">Select Type</option>
                <?php if ($unitTypes): foreach ($unitTypes as $ut): ?>
                    <option value="<?= $ut->unitTypeID ?>"><?= htmlspecialchars($ut->unitTypeName) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label mb-0">Unit Category</label>
            <select class="form-select form-control-sm" id="categoryID" name="categoryID">
                <option value="">Select Category</option>
                <?php if ($businessUnitCategories): foreach ($businessUnitCategories as $cat): ?>
                    <option value="<?= $cat->categoryID ?>">
                        <?= htmlspecialchars($cat->categoryName) ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="bu_headOfUnitID" class="form-label mb-0">Head of Unit</label>
            <select id="bu_headOfUnitID" name="headOfUnitID" class="form-select form-control-sm">
                <option value="0">None (No Head Assigned)</option>
                <?php if (isset($entityEmployees) && $entityEmployees): foreach ($entityEmployees as $emp): ?>
                    <option value="<?= $emp->ID ?? $emp->userID ?? $emp->employeeID ?>">
                        <?= htmlspecialchars($emp->employeeName ?? $emp->fullName ?? ($emp->FirstName . ' ' . $emp->Surname)) ?>
                        <?php if (isset($emp->jobTitle) && $emp->jobTitle): ?>
                            - <?= htmlspecialchars($emp->jobTitle) ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="col-md-12">
            <label for="businessUnitDescription" class="form-label mb-0">Description</label>
            <textarea id="businessUnitDescription" name="businessUnitDescription" class="form-control form-control-sm" rows="3" placeholder="Describe the purpose and scope of this business unit"></textarea>
        </div>
    </div>
</div>
<?php
echo Utility::form_modal_footer('Save Business Unit');
?>

