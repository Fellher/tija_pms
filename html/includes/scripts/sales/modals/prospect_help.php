<!-- Prospect Help Documentation Modal -->
<div class="modal fade" id="prospectHelpModal" tabindex="-1" aria-labelledby="prospectHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="prospectHelpModalLabel">
                    <i class="ri-question-line me-2"></i>Prospect Management Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Navigation Tabs -->
                <ul class="nav nav-pills mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#helpOverview">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#helpAddProspect">Adding Prospects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#helpManage">Managing Prospects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#helpImport">Bulk Import</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#helpAnalytics">Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#helpWorkflow">Workflow</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="helpOverview">
                        <h5 class="mb-3"><i class="ri-information-line text-primary me-2"></i>What is Prospect Management?</h5>
                        <p>The Prospect Management module helps you track and nurture potential clients through your sales pipeline. It provides tools to:</p>
                        <ul>
                            <li><strong>Capture leads</strong> from various sources</li>
                            <li><strong>Qualify prospects</strong> using BANT criteria (Budget, Authority, Need, Timeline)</li>
                            <li><strong>Score leads</strong> automatically based on configurable rules</li>
                            <li><strong>Track interactions</strong> with detailed timeline</li>
                            <li><strong>Assign prospects</strong> to teams and territories</li>
                            <li><strong>Analyze performance</strong> with comprehensive dashboards</li>
                        </ul>

                        <div class="alert alert-info mt-4">
                            <h6><i class="ri-lightbulb-line me-2"></i>Key Concepts</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Lead Score</dt>
                                <dd class="col-sm-9">A numerical value (0-100) indicating the quality and likelihood of conversion</dd>

                                <dt class="col-sm-3">Qualification Status</dt>
                                <dd class="col-sm-9">The stage of prospect evaluation: Unqualified → Cold → Warm → Hot → Qualified</dd>

                                <dt class="col-sm-3">BANT</dt>
                                <dd class="col-sm-9">Budget, Authority, Need, Timeline - criteria for qualifying prospects</dd>

                                <dt class="col-sm-3">Pipeline</dt>
                                <dd class="col-sm-9 mb-0">The journey from initial contact to conversion or closure</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Adding Prospects Tab -->
                    <div class="tab-pane fade" id="helpAddProspect">
                        <h5 class="mb-3"><i class="ri-add-circle-line text-success me-2"></i>Adding New Prospects</h5>

                        <h6 class="mt-4">Method 1: Quick Add</h6>
                        <p>Use Quick Add for rapid entry of essential information:</p>
                        <ol>
                            <li>Click the <strong>"Quick Add"</strong> button</li>
                            <li>Fill in required fields (marked with *):
                                <ul>
                                    <li>Prospect Name</li>
                                    <li>Email Address</li>
                                    <li>Business Unit</li>
                                    <li>Lead Source</li>
                                </ul>
                            </li>
                            <li>Optionally add phone, estimated value, and notes</li>
                            <li>Click <strong>"Add Prospect"</strong></li>
                        </ol>
                        <div class="alert alert-success">
                            <i class="ri-check-line me-2"></i><strong>Best for:</strong> Quick capture during calls or meetings
                        </div>

                        <h6 class="mt-4">Method 2: Full Wizard</h6>
                        <p>Use the Full Wizard for complete prospect information:</p>
                        <ol>
                            <li>Click dropdown arrow → <strong>"Full Wizard"</strong></li>
                            <li>Complete 5 steps:
                                <ul>
                                    <li><strong>Step 1:</strong> Basic Information (name, email, phone, case name)</li>
                                    <li><strong>Step 2:</strong> Classification (business unit, source, industry, company size)</li>
                                    <li><strong>Step 3:</strong> Qualification (status, BANT criteria, estimated value)</li>
                                    <li><strong>Step 4:</strong> Assignment (team, territory, owner)</li>
                                    <li><strong>Step 5:</strong> Additional Details (address, website, tags, notes)</li>
                                </ul>
                            </li>
                            <li>Review and submit</li>
                        </ol>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i><strong>Tip:</strong> Lead score is calculated automatically upon creation
                        </div>
                    </div>

                    <!-- Managing Prospects Tab -->
                    <div class="tab-pane fade" id="helpManage">
                        <h5 class="mb-3"><i class="ri-settings-3-line text-warning me-2"></i>Managing Prospects</h5>

                        <h6 class="mt-3">Viewing Prospect Details</h6>
                        <p>Click on any prospect name to view comprehensive details including:</p>
                        <ul>
                            <li><strong>KPI Cards:</strong> Lead Score, Estimated Value, Days in Pipeline, BANT Score</li>
                            <li><strong>Overview:</strong> Contact info, classification, sales information</li>
                            <li><strong>Interactions:</strong> Complete timeline of all communications</li>
                            <li><strong>Activities:</strong> Scheduled tasks and follow-ups</li>
                        </ul>

                        <h6 class="mt-4">Editing Prospects</h6>
                        <ol>
                            <li>Click the <i class="ri-edit-line text-info"></i> edit icon on any prospect</li>
                            <li>Update information in the tabbed modal</li>
                            <li>Click <strong>"Save Changes"</strong></li>
                        </ol>
                        <div class="alert alert-warning">
                            <i class="ri-alert-line me-2"></i><strong>Note:</strong> Lead score is recalculated automatically when qualification changes
                        </div>

                        <h6 class="mt-4">Logging Interactions</h6>
                        <p>Keep track of all communications with prospects:</p>
                        <ol>
                            <li>Click <i class="ri-chat-3-line text-success"></i> on prospect or <strong>"Log Interaction"</strong> button</li>
                            <li>Select interaction type (Call, Email, Meeting, Note)</li>
                            <li>Fill in details:
                                <ul>
                                    <li>Date/Time</li>
                                    <li>Subject</li>
                                    <li>Description</li>
                                    <li>Outcome (Positive, Neutral, Negative)</li>
                                    <li>Next Steps</li>
                                </ul>
                            </li>
                            <li>Save to update timeline</li>
                        </ol>

                        <h6 class="mt-4">Using Filters</h6>
                        <ol>
                            <li>Click <strong>"Toggle Filters"</strong> to expand filter panel</li>
                            <li>Select criteria:
                                <ul>
                                    <li>Business Unit</li>
                                    <li>Lead Source</li>
                                    <li>Status (Open/Closed)</li>
                                    <li>Qualification Level</li>
                                    <li>Team/Territory</li>
                                    <li>Industry</li>
                                    <li>Owner</li>
                                    <li>Search by name/email</li>
                                </ul>
                            </li>
                            <li>Click <strong>"Apply Filters"</strong></li>
                            <li>Use <strong>"Reset"</strong> to clear all filters</li>
                        </ol>

                        <h6 class="mt-4">Bulk Operations</h6>
                        <p>Perform actions on multiple prospects simultaneously:</p>
                        <ol>
                            <li>Check boxes next to prospects you want to update</li>
                            <li>Select action from <strong>"Bulk Actions"</strong> dropdown:
                                <ul>
                                    <li>Assign to Team</li>
                                    <li>Update Status</li>
                                    <li>Update Qualification</li>
                                    <li>Export Selected</li>
                                    <li>Delete Selected</li>
                                </ul>
                            </li>
                            <li>Click <strong>"Apply"</strong></li>
                        </ol>
                    </div>

                    <!-- Bulk Import Tab -->
                    <div class="tab-pane fade" id="helpImport">
                        <h5 class="mb-3"><i class="ri-upload-cloud-line text-primary me-2"></i>Bulk Import Guide</h5>

                        <p>Import multiple prospects at once from a CSV file:</p>

                        <h6 class="mt-3">Step 1: Prepare Your File</h6>
                        <ul>
                            <li>Download the CSV template by clicking <strong>"Download Template"</strong></li>
                            <li>Fill in your prospect data</li>
                            <li>Required columns: Prospect Name, Email</li>
                            <li>Optional columns: Phone, Address, Website, Estimated Value, etc.</li>
                        </ul>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i><strong>Tip:</strong> Keep column headers in the first row
                        </div>

                        <h6 class="mt-4">Step 2: Upload File</h6>
                        <ol>
                            <li>Click <strong>"Import"</strong> button</li>
                            <li>Select your CSV file</li>
                            <li>Wait for file processing</li>
                        </ol>

                        <h6 class="mt-4">Step 3: Map Columns</h6>
                        <p>The system will auto-detect column mappings, but you can adjust:</p>
                        <ul>
                            <li>Review suggested mappings</li>
                            <li>Change dropdown selections if needed</li>
                            <li>Skip columns by selecting "-- Skip --"</li>
                            <li>Ensure required fields are mapped (marked with *)</li>
                        </ul>

                        <h6 class="mt-4">Step 4: Review & Import</h6>
                        <ol>
                            <li>Review import summary</li>
                            <li>Check for missing required fields</li>
                            <li>Click <strong>"Start Import"</strong></li>
                            <li>Wait for completion</li>
                            <li>Review results (success count, errors, duplicates)</li>
                        </ol>

                        <div class="alert alert-warning">
                            <i class="ri-alert-line me-2"></i><strong>Note:</strong> Duplicates are detected by email address and will be skipped
                        </div>
                    </div>

                    <!-- Analytics Tab -->
                    <div class="tab-pane fade" id="helpAnalytics">
                        <h5 class="mb-3"><i class="ri-bar-chart-line text-info me-2"></i>Understanding Analytics</h5>

                        <p>Navigate to <strong>Sales → Prospect Analytics</strong> to view comprehensive metrics:</p>

                        <h6 class="mt-3">KPI Cards</h6>
                        <dl class="row">
                            <dt class="col-sm-3">Total Prospects</dt>
                            <dd class="col-sm-9">All prospects in your pipeline</dd>

                            <dt class="col-sm-3">Qualified Leads</dt>
                            <dd class="col-sm-9">Prospects marked as "Qualified"</dd>

                            <dt class="col-sm-3">Avg Lead Score</dt>
                            <dd class="col-sm-9">Average score across all prospects</dd>

                            <dt class="col-sm-3">Pipeline Value</dt>
                            <dd class="col-sm-9 mb-0">Total estimated value of all open prospects</dd>
                        </dl>

                        <h6 class="mt-4">Charts</h6>
                        <ul>
                            <li><strong>Lead Source Distribution:</strong> Pie chart showing prospects by source</li>
                            <li><strong>Conversion Funnel:</strong> Bar chart showing prospects by qualification stage</li>
                            <li><strong>Lead Score Distribution:</strong> Histogram of score ranges</li>
                            <li><strong>Team Performance:</strong> Comparison of team metrics</li>
                        </ul>

                        <h6 class="mt-4">Top Lead Sources Table</h6>
                        <p>Detailed metrics for each lead source:</p>
                        <ul>
                            <li>Total prospects from source</li>
                            <li>Total estimated value</li>
                            <li>Conversion rate</li>
                            <li>Average lead score</li>
                        </ul>

                        <div class="alert alert-success">
                            <i class="ri-lightbulb-line me-2"></i><strong>Tip:</strong> Use date range filter to analyze specific time periods
                        </div>
                    </div>

                    <!-- Workflow Tab -->
                    <div class="tab-pane fade" id="helpWorkflow">
                        <h5 class="mb-3"><i class="ri-flow-chart text-success me-2"></i>Recommended Workflow</h5>

                        <div class="timeline">
                            <div class="timeline-item mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <span class="avatar avatar-sm avatar-rounded bg-primary">1</span>
                                    </div>
                                    <div>
                                        <h6>Capture Lead</h6>
                                        <p>Use Quick Add or Full Wizard to enter prospect information. Ensure you capture the lead source for tracking ROI.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-item mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <span class="avatar avatar-sm avatar-rounded bg-info">2</span>
                                    </div>
                                    <div>
                                        <h6>Initial Contact</h6>
                                        <p>Reach out to the prospect and log the interaction. Update BANT criteria based on the conversation.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-item mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <span class="avatar avatar-sm avatar-rounded bg-warning">3</span>
                                    </div>
                                    <div>
                                        <h6>Qualify Prospect</h6>
                                        <p>Update qualification status (Cold → Warm → Hot → Qualified) as you learn more. The lead score will update automatically.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-item mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <span class="avatar avatar-sm avatar-rounded bg-secondary">4</span>
                                    </div>
                                    <div>
                                        <h6>Assign & Track</h6>
                                        <p>Assign to appropriate team/territory. Set next follow-up date. Log all interactions to maintain complete history.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-item mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <span class="avatar avatar-sm avatar-rounded bg-success">5</span>
                                    </div>
                                    <div>
                                        <h6>Convert or Close</h6>
                                        <p>When prospect becomes a client, update status to "Closed" and link to client record. For lost opportunities, mark as closed with notes.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <span class="avatar avatar-sm avatar-rounded bg-danger">6</span>
                                    </div>
                                    <div>
                                        <h6>Analyze & Improve</h6>
                                        <p>Review analytics to identify best-performing sources, optimize team assignments, and improve conversion rates.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-primary mt-4">
                            <h6><i class="ri-star-line me-2"></i>Best Practices</h6>
                            <ul class="mb-0">
                                <li>Log every interaction immediately while details are fresh</li>
                                <li>Set next follow-up dates to avoid losing prospects</li>
                                <li>Update BANT criteria as you gather information</li>
                                <li>Use tags to categorize prospects for easy filtering</li>
                                <li>Review overdue follow-ups daily</li>
                                <li>Analyze lead sources monthly to optimize marketing spend</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
#prospectHelpModal .nav-pills .nav-link {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

#prospectHelpModal .nav-pills .nav-link.active {
    background-color: #0d6efd;
}

#prospectHelpModal dl dt {
    font-weight: 600;
    color: #495057;
}

#prospectHelpModal .timeline-item {
    position: relative;
    padding-left: 0;
}

#prospectHelpModal .timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -20px;
    width: 2px;
    background: #e9ecef;
}
</style>
