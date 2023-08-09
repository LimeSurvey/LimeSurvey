<?php

/** @var String $id */
/** @var String $ariaLabel */
/** @var String $name */
/** @var string $value */
/** @var bool $isImportant */
/** @var bool $isAttached */
/** @var string $attachContent */
/** @var array $htmlOptions */
/** @var array $wrapperHtmlOptions */

?>
<?php if ($isAttached) : ?>

  <?php if ($attachContent != '') : ?>
    <?= CHtml::tag('div', $wrapperHtmlOptions, CHtml::textField($name, $value, $htmlOptions) . $attachContent, true)  ?>
  
  <?php else : ?>
  <?= CHtml::textField($name, $value, $htmlOptions)  ?>

<?php endif; ?>


<?php endif; ?>