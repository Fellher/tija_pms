-- Updated View Definitions with DEFINER=CURRENT_USER
-- This replaces hard-coded definers (sbsl_user, root) for portability

-- --------------------------------------------------------
--
-- Structure for view `vw_leave_approval_policies`
--
DROP TABLE IF EXISTS `vw_leave_approval_policies`;

DROP VIEW IF EXISTS `vw_leave_approval_policies`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_leave_approval_policies`  AS SELECT `p`.`policyID` AS `policyID`, `p`.`entityID` AS `entityID`, `p`.`orgDataID` AS `orgDataID`, `p`.`policyName` AS `policyName`, `p`.`policyDescription` AS `policyDescription`, `p`.`isActive` AS `isActive`, `p`.`isDefault` AS `isDefault`, `p`.`requireAllApprovals` AS `requireAllApprovals`, `p`.`allowDelegation` AS `allowDelegation`, `p`.`autoApproveThreshold` AS `autoApproveThreshold`, count(distinct `s`.`stepID`) AS `totalSteps`, count(distinct (case when (`s`.`isRequired` = 'Y') then `s`.`stepID` end)) AS `requiredSteps`, `p`.`createdBy` AS `createdBy`, `p`.`createdAt` AS `createdAt`, concat(`creator`.`FirstName`,' ',`creator`.`Surname`) AS `createdByName` FROM ((`tija_leave_approval_policies` `p` left join `tija_leave_approval_steps` `s` on(((`p`.`policyID` = `s`.`policyID`) and (`s`.`Suspended` = 'N')))) left join `people` `creator` on((`p`.`createdBy` = `creator`.`ID`))) WHERE (`p`.`Lapsed` = 'N') GROUP BY `p`.`policyID` ;

-- --------------------------------------------------------
--
-- Structure for view `vw_leave_approval_workflow`
--
DROP TABLE IF EXISTS `vw_leave_approval_workflow`;

DROP VIEW IF EXISTS `vw_leave_approval_workflow`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_leave_approval_workflow`  AS SELECT `p`.`policyID` AS `policyID`, `p`.`policyName` AS `policyName`, `p`.`entityID` AS `entityID`, `s`.`stepID` AS `stepID`, `s`.`stepOrder` AS `stepOrder`, `s`.`stepName` AS `stepName`, `s`.`stepType` AS `stepType`, `s`.`stepDescription` AS `stepDescription`, `s`.`isRequired` AS `isRequired`, `s`.`isConditional` AS `isConditional`, `s`.`conditionType` AS `conditionType`, `s`.`escalationDays` AS `escalationDays`, count(`a`.`approverID`) AS `customApproversCount` FROM ((`tija_leave_approval_policies` `p` join `tija_leave_approval_steps` `s` on((`p`.`policyID` = `s`.`policyID`))) left join `tija_leave_approval_step_approvers` `a` on(((`s`.`stepID` = `a`.`stepID`) and (`a`.`Suspended` = 'N')))) WHERE ((`p`.`Lapsed` = 'N') AND (`p`.`Suspended` = 'N') AND (`s`.`Suspended` = 'N')) GROUP BY `s`.`stepID` ORDER BY `p`.`policyID` ASC, `s`.`stepOrder` ASC ;

-- --------------------------------------------------------
--
-- Structure for view `vw_notification_events_with_templates`
--
DROP TABLE IF EXISTS `vw_notification_events_with_templates`;

