<?php
/* @var Quota $oQuota */
/* @var QuotaLanguageSetting $oQuotaLanguageSetting */
/* @var CActiveForm $form */
?>
<div class="form-group">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'quotals_name',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php echo $form->textField($oQuotaLanguageSetting,'quotals_name',array('class'=>'form-control')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'quotals_name'); ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'quotals_message',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php echo $form->textArea($oQuotaLanguageSetting,'quotals_message',array('class'=>'form-control')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'quotals_message'); ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'quotals_url',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php echo $form->textField($oQuotaLanguageSetting,'quotals_url',array('class'=>'form-control')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'quotals_url'); ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'quotals_urldescrip',array('class'=>'control-label col-sm-3')); ?>
    <div class='col-sm-9'>
        <?php echo $form->textField($oQuotaLanguageSetting,'quotals_urldescrip',array('class'=>'form-control')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'quotals_urldescrip'); ?>
    </div>
</div>