<?php
/* @var string $lang */
/* @var Quota $oQuota */
/* @var CActiveForm $form */
/* @var QuotaLanguageSetting[] $aQuotaLanguageSettings */
?>
<?php $form = $this->beginWidget('CActiveForm', ['id' => 'editquota',]); ?>
<?php $this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $oQuota]); ?>

<div class="row">
    <div class="col-xl-4">
        <?php echo $form->hiddenField($oQuota, 'id'); ?>
        <div class="mb-3">
            <?php echo $form->labelEx($oQuota, 'name', ['class' => 'form-label ']); ?>
            <div class=''>
                <?php echo $form->textField($oQuota, 'name', ['class' => 'form-control']); ?>
                <?php echo $form->error($oQuota, 'name'); ?>
            </div>
        </div>

        <div class="mb-3">
            <?php echo $form->labelEx($oQuota, 'qlimit', ['class' => 'form-label ']); ?>
            <div class=''>
                <?php echo $form->textField($oQuota, 'qlimit', ['class' => 'form-control']); ?>
                <?php echo $form->error($oQuota, 'qlimit'); ?>
            </div>
        </div>

        <div class="mb-3">
            <?php echo $form->labelEx($oQuota, 'action', ['class' => 'form-label ']); ?>
            <div class=''>
                <?php echo $form->dropDownList($oQuota, 'action',
                    [
                        Quota::TERMINATE_AFTER_ALL_VISIBLE_QUOTA_QUESTION      => gT("Terminate after related visible question was submitted"),
                        Quota::TERMINATE_AFTER_ALL_QUOTA_QUESTIONS             => gT("Terminate after related visible and hidden questions were submitted"),
                        Quota::TERMINATE_AFTER_ALL_GROUPS                      => gT("Terminate after all page submissions"),
                        Quota::SOFT_TERMINATE_AFTER_ALL_VISIBLE_QUOTA_QUESTION => gT("Soft terminate after related visible question was submitted, answer will be editable"),
                    ],
                    ['class' => 'form-select']); ?>
                <?php echo $form->error($oQuota, 'action'); ?>
            </div>
        </div>

        <div class="mb-3 col-md-6">
            <?php echo $form->labelEx($oQuota, 'active', ['class' => 'form-label ']); ?>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'model'         => $oQuota,
                    'attribute'     => 'active',
                    'checkedOption' => $oQuota->active,
                    'selectOptions' => [
                        '1' => gT('Yes'),
                        '0' => gT('No'),
                    ],
                ]); ?>
                <?php echo $form->error($oQuota, 'active'); ?>
            </div>
        </div>

        <div class="mb-3 col-md-6">
            <?php echo $form->labelEx($oQuota, 'autoload_url', ['class' => 'form-label ']); ?>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'model'         => $oQuota,
                    'attribute'     => 'autoload_url',
                    'checkedOption' => $oQuota->autoload_url,
                    'selectOptions' => [
                        '1' => gT('Yes'),
                        '0' => gT('No'),
                    ],
                ]); ?>
                <?php echo $form->error($oQuota, 'autoload_url'); ?>
            </div>
        </div>

    </div>
    <div class="col-xl-8">
        <?php $this->renderPartial('_form_langsettings',
            [
                'form'                   => $form,
                'oQuota'                 => $oQuota,
                'aQuotaLanguageSettings' => $aQuotaLanguageSettings,
            ]); ?>
        <input type="submit" name="submit" class="d-none"/>
    </div>
</div>
<?php $this->endWidget(); ?>
