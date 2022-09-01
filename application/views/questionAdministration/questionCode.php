<?php

/**@var Question $question */
?>
<div class="form-group col-sm-6 col-lg-5 scoped-responsive-fix-height">
    <label for="questionCode"><?= gT('Code'); ?></label>
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
    <p id="question-title-warning" class="hidden text-warning"></p>
</div>
