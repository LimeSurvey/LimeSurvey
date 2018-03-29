<?php
/* @var Quota $oQuota */
/* @var QuotaLanguageSetting $oQuotaLanguageSetting */
/* @var CActiveForm $form */
/* @var string $language */
?>

<?php echo $form->errorSummary($oQuotaLanguageSetting); ?>


<!-- Quota message -->
<div class="form-group">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'['.$language.']quotals_message',array('class'=>'control-label')); ?>
    <div class=''>
        <?php echo $form->textArea($oQuotaLanguageSetting,'['.$language.']quotals_message',array('class'=>'form-control custom-data')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'['.$language.']quotals_message'); ?>
    </div>
</div>

<!-- URL -->
<div class="form-group">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'['.$language.']quotals_url',array('class'=>'control-label')); ?>
    <div class=''>
        <?php echo $form->textField($oQuotaLanguageSetting,'['.$language.']quotals_url',array('class'=>'form-control custom-data')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'['.$language.']quotals_url'); ?>
    </div>
</div>

<!-- URL Description -->
<div class="form-group">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'['.$language.']quotals_urldescrip',array('class'=>'control-label')); ?>
    <div class=''>
        <?php echo $form->textField($oQuotaLanguageSetting,'['.$language.']quotals_urldescrip',array('class'=>'form-control custom-data')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'['.$language.']quotals_urldescrip'); ?>
    </div>
</div>
