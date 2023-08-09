<?php
/** @var String $id */
/** @var String $ariaLabel */
/** @var String $name */
/** @var string $text */
/** @var string $icon */
/** @var string $iconPosition */
/** @var bool $isDropDown */
/** @var bool $displayDropDownIcon */
/** @var string $dropDownIcon */
/** @var string $dropDownContent */
/** @var string $link */
/** @var array $htmlOptions */

$iconLeft = $icon && $iconPosition != 'right' ? '<i class="' .  $icon . '" ></i> ' : '';
$iconRight = $icon && $iconPosition == 'right' ? ' <i class="' .  $icon . '" ></i>' : '';
$dropDownIconHtml = $displayDropDownIcon ? '<span class="menu-button-divider"></span><i class="' . $dropDownIcon . '" ></i>' : '';
?>
<?php if ($link == '' || $isDropDown || array_key_exists('disabled', $htmlOptions)) : ?>
        <?= CHtml::htmlButton($iconLeft . $text . $iconRight . $dropDownIconHtml, $htmlOptions) ?>
    <?php if ($dropDownContent != '') : ?>
        <?= $dropDownContent ?>
    <?php endif; ?>
<?php else : ?>
    <?= CHtml::link($iconLeft . $text . $iconRight, $link, $htmlOptions); ?>
<?php endif; ?>
