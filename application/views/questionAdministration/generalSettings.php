<?php
/** @var Survey $oSurvey */
/** @var Question $question */
/** @var array $generalSettings */
/** @var array $aQuestionTypeGroups */
/** @var QuestionTheme $questionTheme */
/** @var string $selectormodeclass */

?>
<div class="accordion-item question-option-general-container" id="general-settings">
    <h2 class="accordion-header" id="general-setting-heading">
        <button
            id="button-collapse-General"
            class="accordion-button selector--questionEdit-collapse"
            type="button"
            role="button"
            data-bs-toggle="collapse"
            data-bs-parent="#accordion"
            href="#collapse-question"
            aria-expanded="true"
            aria-controls="collapse-question"
        >
            <?= gT('General settings'); ?>
        </button>
    </h2>
    <div
        id="collapse-question"
        class="accordion-collapse collapse show"
        role="tabpanel"
        data-bs-parent="#accordion"
        aria-labelledby="general-setting-heading"
    >
        <div class="accordion-body collapse show">
        <!-- Question code -->
            <div class="mb-3">
                <?php $this->renderPartial(
                    "questionCode",
                    [ 'question' => $question, 'generalSettingsView' => true ]
                ); ?>
            </div>
            <!-- Question type selector -->
            <div class="mb-3">
                <?php
                $this->renderPartial(
                    "typeSelector",
                    [
                        'oSurvey' => $oSurvey,
                        'question' => $question,
                        'aQuestionTypeGroups' => $aQuestionTypeGroups,
                        'questionTheme' => $questionTheme,
                        'selectormodeclass' => $selectormodeclass,
                    ]
                ); ?>
            </div>
            <!-- General settings -->
            <?php foreach ($generalSettings as $generalOption) : ?>
                <?php $this->widget(
                    'ext.GeneralOptionWidget.GeneralOptionWidget',
                    ['generalOption' => $generalOption]
                ); ?>
                <?php if ($generalOption->name === 'gid' && empty($question->qid)) : ?>
                    <!-- Position of the new question inside the group, reloaded by questionEditor.js when the group changes -->
                    <div
                        id="question-position-container"
                        data-url="<?= CHtml::encode($this->createUrl('questionAdministration/ajaxLoadPositionWidget')) ?>"
                    >
                        <?php $this->widget(
                            'ext.admin.survey.question.PositionWidget.PositionWidget',
                            [
                                'display' => 'general_option',
                                'oQuestionGroup' => $question->group,
                                'oSurvey' => $oSurvey,
                            ]
                        ); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
