<?php
/** @var array $attrDescription */
/** @var string $attrName */
/** @var string $inputValue */
/** @var boolean $batchEdit */
$batchEdit = isset($batchEdit) && $batchEdit;
$inputClass = $batchEdit ? ' custom-data selector_submitField' : '';
$inputClass .=  $attrDescription['mandatory'] == 'Y' ? ' mandatory-attribute' : '';
$id = $batchEdit ? 'massedit_' . $attrName : $attrName;
$inputValue = $batchEdit ? 'lskeep' : (isset($inputValue) ? htmlspecialchars(
    (string)$inputValue,
    ENT_QUOTES,
    'utf-8'
) : null);
$emptyOptionValue = $batchEdit ? 'lskeep' : '';
?>
<select
    class="form-select <?= $inputClass ?>"
    name="<?=CHtml::encode($attrName)?>"
    id="<?=CHtml::encode($id)?>"
    <?= $batchEdit ? 'disabled' : '' ?>
>
    <option value="<?= $emptyOptionValue ?>"></option>  <!-- Nothing selected -->
    <?php foreach ($attrDescription['type_options'] as $optionKey => $optionValue): ?>
        <option
            <?php if ($optionValue == $inputValue): echo 'selected'; endif; ?>
            value='<?= CHtml::encode($optionValue); ?>'
        >
            <?php echo CHtml::encode($optionValue); ?>
        </option>
    <?php endforeach; ?>
</select>
