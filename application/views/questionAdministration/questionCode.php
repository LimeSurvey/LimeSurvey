<?php

/**@var Question $question */
/** @var bool $generalSettingsView */
$generalSettingsView = $generalSettingsView ?? false;
$class = !$generalSettingsView ? 'mb-3 col-md-6 col-xl-5 scoped-responsive-fix-height' : '';
?>
<div class="<?= $class ?>">
    <label for="questionCode"><?= gT('Code'); ?></label>
    <i class="ri-information-fill"
        data-bs-toggle="tooltip"
        title="<?= gT("The question code is used for quick identification of this question and must be unique. It is especially useful if you wish to use the LimeSurvey assessments feature and/or the ExpressionScript."); ?>"
        ></i>
    <?php
    $this->widget('ext.InputWidget.InputWidget', [
        'name' => 'question[title]',
        'id' => 'questionCode',
        'value' => !empty($newTitle) ? $newTitle : $question->title,
        'isAttached' => true,
        'attachContent' => $this->renderPartial('partials/questionCodeButton', null, true),
        'wrapperHtmlOptions' => [
            'class' => 'scoped-keep-in-line',
        ],
    ]);
    ?>
    <p id="question-title-warning" class="d-none text-warning"></p>
</div>

