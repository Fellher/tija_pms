<?php
/**
 * Project Team Management Page
 * View and manage project team members with roles and assignments
 *
 * @package Tija Practice Management System
 * @subpackage Projects - Team Management
 * @version 3.0.0
 */

// Get all team members for the project
$teamMembers = Projects::project_team_full(
    array('projectID' => $projectID, 'Suspended' => 'N'),
    false,
    $DBConn
);

// Get project team roles for the modal
$projectTeamRoles = Projects::project_team_roles(array('Suspended' => 'N'), false, $DBConn);

// Get project details for context
$projectDetails = Projects::projects_mini(array('projectID' => $projectID), true, $DBConn);

// Get employees list for adding team members
$employees = Employee::employees(array('Suspended' => 'N'), false, $DBConn);
?>

<div class="container-fluid my-3" id="projectTeamContainer">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                <i class="ri-team-line me-2 text-primary"></i>Project Team
            </h3>
            <p class="text-muted mb-0">Manage team members, roles, and assignments for this project</p>
        </div>
        <button type="button"
                class="btn btn-primary addProjectTeam"
                data-bs-toggle="modal"
                data-bs-target="#manage_project_team"
                data-user-id="<?= htmlspecialchars($userDetails->ID ?? '') ?>"
                data-project-id="<?= htmlspecialchars($projectID) ?>">
            <i class="ri-user-add-line me-1"></i>Add Team Member
        </button>
    </div>

    <!-- Team Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Members</h6>
                            <h3 class="mb-0"><?= $teamMembers && is_array($teamMembers) ? count($teamMembers) : 0 ?></h3>
                        </div>
                        <div class="ms-3">
                            <i class="ri-team-line text-primary" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Unique Roles</h6>
                            <h3 class="mb-0">
                                <?php
                                $uniqueRoles = array();
                                if ($teamMembers && is_array($teamMembers)) {
                                    foreach ($teamMembers as $member) {
                                        if (isset($member->projectTeamRoleID) && !in_array($member->projectTeamRoleID, $uniqueRoles)) {
                                            $uniqueRoles[] = $member->projectTeamRoleID;
                                        }
                                    }
                                }
                                echo count($uniqueRoles);
                                ?>
                            </h3>
                        </div>
                        <div class="ms-3">
                            <i class="ri-user-star-line text-info" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active Members</h6>
                            <h3 class="mb-0">
                                <?php
                                $activeMembers = 0;
                                if ($teamMembers && is_array($teamMembers)) {
                                    foreach ($teamMembers as $member) {
                                        if (!isset($member->Suspended) || $member->Suspended == 'N') {
                                            $activeMembers++;
                                        }
                                    }
                                }
                                echo $activeMembers;
                                ?>
                            </h3>
                        </div>
                        <div class="ms-3">
                            <i class="ri-user-check-line text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Project Owner</h6>
                            <h6 class="mb-0">
                                <?php
                                if ($projectDetails && isset($projectDetails->projectOwnerID)) {
                                    $owner = Data::users(array('ID' => $projectDetails->projectOwnerID), true, $DBConn);
                                    echo $owner ? htmlspecialchars(($owner->FirstName ?? '') . ' ' . ($owner->Surname ?? '')) : 'Not Set';
                                } else {
                                    echo 'Not Set';
                                }
                                ?>
                            </h6>
                        </div>
                        <div class="ms-3">
                            <i class="ri-user-settings-line text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Table -->
    <div class="card custom-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="ri-group-line me-2"></i>Team Members
            </h5>
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text"><i class="ri-search-line"></i></span>
                <input type="text" class="form-control" id="teamSearchInput" placeholder="Search team members...">
            </div>
        </div>
        <div class="card-body">
            <?php if ($teamMembers && is_array($teamMembers) && count($teamMembers) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="teamMembersTable">
                        <thead>
                            <tr>
                                <th>Team Member</th>
                                <th>Project Role</th>
                                <th>Job Title</th>
                                <th>Business Unit</th>
                                <th>Date Added</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teamMembers as $member): ?>
                                <tr data-member-name="<?= strtolower(htmlspecialchars($member->teamMemberName ?? '')) ?>"
                                    data-role-name="<?= strtolower(htmlspecialchars($member->projectTeamRoleName ?? '')) ?>"
                                    data-job-title="<?= strtolower(htmlspecialchars($member->jobTitle ?? '')) ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <?= strtoupper(substr($member->teamMemberName ?? '?', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($member->teamMemberName ?? 'Unknown Member') ?></h6>
                                                <?php if (isset($member->userInitials) && $member->userInitials): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($member->userInitials) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (isset($member->projectTeamRoleName) && $member->projectTeamRoleName): ?>
                                            <span class="badge bg-primary-transparent">
                                                <i class="ri-user-star-line me-1"></i>
                                                <?= htmlspecialchars($member->projectTeamRoleName) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">No Role</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($member->jobTitle) && $member->jobTitle): ?>
                                            <span class="text-dark"><?= htmlspecialchars($member->jobTitle) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Not Specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($member->businessUnitName) && $member->businessUnitName): ?>
                                            <small class="text-muted"><?= htmlspecialchars($member->businessUnitName) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($member->DateAdded) && $member->DateAdded): ?>
                                            <small class="text-muted">
                                                <?= date('M d, Y', strtotime($member->DateAdded)) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary addProjectTeam"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#manage_project_team"
                                                    data-project-team-member-id="<?= htmlspecialchars($member->projectTeamMemberID ?? '') ?>"
                                                    data-user-id="<?= htmlspecialchars($member->userID ?? '') ?>"
                                                    data-project-id="<?= htmlspecialchars($member->projectID ?? '') ?>"
                                                    data-suspended="<?= htmlspecialchars($member->Suspended ?? 'N') ?>"
                                                    data-project-team-role-id="<?= htmlspecialchars($member->projectTeamRoleID ?? '') ?>"
                                                    title="Edit Team Member">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <?php if (isset($member->Suspended) && $member->Suspended == 'N'): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger suspendTeamMember"
                                                        data-member-id="<?= htmlspecialchars($member->projectTeamMemberID ?? '') ?>"
                                                        data-member-name="<?= htmlspecialchars($member->teamMemberName ?? '') ?>"
                                                        title="Suspend Team Member">
                                                    <i class="ri-user-unfollow-line"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-success activateTeamMember"
                                                        data-member-id="<?= htmlspecialchars($member->projectTeamMemberID ?? '') ?>"
                                                        data-member-name="<?= htmlspecialchars($member->teamMemberName ?? '') ?>"
                                                        title="Activate Team Member">
                                                    <i class="ri-user-follow-line"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="ri-team-line text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                    </div>
                    <h5 class="text-muted mb-3">No Team Members Found</h5>
                    <p class="text-muted mb-4">Start building your project team by adding team members.</p>
                    <button type="button"
                            class="btn btn-primary addProjectTeam"
                            data-bs-toggle="modal"
                            data-bs-target="#manage_project_team"
                            data-user-id="<?= htmlspecialchars($userDetails->ID ?? '') ?>"
                            data-project-id="<?= htmlspecialchars($projectID) ?>">
                        <i class="ri-user-add-line me-1"></i>Add Your First Team Member
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Team Roles Summary -->
    <?php if ($teamMembers && is_array($teamMembers) && count($teamMembers) > 0): ?>
    <div class="card custom-card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="ri-user-star-line me-2"></i>Team Roles Distribution
            </h5>
        </div>
        <div class="card-body">
            <?php
            // Group team members by role
            $roleGroups = array();
            foreach ($teamMembers as $member) {
                $roleName = $member->projectTeamRoleName ?? 'No Role';
                if (!isset($roleGroups[$roleName])) {
                    $roleGroups[$roleName] = array(
                        'name' => $roleName,
                        'count' => 0,
                        'members' => array()
                    );
                }
                $roleGroups[$roleName]['count']++;
                $roleGroups[$roleName]['members'][] = $member->teamMemberName ?? 'Unknown';
            }
            ?>
            <div class="row g-3">
                <?php foreach ($roleGroups as $role): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card border-left-primary h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">
                                        <i class="ri-user-star-line text-primary me-1"></i>
                                        <?= htmlspecialchars($role['name']) ?>
                                    </h6>
                                    <span class="badge bg-primary"><?= $role['count'] ?></span>
                                </div>
                                <div class="mt-2">
                                    <?php foreach ($role['members'] as $memberName): ?>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="ri-user-line text-muted me-2" style="font-size: 0.875rem;"></i>
                                            <small class="text-muted"><?= htmlspecialchars($memberName) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Team Management Modal -->
