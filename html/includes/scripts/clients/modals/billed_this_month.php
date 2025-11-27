<div class="table-resposive">
   <table class="table table-bordered table-striped table-vcenter table-sm js-dataTable-full" id="js-dataTable-full">
      <thead>
            <tr>
               <th class="">#</th>
               <th class="">invoice No</th>
               <th class="">invoice Status</th>
               <th class="">invoice Date</th>
               <th class="">Due Date</th>
               <th class="">Billing Customer</th>
               <th class="">Case Code</th>
               <th class="">Case</th>
               <th class="">Ref Number</th>
            </tr>
      </thead>
      <tbody>
            <?php
            $invoices= array();
            //  = Margin::get_margin_years(array(), false, $DBConn);
         
            if($invoices){
               foreach($invoices as $key => $invoice){
                  ?>
                  <tr>
                        <td class=""><?php echo $key + 1; ?></td>
                        <td class=""><?= $invoice->invoiceNo ?></td>
                        <td class=""><?= $invoice->invoiceStatus ?></td>
                        <td class=""><?= $invoice->invoiceDate ?></td>
                        <td class=""><?= $invoice->dueDate ?></td>
                        <td class=""><?= $invoice->billingCustomer ?></td>
                        <td class=""><?= $invoice->caseCode ?></td>
                        <td class=""><?= $invoice->case ?></td>
                        <td class=""><?= $invoice->refNumber ?></td>
                  </tr>
                  <?php
               }
            } else {
               ?>
               <tr>
                  <td colspan="9" class="text-center"><?php Alert::info("No margin years found", false, array('fst-italic', 'font-18')); ?></td>
               </tr>
               <?php
            }?>
      </tbody>
   </table>      
</div>