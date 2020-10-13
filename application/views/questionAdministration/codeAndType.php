<?php
$generalSettingsUrl = $this->createUrl(
    'questionAdministration/getGeneralSettingsHTML',
    ['surveyId' => $oQuestion->sid, 'questionId' => $oQuestion->qid]
);
$advancedSettingsUrl = $this->createUrl(
    'questionAdministration/getAdvancedSettingsHTML',
    ['surveyId' => $oQuestion->sid, 'questionId' => $oQuestion->qid]
);
$oQuestionSelector = $this->beginWidget(
    'ext.admin.PreviewModalWidget.PreviewModalWidget',
    [
        'widgetsJsName' => "questionTypeSelector",
        'renderType'    => isset($selectormodeclass) && $selectormodeclass == "none" ? "group-simple" : "group-modal",
        'modalTitle'    => gT("Select question type"),
        'groupTitleKey' => "questionGroupName",
        'groupItemsKey' => "questionTypes",
        'debugKeyCheck' => gT("Type:") . " ",
        'previewWindowTitle' => gT("Preview question type"),
        'groupStructureArray' => $aQuestionTypeGroups,
        'value' => $oQuestion->type,
        'debug' => YII_DEBUG,
        'currentSelected' => Question::getQuestionTypeName($oQuestion->type),
        'optionArray' => [
            'selectedClass' => Question::getQuestionClass($oQuestion->type),
            'onUpdate' => [
                'value',
                // NB: updateQuestionAttributes is defined in assets/scripts/admin/questionEditor.js"
                "$('#question_type').val(value); updateQuestionAttributes(value, '$generalSettingsUrl', '$advancedSettingsUrl'); updateQuestionTemplateOptions(value);"
            ]
        ]
    ]
);
?>
<?= $oQuestionSelector->getModal(); ?>

<div class="form-group col-sm-6 scoped-responsive-fix-height">
    <label for="questionCode"><?= gT('Code'); ?></label>
    <div class="scoped-keep-in-line">
        <input
            text="text"
            class="form-control"
            id="questionCode"
            name="question[title]"
            :maxlength="this.maxQuestionCodeLength"
            :required="true"
            :readonly="!(editQuestion || isCreateQuestion || initCopy)"
        />
        <!--
        <type-counter 
            :countable="currentQuestionCode.length"
            :max-value="this.maxQuestionCodeLength"
            :valid="inputValid"
        />
        -->
    </div>
    <p class="well bg-warning scoped-highten-z" v-if="noCodeWarning!=null">{{noCodeWarning}}</p>
</div>
<div class="form-group col-sm-6 contains-question-selector">
    <label for="questionCode"><?= gT('Question type'); ?></label>
    <div class="btn-group" style="width: 100%;">
        <?= $oQuestionSelector->getButtonOrSelect(); ?>
        <?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>
    </div>
    <input type="hidden" id="questionTypeVisual" name="questionTypeVisual" />
    <input type="hidden" id="question_type" name="question[type]" value="<?= $oQuestion->type; ?>" />
</div>
