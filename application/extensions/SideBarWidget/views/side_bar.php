<?php
/**
 * Renders the main view of the SideBarWidget
 * @var $icons array[] the icons to be displayed in the sidebar
 */

?>
<div class="sidebar">
    <div class="sidebar-icons">
        <?php foreach ($icons as $icon) : ?>
            <div class="sidebar-icon">
                <div data-bs-toggle="tooltip"
                     title="<?= CHtml::encode($icon['title']) ?>"
                     data-bs-offset="0, 20"
                     data-bs-placement="right">
                    <a href="<?= CHtml::encode($icon['url']) ?>"
                       target="<?= $icon['external'] ? '_blank' : '' ?>"
                       class="btn btn-g-800 btn-icon <?= $icon['selected'] ? 'active' : '' ?>"
                       aria-label="<?= $icon['title'] ?>"
                        <?= $icon['selected'] ? 'aria-current="page" selected' : '' ?>>
                        <i class="<?= CHtml::encode($icon['ico']); ?>" aria-hidden="true"></i>
                    </a>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php /** here we could at the menu part of the sidebar */ ?>
</div>
