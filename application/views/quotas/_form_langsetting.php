<?php
/* @var Quota $oQuota */
/* @var QuotaLanguageSetting $oQuotaLanguageSetting */
/* @var CActiveForm $form */
/* @var string $language */
?>

<?php
$this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $oQuotaLanguageSetting]);
?>


<!-- Quota message -->
<div class="mb-3">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'['.$language.']quotals_message',array('class'=>'form-label')); ?>
    <div class=''>
        <?php echo $form->textArea($oQuotaLanguageSetting,'['.$language.']quotals_message',array('class'=>'form-control custom-data')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'['.$language.']quotals_message'); ?>
        <?php echo CHtml::textArea(
            'quota_message_en',
            '',
            array('class' => 'form-control', 'cols' => '80', 'rows' => '15', 'id' => "quota_message_en")
        ); ?>
        <?php echo getEditor(
            'quota_message',
            'quota_message_en',
            "[" . gT("End message:", "js") . "](en)",
            153519,
            '',
            '',
            'AddNewQuota'
        ); ?>
    </div>
</div>

<!-- URL -->
<div class="mb-3">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'['.$language.']quotals_url',array('class'=>'form-label')); ?>
    <div class=''>
        <?php echo $form->textField($oQuotaLanguageSetting,'['.$language.']quotals_url',array('class'=>'form-control custom-data')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'['.$language.']quotals_url'); ?>
    </div>
</div>

<!-- URL Description -->
<div class="mb-3">
    <?php echo $form->labelEx($oQuotaLanguageSetting,'['.$language.']quotals_urldescrip',array('class'=>'form-label')); ?>
    <div class=''>
        <?php echo $form->textField($oQuotaLanguageSetting,'['.$language.']quotals_urldescrip',array('class'=>'form-control custom-data')); ?>
        <?php echo $form->error($oQuotaLanguageSetting,'['.$language.']quotals_urldescrip'); ?>
    </div>
</div>