<?php
echo Utility::form_modal_header(
    "manage_project_team",
    "projects/manage_project_team.php",
    "Manage Project Team Member",
    array('modal-lg', 'modal-dialog-centered'),
    $base
);
include 'includes/scripts/projects/modals/manage_project_team.php';
echo Utility::form_modal_footer("Save Team Member", "manage_project_team_btn", 'btn btn-primary btn-sm');
?>

<!-- Team Management Scripts -->
<script>
(function() {
    'use strict';

    // Search functionality
    const searchInput = document.getElementById('teamSearchInput');
    const teamTable = document.getElementById('teamMembersTable');

    if (searchInput && teamTable) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = teamTable.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const memberName = row.getAttribute('data-member-name') || '';
                const roleName = row.getAttribute('data-role-name') || '';
                const jobTitle = row.getAttribute('data-job-title') || '';

                const matches = memberName.includes(searchTerm) ||
                              roleName.includes(searchTerm) ||
                              jobTitle.includes(searchTerm);

                row.style.display = matches ? '' : 'none';
            });
        });
    }

    // Initialize modal data population
    document.querySelectorAll('.addProjectTeam').forEach(button => {
        button.addEventListener('click', function() {
            const form = document.getElementById('manage_project_team_form');
            if (!form) return;

            // Get all data attributes from the button
            const data = this.dataset;

            // Map form fields to their corresponding data attributes
            const fieldMappings = {
                'projectTeamMemberID': 'projectTeamMemberId',
                'projectID': 'projectId',
                'userID': 'userId',
                'projectTeamRoleID': 'projectTeamRoleId'
            };

            // Fill regular form inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
                const input = form.querySelector(`[name="${fieldName}"]`);
                if (input && data[dataAttribute]) {
                    input.value = data[dataAttribute];
                }
            }

            // Fill select elements
            const selectElements = form.querySelectorAll('select');
            selectElements.forEach(select => {
                const dataValue = data[select.name.toLowerCase().replace('id', 'Id')] ||
                                data[select.name] ||
                                data[select.id];

                if (dataValue) {
                    const option = select.querySelector(`option[value="${dataValue}"]`);
                    if (option) {
                        option.selected = true;
                        // Trigger change event for select elements with change listeners
                        select.dispatchEvent(new Event('change'));
                    }
                }
            });

            // Reset form if adding new member
            if (!data.projectTeamMemberId) {
                form.reset();
                const projectIDInput = form.querySelector('[name="projectID"]');
                if (projectIDInput) {
                    projectIDInput.value = data.projectId || '<?= $projectID ?>';
                }
            }
        });
    });

    // Suspend/Activate team member functionality
    document.querySelectorAll('.suspendTeamMember, .activateTeamMember').forEach(button => {
        button.addEventListener('click', function() {
            const memberID = this.getAttribute('data-member-id');
            const memberName = this.getAttribute('data-member-name');
            const isSuspend = this.classList.contains('suspendTeamMember');
            const action = isSuspend ? 'suspend' : 'activate';

            if (confirm(`Are you sure you want to ${action} ${memberName}?`)) {
                // TODO: Implement suspend/activate API call
                console.log(`${action} team member:`, memberID);
                // You can add AJAX call here to handle suspend/activate
                alert(`Team member ${action} functionality will be implemented here.`);
            }
        });
    });

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"], [title]')
        );
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
})();
</script>

<!-- Custom Styles -->
<style>
#projectTeamContainer .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

#projectTeamContainer .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

#projectTeamContainer .avatar {
    width: 40px;
    height: 40px;
    font-size: 1rem;
    font-weight: 600;
}

#projectTeamContainer .table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    color: #6c757d;
    border-bottom: 2px solid #dee2e6;
}

#projectTeamContainer .table td {
    vertical-align: middle;
}

#projectTeamContainer .badge {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

#projectTeamContainer .border-left-primary {
    border-left: 4px solid #0d6efd !important;
}

#projectTeamContainer .btn-group .btn {
    border-radius: 0.375rem;
    margin-left: 0.25rem;
}

#projectTeamContainer .btn-group .btn:first-child {
    margin-left: 0;
}

@media (max-width: 768px) {
    #projectTeamContainer .table-responsive {
        font-size: 0.875rem;
    }

    #projectTeamContainer .avatar {
        width: 32px;
        height: 32px;
        font-size: 0.875rem;
    }
}
</style>
