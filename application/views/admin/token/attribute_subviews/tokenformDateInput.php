<?php
/** @var array $attrDescription */
/** @var string $attrName */
/** @var string $inputValue */
/** @var string $jsDate */
/** @var boolean $batchEdit */
$batchEdit = isset($batchEdit) && $batchEdit;
$inputClass = $batchEdit ? 'custom-data selector_submitField' : '';
$inputClass .=  $attrDescription['mandatory'] == 'Y' ? ' mandatory-attribute' : '';
$elementId = ($batchEdit ? 'massedit_' : 'attribute_date_') . $attrName;
$inputValue = $batchEdit ? 'lskeep' : (!empty($inputValue) ? convertToGlobalSettingFormat($inputValue) : $inputValue);

App()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', [
    'name' => $attrName,
    'id' => $elementId,
    'value' => $inputValue ?? '',
    'htmlOptions' => [
        'disabled' => $batchEdit,
        'class' => $inputClass,
    ],
    'pluginOptions' => [
        'format' => $jsDate,
        'allowInputToggle' => true,
        'theme' => 'light',
        'locale' => convertLStoDateTimePickerLocale(
            App()->session['adminlang']
        )
    ]
]);
?>

<script>
    // Reinitialize datepicker when loaded in modal
    $(document).ready(function() {
        var element = document.getElementById('<?= $elementId ?>');
        if (element && element.closest('.modal')) {
            // Element is inside a modal, reinitialize
            initDatePicker(element, '<?= convertLStoDateTimePickerLocale(App()->session['adminlang']) ?>', '<?= $jsDate ?>');
        }
    });
</script>

