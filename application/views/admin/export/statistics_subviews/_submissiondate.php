<?php if (isset($datestamp) && $datestamp == "Y"): ?>
    <div class="panel panel-primary " id="panel-1">
        <div class="panel-heading">
            <div class="panel-title h4"><?php eT("Submission date"); ?></div>
        </div>
        <div class="panel-body">

            <div class="row ls-space margin top-5">
                <div class='form-group'>
                    <label class="col-sm-4 control-label" for='datestampE'><?php eT("Equals:"); ?></label>
                    <div class="col-sm-5 has-feedback">
                        <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                                'name' => "datestampE",
                                'id' => 'datestampE',
                                'value' => isset($_POST['datestampE']) ? $_POST['datestampE'] : '',
                                'pluginOptions' => array(
                                    'format' => ($dateformatdetails['jsdate']),
                                    'allowInputToggle' =>true,
                                    'showClear' => true,
                                    'tooltips' => array(
                                        'clear'=> gT('Clear selection'),
                                        'prevMonth'=> gT('Previous month'),
                                        'nextMonth'=> gT('Next month'),
                                        'selectYear'=> gT('Select year'),
                                        'prevYear'=> gT('Previous year'),
                                        'nextYear'=> gT('Next year'),
                                        'selectDecade'=> gT('Select decade'),
                                        'prevDecade'=> gT('Previous decade'),
                                        'nextDecade'=> gT('Next decade'),
                                        'prevCentury'=> gT('Previous century'),
                                        'nextCentury'=> gT('Next century'),
                                    'selectTime'=> gT('Select time')
                                    ),
                                    'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                                )
                            ));
                        ?>
                    </div>
                </div>
            </div>

            <div class="row ls-space margin top-5">
                <div class='form-group'>
                <label class="col-sm-4 control-label" for='datestampG'><?php eT("Later than:");?></label>
                    <div class="col-sm-5 has-feedback">
                        <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                                'name' => "datestampG",
                                'id' => 'datestampG',
                                'value' => isset($_POST['datestampG']) ? $_POST['datestampG'] : '',
                                'pluginOptions' => array(
                                    'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                    'allowInputToggle' =>true,
                                    'showClear' => true,
                                    'tooltips' => array(
                                        'clear'=> gT('Clear selection'),
                                        'prevMonth'=> gT('Previous month'),
                                        'nextMonth'=> gT('Next month'),
                                        'selectYear'=> gT('Select year'),
                                        'prevYear'=> gT('Previous year'),
                                        'nextYear'=> gT('Next year'),
                                        'selectDecade'=> gT('Select decade'),
                                        'prevDecade'=> gT('Previous decade'),
                                        'nextDecade'=> gT('Next decade'),
                                        'prevCentury'=> gT('Previous century'),
                                        'nextCentury'=> gT('Next century'),
                                    'selectTime'=> gT('Select time')
                                    ),

                                    'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                                )
                            ));
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="row ls-space margin top-5">
                <div class='form-group'>
                    <label class="col-sm-4 control-label" for='datestampL'><?php eT("Earlier than:");?></label>
                    <div class="col-sm-5 has-feedback">
                        <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                                'name' => "datestampL",
                                'id' => 'datestampL',
                                'value' => isset($_POST['datestampL']) ? $_POST['datestampL'] : '',
                                'pluginOptions' => array(
                                    'format' => $dateformatdetails['jsdate'] . " HH:mm",
                                    'allowInputToggle' =>true,
                                    'showClear' => true,
                                    'tooltips' => array(
                                        'clear'=> gT('Clear selection'),
                                        'prevMonth'=> gT('Previous month'),
                                        'nextMonth'=> gT('Next month'),
                                        'selectYear'=> gT('Select year'),
                                        'prevYear'=> gT('Previous year'),
                                        'nextYear'=> gT('Next year'),
                                        'selectDecade'=> gT('Select decade'),
                                        'prevDecade'=> gT('Previous decade'),
                                        'nextDecade'=> gT('Next decade'),
                                        'prevCentury'=> gT('Previous century'),
                                        'nextCentury'=> gT('Next century'),
                                    'selectTime'=> gT('Select time')
                                    ),
                                    'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                                )
                            ));
                        ?>
                    </div>
                </div>
            </div>
            
            <input type='hidden' name='summary[]' value='datestampE' />
            <input type='hidden' name='summary[]' value='datestampG' />
            <input type='hidden' name='summary[]' value='datestampL' />
        </div>
    </div>
    <?php endif; ?>