DROP VIEW IF EXISTS `vw_notification_events_with_templates`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_notification_events_with_templates`  AS SELECT `e`.`eventID` AS `eventID`, `e`.`eventName` AS `eventName`, `e`.`eventSlug` AS `eventSlug`, `e`.`eventDescription` AS `eventDescription`, `e`.`eventCategory` AS `eventCategory`, `e`.`priorityLevel` AS `priorityLevel`, `m`.`moduleID` AS `moduleID`, `m`.`moduleName` AS `moduleName`, `m`.`moduleSlug` AS `moduleSlug`, count(distinct `t`.`templateID`) AS `templateCount`, `e`.`isActive` AS `isActive`, `e`.`isUserConfigurable` AS `isUserConfigurable` FROM ((`tija_notification_events` `e` join `tija_notification_modules` `m` on((`e`.`moduleID` = `m`.`moduleID`))) left join `tija_notification_templates` `t` on(((`e`.`eventID` = `t`.`eventID`) and (`t`.`Suspended` = 'N')))) WHERE ((`e`.`Suspended` = 'N') AND (`m`.`Suspended` = 'N')) GROUP BY `e`.`eventID` ;

-- --------------------------------------------------------
--
-- Structure for view `vw_pending_leave_approvals`
--
DROP TABLE IF EXISTS `vw_pending_leave_approvals`;

DROP VIEW IF EXISTS `vw_pending_leave_approvals`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_pending_leave_approvals`  AS SELECT `i`.`instanceID` AS `instanceID`, `i`.`leaveApplicationID` AS `leaveApplicationID`, `la`.`employeeID` AS `employeeID`, concat(`emp`.`FirstName`,' ',`emp`.`Surname`) AS `employeeName`, `la`.`leaveTypeID` AS `leaveTypeID`, `lt`.`leaveTypeName` AS `leaveTypeName`, `la`.`startDate` AS `startDate`, `la`.`endDate` AS `endDate`, `la`.`noOfDays` AS `totalDays`, `i`.`policyID` AS `policyID`, `p`.`policyName` AS `policyName`, `i`.`currentStepID` AS `currentStepID`, `s`.`stepName` AS `currentStepName`, `s`.`stepType` AS `currentStepType`, `s`.`stepOrder` AS `currentStepOrder`, `i`.`workflowStatus` AS `workflowStatus`, `i`.`startedAt` AS `startedAt`, `i`.`lastActionAt` AS `lastActionAt`, (to_days(now()) - to_days(`i`.`lastActionAt`)) AS `daysPending` FROM (((((`tija_leave_approval_instances` `i` join `tija_leave_applications` `la` on((`i`.`leaveApplicationID` = `la`.`leaveApplicationID`))) join `people` `emp` on((`la`.`employeeID` = `emp`.`ID`))) join `tija_leave_types` `lt` on((`la`.`leaveTypeID` = `lt`.`leaveTypeID`))) join `tija_leave_approval_policies` `p` on((`i`.`policyID` = `p`.`policyID`))) left join `tija_leave_approval_steps` `s` on((`i`.`currentStepID` = `s`.`stepID`))) WHERE (`i`.`workflowStatus` in ('pending','in_progress')) ORDER BY `i`.`lastActionAt` ASC ;

-- --------------------------------------------------------
--
-- Structure for view `vw_user_notification_summary`
--
DROP TABLE IF EXISTS `vw_user_notification_summary`;

DROP VIEW IF EXISTS `vw_user_notification_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_user_notification_summary`  AS SELECT `tija_notifications_enhanced`.`userID` AS `userID`, count(0) AS `totalNotifications`, sum((case when (`tija_notifications_enhanced`.`status` = 'unread') then 1 else 0 end)) AS `unreadCount`, sum((case when (`tija_notifications_enhanced`.`status` = 'read') then 1 else 0 end)) AS `readCount`, sum((case when ((`tija_notifications_enhanced`.`priority` = 'critical') and (`tija_notifications_enhanced`.`status` = 'unread')) then 1 else 0 end)) AS `criticalUnread`, max(`tija_notifications_enhanced`.`DateAdded`) AS `lastNotificationDate` FROM `tija_notifications_enhanced` WHERE ((`tija_notifications_enhanced`.`Lapsed` = 'N') AND (`tija_notifications_enhanced`.`Suspended` = 'N')) GROUP BY `tija_notifications_enhanced`.`userID` ;

