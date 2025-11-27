<div class="form-group">
    <label for="salaryComponentID">Salary Componenet ID</label>
    <input type="text" name="salaryComponentID" id="salaryComponentID<?php echo $nodeID ?>" class="form-control-xs form-control-plaintext border-bottom bg-light-blue" value="<?php echo (isset($salaryComponent->salaryComponentID) && !empty($salaryComponent->salaryComponentID)) ? $salaryComponent->salaryComponentID :''; ?>" placeholder="Please insert salary component ID">
</div>
<div class="form-group">
    <label for="salaryComponentTitle"> Component Name</label>
    <input type="text" name="salaryComponentTitle" id="salaryComponentTitle<?php echo $nodeID ?>" class="form-control-xs form-control-plaintext border-bottom bg-light-blue" value="<?php echo (isset($salaryComponent->salaryComponentTitle) && !empty($salaryComponent->salaryComponentTitle)) ? $salaryComponent->salaryComponentTitle :''; ?>" placeholder="Please insert salary component">
</div>
<div class="form-group">
    <label for="salaryComponentDescription">Component Description</label>
    <textarea name="salaryComponentDescription" id="salaryComponentDescription<?php echo $nodeID ?>" class="form-control form-control-xs form-control-plaintext border-bottom bg-light-blue" placeholder="Please insert salary component description"><?php echo (isset($salaryComponent->salaryComponentDescription) && !empty($salaryComponent->salaryComponentDescription)) ? $salaryComponent->salaryComponentDescription :''; ?></textarea>
</div>
<div class="form-group">
    <label for="salaryComponentType">Component Type</label>
    <select name="salaryComponentType" id="salaryComponentType<?php echo $nodeID ?>" class="form-control-xs form-control-plaintext border-bottom bg-light-blue">
        <option value="earning" <?php echo (isset($salaryComponent->salaryComponentType) && !empty($salaryComponent->salaryComponentType) && $salaryComponent->salaryComponentType == "earning") ? 'selected' : ''; ?>>Earnings</option>
        <option value="deduction" <?php echo (isset($salaryComponent->salaryComponentType) && !empty($salaryComponent->salaryComponentType) && $salaryComponent->salaryComponentType == "deduction") ? 'selected' : ''; ?>>Deductions</option>
       
    </select>
</div>
<?php 
 ?>
<div class="form-group">
    <label for="contributionCategory"> Contribution Category </label>   
       <?php
    //    var_dump($componentCategories);
        if($componentCategories){
            // var_dump($componentCategories);
            ?>
         <select name="salaryComponentCategoryID" id="salaryComponentCategoryID<?php echo $nodeID ?>" class="form-control-xs form-control-plaintext border-bottom bg-light-blue salaryCategory">
            <?php
            foreach($componentCategories as $category){
                ?>
                <option value="<?php echo $category->salaryComponentCategoryID; ?>" <?php echo (isset($salaryComponent->salaryComponentCategoryID) && !empty($salaryComponent->salaryComponentCategoryID) && $salaryComponent->salaryComponentCategoryID == $category->salaryComponentCategoryID) ? 'selected' : ''; ?>><?php echo $category->salaryComponentCategoryTitle; ?></option>
                <?php
            }?>
            <option value="addNew">Add Category</option>
        </select>
        <?php
        } else {?>
           <button type="button" class="btn btn-primary btn-sm w-100 addSalaryComponentCategory" >Add Category</button>
            <?php
        }?>
    </select>
</div>

<div class="form-group">
    <label for="salaryComponentValueType">Component Value</label>
    <select name="salaryComponentValueType" id="salaryComponentValueType<?php echo $nodeID ?>" class="form-control-xs form-control-plaintext border-bottom bg-light-blue">
        <option value=""> Select Salary component type </option>
        <option value="amount" <?php echo (isset($salaryComponent->salaryComponentValueType) && !empty($salaryComponent->salaryComponentValueType) && $salaryComponent->salaryComponentValueType == "amount") ? 'selected' : ''; ?>>Fixed (Amount)</option>
        <option value="percentage" <?php echo (isset($salaryComponent->salaryComponentValueType) && !empty($salaryComponent->salaryComponentValueType) && $salaryComponent->salaryComponentValueType == "percentage") ? 'selected' : ''; ?>>Percentage</option>
    </select>
</div>
<div class="form-group">
    <label for="applyTo"> Apply component to</label>
    <select name="applyTo" id="applyTo<?php echo $nodeID ?>" class="form-control-xs form-control-plaintext border-bottom bg-light-blue">
        <option value="total_payable" <?php echo (isset($salaryComponent->applyTo) && !empty($salaryComponent->applyTo) && $salaryComponent->applyTo == "total_payable") ? 'selected' : ''; ?>>Cost to employee</option>
        <option value="cost_to_company"  <?php echo (isset($salaryComponent->applyTo) && !empty($salaryComponent->applyTo) && $salaryComponent->applyTo == "cost_to_company") ? 'selected' : ''; ?>>Cost to Employer </option>
    </select>
</div>