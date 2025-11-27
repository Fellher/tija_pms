<?php
// This is a new sub-menu reporting file for 'sales_overview.php'
// Add your content here.
$sales = Sales::sales_case_full([], false, $DBConn);
// var_dump($sales);
if($sales){
?>

<table class="table table-striped table-sm">
   <thead>
      <tr>
         <th>Sale</th>
         <th>Sales Person</th>
         <th>Sales Status</th>
         <th>Sales Stage</th>
         <th>Sales Estimate</th>
         <th>Sales Probability</th>
         <th>Sales Expected Close Date</th>
         <th>Sales Date Closed</th>
      </tr>
   </thead>
   <tbody>
      <?php foreach ($sales as $sale): ?>
         <tr>
            <td><?= $sale->salesCaseName ?></td>
            <td><?= $sale->salesPersonName ?></td>
            <td><?= $sale->statusLevel  ? $sale->statusLevel : '-' ?></td>
            <td><?= $sale->saleStage ?></td>
            <td><?= $sale->salesCaseEstimate > 0 ? $sale->salesCaseEstimate : '-' ?></td>
            <td><?= $sale->probability > 0 ? $sale->probability . '%' : '-' ?></td>
            <td><?= $sale->expectedCloseDate && $sale->expectedCloseDate != '0000-00-00' ? $sale->expectedCloseDate : '-' ?></td>
            <td><?= $sale->dateClosed && $sale->dateClosed != '0000-00-00' ? $sale->dateClosed : '-' ?></td>
         </tr>
      <?php endforeach; ?>
   </tbody>
</table>
<?php
} else {
   Alert::warning("There are no sales cases for the current month");
}
?>

