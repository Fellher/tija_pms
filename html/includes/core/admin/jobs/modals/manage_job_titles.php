<div class="row manageJobTitleForm" id="manageJobTitleFormModal" >
    <div class="form-group d-none ">
        <label for="jobTitleID" class="nott mb-0 t400 text-primary">Job Title ID</label>
        <input type="text" name="jobTitleID" id="jobTitleID" class="form-control form-control-xs form-control-plaintext border-bottom border-dark" value="">
    </div>   
    <div class="form-group my-3">
        <label for="jobTitle" class="nott mb-0 t400 text-primary">Job Title </label>
        <input type="text" name="jobTitle" id="jobTitle" class="form-control form-control-xs form-control-plaintext border-bottom" value="">
    </div>
    <div class="mb-3 form-group">
        <label for="formFile" class="form-label">Default file input example</label>
        <div class="row">          
                    
            <div class="col">
                <input class="form-control form-control-xs " type="file" id="formFile" name='jobDescriptionDoc'>
            </div>
        </div>      
    </div>  
    <div class="form-group">
        <label for="jobCategoryID" class="nott mb-0 t400 text-primary">Job Category</label>
        <select name="jobCategoryID" id="jobCategoryID" class="form-select form-select-xs form-select-plaintext border-bottom">
            <option value="">Select Job Category</option>
            <?php 
            if($jobCategories){
                foreach($jobCategories as $jobCategory){?>
                    <option value="<?php echo $jobCategory->jobCategoryID ?>" ><?php echo $jobCategory->jobCategoryTitle ?></option>
                    <?php
                }
            } ?>
        </select>
    </div>
    
</div>
<div class="col-12">
    <label for="jobDescription" class="nott mb-0 t400 text-primary"> Job Description</label>    
    <textarea  class="form-control form-control-sm borderless" name="jobDescription" id="jobDescription" rows="5"></textarea>
</div> 


