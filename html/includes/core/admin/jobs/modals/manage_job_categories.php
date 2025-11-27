<div class="form-group">
    <label for="catregoryName"> Job Category Name</label>
    <input type="text" name="jobCategoryTitle" id="jobCategoryTitle" class="form-control-xs form-control-plaintext border-bottom bg-light-blue" value="<?php echo (isset($jobCategory->jobCategoryTitle) && !empty($jobCategory->jobCategoryTitle)) ? $jobCategory->jobCategoryTitle :''; ?>" placeholder="Please insert job category">

</div>
<div class="form-group">
    <label for="jobCategoryDescription">Job Category Description</label>
    <textarea name="jobCategoryDescription" id="jobCategoryDescription" class="form-control form-control-xs form-control-plaintext border-bottom bg-light-blue" placeholder="Please insert job category description"><?php echo (isset($jobCategory->jobCategoryDescription) && !empty($jobCategory->jobCategoryDescription)) ? $jobCategory->jobCategoryDescription :''; ?></textarea>
</div>
<div class="form-group d-none">
    <label for="jobCategoryID" class="nott mb-0 t400 text-primary">Job Category ID</label>
    <input type="text" name="jobCategoryID" id="jobCategoryID" class="form-control form-control-xs form-control-plaintext border-bottom" value="<?php echo (isset($jobCategory->jobCategoryID) && !empty($jobCategory->jobCategoryID)) ? $jobCategory->jobCategoryID :'' ?>">
</div>
<!-- input -->
