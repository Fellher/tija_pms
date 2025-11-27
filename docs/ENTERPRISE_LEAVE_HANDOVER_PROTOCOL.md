# Enterprise Leave Handover Protocol Implementation Plan

## Overview
Transform the existing basic handover system into an enterprise-level protocol that supports flexible policy targeting (entity-wide, role-based, job group, job level) and implements a comprehensive FSM workflow with peer negotiation capabilities.

## Current State Analysis
- Basic handover policies exist in `tija_leave_handover_policies` (entity-wide and leave-type only)
- Handover workflow: pending → in_progress → completed/rejected
- No FSM states, peer negotiation, or role-based targeting
- Handover assignments can be confirmed but no revision loop exists

## Implementation Components

### 1. Database Schema Enhancements

#### 1.1 Enhance Policy Table (`tija_leave_handover_policies`)
**File:** `php/migrations/leave_handover_system_migration.php`

Add columns to support flexible targeting:
- `policyScope` ENUM('entity_wide', 'role_based', 'job_group', 'job_level', 'job_title') DEFAULT 'entity_wide'
- `targetRoleID` INT NULL (FK to roles/permissions)
- `targetJobCategoryID` INT NULL (FK to job categories)
- `targetJobBandID` INT NULL (FK to job bands)
- `targetJobLevelID` INT NULL (FK to tija_role_levels)
- `targetJobTitleID` INT NULL (FK to tija_job_titles)
- `requireNomineeAcceptance` ENUM('Y','N') DEFAULT 'Y'
- `nomineeResponseDeadlineHours` INT DEFAULT 48
- `allowPeerRevision` ENUM('Y','N') DEFAULT 'Y'
- `maxRevisionAttempts` INT DEFAULT 3

#### 1.2 Create FSM State Tracking Table
**New File:** `php/migrations/create_handover_fsm_states_table.php`

