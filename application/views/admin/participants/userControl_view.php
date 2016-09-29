<script src="<?php echo Yii::app()->getConfig('adminscripts') . "userControl.js" ?>" type="text/javascript"></script>

<div class="col-lg-12 list-surveys">
    <h3><?php eT("Global participant settings"); ?></h3>

    <?php echo CHtml::beginForm($this->createUrl(
        '/admin/participants/sa/storeUserControlValues'),
        'post',
        array(
            'class' => 'form form-horizontal col-md-6 col-md-offset-1'
        )
    ); ?>

        <div class="form-group">
            <label class='control-label col-sm-8'>
                <?php eT('User ID editable:'); ?>
            </label>
            <div class='col-sm-3'>
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'userideditable',
                    'onLabel' => gT('Yes'),
                    'offLabel' => gT('No'),
                    'value' => $userideditable == 'Y' ? '1' : 0
                )); ?>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-8'></div>
            <div class='col-sm-3'>
                <?php echo CHtml::submitButton('submit', array('value' => gT('Save'), 'class'=>'btn btn-default')); ?>
            </div>
        </div>
    <?php echo CHtml::endForm(); ?>

</div>

