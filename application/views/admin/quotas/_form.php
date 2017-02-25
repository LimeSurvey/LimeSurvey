<?php
/* @var $this AdminController */
/* @var string $lang */
/* @var Quota $oQuota */
/* @var CActiveForm $form */
/* @var array $aTabContents*/
?>
<?php $form = $this->beginWidget('CActiveForm', array(
    'id'=>'editquota',
    //'action'=>array("admin/quotas/sa/modifyquota/surveyid/{$oSurvey->primaryKey}"),
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'afterValidate'=>'js:yiiFix.ajaxSubmit.afterValidate'
    ),
)); ?>
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
        <?php echo $form->error($oQuota,'qlmit'); ?>
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

<!-- Language tabs -->
<ul class="nav nav-tabs">
    <?php foreach ($oQuota->survey->getAllLanguages() as $lang): ?>
        <li role="presentation" <?php echo ($lang==$oQuota->survey->language ? 'class="active"': null);?>>
            <a data-toggle="tab" href="#edittxtele<?php echo $lang ?>">
                <?php echo getLanguageNameFromCode($lang,false); ?>
                <?php echo ($lang==$oQuota->survey->language ? '('.gT("Base language").')':null);?>
            </a>
        </li>
    <?php endforeach?>
</ul>
<div class='tab-content'>
    <?php foreach ($oQuota->survey->getAllLanguages() as $language)
    {
        echo CHtml::tag(
            'div',
            array(
                'id' => 'edittxtele' . $language,
                'class' => 'tab-pane fade in' . ($language == $oQuota->survey->language ? ' active ' : ''),
            ),$this->renderPartial('/admin/quotas/_form_langsetting',
            array(
                'form'=>$form,
                'oQuota'=>$oQuota,
                'oQuotaLanguageSetting' =>$oQuota->getLanguagesetting($language),
                'language' =>$language,
            ),true)
        );
    }?>
</div>
<input type="submit" name="submit" class="hidden" />

<?php $this->endWidget(); ?>
