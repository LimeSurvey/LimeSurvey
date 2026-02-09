<?php
/** @var array $extraMenus */

/** @var bool $middleSection */

/** @var bool $prependedMenu */

use LimeSurvey\Menu\Menu;
use LimeSurvey\Menu\MenuButton;

foreach ($extraMenus as $menu): ?>
    <?php
    $menuId = CHtml::encode($menu->getId());
    $menuLabel = CHtml::encode($menu->getLabel());
    $menuIconClass = CHtml::encode($menu->getIconClass());
    $menuHref = CHtml::encode($menu->getHref());
    $idAttr = ($menu->getId())? 'id="'. $menuId.'"' : '';
    $ariaLabelledBy = $idAttr ? 'aria-labelledby="' . $menuId . '"' : '';
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
                            class="dropdown-toggle <?= CHtml::encode($menu->getDropDownButtonClass()) ?>"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="<?= $menuLabel ?>">
                        <i class="<?= $menuIconClass ?>" aria-hidden="true"></i>
                    </button>
                    <?php
                } else { ?>
                    <a class="dropdown-toggle nav-link"
                       <?= $idAttr ?>
                       data-bs-toggle="dropdown"
                       href="#"
                       aria-expanded="false"
                       aria-haspopup="true">
                        <?= $menuLabel; ?>
                    </a>
                <?php }?>
                <ul class="dropdown-menu" <?= $ariaLabelledBy ?>>
                    <?php
                    foreach ($menu->getMenuItems() as $menuItem): ?>
                        <?php
                        $menuItemLabel = CHtml::encode($menuItem->getLabel());
                        $menuItemIconClass = CHtml::encode($menuItem->getIconClass());
                        if ($menuItem->isDivider()): ?>
                            <li class="dropdown-divider" role="separator"></li>
                        <?php
                        elseif ($menuItem->isSmallText()): ?>
                            <li class="dropdown-header"><?= $menuItemLabel; ?></li>
                        <?php
                        else: ?>
                            <li class="<?= CHtml::encode($menuItem->getItemClass()) ?> ms-3 me-3">
                                <?php
                                $menuItemId = ($menuItem->getId() !== null) ? 'id="' . CHtml::encode($menuItem->getId()) . '"' : '';
                                ?>
                                <?php if ($menuItem->isModal()): ?>
                                    <button class="dropdown-item"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#<?= CHtml::encode($menuItem->getModalId()) ?>"
                                        <?= $menuItemId ?>>
                                        <?php if ($menuItem->getIconClass() != ''): ?>
                                            <span class="<?= $menuItemIconClass; ?>" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <?= $menuItemLabel; ?>
                                    </button>
                                <?php else: ?>
                                    <a href="<?= CHtml::encode($menuItem->getHref()); ?>"
                                       class="dropdown-item"
                                        <?= $menuItemId ?>>
                                        <?php if ($menuItem->getIconClass() != ''): ?>
                                            <span class="<?= $menuItemIconClass; ?>" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <?= $menuItemLabel; ?>
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
                $menuButtonLabel = CHtml::encode($menuButton->getLabel());
                $menuButtonTooltip = CHtml::encode($menuButton->getTooltip());
                /** @var MenuButton $menuButton */
                $target = $menuButton->getOpenInNewTab() ? '_blank' : '_self';
                $relAttribute = $menuButton->getOpenInNewTab() ? 'noopener noreferrer' : '';
                ?>
                <a id="<?= CHtml::encode($menuButton->getButtonId()) ?>"
                   href="<?= CHtml::encode($menuButton->getHref()) ?>"
                   class="<?= CHtml::encode($menuButton->getButtonClass()) ?>"
                   title="<?= $menuButtonTooltip ?>"
                   data-bs-toggle="tooltip"
                   data-bs-placement="bottom"
                   onclick="<?= CHtml::encode($menuButton->getOnClick()) ?>"
                   target="<?= $target ?>"
                   <?php if ($relAttribute): ?>rel="<?= $relAttribute ?>"<?php endif; ?>
                   aria-label="<?= $menuButtonTooltip ?>">
                    <?= $menuButtonLabel; ?>
                </a>
            <?php
            else: ?>
                <a href="<?= $menuHref; ?>" class="nav-link" <?= $idAttr ?>>
                    <?php if ($menu->getIconClass()): ?>
                        <i class="<?= $menuIconClass; ?>" aria-hidden="true"></i>
                    <?php endif; ?>
                    <?= $menuLabel; ?>
                </a>
            <?php
            endif; ?>
        </li>
    <?php
    endif; ?>
<?php
endforeach; ?>