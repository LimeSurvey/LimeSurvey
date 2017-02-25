<?php
/* @var Quota $oQuota */
/* @var QuotaLanguageSetting $oQuotaLanguageSetting */
/* @var CActiveForm $form */
/* @var string $language */
?>

<?php echo $form->errorSummary($oQuotaLanguageSetting); ?>

<?php echo $form->hiddenField($oQuotaLanguageSetting,'['.$language.']quotals_name'); ?>

<div class="form-group">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'['.$language.']quotals_message',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php echo $form->textArea($oQuotaLanguageSetting,'['.$language.']quotals_message',array('class'=>'form-control')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'['.$language.']quotals_message'); ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'['.$language.']quotals_url',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php echo $form->textField($oQuotaLanguageSetting,'['.$language.']quotals_url',array('class'=>'form-control')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'['.$language.']quotals_url'); ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'['.$language.']quotals_urldescrip',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php echo $form->textField($oQuotaLanguageSetting,'['.$language.']quotals_urldescrip',array('class'=>'form-control')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'['.$language.']quotals_urldescrip'); ?>
    </div>
</div>