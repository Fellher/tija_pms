<div class="row">

    <div class="form-group col-md-2 col-12 mt-2">
        <label for="unitCode"  class="text-primary   font-14"> Code #</label>
        <input type="text" class="form-control form-control-sm"  id="unitCode<?php echo $nodeID ?>" name="unitCode" value="" placeholder="Enter Unit Code" required readonly/>
    </div>
    <div class="form-group col-md-5 mt-2">
        <label for="unitName"  class="text-primary   font-14">Unit Name</label>
        <input type="text" class="form-control form-control-sm" id="unitName<?php echo $nodeID ?>" name="unitName" value="" placeholder="Enter Unit Name" required />
    </div>



    <?php
    echo $nodeID;
    $unitTypes = Data::unit_types(array("Suspended"=>'N'), false, $DBConn);?>
    <div class="form-group col-md-5 mt-2">
        <label for="unitType" class="text-primary   font-14">Unit Type</label>
        <select class="form-control form-control-sm"  id="unitType<?php echo $nodeID ?>" name="unitType" required>
            <option value="">Select Unit Type</option>
            <?php

            if ($unitTypes) {
                foreach ($unitTypes as $unitType) {
                    echo "<option value='{$unitType->unitTypeID}' >{$unitType->unitTypeName}</option>";
                }
            }?>
            <option value="addNew">Add New Unit Type</option>
        </select>
    </div>
    <div class="form-group col-md-6">
        <label for="headOfUnit"  class="text-primary   font-14">Head of Unit</label>
        <select class="form-control form-control-sm" id="headOfUnit<?php echo $nodeID ?>" name="headOfUnit" required>
            <option value="">Select Head of Unit</option>
            <?php
             $unitHeads = Employee::employees([], false,  $DBConn);
            if ($unitHeads) {
                foreach ($unitHeads as $unitHead) {
                    echo "<option value='{$unitHead->ID}' >{$unitHead->FirstName} {$unitHead->Surname}</option>";
                }
            } ?>
        </select>
    </div>
    <div class="form-group col-md-6">
        <label for="parentUnit"  class="text-primary   font-14">Parent Unit</label>
        <select class="form-select form-select-sm" id="parentUnit<?php echo $nodeID ?>" name="parentUnitID" required>
            <option value="">Select Parent Unit</option>
            <option value="0">None (Top Level)</option>
            <?php
            // Filter by entity if available
            $parentUnitFilter = array("Suspended" => 'N');
            if (isset($entityDetails) && is_object($entityDetails) && isset($entityDetails->entityID)) {
                $parentUnitFilter['entityID'] = $entityDetails->entityID;
            }
            $parentUnits = Data::units($parentUnitFilter, false, $DBConn);
            if ($parentUnits) {
                foreach ($parentUnits as $parentUnit) {
                    echo "<option value='{$parentUnit->unitID}'>{$parentUnit->unitName}</option>";
                }
            }?>
        </select>
    </div>

    <div class="form-group mt-3">
        <label for="unitDescription"  class="text-primary   font-14">Unit Description</label>
        <textarea class="form-control form-control-sm borderless " id="unitDescription<?php echo $nodeID ?>" name="unitDescription" placeholder="Enter Unit Description" required>

        </textarea>
    </div>
    <input type="hidden" class="form-control" name="unitID" value=""/>
    <input type="hidden" name="orgDataID" class="form-control" Value="<?= isset($entityDetails) && is_object($entityDetails) ? $entityDetails->orgDataID : (isset($orgDetails) && is_object($orgDetails) ? $orgDetails->orgDataID : '') ?>" />
    <input type="hidden" name="entityID" class="form-control" value="<?= isset($entityDetails) && is_object($entityDetails) ? $entityDetails->entityID : '' ?>" />


</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get unit name input element
        let unitNameInput = document.querySelector('input[name="unitName"]');
        let unitCodeInput = document.querySelector('input[name="unitCode"]');

        if (unitNameInput && unitCodeInput) {
            unitNameInput.addEventListener('input', function() {
                let unitName = this.value;
                if (unitName) {
                    // Remove special characters and get first 3 characters
                    let namePrefix = unitName.replace(/[^a-zA-Z0-9]/g, '').substring(0,3).toUpperCase();

                    // Generate 5 random alphanumeric characters
                    let chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    let randomStr = '';
                    for (let i = 0; i < 5; i++) {
                        randomStr += chars.charAt(Math.floor(Math.random() * chars.length));
                    }

                    // Combine to create unit code
                    let unitCode = namePrefix + '_' + randomStr;
                    unitCodeInput.value = unitCode;
                }
            });
        }
    });
    let unitType = document.getElementById('unitType<?php echo $nodeID ?>');
    let headOfUnit = document.getElementById('headOfUnit<?php echo $nodeID ?>');
    unitType.addEventListener('change', function() {
        if(unitType.value == 'addNew'){
            unitType.insertAdjacentHTML('afterend', '<input type="text" class="form-control form-control-sm mt-2" id="newUnitType<?php echo $nodeID ?>" name="newUnitType" placeholder="Enter New Unit Type" required>');
        }
    });
    headOfUnit.addEventListener('change', function() {
        if(headOfUnit.value == 'addNew'){
            headOfUnit.insertAdjacentHTML('afterend', '<input type="text" class="form-control form-control-sm mt-2" id="newHeadOfUnit<?php echo $nodeID ?>" name="newHeadOfUnit" placeholder="Enter New Head of Unit" required>');
        }
    });

</script>