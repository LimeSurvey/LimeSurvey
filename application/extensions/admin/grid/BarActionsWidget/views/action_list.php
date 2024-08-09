<?php
/**
 * @var array $items
 */

?>
<div id="<?= 'action-list-' . $id; ?>" class="action-list">
    <?php foreach ($items as $item) : ?>
        <a href="<?= $item['enabledCondition'] ? $item['url'] : '#' ?>"
           class="<?= $item['enabledCondition'] ? '' : 'disabled' ?>"
            <?php if ($item['enabledCondition']) : ?>
                data-bs-toggle="tooltip"
                data-bs-original-title="<?= $item['title'] ?>"
            <?php endif; ?>
        >
            <i class="<?= $item['iconClass'] ?>"></i>
        </a>
    <?php endforeach; ?>
</div>
