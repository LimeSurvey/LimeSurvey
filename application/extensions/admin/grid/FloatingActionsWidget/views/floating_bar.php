<?php
/**
 * Floating Actions Widget – bar view
 *
 * @var FloatingActionsWidget $this
 */
$gridId = CHtml::encode($this->gridId);
$pk     = CHtml::encode($this->pk);
?>
<!-- FloatingActionsWidget: bar for grid "<?= $gridId ?>" -->
<div
    id="floating-actions-bar-<?= $gridId ?>"
    class="floating-actions-bar"
    data-grid-id="<?= $gridId ?>"
    data-pk="<?= $pk ?>"
    role="toolbar"
    aria-label="<?= gT('Actions for selected items') ?>"
>
    <!-- Selected-item counter -->
    <span class="floating-actions-count" aria-live="polite" aria-atomic="true">
        <span class="floating-actions-count-number">0</span>&nbsp;<?= gT('selected') ?>
    </span>

    <?php foreach ($this->aActions as $key => $action) : ?>
        <?php if (!is_array($action) || empty($action['type'])) : ?>
            <?php continue; ?>
        <?php endif; ?>

        <?php if ($action['type'] === 'separator') : ?>

            <div class="floating-actions-separator" role="separator" aria-hidden="true"></div>

        <?php elseif ($action['type'] === 'action') : ?>

            <button
                type="button"
                class="floating-actions-btn<?= !empty($action['btnClass']) ? ' ' . CHtml::encode($action['btnClass']) : '' ?>"
                data-url="<?= CHtml::encode($action['url'] ?? '') ?>"
                data-action="<?= CHtml::encode($action['action'] ?? '') ?>"
                data-action-type="<?= CHtml::encode($action['actionType'] ?? '') ?>"
                data-grid-reload="<?= CHtml::encode($action['grid-reload'] ?? 'no') ?>"
                <?php if (($action['actionType'] ?? '') === 'modal') : ?>
                    data-modal-id="<?= $this->getModalId((string) $key, $action['action']) ?>"
                <?php endif; ?>
                <?php if (isset($action['aLinkSpecificDatas'])) : ?>
                    <?php foreach ($action['aLinkSpecificDatas'] as $dataName => $dataValue) : ?>
                        data-<?= CHtml::encode($dataName) ?>="<?= CHtml::encode($dataValue) ?>"
                    <?php endforeach; ?>
                <?php endif; ?>
            >
                <i class="<?= CHtml::encode($action['iconClasses'] ?? '') ?>"></i>
                <?= CHtml::encode($action['text'] ?? '') ?>
            </button>

        <?php elseif ($action['type'] === 'dropdown' && !empty($action['items'])) : ?>

            <div class="dropdown">
                <button
                    type="button"
                    class="floating-actions-btn dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="<?= CHtml::encode($action['icon'] ?? '') ?>"></i>
                    <?= CHtml::encode($action['text'] ?? '') ?>
                </button>
                <ul class="dropdown-menu">
                    <?php foreach ($action['items'] as $subKey => $subAction) : ?>
                        <li>
                            <a href="#"
                               class="dropdown-item floating-actions-item"
                               data-url="<?= CHtml::encode($subAction['url'] ?? '') ?>"
                               data-action="<?= CHtml::encode($subAction['action'] ?? '') ?>"
                               data-action-type="<?= CHtml::encode($subAction['actionType'] ?? '') ?>"
                               data-grid-reload="<?= CHtml::encode($subAction['grid-reload'] ?? 'no') ?>"
                               <?php if (($subAction['actionType'] ?? '') === 'modal') : ?>
                                   data-modal-id="<?= $this->getModalId('d' . $key . '_' . $subKey, $subAction['action']) ?>"
                               <?php endif; ?>
                               <?php if (isset($subAction['aLinkSpecificDatas'])) : ?>
                                   <?php foreach ($subAction['aLinkSpecificDatas'] as $dataName => $dataValue) : ?>
                                       data-<?= CHtml::encode($dataName) ?>="<?= CHtml::encode($dataValue) ?>"
                                   <?php endforeach; ?>
                               <?php endif; ?>
                            >
                                <?php if (!empty($subAction['iconClasses'])) : ?>
                                    <i class="<?= CHtml::encode($subAction['iconClasses']) ?>"></i>
                                <?php endif; ?>
                                <?= CHtml::encode($subAction['text'] ?? '') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Close / deselect-all -->
    <button
        type="button"
        class="floating-actions-close"
        aria-label="<?= gT('Deselect all') ?>"
        title="<?= gT('Deselect all') ?>"
    >
        <i class="ri-close-line"></i>
    </button>
</div>
<!-- /FloatingActionsWidget -->

