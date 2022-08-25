<?php
$colClass = 'col-md-4 col-sm-12';
if (!isset($datestamp) || $datestamp == "N"){
    $colClass = 'col-md-6 col-sm-12';
}
?>
<h4 class="h4"><?php
    eT("Filter"); ?></h4>
<div class="row">
    <div class="<?= $colClass ?>">
        <div class='form-group'>
            <label class="control-label" for='idG'><?php
                eT("Response ID greater than:"); ?></label>
            <div class=''>
                <input class="form-control" type='number' id='idG' name='idG' size='10' value='<?php
                if (isset($_POST['idG'])) {
                    echo sanitize_int($_POST['idG']);
                } ?>' onkeypress="returnwindow.LS.goodchars(event,'0123456789')"/>
            </div>
        </div>
    </div>
    <div class="<?= $colClass ?>">
        <div class='form-group'>
            <label class="control-label" for='idL'><?php
                eT("Response ID less than:"); ?></label>
            <div class=''>
                <input class="form-control" type='number' id='idL' name='idL' size='10' value='<?php
                if (isset($_POST['idL'])) {
                    echo sanitize_int($_POST['idL']);
                } ?>' onkeypress="returnwindow.LS.goodchars(event,'0123456789')"/>
            </div>
        </div>
    </div>
</div>
<?php
if (isset($datestamp) && $datestamp == "Y"): ?>
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class='form-group'>
                <label class="control-label" for='datestampE'><?php
                    eT("Submission date equals:"); ?></label>
                <div class="has-feedback">
                    <?php
                    Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                        'name' => "datestampE",
                        'id' => 'datestampE',
                        'value' => isset($_POST['datestampE']) ? $_POST['datestampE'] : '',
                        'pluginOptions' => array(
                            'format' => ($dateformatdetails['jsdate']),
                            'allowInputToggle' => true,
                            'showClear' => true,
                            'tooltips' => array(
                                'clear' => gT('Clear selection'),
                                'prevMonth' => gT('Previous month'),
                                'nextMonth' => gT('Next month'),
                                'selectYear' => gT('Select year'),
                                'prevYear' => gT('Previous year'),
                                'nextYear' => gT('Next year'),
                                'selectDecade' => gT('Select decade'),
                                'prevDecade' => gT('Previous decade'),
                                'nextDecade' => gT('Next decade'),
                                'prevCentury' => gT('Previous century'),
                                'nextCentury' => gT('Next century'),
                                'selectTime' => gT('Select time')
                            ),
                            'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                        )
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class='form-group'>
                <label class="control-label" for='datestampG'><?php
                    eT("Submission date later than:"); ?></label>
                <div class="has-feedback">
                    <?php
                    Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                        'name' => "datestampG",
                        'id' => 'datestampG',
                        'value' => isset($_POST['datestampG']) ? $_POST['datestampG'] : '',
                        'pluginOptions' => array(
                            'format' => $dateformatdetails['jsdate'] . " HH:mm",
                            'allowInputToggle' => true,
                            'showClear' => true,
                            'tooltips' => array(
                                'clear' => gT('Clear selection'),
                                'prevMonth' => gT('Previous month'),
                                'nextMonth' => gT('Next month'),
                                'selectYear' => gT('Select year'),
                                'prevYear' => gT('Previous year'),
                                'nextYear' => gT('Next year'),
                                'selectDecade' => gT('Select decade'),
                                'prevDecade' => gT('Previous decade'),
                                'nextDecade' => gT('Next decade'),
                                'prevCentury' => gT('Previous century'),
                                'nextCentury' => gT('Next century'),
                                'selectTime' => gT('Select time')
                            ),

                            'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                        )
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class='form-group top-5'>
                <label class="control-label" for='datestampL'><?php
                    eT("Submission date earlier than:"); ?></label>
                <div class="has-feedback">
                    <?php
                    Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                        'name' => "datestampL",
                        'id' => 'datestampL',
                        'value' => isset($_POST['datestampL']) ? $_POST['datestampL'] : '',
                        'pluginOptions' => array(
                            'format' => $dateformatdetails['jsdate'] . " HH:mm",
                            'allowInputToggle' => true,
                            'showClear' => true,
                            'tooltips' => array(
                                'clear' => gT('Clear selection'),
                                'prevMonth' => gT('Previous month'),
                                'nextMonth' => gT('Next month'),
                                'selectYear' => gT('Select year'),
                                'prevYear' => gT('Previous year'),
                                'nextYear' => gT('Next year'),
                                'selectDecade' => gT('Select decade'),
                                'prevDecade' => gT('Previous decade'),
                                'nextDecade' => gT('Next decade'),
                                'prevCentury' => gT('Previous century'),
                                'nextCentury' => gT('Next century'),
                                'selectTime' => gT('Select time')
                            ),
                            'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                        )
                    ));
                    ?>
                </div>
            </div>
        </div>
        <input type='hidden' name='summary[]' value='datestampE'/>
        <input type='hidden' name='summary[]' value='datestampG'/>
        <input type='hidden' name='summary[]' value='datestampL'/>
    </div>
<?php
endif; ?>
