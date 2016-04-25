<?php if (isset($datestamp) && $datestamp == "Y"): ?>
    <div class="panel panel-primary " id="pannel-1">
        <div class="panel-heading">
            <h4 class="panel-title"><?php eT("Submission date"); ?></h4>
        </div>
        <div class="panel-body">
            <div class='form-group'>
                <label class="col-sm-4 control-label" for='datestampE'><?php eT("Equals:"); ?></label>
                <div class="col-sm-5">
                    <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                            'name' => "datestampE",
                            'id' => 'datestampE',
                            'value' => isset($_POST['datestampE']) ? $_POST['datestampE'] : '',
                            'pluginOptions' => array(
                                'format' => reverseDateToFitDatePicker($dateformatdetails['dateformat']) . " HH:mm",
                                'singleDatePicker' => true,
                                'startDate' => date("Y-m-d H:i", time()),
                                'drops' => 'up',  // TODO: Does not work. Why?
                                'timePicker' => true,
                                'timePicker12Hour' => false,  // NB: timePicker24Hour = true does not work
                                'timePickerIncrement' => 1
                            )
                        ));
                    ?>
                </div>
            </div>
            <div class='form-group'>
                <label class="col-sm-4 control-label" for='datestampG'><?php eT("Later than:");?></label>
                <div class="col-sm-5">
                    <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                            'name' => "datestampG",
                            'id' => 'datestampG',
                            'value' => isset($_POST['datestampG']) ? $_POST['datestampG'] : '',
                            'pluginOptions' => array(
                                'format' => reverseDateToFitDatePicker($dateformatdetails['dateformat']) . " HH:mm",
                                'singleDatePicker' => true,
                                'startDate' => date("Y-m-d H:i", time()),
                                'drops' => 'up',  // TODO: Does not work. Why?
                                'timePicker' => true,
                                'timePicker12Hour' => false,  // NB: timePicker24Hour = true does not work
                                'timePickerIncrement' => 1
                            )
                        ));
                    ?>
                </div>
            </div>
            <div class='form-group'>
                <label class="col-sm-4 control-label" for='datestampL'><?php eT("Earlier than:");?></label>
                <div class="col-sm-5">
                    <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                            'name' => "datestampL",
                            'id' => 'datestampL',
                            'value' => isset($_POST['datestampL']) ? $_POST['datestampL'] : '',
                            'pluginOptions' => array(
                                'format' => reverseDateToFitDatePicker($dateformatdetails['dateformat']) . " HH:mm",
                                'singleDatePicker' => true,
                                'startDate' => date("Y-m-d H:i", time()),
                                'drops' => 'up',  // TODO: Does not work. Why?
                                'timePicker' => true,
                                'timePicker12Hour' => false,  // NB: timePicker24Hour = true does not work
                                'timePickerIncrement' => 1
                            )
                        ));
                    ?>
                </div>
            </div>
            <input type='hidden' name='summary[]' value='datestampE' />
            <input type='hidden' name='summary[]' value='datestampG' />
            <input type='hidden' name='summary[]' value='datestampL' />
        </div>
    </div>
    <?php endif; ?>
