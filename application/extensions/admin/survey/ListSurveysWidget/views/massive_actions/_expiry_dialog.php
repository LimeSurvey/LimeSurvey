<?php
/** @var AdminController $this */
/** @var array $dateformatdata */
$dateformatdetails=getDateFormatData(Yii::app()->session['dateformat']);

?>
<div id='publication' class="tab-pane fade in">
    <!-- Expiry date/time -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='expires'><?php  eT("Expiry date/time:"); ?></label>
        <div class="col-sm-6 has-feedback">
            <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                'name' => "expires",
                'id' => 'expires',
                'value' => null,
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

