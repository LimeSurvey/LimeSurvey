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
                       class="btn btn-g-800 btn-icon"
                        <?= $icon['selected'] ? 'selected' : '' ?>
                    >
                        <i class="<?php echo CHtml::encode($icon['ico']); ?>"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php /** here we could at the menu part of the sidebar */ ?>
</div>
