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
            <!-- Question summary -->
            <div class="pagetitle">
                <h1 aria-describedby="question-summary-meta"><?php eT('Question summary'); ?></h1>
                <small id="question-summary-meta" class="pagetitle__description"><em><?= $question->title; ?> (ID: <?= (int)$question->qid; ?>)</em></small>
            </div>
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
