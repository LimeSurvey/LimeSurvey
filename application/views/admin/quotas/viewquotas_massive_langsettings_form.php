<?php
/* @var $this AdminController */
/* @var string $lang */
/* @var Quota $oQuota */
/* @var CActiveForm $form */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>

<?php $form = $this->beginWidget('CActiveForm', array('id'=>'edit-quota-ls',)); ?>

<?php //echo $form->errorSummary($oQuota); ?>

<input type="submit" name="submit" class="hidden" />

<?php $this->endWidget(); ?>
