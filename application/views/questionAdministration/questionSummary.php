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
                <span class="h1"><?php
                    eT('Question summary'); ?>&nbsp;</span>
                <small>
                    <em>
                        <?= $question->title; ?> (ID: <?= (int)$question->qid; ?>)
                    </em>&nbsp;
                </small>
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
