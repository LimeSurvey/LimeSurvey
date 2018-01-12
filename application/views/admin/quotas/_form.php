<?php
/* @var $this AdminController */
/* @var string $lang */
/* @var Quota $oQuota */
/* @var CActiveForm $form */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>
<div class="container-center">
    <?php $form = $this->beginWidget('CActiveForm', array('id'=>'editquota',)); ?>
    <?php echo $form->errorSummary($oQuota); ?>
        
        <div class="row">
            <div class="col-lg-4">
                <?php echo $form->hiddenField($oQuota,'id'); ?>
                <div class="form-group">
                    <?php echo $form->labelEx($oQuota,'name',array('class'=>'control-label ')); ?>
                    <div class=''>
                        <?php echo $form->textField($oQuota,'name',array('class'=>'form-control')); ?>
                        <?php echo $form->error($oQuota,'name'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($oQuota,'qlimit',array('class'=>'control-label ')); ?>
                    <div class=''>
                        <?php echo $form->textField($oQuota,'qlimit',array('class'=>'form-control')); ?>
                        <?php echo $form->error($oQuota,'qlimit'); ?>
                    </div>
                </div>

                <div class="form-group">
                    <?php echo $form->labelEx($oQuota,'action',array('class'=>'control-label ')); ?>
                    <div class=''>
                        <?php echo $form->dropDownList($oQuota,'action',
                            array(
                                Quota::ACTION_TERMINATE =>gT("Terminate survey"),
                                Quota::ACTION_CONFIRM_TERMINATE =>gT("Allow user to modify his last answers before terminate survey."),
                            ),
                            array('class'=>'form-control')); ?>
                        <?php echo $form->error($oQuota,'action'); ?>
                    </div>
                </div>

                <div class="form-group col-sm-6">
                    <?php echo $form->labelEx($oQuota,'active',array('class'=>'control-label ')); ?>
                    <div class=''>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'model' => $oQuota,
                            'attribute' => 'active',
                            'onLabel'=>gT('Yes'),
                            'offLabel' => gT('No')));
                        ?>
                        <?php echo $form->error($oQuota,'active'); ?>
                    </div>
                </div>
                
                <div class="form-group col-sm-6">
                    <?php echo $form->labelEx($oQuota,'autoload_url',array('class'=>'control-label ')); ?>
                    <div class=''>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'model' => $oQuota,
                            'attribute' => 'autoload_url',
                            'onLabel'=>gT('Yes'),
                            'offLabel' => gT('No')));
                        ?>
                        <?php echo $form->error($oQuota,'autoload_url'); ?>
                    </div>
                </div>

            </div>
            <div class="col-lg-8">
                <?php $this->renderPartial('/admin/quotas/_form_langsettings',
                    array(
                        'form'=>$form,
                        'oQuota'=>$oQuota,
                        'aQuotaLanguageSettings' =>$aQuotaLanguageSettings,
                    ));?>
                <input type="submit" name="submit" class="hidden" />
            </div>
        </div>
    <?php $this->endWidget(); ?>
</div>
