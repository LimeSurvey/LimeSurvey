<?php
/** @var array $attrDescription */
/** @var string $attrName */
/** @var string $inputValue */
/** @var string $jsDate */
$elementId = 'attribute_select_'. $attrName;
App()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', [
    'name' => $attrName,
    'id' => $elementId,
    'value' => $inputValue ?? '',
    'pluginOptions' => [
        'format' => $jsDate,
        'allowInputToggle' => true,
        'showClear' => true,
        'theme' => 'light',
        'locale' => convertLStoDateTimePickerLocale(
            Yii::app()->session['adminlang']
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

