<?php
/* @var $this AdminController */
/* @var string $lang */
/* @var Quota $oQuota */
/* @var CActiveForm $form */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>
<?php $form = $this->beginWidget('CActiveForm', array('id'=>'editquota',)); ?>
<?php echo $form->errorSummary($oQuota); ?>

<?php echo $form->hiddenField($oQuota,'id'); ?>
<div class="form-group">
    <?php echo $form->labelEx($oQuota,'name',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php echo $form->textField($oQuota,'name',array('class'=>'form-control')); ?>
        <?php echo $form->error($oQuota,'name'); ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($oQuota,'qlimit',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php echo $form->textField($oQuota,'qlimit',array('class'=>'form-control')); ?>
        <?php echo $form->error($oQuota,'qlimit'); ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($oQuota,'action',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php echo $form->dropDownList($oQuota,'action',
            array(
                1 =>gT("Terminate survey"),
                2 =>gT("Terminate survey with warning"),
            ),
            array('class'=>'form-control')); ?>
        <?php echo $form->error($oQuota,'action'); ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($oQuota,'autoload_url',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
            'model' => $oQuota,
            'attribute' => 'autoload_url',
            'onLabel'=>gT('Yes'),
            'offLabel' => gT('No')));
        ?>
        <?php echo $form->error($oQuota,'autoload_url'); ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($oQuota,'active',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
            'model' => $oQuota,
            'attribute' => 'active',
            'onLabel'=>gT('Yes'),
            'offLabel' => gT('No')));
        ?>
        <?php echo $form->error($oQuota,'active'); ?>
    </div>
</div>
<?php $this->renderPartial('/admin/quotas/_form_langsettings',
    array(
        'form'=>$form,
        'oQuota'=>$oQuota,
        'aQuotaLanguageSettings' =>$aQuotaLanguageSettings,
    ));?>
<input type="submit" name="submit" class="hidden" />

<?php $this->endWidget(); ?>
