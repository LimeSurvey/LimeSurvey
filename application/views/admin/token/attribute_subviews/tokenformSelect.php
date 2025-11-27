<?php
/** @var array $attrDescription */
/** @var string $attrName */
/** @var string $inputValue */
?>
<select class="form-select" name="<?=CHtml::encode($attrName)?>" id="<?=CHtml::encode($attrName)?>">
    <option></option>  <!-- Nothing selected -->
    <?php foreach ($attrDescription['type_options'] as $optionKey => $optionValue): ?>
        <option
            <?php if ($optionValue == $inputValue): echo 'selected'; endif; ?>
            value='<?= CHtml::encode($optionValue); ?>'
        >
            <?php echo CHtml::encode($optionValue); ?>
        </option>
    <?php endforeach; ?>
</select>
