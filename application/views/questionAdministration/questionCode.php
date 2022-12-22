<?php

/**@var Question $question */
?>
<div class="mb-3 col-md-8  scoped-responsive-fix-height">
    <label for="questionCode"><?= gT('Code'); ?></label>
    <i class="ri-question-fill text-success" data-bs-toggle="tooltip" title="<?= gT("The question code is used for quick identification of this question and must be unique. It is especially useful if you wish to use the LimeSurvey assessments feature and/or the ExpressionScript."); ?>"></i>
    <?php

    $this->widget('ext.InputWidget.InputWidget', [
        'name' => 'question[title]',
        'id' => 'questionCode',
        'value' => !empty($newTitle) ? $newTitle : $question->title,
        'isAttached' => true,
        'attachContent' => '<button class="btn btn-primary position-absolute" style="position: absolute; top:3px; right:5px">Save Template</button>',
        'wrapperHtmlOptions' => [
            'class' => 'scoped-keep-in-line',
        ],
    ]);
    ?>
    <p id="question-title-warning" class="d-none text-warning"></p>
</div>

