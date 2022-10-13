<?php
/** @var String $id */
/** @var String $ariaLabel */
/** @var String $name */
/** @var string $text */
/** @var string $icon */
/** @var string $iconPosition */
/** @var bool $menu */
/** @var bool $menuIcon */
/** @var string $link */
/** @var array $htmlOptions */

$iconLeft = $icon && $iconPosition != 'right' ? '<i class="' .  $icon . '" ></i> ' : '';
$iconRight = $icon && $iconPosition == 'right' ? ' <i class="' .  $icon . '" ></i>' : '';
//@TODO switch to new icon when done
$menuIcon = $menuIcon ? '<span class="menu-button-divider"></span><i class="fa fa-ellipsis-h" ></i>' : '';
?>
<?php if ($link == '' || $menu) : ?>
    <?= CHtml::htmlButton($iconLeft . $text . $iconRight . $menuIcon, $htmlOptions) ?>
<?php else : ?>
    <?= CHtml::link($iconLeft . $text . $iconRight, $link, $htmlOptions); ?>
<?php endif; ?>