```sql
CREATE TABLE `tija_leave_handover_fsm_states` (
  `stateID` INT NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` INT NOT NULL,
  `handoverID` INT NULL,
  `currentState` ENUM('ST_00','ST_01','ST_02','ST_03','ST_04','ST_05','ST_06','ST_07') NOT NULL,
  `previousState` ENUM('ST_00','ST_01','ST_02','ST_03','ST_04','ST_05','ST_06','ST_07') NULL,
  `stateOwnerID` INT NULL COMMENT 'Employee ID who owns current state',
  `nomineeID` INT NULL COMMENT 'Peer/nominee assigned for handover',
  `stateEnteredAt` DATETIME NOT NULL,
  `stateCompletedAt` DATETIME NULL,
  `timerStartedAt` DATETIME NULL COMMENT 'For peer response deadlines',
  `timerExpiresAt` DATETIME NULL,
  `revisionCount` INT DEFAULT 0,
  `chainOfCustodyLog` TEXT NULL COMMENT 'JSON log of state transitions',
  `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stateID`),
  KEY `idx_application` (`leaveApplicationID`),
  KEY `idx_handover` (`handoverID`),
  KEY `idx_current_state` (`currentState`)
) ENGINE=InnoDB;
```

#### 1.3 Create Peer Negotiation Table
**New File:** `php/migrations/create_peer_negotiation_table.php`

```sql
CREATE TABLE `tija_leave_handover_peer_negotiations` (
  `negotiationID` INT NOT NULL AUTO_INCREMENT,
  `handoverID` INT NOT NULL,
  `assignmentID` INT NULL,
  `nomineeID` INT NOT NULL,
  `requesterID` INT NOT NULL,
  `negotiationType` ENUM('request_change','reject','accept') NOT NULL,
  `requestedChanges` TEXT NULL COMMENT 'Details of what needs to be changed',
  `negotiationStatus` ENUM('pending','resolved','escalated') DEFAULT 'pending',
  `responseDate` DATETIME NULL,
  `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`negotiationID`),
  KEY `idx_handover` (`handoverID`),
  KEY `idx_nominee` (`nomineeID`)
) ENGINE=InnoDB;
```

#### 1.4 Add Columns to Existing Tables
- `tija_leave_handovers`: Add `nomineeID`, `fsmStateID`, `revisionCount`
- `tija_leave_handover_assignments`: Add `negotiationID`, `revisionRequested`

### 2. Policy Management System

#### 2.1 Enhanced Policy Check Method
**File:** `php/classes/leavehandover.php`

Enhance `check_handover_policy()` to:
- Accept employee context (roleID, jobCategoryID, jobBandID, jobLevelID, jobTitleID)
- Match policies by scope priority: specific (job_title) → job_level → job_group → role → entity_wide
- Return matched policy with scope details

#### 2.2 Policy CRUD Operations
**New File:** `php/classes/leavehandoverpolicy.php`

Create class with methods:
- `create_policy($policyData, $DBConn)`
- `update_policy($policyID, $policyData, $DBConn)`
- `get_policies($filters, $DBConn)`
- `delete_policy($policyID, $DBConn)`
- `get_policy_by_employee_context($employeeID, $entityID, $leaveTypeID, $noOfDays, $DBConn)`

#### 2.3 Admin Interface for Policy Management
**New File:** `html/pages/admin/leave/handover_policies.php`

Features:
- List all policies with scope indicators
- Create/edit policy form with scope selector
- Policy targeting options (entity-wide, role, job group, job level, job title)
- Policy priority/ordering
- Effective date range management
- Policy testing/preview tool

**New File:** `php/scripts/leave/policies/manage_handover_policy.php`
- Handle CRUD operations via AJAX
- Validate policy conflicts
- Test policy matching

### 3. FSM Workflow Engine

#### 3.1 FSM State Manager Class
**New File:** `php/classes/leavehandoverfsm.php`

Core methods:
- `initialize_fsm($leaveApplicationID, $employeeID, $DBConn)` - Create ST_00 (Draft)
- `transition_state($leaveApplicationID, $trigger, $actorID, $data, $DBConn)` - Handle state transitions
- `get_current_state($leaveApplicationID, $DBConn)` - Get current FSM state
- `can_transition($currentState, $trigger, $DBConn)` - Validate transition rules
- `log_state_transition($leaveApplicationID, $fromState, $toState, $trigger, $actorID, $DBConn)` - Chain of custody logging
- `check_timer_expiry($leaveApplicationID, $DBConn)` - Check for expired peer response timers

**State Transition Rules:**
- ST_00 → ST_01: `Submit_Draft` (by Requester)
- ST_01 → ST_02: `Submit_Handover` (by Requester, locks request)
- ST_02 → ST_03: `Peer_Request_Change` (by Nominee)
- ST_02 → ST_04: `Peer_Accept` (by Nominee)
- ST_03 → ST_01: `Resubmit_Handover` (by Requester after revision)
- ST_04 → ST_05: `System_Auto_Route` (automatic when handover accepted)
- ST_05 → ST_06: `Manager_Approve` (by Manager/Approver)
- ST_05 → ST_07: `Manager_Reject` (by Manager/Approver)

#### 3.2 Integration with Leave Application Workflow
**File:** `php/scripts/leave/applications/submit_leave_application.php`

Modify to:
- Initialize FSM when handover is required
- Set state to ST_00 (Draft) on creation
- Transition to ST_01 when handover composition is submitted
- Lock application editing when in ST_02 (Peer Negotiation)

**File:** `php/scripts/leave/workflows/submit_approval_decision.php`

Modify to:
- Check FSM state before allowing approval
- Only allow approval if state is ST_05 (Manager Review)
- Transition to ST_06 on approval or ST_07 on rejection

### 4. Peer Negotiation System

#### 4.1 Nominee Selection Interface
**File:** `html/pages/user/leave/apply_leave_workflow.php` (Step 4 - Handover)

Enhance handover step to:
- Show nominee selection dropdown (filtered by policy requirements)
- Display role-specific handover checklist based on policy
- Allow adding handover items with assignments
- Submit handover composition (triggers ST_01 → ST_02)

#### 4.2 Peer Notification and Response
**New File:** `php/scripts/leave/handovers/notify_nominee.php`

- Send notification to nominee when handover submitted
- Include handover details and deadline
- Link to peer response interface

**New File:** `html/pages/user/leave/peer_handover_response.php`

Interface for nominee to:
- View assigned handover items
- Accept handover (triggers ST_02 → ST_04)
- Request changes (triggers ST_02 → ST_03) with reason
- View revision history

#### 4.3 Revision Handling
**New File:** `php/scripts/leave/handovers/handle_peer_response.php`

Handle:
- Peer acceptance → transition to ST_04, log chain of custody
- Peer change request → transition to ST_03, notify requester, start revision timer
- Revision resubmission → transition back to ST_02, increment revision count
- Max revision attempts check → escalate if exceeded

**File:** `html/pages/user/leave/apply_leave_workflow.php`

Add revision interface:
- Show requested changes when in ST_03
- Allow editing handover items
- Resubmit revised handover

### 5. Timer and Deadline Management

#### 5.1 Timer Service
**New File:** `php/classes/leavehandovertimer.php`

Methods:
- `start_peer_response_timer($handoverID, $nomineeID, $deadlineHours, $DBConn)`
- `check_expired_timers($DBConn)` - Background job to check expired timers
- `get_remaining_time($handoverID, $DBConn)` - Get time remaining for response
- `handle_timer_expiry($handoverID, $DBConn)` - Auto-escalate or notify on expiry

#### 5.2 Background Job/Cron
**New File:** `php/scripts/cron/check_handover_timers.php`

- Run periodically (every hour)
- Check for expired peer response timers
- Send escalation notifications
- Update FSM states if needed

### 6. Chain of Custody Logging

#### 6.1 Logging Implementation
**File:** `php/classes/leavehandoverfsm.php`

Enhance `log_state_transition()` to:
- Store JSON log entry with: timestamp, from_state, to_state, trigger, actor, metadata
- Append to `chainOfCustodyLog` field
- Include nominee acceptance/rejection details
- Track all handover item assignments

#### 6.2 Audit Trail View
**New File:** `html/pages/user/leave/handover_audit_trail.php`

Display:
- Complete FSM state history
- All transitions with timestamps and actors
- Chain of custody log
- Peer negotiation history
- Timer events

### 7. UI/UX Enhancements

#### 7.1 Handover Status Indicators
**Files:** `html/pages/user/leave/pending_approvals.php`, `html/pages/user/leave/view_leave_application.php`

Add FSM state badges:
- ST_00: "Draft" (gray)
- ST_01: "Composing Handover" (blue)
- ST_02: "Awaiting Peer Response" (yellow, show timer)
- ST_03: "Revision Required" (orange)
- ST_04: "Handover Accepted" (green)
- ST_05: "Manager Review" (purple)
- ST_06: "Approved" (success)
- ST_07: "Rejected" (danger)

#### 7.2 Manager Dashboard Enhancements
**File:** `html/pages/user/leave/leave_approval_views/pending_view.php`

Show:
- Handover status badge
- FSM state indicator
- "Handover Accepted" badge when in ST_05
- Block approval if handover not accepted (ST_04 not reached)

### 8. API Endpoints

**New Files:**
- `php/scripts/leave/handovers/get_fsm_state.php` - Get current FSM state
- `php/scripts/leave/handovers/transition_state.php` - Trigger state transition
- `php/scripts/leave/handovers/get_peer_assignments.php` - Get assignments for nominee
- `php/scripts/leave/handovers/submit_peer_response.php` - Handle peer accept/reject
- `php/scripts/leave/handovers/get_chain_of_custody.php` - Get audit trail

### 9. Testing and Validation

#### 9.1 Policy Matching Tests
- Test entity-wide policy matching
- Test role-based policy matching
- Test job group/level/title matching
- Test policy priority (most specific wins)

#### 9.2 FSM Workflow Tests
- Test all valid state transitions
- Test invalid transition blocking
- Test timer expiry handling
- Test revision loop limits

#### 9.3 Integration Tests
- Test handover workflow end-to-end
- Test integration with approval workflow
- Test notification system
- Test chain of custody logging

## FSM State Definitions

| State ID | State Name | Owner | Trigger | Description and System Logic |
|----------|------------|-------|---------|----------------------------|
| ST_00 | Draft | Requester | Creation | User initiates request. System validates accrual balances and blocks dates tentatively. |
| ST_01 | Handover Composition | Requester | Submit_Draft | User selects a "Nominee" (Peer) and populates the role-specific handover checklist. |
| ST_02 | Peer Negotiation | Nominee | Submit_Handover | Notification sent to Nominee. Request is locked for Requester. Timer starts for peer response. |
| ST_03 | Handover Revision | Requester | Peer_Request_Change | Nominee rejects the handover plan as insufficient (e.g., "Missing passwords"). Request returns to Requester for edits. |
| ST_04 | Handover Accepted | System | Peer_Accept | Nominee accepts duties. System logs "Chain of Custody" transfer. Transition to Manager Review. |
| ST_05 | Manager Review | Approver | System_Auto_Route | Manager receives request with the "Handover Accepted" badge. |
| ST_06 | Approved | System | Manager_Approve | Final commitment. Calendar updated, payroll notified, OOO configured. |
| ST_07 | Rejected | System | Manager_Reject | Request terminated. Balances unlocked. |

## Migration Strategy

1. **Phase 1:** Database schema updates (non-breaking, add columns)
2. **Phase 2:** Policy management system (admin interface)
3. **Phase 3:** FSM engine implementation
4. **Phase 4:** Peer negotiation system
5. **Phase 5:** UI/UX enhancements
6. **Phase 6:** Testing and refinement

## Key Files to Modify/Create

### New Files:
- `php/migrations/create_handover_fsm_states_table.php`
- `php/migrations/create_peer_negotiation_table.php`
- `php/classes/leavehandoverfsm.php`
- `php/classes/leavehandoverpolicy.php`
- `php/classes/leavehandovertimer.php`
- `html/pages/admin/leave/handover_policies.php`
- `html/pages/user/leave/peer_handover_response.php`
- `html/pages/user/leave/handover_audit_trail.php`
- `php/scripts/leave/policies/manage_handover_policy.php`
- `php/scripts/leave/handovers/notify_nominee.php`
- `php/scripts/leave/handovers/handle_peer_response.php`
- `php/scripts/cron/check_handover_timers.php`
- `php/scripts/leave/handovers/get_fsm_state.php`
- `php/scripts/leave/handovers/transition_state.php`
- `php/scripts/leave/handovers/get_peer_assignments.php`
- `php/scripts/leave/handovers/submit_peer_response.php`
- `php/scripts/leave/handovers/get_chain_of_custody.php`

### Modified Files:
- `php/migrations/leave_handover_system_migration.php` (add new columns)
- `php/classes/leavehandover.php` (enhance policy checking)
- `php/scripts/leave/applications/submit_leave_application.php` (FSM integration)
- `php/scripts/leave/workflows/submit_approval_decision.php` (FSM state checks)
- `html/pages/user/leave/apply_leave_workflow.php` (nominee selection, revision UI)
- `html/pages/user/leave/pending_approvals.php` (FSM state badges)
- `html/pages/user/leave/view_leave_application.php` (FSM state display)
- `html/pages/user/leave/leave_approval_views/pending_view.php` (handover status indicators)

## Success Criteria

1. Admins can create policies targeting roles, job groups, job levels, or job titles
2. FSM workflow correctly manages all 8 states (ST_00 to ST_07)
3. Peer negotiation allows nominees to request changes and requester to revise
4. Timer system tracks and enforces peer response deadlines
5. Chain of custody logging captures all state transitions
6. Manager approval blocked until handover is accepted (ST_04)
7. System handles revision loops with max attempt limits
8. All existing handover functionality remains intact

## Implementation Checklist

- [ ] Database schema enhancements
  - [ ] Add policy targeting columns to `tija_leave_handover_policies`
  - [ ] Create `tija_leave_handover_fsm_states` table
  - [ ] Create `tija_leave_handover_peer_negotiations` table
  - [ ] Add nominee/timer fields to existing tables

- [ ] Policy management system
  - [ ] Enhance `check_handover_policy()` method
  - [ ] Create `LeaveHandoverPolicy` class
  - [ ] Build admin interface for policy management
  - [ ] Create policy CRUD API endpoints

- [ ] FSM workflow engine
  - [ ] Create `LeaveHandoverFSM` class
  - [ ] Implement state transition logic
  - [ ] Integrate FSM into leave application submission
  - [ ] Integrate FSM checks into approval workflow

- [ ] Peer negotiation system
  - [ ] Enhance nominee selection interface
  - [ ] Create peer notification system
  - [ ] Build peer response interface
  - [ ] Implement revision handling

- [ ] Timer and deadline management
  - [ ] Create `LeaveHandoverTimer` class
  - [ ] Build background job for timer checks
  - [ ] Integrate timer display in UI

- [ ] Chain of custody logging
  - [ ] Implement detailed state transition logging
  - [ ] Create audit trail view interface

- [ ] UI/UX enhancements
  - [ ] Add FSM state badges
  - [ ] Enhance manager dashboard
  - [ ] Add revision interface

- [ ] API endpoints
  - [ ] Get FSM state
  - [ ] Transition state
  - [ ] Get peer assignments
  - [ ] Submit peer response
  - [ ] Get chain of custody

- [ ] Testing and validation
  - [ ] Policy matching tests
  - [ ] FSM workflow tests
  - [ ] Integration tests

