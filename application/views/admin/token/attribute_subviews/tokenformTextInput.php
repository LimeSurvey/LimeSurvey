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
?>
<input
    class='form-control <?= $inputClass ?>'
    type='text'
    size='55'
    id='<?= $id; ?>'
    name='<?= $attrName; ?>'
    value='<?= $inputValue ?>'
    <?= $batchEdit ? 'disabled' : '' ?>
/>
