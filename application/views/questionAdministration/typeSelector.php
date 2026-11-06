<?php

/** @var Question $question */
/** @var QuestionTheme $questionTheme */

$generalSettingsUrl = $this->createUrl(
    'questionAdministration/getGeneralSettingsHTML',
    ['surveyId' => $question->sid, 'questionId' => $question->qid]
);
$advancedSettingsUrl = $this->createUrl(
    'questionAdministration/getAdvancedSettingsHTML',
    ['surveyId' => $question->sid, 'questionId' => $question->qid]
);
$extraOptionsUrl = $this->createUrl(
    'questionAdministration/getExtraOptionsHTML',
    ['surveyId' => $question->sid, 'questionId' => $question->qid]
);
$renderType = isset($selectormodeclass) && $selectormodeclass == "none" ? "group-simple" : "group-modal";
$viewType = $renderType == 'group-modal' ? 'grouped_select_modal' : 'simple_grouped_select';

$oQuestionSelector = $this->beginWidget(
    'ext.admin.PreviewModalWidget.PreviewModalWidget',
    [
        'widgetsJsName' => "questionTypeSelector",
        'renderType'    => $renderType,
        'modalTitle'    => gT("Select question type"),
        'groupTitleKey' => "questionGroupName",
        'groupItemsKey' => "questionTypes",
        'debugKeyCheck' => gT("Type:") . " ",
        'previewWindowTitle' => "",
        'groupStructureArray' => $aQuestionTypeGroups,
        'survey_active' => $question->survey->active == 'Y',
        'value' => $question->type,
        'theme' => $questionTheme->name,
        'debug' => YII_DEBUG,
        'buttonClasses' => ['btn-primary'],
        'currentSelected' => gT($questionTheme->title), //todo: use questiontheme instead ...
        'optionArray' => [
            'selectedClass' => $questionTheme->getDecodedSettings()->class, //Question::getQuestionClass($question->type),
            'onModalOpen' => [
                // Show title and preview of the current question theme, several themes can share the same question type
                // Expand its group, the accordion closes the other groups
                "var currentItem = $('.selector__Item--select-questionTypeSelector').filter(function () {
                    return $(this).data('item-value').itemArray.name === $('#question_theme_name').val();
                }).first();
                currentItem.trigger('click');
                var currentGroup = currentItem.closest('.accordion-collapse')[0];
                if (currentGroup) {
                    bootstrap.Collapse.getOrCreateInstance(currentGroup, {toggle: false}).show();
                }"
            ],
            'onUpdate' => [
                'value',
                'theme',
                'option',
                // NB: updateQuestionTypeButton and updateQuestionAttributes are defined in assets/scripts/admin/questionEditor.js"
                "$('#question_type').val(value);
                 $('#question_theme_name').val(theme);
                LS.questionEditor.updateQuestionTypeButton(value, option);
                LS.questionEditor.updateQuestionAttributes(value, theme, '$generalSettingsUrl', '$advancedSettingsUrl', '$extraOptionsUrl');"
            ]
        ]
    ]
);
?>
<?= $oQuestionSelector->getModal(); ?>

<div id="question-type-selector-wrapper" class="mb-3 contains-question-selector" data-viewtype="<?= $viewType ?>" data-debug="<?= YII_DEBUG ?>">
    <?php // The modal mode shows a button which is named by the selected type, only the select of the simple mode can be labelled ?>
    <label<?= $renderType === 'group-simple' ? ' for="questionTypeSelector"' : '' ?>><?= gT('Question type'); ?></label>
    <?= $oQuestionSelector->getButtonOrSelect(); ?>
    <?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>
    <input type="hidden" id="questionTypeVisual" name="questionTypeVisual" />
    <input type="hidden" id="question_type" name="question[type]" value="<?= $question->type; ?>" />
    <input type="hidden" id="question_theme_name" name="question[question_theme_name]" value="<?= $questionTheme->name; ?>" />
</div>