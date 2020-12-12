<?php

/**@var Question $question */
/**@var string $questionThemeTitle */
/**@var string $questionThemeClass */

$generalSettingsUrl = $this->createUrl(
    'questionAdministration/getGeneralSettingsHTML',
    ['surveyId' => $question->sid, 'questionId' => $question->qid]
);
$advancedSettingsUrl = $this->createUrl(
    'questionAdministration/getAdvancedSettingsHTML',
    ['surveyId' => $question->sid, 'questionId' => $question->qid]
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
        'value' => $question->type,
        'theme' => $questionThemeName,
        'debug' => YII_DEBUG,
        'currentSelected' => $questionThemeTitle, //todo: use questiontheme instead ...
        'optionArray' => [
            'selectedClass' => $questionThemeClass,//Question::getQuestionClass($question->type),
            'onUpdate' => [
                'value',
                'theme',
                // NB: updateQuestionAttributes is defined in assets/scripts/admin/questionEditor.js"
                "$('#question_type').val(value);
                 $('#question_template').val(theme); 
                LS.questionEditor.updateQuestionAttributes(value, theme, '$generalSettingsUrl', '$advancedSettingsUrl');"
            ]
        ]
    ]
);
?>
<?= $oQuestionSelector->getModal(); ?>

<div class="form-group col-sm-6 scoped-responsive-fix-height">
    <label for="questionCode"><?= gT('Code'); ?></label>
    <div class="scoped-keep-in-line">
        <!-- TODO: Max lenght. -->
        <!-- TODO: Read-only when survey is active. -->
        <?php if ($oSurvey->active !== 'Y'): ?>
          <input
              text="text"
              class="form-control"
              id="questionCode"
              name="question[title]"
              value="<?= $question->title; ?>"
              required="true"
              maxlength="20"
              onfocusout="LS.questionEditor.checkQuestionCodeUniqueness($(this).val(), <?= $question->qid; ?>)"
          />
        <?php else: ?>
          <span><?= $question->title; ?></span>
        <?php endif; ?>
        <!--
        <type-counter 
            :countable="currentQuestionCode.length"
            :max-value="this.maxQuestionCodeLength"
            :valid="inputValid"
        />
        -->
    </div>
    <p id="question-code-unique-warning" class="hidden text-warning"><?= gT('Question codes must be unique.'); ?></p>
</div>
<div class="form-group col-sm-6 contains-question-selector">
    <label for="questionCode"><?= gT('Question type'); ?></label>
    <div class="btn-group" style="width: 100%;">
        <?= $oQuestionSelector->getButtonOrSelect(); ?>
        <?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>
    </div>
    <input type="hidden" id="questionTypeVisual" name="questionTypeVisual" />
    <input type="hidden" id="question_type" name="question[type]" value="<?= $question->type; ?>" />
    <input type="hidden" id="question_template" name="question[template]" value="<?= 'core'; ?>" />
</div>
