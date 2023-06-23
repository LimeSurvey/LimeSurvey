<?php
/* @var $this AdminController */
/* @var string $lang */
/* @var Quota $oQuota */
/* @var CActiveForm $form */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>

<?php $form = $this->beginWidget('CActiveForm', array('id'=>'edit-quota-ls',)); ?>
<?php
$this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $aQuotaLanguageSettings]);
?>
<?php $this->renderPartial('_form_langsettings',
    array(
        'form'=>$form,
        'oQuota'=>$oQuota,
        'aQuotaLanguageSettings' =>$aQuotaLanguageSettings,
    ));?>
&nbsp;
<?php $this->endWidget(); ?>
