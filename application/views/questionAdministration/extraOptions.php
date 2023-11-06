<div class="col-12" id="extra-options-container">
    <div class="container-fluid">
        <div class="row">
            <?php $tabCount = 0; ?>
            <!-- Subquestions and Answers tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <?php if ($question->questionType->subquestions > 0): ?>
                    <li role="presentation" <?php echo (++$tabCount == 1 ? 'class="active"' : ''); ?>>
                        <a
                            href="#subquestions"
                            aria-controls="subquestions"
                            role="tab"
                            data-toggle="tab"
                        >
                            <?= gT('Subquestions'); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($question->questionType->answerscales > 0): ?>
                    <li role="presentation" <?php echo (++$tabCount == 1 ? 'class="active"' : ''); ?>>
                        <a
                            href="#answeroptions"
                            aria-controls="answeroptions"
                            role="tab"
                            data-toggle="tab"
                        >
                            <?= gT('Answer options'); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <?php $tabCount = 0; ?>
            <div class="tab-content">
                <?php if ($question->questionType->subquestions > 0): ?>
                    <div role="tabpanel" class="tab-pane<?php echo (++$tabCount == 1 ? ' active' : ''); ?>" id="subquestions">
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
    </div>
</div>
