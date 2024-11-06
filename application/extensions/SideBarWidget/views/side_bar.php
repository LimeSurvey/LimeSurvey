<?php
/**
 * Renders the main view of the SideBarWidget
 * @var $icons array[] the icons to be displayed in the sidebar
 */

?>
<div class="main-sidebar-container">
    <div class="sidebar sidebar-left">
        <div class="sidebar-icons">
            <?php foreach ($icons as $icon) : ?>
                <div class="sidebar-icon">
                    <div data-bs-toggle="tooltip"
                         title="<?= $icon['title'] ?>"
                         data-bs-offset="0, 20"
                         data-bs-placement="right">
                        <a href="<?= $icon['url'] ?>"
                           target="<?= $icon['external'] ? '_blank' : '' ?>"
                           class="btn btn-g-800 btn-icon <?= $icon['selected'] ? 'active' : '' ?>"
                        >
                            <i class="<?php echo CHtml::encode($icon['ico']); ?>"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php /** here we could at the menu part of the sidebar */ ?>
    </div>

</div>
