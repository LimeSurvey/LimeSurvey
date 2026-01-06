<div class="mb-3">
    <label class="form-label" for="datepickerInputField_[<?=CHtml::encode($name)?>]"><?php echo $defaultname; ?></label>
    <?php
    $dateFormatDetails = getDateFormatData(App()->session['dateformat']);
    $elementId = 'attribute_date_'. $name;
    App()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', [
            'name' => $name,
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
