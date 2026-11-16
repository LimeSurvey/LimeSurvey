<?php
/**
 * Renders the main view of the SideBarWidget
 * @var $icons array[] the icons to be displayed in the sidebar; 'svgPath' is set for icons in SideBarWidget::SIDEBAR_ICON_PATHS
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
                       aria-label="<?= CHtml::encode($icon['title']) ?>"
                        <?= $icon['selected'] ? 'aria-current="page" selected' : '' ?>>
                        <?php if ($icon['svgPath']) : ?>
                            <svg width="<?= (int)$icon['svgSize'] ?>" height="<?= (int)$icon['svgSize'] ?>" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                                <path d="<?= $icon['svgPath'] ?>"></path>
                            </svg>
                        <?php else : ?>
                            <i class="<?= CHtml::encode($icon['ico']); ?>" aria-hidden="true"></i>
                        <?php endif; ?>
                    </a>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php

/** here we could at the menu part of the sidebar */

    ?>
</div>
