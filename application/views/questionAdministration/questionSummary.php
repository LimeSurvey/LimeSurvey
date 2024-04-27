<?php

/** @var Survey $survey */

/** @var Question $question */
/** @var QuestionTheme $questionTheme */
/** @var boolean $visibilityOverview */
/** @var array<string,array<mixed>> $advancedSettings */
?>

<div id="question-overview"<?= empty($visibilityOverview) ? ' style="display:none;"' : '' ?>>
    <?php
    if ($question->qid !== 0): ?>
        <?php $this->widget('ext.admin.survey.PageTitle.PageTitle', array(
            'title' => sprintf(gT("Question “%s” summary (ID %s)"), "<em>" . CHtml::encode($question->title) . "</em>", intval($question->qid)),
            'model' => $survey,
        )); ?>
        <?php
            $this->renderPartial(
                "summary",
                [
                    'question' => $question,
                    'questionTheme' => $questionTheme,
                    'answersCount' => count($question->answers),
                    'subquestionsCount' => count($question->subquestions),
                    'advancedSettings' => $advancedSettings
                ]
            ); ?>
    <?php endif; ?>
</div>
