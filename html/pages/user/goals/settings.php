<?php
/**
 * Goals Settings Page
 * User automation preferences and settings
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

// Security check
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

require_once 'php/classes/goalautomation.php';

// Get current settings
$settings = GoalAutomation::getSettings($userDetails->ID, $DBConn);

// Handle form submission
if (isset($_POST['save_settings'])) {
    foreach ($_POST['automation'] as $type => $data) {
        GoalAutomation::updateSetting(
            $userDetails->ID,
            $type,
            array(
                'executionMode' => $data['executionMode'] ?? 'automatic',
                'scheduleFrequency' => $data['scheduleFrequency'] ?? null,
                'scheduleTime' => $data['scheduleTime'] ?? null,
                'isEnabled' => $data['isEnabled'] ?? 'Y',
                'notificationPreference' => $data['notificationPreference'] ?? 'both'
            ),
            $DBConn
        );
    }
    Alert::success("Settings saved successfully", true);
    $settings = GoalAutomation::getSettings($userDetails->ID, $DBConn); // Reload
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Goal Automation Settings</h4>
                    <p class="text-muted mb-0">Configure how goal automation runs for your account</p>
                </div>
                <div>
                    <a href="<?= "{$base}html/?s=user&ss=goals&p=dashboard" ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle me-2"></i>About Automation</h6>
                <p class="mb-0">
                    Automation helps keep your goals up-to-date automatically. You can choose to run tasks automatically (via cron jobs),
                    manually (when you trigger them), or on a schedule. See <a href="<?= "{$base}docs/CRON_JOBS_GOALS_MODULE.md" ?>" target="_blank">Cron Jobs Documentation</a> for setup instructions.
                </p>
            </div>
        </div>
    </div>

    <!-- Automation Settings Form -->
    <form method="POST" action="">
        <input type="hidden" name="save_settings" value="1">

        <!-- Score Calculation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Score Calculation</h5>
                        <small class="text-muted">How goal scores are calculated and updated</small>
                    </div>
                    <div class="card-body">
                        <?php $setting = $settings['score_calculation'] ?? null; ?>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Execution Mode</label>
                                <select class="form-select" name="automation[score_calculation][executionMode]">
                                    <option value="automatic" <?php echo ($setting && $setting->executionMode === 'automatic') ? 'selected' : ''; ?>>Automatic (Cron Job)</option>
                                    <option value="manual" <?php echo ($setting && $setting->executionMode === 'manual') ? 'selected' : ''; ?>>Manual</option>
                                    <option value="scheduled" <?php echo ($setting && $setting->executionMode === 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Schedule Frequency</label>
                                <select class="form-select" name="automation[score_calculation][scheduleFrequency]"
                                        <?php echo ($setting && $setting->executionMode !== 'scheduled') ? 'disabled' : ''; ?>>
                                    <option value="">Select Frequency</option>
                                    <option value="daily" <?php echo ($setting && $setting->scheduleFrequency === 'daily') ? 'selected' : ''; ?>>Daily</option>
                                    <option value="weekly" <?php echo ($setting && $setting->scheduleFrequency === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                                    <option value="monthly" <?php echo ($setting && $setting->scheduleFrequency === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Notification Preference</label>
                                <select class="form-select" name="automation[score_calculation][notificationPreference]">
                                    <option value="both" <?php echo ($setting && $setting->notificationPreference === 'both') ? 'selected' : ''; ?>>Email & In-App</option>
                                    <option value="email" <?php echo ($setting && $setting->notificationPreference === 'email') ? 'selected' : ''; ?>>Email Only</option>
                                    <option value="in_app" <?php echo ($setting && $setting->notificationPreference === 'in_app') ? 'selected' : ''; ?>>In-App Only</option>
                                    <option value="none" <?php echo ($setting && $setting->notificationPreference === 'none') ? 'selected' : ''; ?>>None</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="automation[score_calculation][isEnabled]" value="Y"
                                       <?php echo ($setting && $setting->isEnabled === 'Y') ? 'checked' : ''; ?>>
                                <label class="form-check-label">Enable Score Calculation Automation</label>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="runManual('score_calculation')">
                                <i class="bi bi-play-circle me-2"></i>Run Now (Manual)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Snapshot Generation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Snapshot Generation</h5>
                        <small class="text-muted">Weekly performance snapshots for reporting</small>
                    </div>
                    <div class="card-body">
                        <?php $setting = $settings['snapshot_generation'] ?? null; ?>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Execution Mode</label>
                                <select class="form-select" name="automation[snapshot_generation][executionMode]">
                                    <option value="automatic" <?php echo ($setting && $setting->executionMode === 'automatic') ? 'selected' : ''; ?>>Automatic (Cron Job)</option>
                                    <option value="manual" <?php echo ($setting && $setting->executionMode === 'manual') ? 'selected' : ''; ?>>Manual</option>
                                    <option value="scheduled" <?php echo ($setting && $setting->executionMode === 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Schedule Frequency</label>
                                <select class="form-select" name="automation[snapshot_generation][scheduleFrequency]">
                                    <option value="weekly" <?php echo ($setting && $setting->scheduleFrequency === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                                    <option value="daily" <?php echo ($setting && $setting->scheduleFrequency === 'daily') ? 'selected' : ''; ?>>Daily</option>
                                    <option value="monthly" <?php echo ($setting && $setting->scheduleFrequency === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Notification Preference</label>
                                <select class="form-select" name="automation[snapshot_generation][notificationPreference]">
                                    <option value="both" <?php echo ($setting && $setting->notificationPreference === 'both') ? 'selected' : ''; ?>>Email & In-App</option>
                                    <option value="email" <?php echo ($setting && $setting->notificationPreference === 'email') ? 'selected' : ''; ?>>Email Only</option>
                                    <option value="in_app" <?php echo ($setting && $setting->notificationPreference === 'in_app') ? 'selected' : ''; ?>>In-App Only</option>
                                    <option value="none" <?php echo ($setting && $setting->notificationPreference === 'none') ? 'selected' : ''; ?>>None</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="automation[snapshot_generation][isEnabled]" value="Y"
                                       <?php echo ($setting && $setting->isEnabled === 'Y') ? 'checked' : ''; ?>>
                                <label class="form-check-label">Enable Snapshot Generation</label>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="runManual('snapshot_generation')">
                                <i class="bi bi-play-circle me-2"></i>Generate Snapshots Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluation Reminders -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Evaluation Reminders</h5>
                        <small class="text-muted">Notifications for pending evaluations</small>
                    </div>
                    <div class="card-body">
                        <?php $setting = $settings['evaluation_reminders'] ?? null; ?>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Execution Mode</label>
                                <select class="form-select" name="automation[evaluation_reminders][executionMode]">
                                    <option value="automatic" <?php echo ($setting && $setting->executionMode === 'automatic') ? 'selected' : ''; ?>>Automatic (Cron Job)</option>
                                    <option value="manual" <?php echo ($setting && $setting->executionMode === 'manual') ? 'selected' : ''; ?>>Manual</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notification Preference</label>
                                <select class="form-select" name="automation[evaluation_reminders][notificationPreference]">
                                    <option value="both" <?php echo ($setting && $setting->notificationPreference === 'both') ? 'selected' : ''; ?>>Email & In-App</option>
                                    <option value="email" <?php echo ($setting && $setting->notificationPreference === 'email') ? 'selected' : ''; ?>>Email Only</option>
                                    <option value="in_app" <?php echo ($setting && $setting->notificationPreference === 'in_app') ? 'selected' : ''; ?>>In-App Only</option>
                                    <option value="none" <?php echo ($setting && $setting->notificationPreference === 'none') ? 'selected' : ''; ?>>None</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="automation[evaluation_reminders][isEnabled]" value="Y"
                                       <?php echo ($setting && $setting->isEnabled === 'Y') ? 'checked' : ''; ?>>
                                <label class="form-check-label">Enable Evaluation Reminders</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deadline Alerts -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Deadline Alerts</h5>
                        <small class="text-muted">Notifications for goals approaching deadlines</small>
                    </div>
                    <div class="card-body">
                        <?php $setting = $settings['deadline_alerts'] ?? null; ?>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Execution Mode</label>
                                <select class="form-select" name="automation[deadline_alerts][executionMode]">
                                    <option value="automatic" <?php echo ($setting && $setting->executionMode === 'automatic') ? 'selected' : ''; ?>>Automatic (Cron Job)</option>
                                    <option value="manual" <?php echo ($setting && $setting->executionMode === 'manual') ? 'selected' : ''; ?>>Manual</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notification Preference</label>
                                <select class="form-select" name="automation[deadline_alerts][notificationPreference]">
                                    <option value="both" <?php echo ($setting && $setting->notificationPreference === 'both') ? 'selected' : ''; ?>>Email & In-App</option>
                                    <option value="email" <?php echo ($setting && $setting->notificationPreference === 'email') ? 'selected' : ''; ?>>Email Only</option>
                                    <option value="in_app" <?php echo ($setting && $setting->notificationPreference === 'in_app') ? 'selected' : ''; ?>>In-App Only</option>
                                    <option value="none" <?php echo ($setting && $setting->notificationPreference === 'none') ? 'selected' : ''; ?>>None</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="automation[deadline_alerts][isEnabled]" value="Y"
                                       <?php echo ($setting && $setting->isEnabled === 'Y') ? 'checked' : ''; ?>>
                                <label class="form-check-label">Enable Deadline Alerts</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Settings
                        </button>
                        <a href="<?= "{$base}html/?s=user&ss=goals&p=dashboard" ?>" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function runManual(automationType) {
    if (!confirm('Run ' + automationType + ' now?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'execute_manual');
    formData.append('automationType', automationType);

    fetch('php/scripts/goals/automation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Automation executed successfully: ' + data.message);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Enable/disable schedule frequency based on execution mode
document.querySelectorAll('select[name*="[executionMode]"]').forEach(select => {
    select.addEventListener('change', function() {
        const row = this.closest('.row');
        const frequencySelect = row.querySelector('select[name*="[scheduleFrequency]"]');
        if (this.value === 'scheduled') {
            frequencySelect.disabled = false;
        } else {
            frequencySelect.disabled = true;
        }
    });
});
</script>

