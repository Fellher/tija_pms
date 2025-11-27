<div class="row">
    <div class="form-group col-md-6">
      <label for="orgChartName">Organisation Chart Name</label>
      <input type="text" class="form-control form-control-sm form-control-plaintext" id="orgChartName" name="orgChartName" value="" required>
    </div>
    
    <div class="form-group col-md-6">
      <label for="orgSelect">Select Organisation Entities</label>
      <select class="form-control form-control-sm" id="EntitySelect" name="entityID" required>
          <option value="">Select Organisation</option>
          <?php foreach ($organisationEntities as $entity) : ?>
              <option value="<?php echo htmlspecialchars($entity->entityID); ?>"><?php echo htmlspecialchars($entity->entityName); ?></option>
          <?php endforeach; ?>
      </select>
    </div>
<div class="form-group col-md-6 d-none">
    <label for="orgChartID">Organisation Chart ID</label>
    <input type="text" class="form-control form-control-sm form-control-plaintext" id="orgChartID" name="orgChartID" value="" required>
</div>
</div>