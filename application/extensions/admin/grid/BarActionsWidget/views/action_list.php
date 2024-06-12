<?php
/**
 * @var array $dropdownItems
 */

?>
<div id="<?= 'action-list-' . $id; ?>" class="action-list">
    <ul id="<?= 'action-list-' . $id; ?>">
        <?php foreach ($items as $item) : ?>
            <li class="list-inline-item">
                <a href="<?= $item['enabledCondition'] ? $item['url'] : '#'?>"
                   class="<?=$item['enabledCondition'] ? '' : 'disabled'?>"
                   data-bs-toggle="tooltip"
                   data-bs-original-title="<?=$item['title']?>"
                >
                    <i class="<?=$item['iconClass']?>"></i>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
