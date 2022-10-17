<?php
/** @var String $id */
/** @var String $ariaLabel */
/** @var String $name */
/** @var string $text */
/** @var string $icon */
/** @var string $iconPosition */
/** @var bool $menu */
/** @var bool $displayMenuIcon */
/** @var string $menuIcon */
/** @var string $menuContent */
/** @var string $link */
/** @var array $htmlOptions */

$iconLeft = $icon && $iconPosition != 'right' ? '<i class="' .  $icon . '" ></i> ' : '';
$iconRight = $icon && $iconPosition == 'right' ? ' <i class="' .  $icon . '" ></i>' : '';
$menuIconHtml = $displayMenuIcon ? '<span class="menu-button-divider"></span><i class="' . $menuIcon . '" ></i>' : '';
?>
<?php if ($link == '' || $menu) : ?>
    <?php if ($menuContent != '') : ?>
        <div class="dropdown">
    <?php endif; ?>
        <?= CHtml::htmlButton($iconLeft . $text . $iconRight . $menuIconHtml, $htmlOptions) ?>
    <?php if ($menuContent != '') : ?>
            <?= $menuContent ?>
        </div>
    <?php endif; ?>
<?php else : ?>
    <?= CHtml::link($iconLeft . $text . $iconRight, $link, $htmlOptions); ?>
<?php endif; ?>
