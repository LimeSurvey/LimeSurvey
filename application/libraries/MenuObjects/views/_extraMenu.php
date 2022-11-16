<?php
/** @var array $extraMenus */

/** @var bool $prependedMenu */

use LimeSurvey\Menu\Menu;
use LimeSurvey\Menu\MenuButton;

foreach ($extraMenus as $menu): ?>
    <?php
    /** @var Menu $menu */
    if (($prependedMenu && $menu->isPrepended()) || (!$prependedMenu && !$menu->isPrepended())) : ?>
        <li class="dropdown">
            <?php
            if ($menu->isDropDown()): ?>
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    <?= $menu->getLabel(); ?>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                    <?php
                    foreach ($menu->getMenuItems() as $menuItem): ?>
                        <?php
                        if ($menuItem->isDivider()): ?>
                            <li class="divider"></li>
                        <?php
                        elseif ($menuItem->isSmallText()): ?>
                            <li class="dropdown-header"><?= $menuItem->getLabel(); ?></li>
                        <?php
                        else: ?>
                            <li>
                                <a href="<?= $menuItem->getHref(); ?>">
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
                <p class="navbar-btn"><a id="<?= $menuButton->getButtonId() ?>"
                                         href="<?= $menuButton->getHref(); ?>"
                                         class="<?= $menuButton->getButtonClass() ?>"
                                         title="<?= $menuButton->getTooltip() ?>"
                                         data-toggle="tooltip"
                                         data-placement="bottom"
                                         onclick="<?= $menuButton->getOnClick() ?>"
                                         target="<?= $target ?>">
                        <?= $menuButton->getLabel(); ?></a>
                </p>
            <?php
            else: ?>
                <a href="<?= $menu->getHref(); ?>">
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
