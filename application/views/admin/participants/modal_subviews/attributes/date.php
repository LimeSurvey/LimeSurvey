<?php
/** @var int $attribute_id */
/** @var string $attribute_type */
/** @var string $core_attribute */
/** @var string $defaultname */
/** @var string $encrypted */
/** @var string $name */
/** @var string $value */
/** @var string $visible */

$name = CHtml::encode($name);
$defaultname = CHtml::encode($defaultname);
$elementId = 'Attributes_'. $name;
$inputName = "Attributes[$name]";
?>
<div class="mb-3">
    <label class="form-label" for="<?=CHtml::encode($elementId)?>"><?php echo $defaultname; ?></label>
    <?php
    $dateFormatDetails = getDateFormatData(App()->session['dateformat']);

    App()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', [
            'name' => $inputName,
            'id' => $elementId,
            'value' => $value ?? '',
            'pluginOptions' => [
                    'format' => $dateFormatDetails['jsdate'],
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
                initDatePicker(element, '<?= convertLStoDateTimePickerLocale(App()->session['adminlang']) ?>', '<?= $dateFormatDetails['jsdate'] ?>');
            }
        });
    </script>
</div>
