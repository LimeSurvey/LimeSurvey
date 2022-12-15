<?php

/** @var Survey $oSurvey */
/** @var Question $oQuestion */
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
<?php $visibilityEditor = true; //should be displayed ?>
<?php $visibilityOverview = false; ?>
<?php
    // Use the question's theme if it exists, or a dummy theme if it doesn't
    $questionTheme = !empty($oQuestion->questionTheme) ? $oQuestion->questionTheme : QuestionTheme::getDummyInstance($oQuestion->type);
?>

<!-- Create form for question -->
<div class="side-body">

    <?php if ($oQuestion->qid !== 0): ?>
        <?php
            if ($this->aData['tabOverviewEditor'] === 'overview') {
                $visibilityOverview = true; //should be displayed
                $visibilityEditor = false;
            } else {
                $visibilityOverview = false;
                $visibilityEditor = true;
            }
        ?>
    <?php endif; ?>

    <div class="container-fluid">
        <?php echo CHtml::form(
            ['questionAdministration/saveQuestionData'],
            'post',
            [
                'id' => 'edit-question-form',
                'data-summary-url' => $this->createUrl(
                    'questionAdministration/getSummaryHTML',
                    ['questionId' => $oQuestion->qid]
                ),
            ]
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
            <div id="advanced-question-editor" class="row"<?= empty($visibilityEditor) ? ' style="display:none;"' : '' ?>>
                <div class="col-lg-7">
                    <div class="container-center scoped-new-questioneditor">
                        <div class="pagetitle h3 scoped-unset-pointer-events">
                            <x-test id="action::addQuestion"></x-test>
                            <?php if ($oQuestion->qid === 0): ?>
                                <?= gT('Create question'); ?>
                            <?php else: ?>
                                <?= gT('Edit question'); ?>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <!-- Question code -->
                            <?php
                            $this->renderPartial(
                                "questionCode",
                                ['question' => $oQuestion]
                            ); ?>
                            <!-- Language selector -->
                            <?php $this->renderPartial(
                                "languageselector",
                                ['oSurvey' => $oSurvey]
                            ); ?>
                        </div>

                        <!-- Question type selector -->
                        <div class="row">
                            <?php
                            $this->renderPartial(
                                "typeSelector",
                                [
                                    'oSurvey'             => $oSurvey,
                                    'question'            => $oQuestion,
                                    'aQuestionTypeGroups' => $aQuestionTypeGroups,
                                    'questionTheme'       => $questionTheme,
                                    'selectormodeclass'   => $selectormodeclass,
                                ]
                            ); ?>
                        </div>

                        <div class="row">
                            <div class="col-xs-12">
                                <!-- Text elements -->
                                <?php $this->renderPartial(
                                    "textElements",
                                    [
                                        'oSurvey'         => $oSurvey,
                                        'question'        => $oQuestion,
                                        //'aStructureArray' => $aQuestionTypeGroups,
                                        'showScriptField' => $showScriptField,
                                    ]
                                ); ?>
                            </div>
                        </div>

                        <div class="row">
                            <?php $this->renderPartial(
                                "extraOptions",
                                [
                                    'question'        => $oQuestion,
                                    'survey'          => $oSurvey,
                                ]
                            ); ?>
                        </div>

                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="container-center">
                        <div class="pagetitle h3">
                            <?= gT('Settings'); ?>
                        </div>
                        <div class="row">
                            <div class="col-xs-12" id="accordion-container">
                                <div class="panel-group" id="accordion" role="tablist">
                                    <!-- General settings -->
                                    <?php $this->renderPartial("generalSettings", ['generalSettings'  => $generalSettings]); ?>

                                    <!-- Advanced settings -->
                                    <?php $this->renderPartial(
                                        "advancedSettings",
                                        [
                                            'oSurvey'          => $oSurvey,
                                            'advancedSettings' => $advancedSettings,
                                        ]
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php // Hidden field 'bFullPOST' is used to confirm the POST data is complete (it could be truncated if max_input_vars is exceeded) ?>
            <input type='hidden' id='bFullPOST' name='bFullPOST' value='1' />
        </form>
    </div>

    <!-- Show summary page if we're editing or viewing. -->
    <?php $this->renderPartial(
        "questionSummary",
        [
            'survey' => $oSurvey,
            'question' => $oQuestion,
            'questionTheme' => $questionTheme,
            'advancedSettings' => $advancedSettings,
            'visibilityOverview' => $visibilityOverview,
        ]
    ); ?>
</div>
