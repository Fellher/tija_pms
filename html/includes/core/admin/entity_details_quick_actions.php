<?php
// Load quick actions configuration
include_once 'includes/core/admin/entity_details_quick_actions_config.php';
?>

<!-- Quick Actions Bar -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <?php foreach ($quickActions as $actionKey => $actionConfig): ?>
                        <?php if ($actionConfig['enabled'] && hasQuickActionPermission($actionConfig['permission'] ?? null)): ?>

                            <?php if (isset($actionConfig['type']) && $actionConfig['type'] === 'separator'): ?>
                                <!-- Separator (invisible flex spacer) -->
                                <div class="flex-grow-1"></div>

                            <?php elseif ($actionConfig['action_type'] === 'link'): ?>
                                <!-- Link Action -->
                                <a href="<?= $actionConfig['href'] ?>"
                                   class="btn <?= $actionConfig['btn_class'] ?> btn-sm btn-wave <?= $actionConfig['additional_classes'] ?? '' ?>">
                                    <i class="fas <?= $actionConfig['icon'] ?> me-2"></i><?= htmlspecialchars($actionConfig['label']) ?>
                                </a>

                            <?php else: ?>
                                <!-- Button Action (Modal or Function) -->
                                <button type="button"
                                        class="btn <?= $actionConfig['btn_class'] ?> btn-sm btn-wave <?= $actionConfig['additional_classes'] ?? '' ?>"
                                        <?php if ($actionConfig['action_type'] === 'modal'): ?>
                                            data-bs-toggle="modal"
                                            data-bs-target="<?= $actionConfig['action_target'] ?>"
                                        <?php endif; ?>
                                        <?php if (isset($actionConfig['onclick'])): ?>
                                            onclick="<?= $actionConfig['onclick'] ?>"
                                        <?php endif; ?>
                                        <?php if (isset($actionConfig['data_attributes'])): ?>
                                            <?php foreach ($actionConfig['data_attributes'] as $dataAttr => $dataValue): ?>
                                                <?= $dataAttr ?>="<?= $dataValue ?>"
                                            <?php endforeach; ?>
                                        <?php endif; ?>>
                                    <i class="fas <?= $actionConfig['icon'] ?> me-2"></i><?= htmlspecialchars($actionConfig['label']) ?>
                                </button>

                            <?php endif; ?>

                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

