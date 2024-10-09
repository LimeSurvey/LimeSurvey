<?php
/**
 * This template renders an action list for a table row, where each action is represented as an icon with a link.
 *
 * @var array $items An array of actions to be displayed.
 * @var int $id A unique identifier for the action list container.
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
