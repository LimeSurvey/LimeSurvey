<?php
$dateFormatDetails = getDateFormatData(Yii::app()->session['dateformat']);
?>
<div class="selector--edit-status-container">
    <div class="form">
        <div class="mb-3">
            <label for="batchExpiresPicker"><?php eT("Expires:"); ?></label>
            <div class='col-md-6'>
                <?php
                $widget = Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                    'name' => 'batchExpiresPicker',
                    'id' => 'batchExpiresPicker',
                    'pluginOptions' => array(
                        'format' => $dateFormatDetails['jsdate'] . " HH:mm",
                        'allowInputToggle' => true,
                        'showClear' => true,
                        'theme' => 'light',
                        'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                    )
                ));
                ?>
                <input class="form-control custom-data" name="batchExpires" id="batchExpires" type="hidden" value="">
                <script type="text/javascript">
                    $(function () {
                        // datepicker needs to be reinitialized, due to ajax reload of modal:
                        <?= $widget->getConfigScript('batchExpiresPicker'); ?>
                        document.getElementById("batchExpiresPicker_datetimepicker").addEventListener("change.td", function(){
                            document.getElementById("batchExpires").value = document.getElementById("batchExpiresPicker").value;
                        });
                    });
                </script>
            </div>
        </div>
    </div>
    <div id="hereBeUserIds">
    </div>
</div>
