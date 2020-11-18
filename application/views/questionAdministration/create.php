<?php

/** @var Survey $oSurvey */

$this->renderPartial(
    'topbars/' . $this->aData['renderSpecificTopbar'],
    [
        'closeBtnUrl'=> $this->createUrl(
            'surveyAdministration/view/',
            ['surveyid' => $oSurvey->sid]
        ),
        'surveyId' => $oSurvey->sid,
        'question' => $question,
    ]
);

?>

<style>
/* TODO: Move where? */
.scoped-unset-pointer-events {
    pointer-events: none;
}
</style>

<!-- Create form for question -->
<div class="side-body">

    <!-- Question overview switch (no summary on create, so hide then) -->
    <?php if ($question->qid !== 0): ?>
        <div
            class="btn-group pull-right clear"
            role="group"
            data-toggle="buttons"
        >
            <label class="btn btn-default active" onclick="LS.questionEditor.showOverview();">
                <input 
                    type="radio" 
                    name="question-overview-switch"
                    checked="checked"
                />
                <?= gt('Question overview'); ?>
            </label>
            <label class="btn btn-default" onclick="LS.questionEditor.showEditor();">
                <input
                    type="radio"
                    name="question-overview-switch"
                />
                <?= gT('Question editor'); ?>
            </label>
        </div>
    <?php endif; ?>

    <div class="container-fluid">
        <?php echo CHtml::form(
            ['questionAdministration/saveQuestionData'],
            'post',
            ['id' => 'edit-question-form']
        ); ?>

            <input type="hidden" name="sid" value="<?= $oSurvey->sid; ?>" />
            <input type="hidden" name="question[qid]" value="<?= $question->qid; ?>" />
            <?php /** this btn is trigger by save&close topbar button in copyQuestiontobar_view  */ ?>
            <input
                type='submit'
                style="display:none"
                class="btn navbar-btn button white btn-success"
                id = 'submit-create-question'
                name="savecreate"
            />
            <div id="advanced-question-editor">
                <div class="container-center scoped-new-questioneditor">
                    <div class="pagetitle h3 scoped-unset-pointer-events">
                        <x-test id="action::addQuestion"></x-test>
                        <?php if ($question->qid === 0): ?>
                            <?= gT('Create question'); ?>
                        <?php else: ?>
                            <?= gT('Edit question'); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Question code and question type selector -->
                    <div class="row">
                        <?php $this->renderPartial(
                            "codeAndType",
                            [
                                'oSurvey'             => $oSurvey,
                                'question'            => $question,
                                'aStructureArray'     => $aQuestionTypeGroups,
                                'questionTypes'       => $aQuestionTypeStateList,
                                'aQuestionTypeGroups' => $aQuestionTypeGroups
                            ]
                        ); ?>
                    </div>

                    <!-- Language selector -->
                    <div class="row">
                        <?php $this->renderPartial(
                            "languageselector",
                            ['oSurvey' => $oSurvey]
                        ); ?>
                    </div>

                    <div class="row">
                        <div class="col-lg-7">
                            <!-- Text elements -->
                            <?php $this->renderPartial(
                                "textElements",
                                [
                                    'oSurvey'         => $oSurvey,
                                    'question'        => $question,
                                    'aStructureArray' => $aQuestionTypeGroups,
                                    'questionTypes'   => $aQuestionTypeStateList,
                                ]
                            ); ?>
                        </div>

                        <!-- General settings -->
                        <div class="col-lg-5">
                            <div class="ls-flex scope-set-min-height scoped-general-settings">
                                <?php $this->renderPartial("generalSettings", ['generalSettings'  => $generalSettings]); ?>
                            </div>
                        </div>
                    </div>

                    <div class="ls-flex ls-flex-row scoped-advanced-settings-block">
                        <?php $this->renderPartial(
                            "advancedSettings",
                            [
                                'question'        => $question,
                                'oSurvey'          => $oSurvey,
                                'advancedSettings' => $advancedSettings,
                            ]
                        ); ?>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- Show summary page if we're editing or viewing. -->
    <?php if ($question->qid !== 0): ?>
        <div class="container-fluid" id="question-overview">
            <form>
            <!-- Question summary -->
            <div class="container-center scoped-new-questioneditor">
                <div class="pagetitle h3" style="padding-top: 0; margin-top: 0;">
                    <?php eT('Question summary'); ?>&nbsp;
                    <small>
                        <em><?= $question->title; ?></em>&nbsp;
                        (ID: <?php echo (int) $question->qid;?>)
                    </small>
                </div>
                <div class="row">
                    <?php $this->renderPartial(
                        "summary",
                        [
                            'question'         => $question,
                            'questionTypes'     => $aQuestionTypeStateList,
                            'answersCount'      => count($question->answers),
                            'subquestionsCount' => count($question->subquestions),
                            'advancedSettings'  => $advancedSettings
                        ]
                    ); ?>
                </div>
            </div>
            </form>
        </div>
    <?php endif; ?>
</div>
