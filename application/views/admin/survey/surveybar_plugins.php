<?php

/**
 * Subview of surveybar_view.
 * @param $beforeSurveyBarRender
 */

?>

<?php // TODO: This views should be in same module as ExtraMenu and ExtraMenuItem classes (not plugin) ?>
<?php // TODO: Copied from adminmenu.php ?>
<?php foreach ($beforeSurveyBarRender as $menu): ?>
    <div class='btn-group'>
        <?php if ($menu->isDropDown()): ?>
	    <button class="dropdown-toggle btn btn-outline-secondary" data-bs-toggle="dropdown" href="#">
              <?php if ($menu->getIconClass()): ?>
                  <span class="<?php echo $menu->getIconClass(); ?>"></span>&nbsp;
              <?php endif; ?>
              <?php echo $menu->getLabel(); ?>
              &nbsp;
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
                <?php foreach ($menu->getMenuItems() as $menuItem): ?>
                    <?php if ($menuItem->isDivider()): ?>
                        <li class="dropdown-divider"></li>
                    <?php elseif ($menuItem->isSmallText()): ?>
                        <li class="dropdown-header"><?php echo $menuItem->getLabel(); ?></li>
                    <?php else: ?>
                        <li>
                            <a href="<?php echo $menuItem->getHref(); ?>">
                                <!-- Spit out icon if present -->
                                <?php if ($menuItem->getIconClass() != ''): ?>
                                  <span class="<?php echo $menuItem->getIconClass(); ?>">&nbsp;</span>
                                <?php endif; ?>
                                <?php echo $menuItem->getLabel(); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <a class='btn btn-outline-secondary' href="<?php echo $menu->getHref(); ?>">
                <?php if ($menu->getIconClass()): ?>
                    <span class="<?php echo $menu->getIconClass(); ?>"></span>&nbsp;
                <?php endif; ?>
                <?php echo $menu->getLabel(); ?>
            </a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
