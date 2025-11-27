<div class="table">
   <?php 
   // var_dump($clients); 
   ?>
   <table class="table table-bordered table-striped table-vcenter table-sm js-dataTable-full" id="js-dataTable-full">
      <thead>
         <tr>
            <th class="">#</th>
            <th class="">AccountName</th>
            <th class="">billed</th>
            <th class="">Other Expenses</th>
            <th class="">Sales Margin</th>
            <th class="">Labour Margin</th>
            <th class="">Margin</th>
            <th class="">Margin %</th>
            <th class="">AccountOwner</th>
         </tr>
      </thead>
      <tbody>
         <?php
         // $marginYears = Margin::get_margin_years(array(), false, $DBConn);
       
         if($clients){
             foreach($clientActiveProjects as $key => $client){
                 ?>
                 <tr>
                     <td class=""><?php echo $key + 1; ?></td>
                     <td class=""><?= $client->clientName ?></td>
                     <td class=""></td>
                     <td class=""></td>
                     <td class=""></td>
                     <td class=""></td>
                     <td class=""></td>
                     <td class=""></td>
                     <td class=""></td>

                     
                 </tr>
                 <?php
             }
         } else {
             ?>
             <tr>
                 <td colspan="9" class="text-center"><?php Alert::info("No margin years found", false, array('fst-italic', 'font-18')); ?></td>
             </tr>
             <?php
         }
         ?>
      </tbody>
   </table>

</div>
