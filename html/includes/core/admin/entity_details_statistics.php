<?php
// Load statistics configuration
include_once 'includes/core/admin/entity_details_statistics_config.php';
?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <?php foreach ($statisticsCards as $statKey => $statConfig): ?>
        <?php if ($statConfig['enabled']): ?>
            <div class="col-xl-2 col-lg-4 col-md-6">
                <?php
                // Start card with optional link
                $cardClass = 'card custom-card';
                $cardStyle = isset($statConfig['link']) && $statConfig['link'] ? 'cursor: pointer;' : '';
                $cardOnclick = isset($statConfig['link']) && $statConfig['link'] ? 'onclick="window.location.href=\'' . $statConfig['link'] . '\'"' : '';
                ?>
                <div class="<?= $cardClass ?>" <?= $cardOnclick ?> style="<?= $cardStyle ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-top justify-content-between">
                            <div class="flex-fill">
                                <p class="mb-1 text-muted"><?= htmlspecialchars($statConfig['label']) ?></p>

                                <?php if (isset($statConfig['badge'])): ?>
                                    <!-- Special badge display (for status) -->
                                    <h3 class="mb-0 fw-semibold">
                                        <span class="badge bg-<?= $statConfig['badge']['color'] ?>-transparent fs-14">
                                            <?= htmlspecialchars($statConfig['badge']['text']) ?>
                                        </span>
                                    </h3>
                                <?php else: ?>
                                    <!-- Regular count display -->
                                    <h3 class="mb-0 fw-semibold"><?= $statConfig['count'] ?></h3>
                                <?php endif; ?>

                                <small class="text-muted fs-11"><?= htmlspecialchars($statConfig['description']) ?></small>
                            </div>
                            <div class="ms-2">
                                <span class="avatar avatar-md bg-<?= $statConfig['color'] ?>-transparent">
                                    <i class="fas <?= $statConfig['icon'] ?> fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

