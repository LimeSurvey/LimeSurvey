<div class="col-12 scope-apply-base-style scope-min-height">
    <div class="container-fluid" id="advanced-options-container">
        <div class="row scoped-tablist-container">
            <!-- Advanced settings tabs -->
            <ul class="nav nav-tabs scoped-tablist-advanced-settings" role="tablist">
                <?php if ($question->questionType->subquestions > 0): ?>
                    <li role="presentation">
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
                <li role="presentation">
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
                <?php foreach ($advancedSettings as $category => $_) : ?>
                    <?php if ($category === 'Display'): ?>
                        <li role="presentation" class="active">
                    <?php else: ?>
                        <li role="presentation">
                    <?php endif; ?>
                        <a
                            href="#<?= CHtml::getIdByName($category); ?>"
                            aria-controls="<?= CHtml::getIdByName($category); ?>"
                            role="tab"
                            data-toggle="tab"
                            >
                            <?= gT($category); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content">
                <?php if ($question->questionType->subquestions > 0): ?>
                    <div role="tabpanel" class="tab-pane" id="subquestions">
                        <!-- TODO: Add path in controller. -->
                        <?php Yii::app()->twigRenderer->getLoader()->addPath(__DIR__, '__main__'); ?>
                        <?= Yii::app()->twigRenderer->renderViewFromFile(
                            '/application/views/questionAdministration/subquestions.twig',
                            [
                                'activated'    => $oSurvey->active !== 'N',
                                // NB: questionType->subquestions === subquestion scale count
                                'scalecount'   => $question->questionType->subquestions,
                                'subquestions' => $question->getScaledSubquestions(),
                                'question'     => $question,
                                'allLanguages' => $oSurvey->allLanguages,
                                'language'     => $oSurvey->language,
                                'hasLabelSetPermission' => Permission::model()->hasGlobalPermission('labelsets','create'),
                            ],
                            true
                        ); ?>
                    </div>
                <?php endif; ?>
                <?php if ($question->questionType->answerscales > 0): ?>
                    <div role="tabpanel" class="tab-pane" id="answeroptions">
                        <!-- TODO: Add path in controller. -->
                        <?php Yii::app()->twigRenderer->getLoader()->addPath(__DIR__, '__main__'); ?>
                        <?= Yii::app()->twigRenderer->renderViewFromFile(
                            '/application/views/questionAdministration/answerOptions.twig',
                            [
                                'activated'  => $oSurvey->active !== 'N',
                                'oldCode'    => true,
                                'scalecount' => $question->questionType->answerscales,
                                'assessmentvisible' => $oSurvey->assessments === 'Y', //todo: check also 'I' if inherit...
                                'answers'    => $question->getScaledAnswerOptions(),
                                'question'     => $question,
                                'allLanguages' => $oSurvey->allLanguages,
                                'language'   => $oSurvey->language,
                                'hasLabelSetPermission' => Permission::model()->hasGlobalPermission('labelsets','create'),
                            ],
                            true
                        ); ?>
                    </div>
                <?php endif; ?>
                <?php foreach ($advancedSettings as $category => $settings): ?>
                    <?php if ($category === 'Display'): ?>
                        <div role="tabpanel" class="tab-pane active" id="<?= CHtml::getIdByName($category); ?>">
                    <?php else: ?>
                        <div role="tabpanel" class="tab-pane" id="<?= CHtml::getIdByName($category); ?>">
                    <?php endif; ?>
                        <?php foreach ($settings as $setting): ?>
                            <?php $this->widget(
                                'ext.AdvancedSettingWidget.AdvancedSettingWidget',
                                ['setting' => $setting, 'survey' => $oSurvey]
                            ); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
