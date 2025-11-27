<!-- Refactored Add Administrator Modal -->
<style>
/* Admin Modal Scrolling */
#manageAdmin .modal-dialog {
    max-height: 90vh;
    margin: 1.75rem auto;
}

#manageAdmin .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#manageAdmin .modal-body {
    overflow-y: auto;
    max-height: calc(90vh - 140px);
    flex: 1 1 auto;
}

#manageAdmin .modal-header,
#manageAdmin .modal-footer {
    flex-shrink: 0;
}

/* Toggle Button Styles */
.user-mode-toggle {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.user-mode-toggle .btn {
    border-radius: 0 !important;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
}

.user-mode-toggle input[type="radio"]:checked + label {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}

/* Section Cards */
.admin-section-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.admin-section-card.active {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.section-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 1rem;
}

.entity-user-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}

.user-item {
    padding: 0.75rem;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: all 0.2s;
}

.user-item:last-child {
    border-bottom: none;
}

.user-item:hover {
    background-color: #f8f9fa;
}

.user-item.selected {
    background-color: #e3f2fd;
    border-left: 3px solid #667eea;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #667eea;
    color: white;
    font-weight: 600;
}

/* Hidden field */
input[name="adminID"] {
    display: none;
}
</style>

<input type="hidden" name="adminID" id="adminID">

