<?php
/** @var AdminController $this */
$dateFormatDetails=getDateFormatData(Yii::app()->session['dateformat']);

?>
<?php $form = $this->beginWidget('CActiveForm', array('id'=>'survey-expiry',)); ?>

<div id='publication' class="container-center">
    <div class="row">
        <!-- Expiry date/time -->
        <div class="form-group">
            <label class="col-sm-6 control-label" for='expires'><?php  eT("Expiry date/time:"); ?></label>
            <div class="col-sm-6 has-feedback">
                <?php Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                    'name' => 'expires',
                    'id' => 'expires',
                    'value' => null,
                    'htmlOptions' => array('class' => 'form-control custom-data'),
                    'pluginOptions' => array(
                        'format' => $dateFormatDetails['jsdate'] . " HH:mm",
                        'allowInputToggle' =>true,
                        'showClear' => true,
                        'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                    )
                ));
                ?>
            </div>
        </div>
    </div>
</div>
<?php $this->endWidget(); ?>
