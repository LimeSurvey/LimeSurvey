<?php
/** @var array $extraMenus */

/** @var bool $middleSection */

/** @var bool $prependedMenu */

use LimeSurvey\Menu\Menu;
use LimeSurvey\Menu\MenuButton;

foreach ($extraMenus as $menu): ?>
    <?php
    $sectionFilter = (($middleSection && $menu->isInMiddleSection()) || (!$middleSection && !$menu->isInMiddleSection()));
    $prependedFilter = (($prependedMenu && $menu->isPrepended()) || (!$prependedMenu && !$menu->isPrepended()));
    /** @var Menu $menu */
    if ($sectionFilter && $prependedFilter) : ?>
        <li class="dropdown nav-item">
            <?php
            if ($menu->isDropDown()): ?>
            <?php if ($menu->isDropDownButton()) { ?>
                    <a href="#" class="nav-link " data-bs-toggle="dropdown" aria-expanded="false" role="button">
                        <button type="button" class="btn btn-info btn-create" data-bs-toggle="tooltip" data-bs-placement="bottom">
                            <i class="ri-add-line"></i>
                        </button>
                    </a>
                <?php
                } else { ?>
                <a class="dropdown-toggle nav-link" data-bs-toggle="dropdown" href="#">
                    <?= $menu->getLabel(); ?>
                    <span class="caret"></span>
                </a>
                <?php }?>
                <ul class="dropdown-menu" role="menu">
                    <?php
                    foreach ($menu->getMenuItems() as $menuItem): ?>
                        <?php
                        if ($menuItem->isDivider()): ?>
                            <li class="dropdown-divider"></li>
                        <?php
                        elseif ($menuItem->isSmallText()): ?>
                            <li class="dropdown-header"><?= $menuItem->getLabel(); ?></li>
                        <?php
                        else: ?>
                            <li class="create-menu-item ms-3 me-3">
                                <a href="<?= $menuItem->getHref(); ?>" class="dropdown-item"
                                    <?php if ($menuItem->getId() !== null) {
                                        echo 'id="'. $menuItem->getId(). '"';
                                    }?>
                                >
                                    <!-- Spit out icon if present -->
                                    <?php
                                    if ($menuItem->getIconClass() != ''): ?>
                                        <span class="<?= $menuItem->getIconClass(); ?>">&nbsp;</span>
                                    <?php
                                    endif; ?>
                                    <?= $menuItem->getLabel(); ?>
                                </a>
                            </li>
                        <?php
                        endif; ?>
                    <?php
                    endforeach; ?>
                </ul>
            <?php
            elseif ($menu->isButton()): ?>
                <?php
                $menuButton = $menu;
                /** @var MenuButton $menuButton */
                $target = $menuButton->getOpenInNewTab() ? '_blank' : '_self';
                ?>
                <a id="<?= $menuButton->getButtonId() ?>"
                   href="<?= $menuButton->getHref(); ?>"
                   class="<?= $menuButton->getButtonClass() ?>"
                   title="<?= $menuButton->getTooltip() ?>"
                   data-bs-toggle="tooltip"
                   data-bs-placement="bottom"
                   onclick="<?= $menuButton->getOnClick() ?>"
                   target="<?= $target ?>">
                    <?= $menuButton->getLabel(); ?>
                </a>
            <?php
            else: ?>
                <a href="<?= $menu->getHref(); ?>" class="nav-link">
                    <?php if ($menu->getIconClass()): ?>
                        <i class="<?= $menu->getIconClass(); ?>"></i>
                    <?php endif; ?>
                    <?= $menu->getLabel(); ?>
                </a>
            <?php
            endif; ?>
        </li>
    <?php
    endif; ?>
<?php
endforeach; ?>
