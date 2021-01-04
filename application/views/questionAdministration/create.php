<?php

/** @var Survey $oSurvey */
?>

<style>
/* TODO: Move where? */
.scoped-unset-pointer-events {
    pointer-events: none;
}
</style>

<!-- NB: These must be inside #pjax-content to work with pjax. -->
<?= $jsVariablesHtml; ?>
<?= $modalsHtml; ?>
<?php $visibilityEditor = ''; //should be displayed ?>

<!-- Create form for question -->
<div class="side-body">

    <!-- Question overview switch (no summary on create, so hide then) -->
    <?php if ($oQuestion->qid !== 0): ?>
        <div
            class="btn-group pull-right clear"
            role="group"
            data-toggle="buttons"
        >
            <?php
            if ($this->aData['tabOverviewEditor'] === 'overview') {
                $activeOverview = 'active';
                $activeEditor = '';
                $visibilityOverview = ''; //should be displayed
                $visibilityEditor = 'style="display:none;"';
            } else {
                $activeOverview = '';
                $activeEditor = 'active';
                $visibilityOverview = 'style="display:none;"';
                $visibilityEditor = '';
            }
            ?>
            <label class="btn btn-default <?= $activeOverview?>" onclick="LS.questionEditor.showOverview();">
                <input 
                    type="radio" 
                    name="question-overview-switch"
                    checked="checked"
                />
                <?= gt('Question overview'); ?>
            </label>
            <label id="questionEditorButton" class="btn btn-default <?= $activeEditor?>" onclick="LS.questionEditor.showEditor();">
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
            <input type="hidden" name="question[qid]" value="<?= $oQuestion->qid; ?>" />
            <input type="hidden" name="tabOverviewEditor" id='tab-overview-editor-input' value="<?=$this->aData['tabOverviewEditor']?>" />
            <?php /** this btn is trigger by save&close topbar button in copyQuestiontobar_view  */ ?>
            <input
                type='submit'
                style="display:none"
                class="btn navbar-btn button white btn-success"
                id = 'submit-create-question'
                name="savecreate"
            />
            <div id="advanced-question-editor" <?= $visibilityEditor?>>
                <div class="container-center scoped-new-questioneditor">
                    <div class="pagetitle h3 scoped-unset-pointer-events">
                        <x-test id="action::addQuestion"></x-test>
                        <?php if ($oQuestion->qid === 0): ?>
                            <?= gT('Create question'); ?>
                        <?php else: ?>
                            <?= gT('Edit question'); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Question code and question type selector -->
                    <div class="row">
                        <?php
                        $questionTheme = QuestionTheme::findQuestionMetaData($oQuestion->type, $questionTemplate);
                        $this->renderPartial(
                            "codeAndType",
                            [
                                'oSurvey'             => $oSurvey,
                                'question'            => $oQuestion,
                                'questionTypes'       => $aQuestionTypeStateList,
                                'aQuestionTypeGroups' => $aQuestionTypeGroups,
                                'questionThemeTitle'  => $questionTheme['title'],
                                'questionThemeName'   => $questionTheme['name'],
                                'questionThemeClass'  => ($questionTheme['settings'])->class
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
                                    'question'        => $oQuestion,
                                    'aStructureArray' => $aQuestionTypeGroups,
                                    'questionTypes'   => $aQuestionTypeStateList,
                                    'showScriptField' => $showScriptField,
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
                                'question'        => $oQuestion,
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
    <?php if ($oQuestion->qid !== 0): ?>
        <div class="container-fluid" id="question-overview" <?= $visibilityOverview?>>
            <form>
            <!-- Question summary -->
            <div class="container-center scoped-new-questioneditor">
                <div class="pagetitle h3" style="padding-top: 0; margin-top: 0;">
                    <?php eT('Question summary'); ?>&nbsp;
                    <small>
                        <em><?= $oQuestion->title; ?></em>&nbsp;
                        (ID: <?php echo (int) $oQuestion->qid;?>)
                    </small>
                </div>
                <div class="row">
                    <?php $this->renderPartial(
                        "summary",
                        [
                            'question'         => $oQuestion,
                            'questionTypes'     => $aQuestionTypeStateList,
                            'answersCount'      => count($oQuestion->answers),
                            'subquestionsCount' => count($oQuestion->subquestions),
                            'advancedSettings'  => $advancedSettings
                        ]
                    ); ?>
                </div>
            </div>
            </form>
        </div>
    <?php endif; ?>
</div>
