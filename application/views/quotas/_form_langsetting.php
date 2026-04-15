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
        <?php
        $htmlOptions = array('class' => 'form-control custom-data');
        $attribute = '[' . $language . ']quotals_message';
        $action = $this->action->id;

        CHtml::resolveNameID($oQuotaLanguageSetting, $attribute, $htmlOptions);
        echo CHtml::activeTextArea($oQuotaLanguageSetting, $attribute, $htmlOptions);

        echo getEditor(
            'quota_message',
            $htmlOptions['id'],
            "[" . gT("Quota message:", "js") . "](" . $language . ")",
            $oQuota->sid,
            '',
            '',
            $action
        );
        ?>
    <?php echo $form->error($oQuotaLanguageSetting,'['.$language.']quotals_message'); ?>
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
