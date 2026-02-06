<?php
/** @var array $extraMenus */

/** @var bool $middleSection */

/** @var bool $prependedMenu */

use LimeSurvey\Menu\Menu;
use LimeSurvey\Menu\MenuButton;

foreach ($extraMenus as $menu): ?>
    <?php
    $idAttr = ($menu->getId())? 'id="'. $menu->getId().'"' : '';
    $ariaLabelledBy = $idAttr ? 'aria-labelledby="' . $menu->getId() . '"' : '';
    $sectionFilter = (($middleSection && $menu->isInMiddleSection()) || (!$middleSection && !$menu->isInMiddleSection()));
    $prependedFilter = (($prependedMenu && $menu->isPrepended()) || (!$prependedMenu && !$menu->isPrepended()));
    /** @var Menu $menu */
    if ($sectionFilter && $prependedFilter) : ?>
        <li class="dropdown nav-item extra-menu-dropdown">
            <?php
            if ($menu->isDropDown()): ?>
                <?php if ($menu->isDropDownButton()) { ?>
                    <button type="button"
                            <?= $idAttr ?>
                            class="dropdown-toggle <?= $menu->getDropDownButtonClass() ?>"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="<?= $menu->getLabel() ?>">
                        <i class="ri-add-line" aria-hidden="true"></i>
                    </button>
                    <?php
                } else { ?>
                    <a class="dropdown-toggle nav-link"
                       <?= $idAttr ?>
                       data-bs-toggle="dropdown"
                       href="#"
                       aria-expanded="false"
                       aria-haspopup="true">
                        <?= $menu->getLabel(); ?>
                    </a>
                <?php }?>
                <ul class="dropdown-menu" <?= $ariaLabelledBy ?>>
                    <?php
                    foreach ($menu->getMenuItems() as $menuItem): ?>
                        <?php
                        if ($menuItem->isDivider()): ?>
                            <li class="dropdown-divider" role="separator"></li>
                        <?php
                        elseif ($menuItem->isSmallText()): ?>
                            <li class="dropdown-header"><?= $menuItem->getLabel(); ?></li>
                        <?php
                        else: ?>
                            <li class="<?= $menuItem->getItemClass() ?> ms-3 me-3">
                                <?php
                                $menuItemId = ($menuItem->getId() !== null) ? 'id="' . $menuItem->getId() . '"' : '';
                                ?>
                                <?php if ($menuItem->isModal()): ?>
                                    <button class="dropdown-item"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#<?= $menuItem->getModalId() ?>"
                                        <?= $menuItemId ?>>
                                        <?php if ($menuItem->getIconClass() != ''): ?>
                                            <span class="<?= $menuItem->getIconClass(); ?>" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <?= $menuItem->getLabel(); ?>
                                    </button>
                                <?php else: ?>
                                    <a href="<?= $menuItem->getHref(); ?>"
                                       class="dropdown-item"
                                        <?= $menuItemId ?>>
                                        <?php if ($menuItem->getIconClass() != ''): ?>
                                            <span class="<?= $menuItem->getIconClass(); ?>" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <?= $menuItem->getLabel(); ?>
                                    </a>
                                <?php endif; ?>
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
                $relAttribute = $menuButton->getOpenInNewTab() ? 'noopener noreferrer' : '';
                ?>
                <a id="<?= $menuButton->getButtonId() ?>"
                   href="<?= $menuButton->getHref(); ?>"
                   class="<?= $menuButton->getButtonClass() ?>"
                   title="<?= $menuButton->getTooltip() ?>"
                   data-bs-toggle="tooltip"
                   data-bs-placement="bottom"
                   onclick="<?= $menuButton->getOnClick() ?>"
                   target="<?= $target ?>"
                   <?php if ($relAttribute): ?>rel="<?= $relAttribute ?>"<?php endif; ?>
                   aria-label="<?= $menuButton->getTooltip() ?>">
                    <?= $menuButton->getLabel(); ?>
                </a>
            <?php
            else: ?>
                <a href="<?= $menu->getHref(); ?>" class="nav-link" <?= $idAttr ?>>
                    <?php if ($menu->getIconClass()): ?>
                        <i class="<?= $menu->getIconClass(); ?>" aria-hidden="true"></i>
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