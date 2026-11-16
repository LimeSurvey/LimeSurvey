<?php
/**
 * This template renders an action list for a table row, where each action is represented as an icon with a link.
 * Disabled actions are rendered without href, so they are not focusable and not announced as working links.
 *
 * @var array $items An array of actions to be displayed.
 * @var int $id A unique identifier for the action list container.
 */
?>

<div id="<?= 'action-list-' . $id; ?>" class="action-list">
    <?php foreach ($items as $item) : ?>
        <a <?php if ($item['enabledCondition']) : ?>
                href="<?= $item['url'] ?>"
                data-bs-toggle="tooltip"
                data-bs-original-title="<?= $item['title'] ?>"
            <?php else : ?>
                class="disabled"
                role="link"
                aria-disabled="true"
            <?php endif; ?>
            aria-label="<?= $item['title'] ?>"
        >
            <i class="<?= $item['iconClass'] ?>" aria-hidden="true"></i>
        </a>
    <?php endforeach; ?>
</div>
