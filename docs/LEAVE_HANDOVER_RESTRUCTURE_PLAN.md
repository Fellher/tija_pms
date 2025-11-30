## Leave + Handover Restructure Plan

### 1. Submission Flow Changes
1. Update `html/pages/user/leave/apply_leave_workflow.php`:
   - Step 1→3 remain the same.
   - Change submit handler to always call new endpoint `/leave/applications/submit_leave_application.php` which no longer requires handover payload (just raw leave data).
2. Modify `php/scripts/leave/applications/submit_leave_application.php`:
   - After saving leave application, immediately call new service `LeaveHandoverService::generate_required_handover($leaveApplicationId)` which:
     - Calculates working days.
     - Pulls scheduled project tasks (use `tija_project_tasks`, assignments table) overlapping dates.
     - Creates pending handover items per task (auto-populated, but flagged as "needs nominee").
   - Fire notifications:
     - Applicant: “Handover plan initialized”.
     - Nominee placeholders? no yet.
   - Spawn FSM into `ST_01`.

### 2. Task Harvesting & Nomination UI
1. New backend service `php/classes/leavehandoverbuilder.php`:
   - `build_default_items($leaveApplicationId)` queries **project** assignments table (TBD: likely `tija_project_task_assignments`).
   - Creates `tija_leave_handover_items` with metadata (project/task names).
2. Front-end:
   - Create a dedicated page `html/pages/user/leave/handover_builder.php` launched immediately after submission (redirect).
   - Page fetches initial auto-generated items via `php/scripts/leave/handovers/get_builder_state.php`.
   - For each item, user selects nominee (employee search component). Add custom tasks too.
   - On save, patch via `php/scripts/leave/handovers/save_builder_state.php`.
   - When user submits builder, FSM transitions `ST_01 -> ST_02`; nominees get notifications.

### 3. Project Task Integration
1. Determine source tables:
   - `tija_project_tasks` for task meta.
   - `tija_project_assignments` (if existing) for employee->task mapping.
   - Need helper in `php/classes/projects.php` to fetch tasks for employee between dates.
2. Builder service uses those helpers to auto-create handover items with type `project_task`, referencing `projectID` and `taskID`.

### 4. Notifications
1. Add new events:
   - `leave_handover_builder_ready` (to applicant).
   - `leave_handover_nominee_invite`.
2. Use existing notification seeding script to add templates.

### 5. FSM & Workflow
1. `LeaveHandoverFSM` adjustments:
   - On submission -> ST_01 (composition).
   - On builder submit -> ST_02 (peer negotiation).
   - Everything else remains.
2. Ensure `submit_leave_application.php` no longer expects `handoverPayload` to be present.

### 6. APIs
1. `php/scripts/leave/handovers/get_builder_state.php`
2. `php/scripts/leave/handovers/save_builder_state.php`
3. `php/scripts/leave/handovers/submit_builder.php` (transition ST_01->ST_02, send notifications).

### 7. UI Flow Summary
1. User fills leave form → hits submit.
2. Backend stores leave, builds default handover, redirects user to builder page.
3. Builder page shows tasks & free-form functions, with nominee selectors.
4. User submits builder → nominees notified, FSM ST_02, existing acceptance loop continues.


