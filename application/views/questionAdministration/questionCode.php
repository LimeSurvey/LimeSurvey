<?php

/**@var Question $question */
/** @var bool $generalSettingsView */
$generalSettingsView = $generalSettingsView ?? false;
$class = !$generalSettingsView ? 'mb-4 col-12 scoped-responsive-fix-height' : '';
?>
<div class="<?= $class ?>">
    <label for="questionCode"><?= gT('Code'); ?></label>
    <i class="ri-information-fill"
        data-bs-toggle="tooltip"
        title="<?= gT("The question code is used for quick identification of this question and must be unique. It is especially useful if you wish to use the LimeSurvey assessments feature and/or the ExpressionScript."); ?>"
        ></i>
    <div class="scoped-keep-in-line">
        <input
                text="text"
                class="form-control ls-important-field"
                id="questionCode"
                name="question[title]"
                value="<?= !empty($newTitle) ? $newTitle : $question->title; ?>"
                required="true"
                maxlength="20"
                pattern="<?= empty($question->title) ?  "^[a-zA-Z][a-zA-Z0-9]*$" : "^([a-zA-Z][a-zA-Z0-9]*|" . $question->title .")$"; // Old survey with bad question code still allowed ?>"
                data-qid="<?= (empty($question->qid) || !empty($newQid)) ? 0 : $question->qid; ?>"
        />

        <!--
        <type-counter
            :countable="currentQuestionCode.length"
            :max-value="this.maxQuestionCodeLength"
            :valid="inputValid"
        />
        -->
    </div>
    <p id="question-title-warning" class="d-none text-danger"></p>
</div>

