

   <div class="card card-body">
      <div class="table-responsive">
         <table class="table table-hover table-borderless table-striped table-vcenter text-nowrap table-sm mb-0">
               <thead>
                  <tr>
                     <th>Client Name</th>
                     <th>Contact Person</th>
                     <th>Client Owner</th>
                     <th>Projects</th>
                     <th>Sales cases</th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach ($clientsArray as $client): 
                 
                        $clientDetails = Client::clients_full(array('clientID' => $client), true, $DBConn);
                        // var_dump($clientDetails);
                        $clientOwner = $clientDetails->accountOwnerID; // Assuming this is how you get the client owner
                        $contactPerson = $clientDetails->clientContactName; // Assuming this is how you get the contact person
                        $projects = Projects::projects_full(['clientID'=>$clientDetails->clientID], false,  $DBConn); // Fetch projects/sales for the client
                        $sales = Sales::sales_case_full(['clientID'=>$clientDetails->clientID], false,  $DBConn); // Fetch sales for the client
                        // var_dump($projects);
                        // var_dump($sales);
                     ?>
                     <tr>
                        <td><?php echo htmlspecialchars($clientDetails->clientName); ?></td>
                        <td><?php echo htmlspecialchars($contactPerson); ?></td>
                        <td><?php echo htmlspecialchars($clientDetails->clientOwnerName); ?></td>
                        <td>
                           <?php if($projects): ?>
                              <ul>
                                 <?php foreach ($projects as $project): ?>
                                    <li><?php echo htmlspecialchars($project->projectName); ?> /
                                       <?php echo htmlspecialchars($project->clientName); ?>
                                 </li>
                                 <?php endforeach; ?>
                              </ul>
                           <?php else: ?>
                              <p>No projects found</p>
                           <?php endif; ?>
                        </td>
                        <td>
                           <?php if($sales): ?>
                                 <ul>
                                    <?php foreach ($sales as $sale): ?>
                                       <li><?php echo htmlspecialchars($sale->salesCaseName); ?> /
                                          <?php echo htmlspecialchars($sale->clientName); ?></li>
                                    </li>
                                    <?php endforeach; ?>
                                 </ul>
                              <?php else: ?>
                              <p>No sales found</p>
                           <?php endif; ?>
                        </td>
                     </tr>
                  <?php endforeach; ?>
               </tbody>
         </table>
      </div>
   </div>