<div class="row">
    <div class="col-12">
        <!-- Admin Mode Selection -->
        <div class="mb-4">
            <div class="btn-group user-mode-toggle w-100" role="group">
                <input type="radio" class="btn-check" name="adminSelect" id="selectExistingUser" value="existing" checked>
                <label class="btn btn-outline-primary" for="selectExistingUser">
                    <i class="fas fa-user-check me-2"></i>Select Existing User
                </label>

                <input type="radio" class="btn-check" name="adminSelect" id="createNewUser" value="new">
                <label class="btn btn-outline-primary" for="createNewUser">
                    <i class="fas fa-user-plus me-2"></i>Create New User
                </label>
            </div>
        </div>

        <!-- Organization & Entity Selection (Always Visible) -->
        <div class="card admin-section-card mb-3">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="fas fa-building text-primary me-2"></i>Organization & Entity Assignment
                </h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="orgDataID" class="form-label">
                            Organization <span class="text-danger">*</span>
                        </label>
                        <select name="orgDataID" id="orgDataID" class="form-select" required>
                            <option value="">Select Organization</option>
                            <?php if ($organisations): ?>
                                <?php foreach ($organisations as $org): ?>
                                    <option value="<?= $org->orgDataID ?>">
                                        <?= htmlspecialchars($org->orgName) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="entityID" class="form-label">
                            Entity <small class="text-muted">(Optional - for filtering users)</small>
                        </label>
                        <select name="entityID" id="entityID" class="form-select">
                            <option value="">All Entities</option>
                            <!-- default option -->
                             <?php if ($entities): ?>
                                <?php foreach ($entities as $entity): ?>
                                    <option value="<?= $entity->entityID ?>">
                                        <?= htmlspecialchars($entity->entityName) ?>
                                    </option>
                                <?php endforeach; ?>
                             <?php endif; ?>
                            <!-- Populated dynamically based on organization -->
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="adminTypeID" class="form-label">
                            Admin Role/Type <span class="text-danger">*</span>
                        </label>
                        <select name="adminTypeID" id="adminTypeID" class="form-select" required>
                            <option value="">Select Admin Type</option>
                            <?php if ($adminTypes): ?>
                                <?php foreach ($adminTypes as $role): ?>
                                    <option value="<?= $role->adminTypeID ?>">
                                        <?= htmlspecialchars($role->adminTypeName) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="form-text text-muted">
                            Super Admin: Full access | System Admin: Org-level | Entity Admin: Entity-level
                        </small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="unitID" class="form-label">
                            Unit <small class="text-muted">(Optional)</small>
                        </label>
                        <select name="unitID" id="unitID" class="form-select">
                            <option value="">Select Unit</option>
                            <!-- Populated dynamically based on entity -->
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Select Existing User Section -->
        <div class="card admin-section-card existing-user-section">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="section-icon bg-primary-transparent">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">Select From Organization Users</h6>
                        <p class="mb-0 text-muted small">Choose an existing user from the organization (optionally filter by entity)</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="input-group">
                            <span class="input-group-text" id="searchIcon">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   id="searchEntityUsers"
                                   placeholder="Search users by name or email...">
                            <span class="input-group-text d-none" id="searchPending">
                                <i class="fas fa-clock text-muted"></i>
                                <small class="ms-2 text-muted">Searching in <span id="searchCountdown">10</span>s...</small>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <label class="form-label">Available Users</label>
                        <div class="entity-user-list" id="entityUserList">
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle fs-24 d-block mb-2"></i>
                                Select an organization above to see available users
                            </div>
                        </div>
                        <input type="hidden" name="userID" id="selectedUserID">
                    </div>
                </div>
            </div>
        </div>

        <!-- Create New User Section -->
        <div class="card admin-section-card new-user-section d-none">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="section-icon bg-success-transparent">
                        <i class="fas fa-user-plus text-success"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">Create New Admin User</h6>
                        <p class="mb-0 text-muted small">Add a new user and assign as administrator</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="FirstName" class="form-label">
                            First Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="FirstName"
                               id="FirstName"
                               class="form-control"
                               placeholder="John">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="Surname" class="form-label">
                            Last Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="Surname"
                               id="Surname"
                               class="form-control"
                               placeholder="Doe">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="OtherNames" class="form-label">
                            Other Names
                        </label>
                        <input type="text"
                               name="OtherNames"
                               id="OtherNames"
                               class="form-control"
                               placeholder="Middle name">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="Email" class="form-label">
                            Email Address <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               name="Email"
                               id="Email"
                               class="form-control"
                               placeholder="john.doe@example.com">
                        <small class="form-text text-muted">
                            Login credentials will be sent to this email
                        </small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="PhoneNumber" class="form-label">
                            Phone Number
                        </label>
                        <input type="tel"
                               name="PhoneNumber"
                               id="PhoneNumber"
                               class="form-control"
                               placeholder="+254712345678">
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> A temporary password will be auto-generated and sent to the user's email.
                </div>
            </div>
        </div>

        <!-- Additional Settings (Optional) -->
        <div class="card admin-section-card">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="fas fa-cog text-primary me-2"></i>Additional Settings
                </h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="unitTypeID" class="form-label">
                            Unit Type
                        </label>
                        <select name="unitTypeID" id="unitTypeID" class="form-select">
                            <option value="">Select Unit Type</option>
                            <!-- Populated dynamically if needed -->
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Options</label>
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="isEmployee"
                                   id="isEmployee"
                                   value="Y">
                            <label class="form-check-label" for="isEmployee">
                                User is also an employee
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="sendNotification"
                                   id="sendNotification"
                                   value="Y"
                                   checked>
                            <label class="form-check-label" for="sendNotification">
                                Send notification email
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // Mode toggle functionality
    const selectExistingBtn = document.getElementById('selectExistingUser');
    const createNewBtn = document.getElementById('createNewUser');
    const existingSection = document.querySelector('.existing-user-section');
    const newSection = document.querySelector('.new-user-section');

    // Track if data is currently being loaded to prevent duplicate calls
    let isLoadingOrgData = false;
    let lastLoadedOrgId = null;

    // Get user initials
    function getInitials(firstName, lastName) {
        const first = firstName ? firstName.charAt(0).toUpperCase() : '';
        const last = lastName ? lastName.charAt(0).toUpperCase() : '';
        return first + last;
    }

    // Render user list
    function renderUserList(users) {
        const userListContainer = document.getElementById('entityUserList');
        const dataDir = '<?= $config["DataDir"] ?>';
        let html = '';

        console.log('Rendering', users.length, 'users');

        users.forEach(user => {
            const initials = getInitials(user.FirstName, user.Surname);
            const isAdmin = user.adminID ? true : false;

            // Construct proper image path
            let avatarHTML;
            if (user.profile_image && user.profile_image.trim() !== '') {
                // Check if it's already a full URL or needs data directory path
                const imagePath = user.profile_image.startsWith('http') ?
                    user.profile_image :
                    dataDir + user.profile_image;
                avatarHTML = `<img src="${imagePath}" class="user-avatar" alt="Profile" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                             <div class="user-avatar" style="display:none;">${initials}</div>`;
            } else {
                avatarHTML = `<div class="user-avatar">${initials}</div>`;
            }

            html += `
                <div class="user-item ${isAdmin ? 'disabled opacity-50' : ''}"
                     data-user-id="${user.ID}"
                     data-user-name="${user.FirstName} ${user.Surname}"
                     data-user-email="${user.Email}"
                     onclick="${isAdmin ? '' : 'selectUser(this)'}">
                    <div class="d-flex align-items-center">
                        ${avatarHTML}
                        <div class="ms-3 flex-fill">
                            <h6 class="mb-0">${user.FirstName} ${user.Surname}</h6>
                            <small class="text-muted">
                                <i class="fas fa-envelope me-1"></i>${user.Email}
                            </small>
                            ${isAdmin ?
                                '<br><span class="badge bg-warning-transparent mt-1">Already Admin</span>' :
                                ''
                            }
                        </div>
                        ${!isAdmin ? '<i class="fas fa-check-circle text-success d-none selected-icon"></i>' : ''}
                    </div>
                </div>
            `;
        });

        userListContainer.innerHTML = html;
        console.log('User list rendered successfully');
    }

    // User selection handler
    window.selectUser = function(element) {
        // Remove selection from all
        document.querySelectorAll('.user-item').forEach(item => {
            item.classList.remove('selected');
            const icon = item.querySelector('.selected-icon');
            if (icon) icon.classList.add('d-none');
        });

        // Add selection to clicked
        element.classList.add('selected');
        const icon = element.querySelector('.selected-icon');
        if (icon) icon.classList.remove('d-none');

        // Set hidden field
        const userId = element.getAttribute('data-user-id');
        document.getElementById('selectedUserID').value = userId;

        // Also set the userID select (for backwards compatibility)
        const userSelect = document.querySelector('select[name="userID"]');
        if (userSelect) {
            userSelect.value = userId;
        }

        console.log('User selected:', {
            id: userId,
            name: element.getAttribute('data-user-name'),
            email: element.getAttribute('data-user-email')
        });
    };

    // Function to load entities and users for an organization
    function loadOrganizationData(orgId) {
        if (!orgId) {
            console.log('No orgId provided to loadOrganizationData');
            return;
        }

        // Prevent duplicate loading for the same org
        if (isLoadingOrgData && lastLoadedOrgId === orgId) {
            console.log('Already loading data for orgDataID:', orgId, '- skipping duplicate call');
            return;
        }

        isLoadingOrgData = true;
        lastLoadedOrgId = orgId;

        console.log('Loading organization data for orgDataID:', orgId);

        const entitySelect = document.getElementById('entityID');
        const userListContainer = document.getElementById('entityUserList');

        if (!entitySelect) {
            console.error('Entity select element not found');
            return;
        }

        // Clear entity dropdown
        entitySelect.innerHTML = '<option value="">All Entities</option>';

        // Show loading for user list
        userListContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2">Loading users...</p>
            </div>
        `;

        // Fetch entities for this organization
        const entitiesUrl = '<?= $base ?>php/scripts/global/admin/get_entities_for_org.php?orgDataID=' + orgId;
        console.log('Fetching entities from:', entitiesUrl);

        fetch(entitiesUrl)
            .then(response => {
                console.log('Entities response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Entities data received:', data);

                if (data && data.success && Array.isArray(data.entities) && data.entities.length > 0) {
                    console.log('Adding', data.entities.length, 'entities to dropdown');
                    data.entities.forEach((entity, index) => {
                        if (entity && entity.entityID && entity.entityName) {
                            const option = document.createElement('option');
                            option.value = entity.entityID;
                            option.textContent = entity.entityName;
                            entitySelect.appendChild(option);
                            console.log('Added entity:', entity.entityName, 'with ID:', entity.entityID);
                        }
                    });
                    console.log('Total options in dropdown:', entitySelect.options.length);
                } else {
                    console.log('No entities found or empty array. Data:', data);
                }
            })
            .catch(error => {
                console.error('Error loading entities:');
                console.error('Error type:', typeof error);
                console.error('Error message:', error ? error.message : 'Unknown error');
                console.error('Error stack:', error ? error.stack : 'No stack trace');
            })
            .finally(() => {
                console.log('Entities fetch completed');
            });

        // Fetch ALL users for this organization
        const usersUrl = '<?= $base ?>php/scripts/global/admin/get_entity_users.php?orgDataID=' + orgId;
        console.log('Fetching users from:', usersUrl);

        fetch(usersUrl)
            .then(response => {
                console.log('Users response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Users data received:', data);

                if (data && data.success && Array.isArray(data.users) && data.users.length > 0) {
                    console.log('Found', data.users.length, 'users, rendering list');
                    renderUserList(data.users);
                } else {
                    console.log('No users found for organization. Data:', data);
                    userListContainer.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-users-slash fs-24 d-block mb-2"></i>
                            <p class="mb-0">No users found in this organization</p>
                            <small>Try creating a new user</small>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading users:');
                console.error('Error type:', typeof error);
                console.error('Error message:', error ? error.message : 'Unknown error');
                console.error('Error stack:', error ? error.stack : 'No stack trace');

                userListContainer.innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-triangle fs-24 d-block mb-2"></i>
                        <p class="mb-0">Error loading users</p>
                        <small>${error ? error.message : 'Unknown error'}</small>
                    </div>
                `;
            })
            .finally(() => {
                console.log('Users fetch completed');
                // Reset loading flag after both operations complete
                isLoadingOrgData = false;
            });
    }

    if (selectExistingBtn && createNewBtn) {
        selectExistingBtn.addEventListener('click', function() {
            existingSection.classList.remove('d-none');
            newSection.classList.add('d-none');
            existingSection.classList.add('active');
            newSection.classList.remove('active');

            // Make existing user fields required
            document.querySelector('#selectedUserID').required = true;
            // Make new user fields optional
            document.querySelector('#FirstName').required = false;
            document.querySelector('#Surname').required = false;
            document.querySelector('#Email').required = false;
        });

        createNewBtn.addEventListener('click', function() {
            newSection.classList.remove('d-none');
            existingSection.classList.add('d-none');
            newSection.classList.add('active');
            existingSection.classList.remove('active');

            // Make new user fields required
            document.querySelector('#FirstName').required = true;
            document.querySelector('#Surname').required = true;
            document.querySelector('#Email').required = true;
            // Make existing user field optional
            document.querySelector('#selectedUserID').required = false;
        });
    }

    // Organization change handler - load entities and users
    // Use querySelector to specifically target the SELECT element within the modal
    const orgSelect = document.querySelector('#manageAdmin select[name="orgDataID"]');
    if (orgSelect) {
        console.log('Organization select element found:', orgSelect.tagName);

        orgSelect.addEventListener('change', function() {
            console.log('=== Organization select changed ===');
            const orgId = this.value;
            console.log('New organization value:', orgId);
            const userListContainer = document.getElementById('entityUserList');

            if (!orgId || orgId.trim() === '') {
                console.log('Organization cleared, resetting UI');
                // Clear entity dropdown and user list if no org selected
                document.getElementById('entityID').innerHTML = '<option value="">All Entities</option>';
                userListContainer.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-info-circle fs-24 d-block mb-2"></i>
                        Select an organization to see available users
                    </div>
                `;
                return;
            }

            // Use the shared function to load data
            console.log('Calling loadOrganizationData from change event');
            loadOrganizationData(orgId);
        });

        // Load entities and users on initial page load if orgDataID is pre-filled
        const initialOrgId = orgSelect.value;
        console.log('Checking for pre-filled organization on page load:', initialOrgId);
        if (initialOrgId && initialOrgId.trim() !== '') {
            console.log('Pre-filled organization detected, loading data for orgDataID:', initialOrgId);
            loadOrganizationData(initialOrgId);
        }
    } else {
        console.error('Organization select element not found during initialization!');
        console.log('Looking for: #manageAdmin select[name="orgDataID"]');
    }

    // Entity change handler - filter users by entity (optional)
    const entitySelect = document.getElementById('entityID');
    if (entitySelect) {
        entitySelect.addEventListener('change', function() {
            const entityId = this.value;
            const orgId = document.getElementById('orgDataID').value;
            const userListContainer = document.getElementById('entityUserList');

            if (!orgId) {
                // No organization selected, can't filter
                return;
            }

            if (!entityId) {
                // No entity filter - reload all organization users
                userListContainer.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading users...</p>
                    </div>
                `;

                fetch('<?= $base ?>php/scripts/global/admin/get_entity_users.php?orgDataID=' + orgId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.users && data.users.length > 0) {
                            renderUserList(data.users);
                        } else {
                            userListContainer.innerHTML = `
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-users-slash fs-24 d-block mb-2"></i>
                                    <p class="mb-0">No users found in this organization</p>
                                </div>
                            `;
                        }
                    })
                    .catch(error => console.error('Error loading users:', error));
                return;
            }

            // Show loading
            userListContainer.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Filtering users...</p>
                </div>
            `;

            // Fetch users filtered by entity
            fetch('<?= $base ?>php/scripts/global/admin/get_entity_users.php?entityID=' + entityId + '&orgDataID=' + orgId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.users && data.users.length > 0) {
                        renderUserList(data.users);
                    } else {
                        userListContainer.innerHTML = `
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-users-slash fs-24 d-block mb-2"></i>
                                <p class="mb-0">No users found in this entity</p>
                                <small>Clear entity filter to see all organization users</small>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading users:', error);
                    userListContainer.innerHTML = `
                        <div class="text-center py-4 text-danger">
                            <i class="fas fa-exclamation-triangle fs-24 d-block mb-2"></i>
                            <p class="mb-0">Error loading users</p>
                        </div>
                    `;
                });
        });
    }

    // Search functionality with debounce (10 second delay)
    const searchInput = document.getElementById('searchEntityUsers');
    const searchPending = document.getElementById('searchPending');
    const searchCountdown = document.getElementById('searchCountdown');
    const searchIcon = document.getElementById('searchIcon');
    let searchTimeout = null;
    let countdownInterval = null;
    let countdownSeconds = 10;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            // Clear existing timeout and countdown
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }

            // If search is empty, show all immediately
            if (searchTerm.trim() === '') {
                const userItems = document.querySelectorAll('.user-item');
                userItems.forEach(item => {
                    item.style.display = '';
                });

                // Hide pending indicator
                if (searchPending) {
                    searchPending.classList.add('d-none');
                    searchIcon.classList.remove('d-none');
                }
                return;
            }

            // Show pending indicator
            if (searchPending) {
                searchPending.classList.remove('d-none');
                searchIcon.classList.add('d-none');
            }

            // Reset countdown
            countdownSeconds = 10;
            if (searchCountdown) {
                searchCountdown.textContent = countdownSeconds;
            }

            // Start countdown
            countdownInterval = setInterval(function() {
                countdownSeconds--;
                if (searchCountdown && countdownSeconds > 0) {
                    searchCountdown.textContent = countdownSeconds;
                }
            }, 1000); // Update every second

            // Set search timeout for 10 seconds (10000ms)
            searchTimeout = setTimeout(function() {
                console.log('Searching for:', searchTerm);

                // Clear countdown
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }

                // Hide pending indicator
                if (searchPending) {
                    searchPending.classList.add('d-none');
                    searchIcon.classList.remove('d-none');
                }

                // Perform search
                const userItems = document.querySelectorAll('.user-item');
                let matchCount = 0;

                userItems.forEach(item => {
                    const userName = item.getAttribute('data-user-name');
                    const userEmail = item.getAttribute('data-user-email');

                    if (userName && userEmail) {
                        const userNameLower = userName.toLowerCase();
                        const userEmailLower = userEmail.toLowerCase();

                        if (userNameLower.includes(searchTerm) || userEmailLower.includes(searchTerm)) {
                            item.style.display = '';
                            matchCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });

                console.log('Search completed. Found', matchCount, 'matches');
            }, 10000); // 10 seconds delay
        });
    }

    // Modal event handlers
    const manageAdminModal = document.getElementById('manageAdmin');
    if (manageAdminModal) {
        // When modal is shown, check if orgDataID has a value and load data
        manageAdminModal.addEventListener('shown.bs.modal', function() {
            console.log('=== Modal shown event fired ===');

            // Small delay to ensure any pre-population has completed
            setTimeout(function() {
                // IMPORTANT: Use querySelector to get the SELECT element, not the input
                const orgSelect = document.querySelector('#manageAdmin select[name="orgDataID"]');

                if (!orgSelect) {
                    console.error('Organization select element not found!');
                    console.log('Looking for: #manageAdmin select[name="orgDataID"]');
                    return;
                }

                const currentOrgId = orgSelect.value;
                console.log('Current orgDataID in modal:', currentOrgId);
                console.log('orgDataID element type:', orgSelect.tagName);
                console.log('orgDataID element:', orgSelect);

                // Safely log options
                if (orgSelect.options && orgSelect.options.length > 0) {
                    console.log('All select options:', Array.from(orgSelect.options).map(o => ({value: o.value, text: o.text})));
                } else {
                    console.log('No options available in orgDataID select');
                }

                if (currentOrgId && currentOrgId.trim() !== '') {
                    console.log('Modal opened with organization:', currentOrgId, '- Loading data');
                    loadOrganizationData(currentOrgId);
                } else {
                    console.log('No organization pre-selected in modal');
                }
            }, 150); // Small delay to ensure DOM is ready
        });

        // Reset modal on close
        manageAdminModal.addEventListener('hidden.bs.modal', function() {
            console.log('=== Modal hidden event fired - Resetting ===');

            // Reset loading flags
            isLoadingOrgData = false;
            lastLoadedOrgId = null;

            // Clear search timeout and countdown
            if (searchTimeout) {
                clearTimeout(searchTimeout);
                searchTimeout = null;
            }
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }

            // Hide search pending indicator
            if (searchPending) {
                searchPending.classList.add('d-none');
            }
            if (searchIcon) {
                searchIcon.classList.remove('d-none');
            }

            // Reset form
            const form = this.querySelector('form');
            if (form) form.reset();

            // Reset to existing user mode
            document.getElementById('selectExistingUser').checked = true;
            document.querySelector('.existing-user-section').classList.remove('d-none');
            document.querySelector('.new-user-section').classList.add('d-none');

            // Clear selections
            document.querySelectorAll('.user-item').forEach(item => {
                item.classList.remove('selected');
            });

            // Clear entity dropdown
            document.getElementById('entityID').innerHTML = '<option value="">All Entities</option>';

            // Clear user list
            document.getElementById('entityUserList').innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle fs-24 d-block mb-2"></i>
                    Select an organization above to see available users
                </div>
            `;
        });
    }

})();
</script>

