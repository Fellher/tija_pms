<?php
// Modal for managing Units (Departments, Sections, Teams)
echo Utility::form_modal_header("manageUnitModal", "organisation/manage_entity_unit.php", "Manage Entity Unit", array('modal-md', 'modal-dialog-centered', "modal-lg"), $base);
?>
<div id="unit_form" class="manageUnits">
    <div class="row g-3">
        <input type="hidden" class="form-control form-control-sm" id="unitID" name="unitID" value="">
        <input type="hidden" class="form-control form-control-sm" id="unit_orgDataID" name="orgDataID" value="<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
        <input type="hidden" class="form-control form-control-sm" id="unit_entityID" name="entityID" value="<?= $entityID ?>">

        <div class="col-md-6">
            <label for="unitName" class="form-label mb-0">Unit Name</label>
            <input type="text" id="unitName" name="unitName" class="form-control form-control-sm" placeholder="Unit Name" required>
        </div>

        <div class="col-md-6">
            <label for="unitCode" class="form-label mb-0">Unit Code</label>
            <input type="text" id="unitCode" name="unitCode" class="form-control form-control-sm" placeholder="Unit Code">
        </div>

        <div class="col-md-6">
            <label for="unitTypeID" class="form-label mb-0">Unit Type</label>
            <select id="unitTypeID" name="unitTypeID" class="form-select form-control-sm" required>
                <option value="">Select Unit Type</option>
                <?php if ($unitTypes): foreach ($unitTypes as $ut): ?>
                    <option value="<?= $ut->unitTypeID ?>"><?= htmlspecialchars($ut->unitTypeName) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="parentUnitID" class="form-label mb-0">Parent Unit</label>
            <select id="parentUnitID" name="parentUnitID" class="form-select form-control-sm">
                <option value="0">None (Main Unit)</option>
                <?php if ($entityUnits): foreach ($entityUnits as $u): ?>
                    <option value="<?= $u->unitID ?>"><?= htmlspecialchars($u->unitName) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="headOfUnitID" class="form-label mb-0">Head of Unit</label>
            <select id="headOfUnitID" name="headOfUnitID" class="form-select form-control-sm">
                <option value="0">None (No Head Assigned)</option>
                <?php if ($entityEmployees): foreach ($entityEmployees as $emp): ?>
                    <option value="<?= $emp->ID ?? $emp->userID ?? $emp->employeeID ?>">
                        <?= htmlspecialchars($emp->fullName ?? $emp->EmployeeName ?? ($emp->FirstName . ' ' . $emp->Surname)) ?>
                        <?php if (isset($emp->jobTitle) && $emp->jobTitle): ?>
                            - <?= htmlspecialchars($emp->jobTitle) ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="col-md-12">
            <label for="unitDescription" class="form-label mb-0">Description</label>
            <textarea id="unitDescription" name="unitDescription" class="form-control form-control-sm" rows="3" placeholder="Unit Description"></textarea>
        </div>
    </div>
</div>
<?php
echo Utility::form_modal_footer('Save Unit');
?>

