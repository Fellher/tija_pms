
<div class="form-group d-none">
    <label for="employmentStatusID"> Employment Status ID</label>
    <input type="text" name="employmentStatusID" id="employmentStatusID" class="form-control-xs form-control-plaintext border-bottom bg-light-blue" value="<?php echo (isset($employmentStatus->employmentStatusID) && !empty($employmentStatus->employmentStatusID)) ? $employmentStatus->employmentStatusID :''; ?>" placeholder="Please insert employment status ID">
</div>
<div class="form-group">
    <label for="employmentStatusTitle"> Employment Status </label>
    <input type="text" name="employmentStatusTitle" id="employmentStatusTitle" class="form-control-xs form-control-plaintext border-bottom bg-light-blue" value="<?php echo (isset($employmentStatus->employmentStatusTitle) && !empty($employmentStatus->employmentStatusTitle)) ? $employmentStatus->employmentStatusTitle :''; ?>" placeholder="Please insert employment status">
</div>
<div class="form-group">
    <label for="employmentStatusDescription">Employment Status Description</label>
    <textarea name="employmentStatusDescription" id="employmentStatusDescription" class="form-control form-control-xs form-control-plaintext border-bottom bg-light-blue" placeholder="Please insert employment status description"><?php echo (isset($employmentStatus->employmentStatusDescription) && !empty($employmentStatus->employmentStatusDescription)) ? $employmentStatus->employmentStatusDescription :''; ?></textarea>
</div>
