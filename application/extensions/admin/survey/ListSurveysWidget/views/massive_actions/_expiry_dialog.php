<?php

/** @var AdminController $this */
$dateFormatDetails = getDateFormatData(Yii::app()->session['dateformat']);

?>
<?php
$form = $this->beginWidget('CActiveForm', array('id' => 'survey-expiry',)); ?>

<div id='publication'>
    <div class="row">
        <!-- Expiry date/time -->
        <div class="mb-3">
            <label class="col-md-6 form-label" for='expires'><?php
                eT("Expiry date/time:"); ?></label>
            <div class='col-md-6'>
                <?php
                $widget = Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                    'name' => 'datepickerInputField',
                    'id' => 'expiryPicker',
                    'pluginOptions' => array(
                        'format' => $dateFormatDetails['jsdate'] . " HH:mm",
                        'allowInputToggle' => true,
                        'showClear' => true,
                        'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                    )
                ));
                ?>
                <input class="form-control custom-data" name="expires" id="expires" type="hidden" value="">
                <script type="text/javascript">
                    $(function () {
                        // datepicker needs to be reinitialized, due to ajax reload of modal:
                        <?= $widget->getConfigScript('expiryPicker'); ?>
                        document.getElementById("expiryPicker_datetimepicker").addEventListener("change.td", function(){
                            document.getElementById("expires").value = document.getElementById("expiryPicker").value;
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</div>
<?php
$this->endWidget(); ?>
