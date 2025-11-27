<?php
// var_dump($isValidAdmin);
if (!$isValidAdmin) {
   Alert::info("You need to be logged in as a valid administrator to access this page", true, 
       array('fst-italic', 'text-center', 'font-18'));
   return;
}
// Initialize variables
$userID = Utility::clean_string($_GET['uid'] ?? $userDetails->ID);
$entityID = Utility::clean_string($_GET['eid'] ?? '');
$entityDetails = Data::entities_full(array("entityID"=>$entityID), true, $DBConn);

$financialStatementID = Utility::clean_string($_GET['stmtID'] ?? '');
$investmentFinancialStatementID = Utility::clean_string($_GET['invID'] ?? '');

// Build query string
$getString .= "&uid={$userID}&eid={$entityID}";
if ($investmentFinancialStatementID) $getString .= "&invID={$investmentFinancialStatementID}";
if ($financialStatementID) $getString .= "&stmtID={$financialStatementID}";

// Get investment accounts
$invAccounts = Tax::statement_of_investment_allowance_accounts(["Lapsed" => 'N', "Suspended" => 'N'], false, $DBConn);
?>

<div class="container-fluid">
    <div class="row pt-2 bg-light px-lg-3 mb-3">
        <h2 class="mb-2 t300 fs-3 border-bottom border-primary">
            Tax Computation
            <span class="float-end t600 fs-4">(<?php echo htmlspecialchars($entityDetails->entityName) ?>)</span>
        </h2>
    </div>
    
    <div class="container-fluid">
        <form action="<?php echo "{$base}php/scripts/tax/admin/map_investment_accounts.php" ?>" method="POST">
            <h3 class="text-center">Select Statement to Map

            <span class='float-end'>
            <a href="<?php echo (isset($_SESSION['mapURLReturn']) && !empty($_SESSION['mapURLReturn'])) ? "{$base}html/{$_SESSION['mapURLReturn']}" : "{$base}html/?s={$s}&ss={$ss}&p=data_upload&eid={$entityID}&finstmtID={$investmentFinancialStatementID}" ?>" 
               class="btn btn-primary">back</a>
        </h3>
            </h3>
            
            <?php
            // Get investment statements
            $statementOfInvestment = Tax::financial_statements_types([
                "statementTypeNode" => 'StatementofInvestmentAllowance',
                "Lapsed" => 'N',
                "Suspended" => 'N'
            ], true, $DBConn);

            $investmentStatement = Tax::financial_statements([
                "financialStatementTypeID" => $statementOfInvestment->financialStatementTypeID,
                'statementTypeNode' => 'StatementofInvestmentAllowance',
                'entityID' => $entityID,
                "Lapsed" => 'N',
                "Suspended" => 'N'
            ], false, $DBConn);

            if ($investmentStatement): 
               // var_dump($investmentStatement);
            ?>
                <input type="hidden" name="entityID" value="<?php echo htmlspecialchars($entityID) ?>">
                <input type="hidden" name="financialStatementID" value="<?php echo htmlspecialchars($financialStatementID) ?>">
                <input type="hidden" name="investmentFinancialStatementID" value="<?php echo htmlspecialchars($investmentFinancialStatementID) ?>">
                <h3> Select Statement of Investment Allowance to Map</h3>
                <div class="list-group list-group-flush mb-6">
                    <?php foreach($investmentStatement as $stmt): 
                        $isActive = ($investmentFinancialStatementID == $stmt->financialStatementID) ? "active" : "";
                        $statementLabel = "{$entityDetails->entityName} {$stmt->financialStatementTypeName} for full year {$stmt->fiscalYear}";
                        ?>
                        <a href="<?php echo "{$getString}&invID={$stmt->financialStatementID}" ?>" 
                           class="list-group-item list-group-item-action <?php echo $isActive ?>"
                           aria-current="true">
                            <?php echo htmlspecialchars($statementLabel) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-6">
                    <?php if ($investmentFinancialStatementID):
                        $investmentStatementData = Tax::statement_of_investment_data([
                            "financialStatementID" => $investmentFinancialStatementID,
                            'entityID' => $entityID,
                            "Lapsed" => 'N',
                            "Suspended" => 'N'
                        ], false, $DBConn);
                        
                        if ($investmentStatementData): ?>
                            <div class='table'>
                                <table class='table table-striped table-bordered table-sm'>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Investment Name</th>
                                            <th>Investment rate</th>
                                            <th>Initial W.D.V</th>
                                            <th>Start Date</th>
                                            <th>Additions (value)</th>
                                            <th>Disposals (Value)</th>
                                            <th>Wear & Tear Allowance Value</th>
                                            <th>End W.D.V</th>
                                            <th>End Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $investmentTotal = 0;
                                        $idata = count($investmentStatementData);
                                        foreach ($investmentStatementData as $key => $stmtData):
                                            $mappedInvestment = Tax::investment_accounts_mapping([
                                                "InvestmentAllowanceID" => $stmtData->InvestmentAllowanceID,
                                                'entityID' => $entityID,
                                                "Lapsed" => 'N',
                                                "Suspended" => 'N'
                                            ], true, $DBConn);                                            
                                            if ($mappedInvestment) continue;                                            
                                            $investmentTotal += $stmtData->endWriteDownValue;?>
                                            <tr>
                                                <td><input type="radio" name="InvestmentAllowanceID" value="<?php echo $stmtData->InvestmentAllowanceID ?>"></td>
                                                <td><?php echo htmlspecialchars($stmtData->investmentName) ?></td>
                                                <td><?php echo $stmtData->rate * 100 ?>%</td>
                                                <td><?php echo Utility::formatToCurrency($stmtData->initialWriteDownValue, "") ?></td>
                                                <td><?php echo $stmtData->beginDate ?></td>
                                                <td><?php echo Utility::formatToCurrency($stmtData->additions, "") ?></td>
                                                <td><?php echo Utility::formatToCurrency($stmtData->disposals, "") ?></td>
                                                <td><?php echo Utility::formatToCurrency($stmtData->wearAndTearAllowance, "") ?></td>
                                                <td><?php echo Utility::formatToCurrency($stmtData->endWriteDownValue, "") ?></td>
                                                <td><?php echo $stmtData->endDate ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="8">Total</td>
                                            <td><?php echo Utility::formatToCurrency($investmentTotal, "KES") ?></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif;
                    endif; ?>
                </div>

                <div class="col-6">
                    <div class="card card-body">
                        <h3>Investment Accounts</h3>
                        <?php if ($invAccounts): ?>
                            <div class="list-group list-group-flush mb-6">
                                <?php foreach($invAccounts as $invstmtAcc):
                                    // Maped accounts for each account
                                    $mappedInvestmentAccounts = Tax::investment_accounts_mapping([
                                        "investmentAllowanceAccountID" => $invstmtAcc->investmentAllowanceAccountID,
                                        'entityID' => $entityID,
                                        "Lapsed" => 'N',
                                        "Suspended" => 'N'
                                    ], false, $DBConn);
                                    ?>
                                    <div class="form-check list-group-item">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="investmentAllowanceAccountID" 
                                               id="inv<?php echo $invstmtAcc->investmentAllowanceAccountID ?>" 
                                               value="<?php echo $invstmtAcc->investmentAllowanceAccountID ?>">
                                        <label class="form-check-label" 
                                               for="inv<?php echo $invstmtAcc->investmentAllowanceAccountID ?>">
                                            <?php echo htmlspecialchars($invstmtAcc->accountName) ?>
                                        </label>
                                    </div>
                                    <?php
                                    if ($mappedInvestmentAccounts): ?>
                                        <div class="list-group list-group-flush mb-2">
                                            <?php foreach($mappedInvestmentAccounts as $mappedInvAcc):
                                                $accountData= Tax::statement_of_investment_data(array("InvestmentAllowanceID" => $mappedInvAcc->InvestmentAllowanceID), true, $DBConn);
                                                ?>
                                                <div class="list-group-item list-group-item-action fst-italic ms-4">
                                                    <span><?php echo htmlspecialchars($accountData->investmentName) ?></span>
                                                    <span> E.W.D value : <?php echo Utility::formatToCurrency($accountData->endWriteDownValue, "KES") ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                              
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="col-6">
                    <button type="submit" class="btn btn-primary">Map Investment Account</button>
                </div>
            </div>
        </form>
    </div>
</div>