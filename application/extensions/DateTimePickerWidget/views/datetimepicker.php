<?php
/** @var $name */
/** @var $id */
/** @var $hasModel */
/** @var $model */
/** @var $attribute */
/** @var $htmlOptions */
/** @var $value */
?>

<div
    class='input-group date'
    id="<?= $id ?>"
    data-td-target-input='nearest'
    data-td-target-toggle='nearest'
>
    <?php
    if ($hasModel) : ?>
        <?= CHtml::activeTextField($model, $attribute, $htmlOptions) ?>
    <?php else : ?>
        <?= CHtml::textField($name, $value, $htmlOptions) ?>
    <?php endif; ?>
    <span
        class='input-group-text datepicker-icon'
        data-td-target='#<?= $id ?>'
        data-td-toggle='datetimepicker'
    >
     <span class='ri-calendar-2-fill'></span>
   </span>
</div>
