<?php
/**
 * @var array $items
 */

?>
<div id="<?= 'action-list-' . $id; ?>" class="action-list">
    <ul id="<?= 'action-list-' . $id; ?>">
        <?php foreach ($items as $item) : ?>
            <li class="list-inline-item">
                <a href="<?= $item['enabledCondition'] ? $item['url'] : '#'?>"
                   class="<?=$item['enabledCondition'] ? '' : 'disabled'?>"
                    <?php if ($item['enabledCondition']) : ?>
                        data-bs-toggle="tooltip"
                        data-bs-original-title="<?=$item['title'] ?>"
                    <?php endif;?>
                >
                    <i class="<?=$item['iconClass']?>"></i>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
