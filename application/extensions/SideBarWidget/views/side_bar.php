<?php
/**
 * Renders the main view of the SideBarWidget
 * @var $icons array[] the icons to be displayed in the sidebar
 */

?>
<div class="sidebar" style="width: 300px; background:white; height: 100vh">
    <div class="sidebar-icons">
        <?php foreach ($icons as $icon) : ?>
            <div class="sidebar-icon d-flex gap-3 ">
                <div data-bs-toggle="tooltip"
                     title="<?= $icon['title'] ?>"
                     data-bs-offset="0, 20"
                     data-bs-placement="right">
                    <a href="<?= $icon['url'] ?>"
                       target="<?= $icon['external'] ? '_blank' : '' ?>"
                       class="btn btn-g-800 btn-icon"
                        <?= $icon['selected'] ? 'selected' : '' ?>
                    >
                        <i class="<?php echo CHtml::encode($icon['ico']); ?>"></i>
                    </a>
                    
                </div>

                <div>  <?= $icon['title'] ?> </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php /** here we could at the menu part of the sidebar */ ?>
</div>
