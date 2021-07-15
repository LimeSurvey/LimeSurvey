<?php

/**@var Question $question */
?>
<div class="form-group col-sm-6 scoped-responsive-fix-height">
    <label for="questionCode"><?= gT('Question code'); ?></label>
    <i class="fa fa-question-circle text-success"
        data-toggle="tooltip"
        title="<?= gT("The question code is used for quick identification of this question and must be unique. It is especially useful if you wish to use the LimeSurvey assessments feature and/or the ExpressionScript."); ?>"
        ></i>
    <div class="scoped-keep-in-line">
        <input
            text="text"
            class="form-control"
            id="questionCode"
            name="question[title]"
            value="<?= !empty($newTitle) ? $newTitle : $question->title; ?>"
            required="true"
            maxlength="20"
            onfocusout="LS.questionEditor.checkQuestionCodeUniqueness($(this).val(), <?= $question->qid; ?>)"
        />

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