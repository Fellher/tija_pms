
<div class="form-group">
    <label for="jobBandTitle">Job/Pay Band Title</label>
    <input type="text" name="jobBandTitle" id="jobBandTitle" class="form-control-xs form-control-plaintext border-bottom bg-light-blue px-2" value="<?php echo (isset($jobBand->jobBandTitle) && !empty($jobBand->jobBandTitle)) ? $jobBand->jobBandTitle : ""; ?>" placeholder="Please insert category title">
</div>
<div class="form-group">
    <label for="jobBandDescription">Job Band Description</label>
    <textarea name="jobBandDescription" id="jobBandDescription" class="form-control form-control-xs form-control-plaintext border-bottom bg-light-blue px-2" placeholder="Please insert category description">
        <?php echo (isset($jobBand->jobBandDescription) && !empty($jobBand->jobBandDescription)) ? Utility::clean_string($jobBand->jobBandDescription) :''; ?>
    </textarea>
</div>

<div class="form-group">
    <label for="jobBandID">Job Band ID</label>
    <input type="text" name="jobBandID" id="jobBandID" class="form-control-xs form-control-plaintext border-bottom bg-light-blue px-2" placeholder="Please insert salary" value="<?php echo (isset($jobBand->jobBandID) && !empty($jobBand->jobBandID)) ? $jobBand->jobBandID: ""; ?>">
</div>
