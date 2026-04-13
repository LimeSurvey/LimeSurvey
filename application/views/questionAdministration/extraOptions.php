<div class="col-12" id="extra-options-container">
    <?php $tabCount = 0; ?>
    <!-- Subquestions and Answers tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <?php if ($question->questionType->subquestions > 0): ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= (++$tabCount == 1) ? "active" : "" ?>"
                    href="#subquestions"
                    aria-controls="subquestions"
                    role="tab"
                    data-bs-toggle="tab"
                >
                    <?= gT('Subquestions'); ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($question->questionType->answerscales > 0): ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= (++$tabCount == 1) ? "active" : "" ?>"
                    href="#answeroptions"
                    aria-controls="answeroptions"
                    role="tab"
                    data-bs-toggle="tab"
                >
                    <?= gT('Answer options'); ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <?php $tabCount = 0; ?>
    <div class="tab-content bg-white pt-0 ps-2 pe-2">
        <?php if ($question->questionType->subquestions > 0): ?>
            <div role="tabpanel" class="tab-pane <?= (++$tabCount == 1) ? 'show active' : '' ?>" id="subquestions">
                <!-- TODO: Add path in controller. -->
                <?php Yii::app()->twigRenderer->getLoader()->addPath(__DIR__, '__main__'); ?>
                <?= Yii::app()->twigRenderer->renderViewFromFile(
                    '/application/views/questionAdministration/subquestions.twig',
                    [
                        'activated'    => $survey->active !== 'N',
                        // NB: questionType->subquestions === subquestion scale count
                        'scalecount'   => $question->questionType->subquestions,
                        'subquestions' => $question->getScaledSubquestions(),
                        'question'     => $question,
                        'allLanguages' => $survey->allLanguages,
                        'language'     => $survey->language,
                        'hasLabelSetPermission' => Permission::model()->hasGlobalPermission('labelsets','create'),
                    ],
                    true
                ); ?>
            </div>
        <?php endif; ?>
        <?php if ($question->questionType->answerscales > 0): ?>
            <div role="tabpanel" class="tab-pane<?php echo (++$tabCount == 1 ? ' active' : ''); ?>" id="answeroptions">
                <!-- TODO: Add path in controller. -->
                <?php Yii::app()->twigRenderer->getLoader()->addPath(__DIR__, '__main__'); ?>
                <?= Yii::app()->twigRenderer->renderViewFromFile(
                    '/application/views/questionAdministration/answerOptions.twig',
                    [
                        'activated'  => $survey->active !== 'N',
                        'oldCode'    => true,
                        'scalecount' => $question->questionType->answerscales,
                        'assessmentvisible' => $survey->assessments === 'Y', //todo: check also 'I' if inherit...
                        'answers'    => $question->getScaledAnswerOptions(),
                        'question'     => $question,
                        'allLanguages' => $survey->allLanguages,
                        'language'   => $survey->language,
                        'hasLabelSetPermission' => Permission::model()->hasGlobalPermission('labelsets','create'),
                    ],
                    true
                ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
