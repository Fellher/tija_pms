<div class="col-md-12 my-4">
	<div class="card  bg-light alert  alert-dismissible fade show border-solid" role="alert">
		<div class="row">
			<div class="col-md-3">
				<h4 class="mb-0 t300 font-16">Sales This Month</h4>
				<div class="col-md border-right">
					<div class="font-26 ">
						<span class="font-14">KES</span>
						<?php 
						if ($allSalesCases) {
							$totalSales=0;
							foreach ($allSalesCases as $key => $sale) {
								if ($sale->saleStatus == 'closed') {
									$totalSales+=$sale->saleEstimate;
								}											
							}
							echo number_format($totalSales, 2, '.', ', ');
						} else {
							echo ' 0 ';
						}?> 									
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<h4 class="mb-0 t300 font-16">Estimated this month </h4>
				<div class="col-md border-right">
					<div class="font-22">
						<span class="font-14">KES</span>
						<?php 
						if ($allSalesCases) {
							$totalSales=0;
							foreach ($allSalesCases as $key => $sale) {										
								$totalSales+=$sale->saleEstimate;
							}
							echo number_format($totalSales, 2, '.', ', ');
						} else {
							echo ' 0 ';
						}?>
					</div>
				</div>
			</div>

			<div class="col-md-3">
				<h4 class="mb-0 t300 font-16">Cases in Need of attention </h4>
				<div class="col-md border-right">
					<div class="font-22">								 
						<?php 
						if ($allSalesCases) {
							$danger=0;								
							foreach ($allSalesCases as $key => $case) {	
								$todayDate= $dt->format('Y-m-d');
								$todaySeconds= strtotime($todayDate);
								($todaySeconds);
								 strtotime($case->expectedCloseDate);

								if(strtotime($dt->format('Y-m-d')) >= strtotime($case->expectedCloseDate) && $case->saleStatus !== 'closed' ) {											
									$danger++;											
								}	
							}	
							echo " {$danger}";								
						} else {
							echo ' 0 ';
						}?>
						<span class="font-14"> Case(s)</span>									
					</div>
				</div>
			</div>		
		</div>					
		<button type="button"  class="btn-close nobg" >
			<a tabindex="0"  role="button" data-bs-toggle="popover" data-bs-trigger="focus" title="Organize your work with projects" data-bs-content="Before you start tracking your work, create projects, assign clients, and add tasks. You can choose from advanced billing rates, set up approval workflow, add custom budgets, and set task estimates. Assign tasks to your team and monitor allocated time."><i class="fa-solid fa-cog i-alt i-style  i-plain font-26  "></i></a>				
		</button>				
	</div>				
</div>
