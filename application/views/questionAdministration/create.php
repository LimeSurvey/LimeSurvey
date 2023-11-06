<?php

/** @var Survey $oSurvey */
/** @var Question $oQuestion */
/** @var array $aQuestionTypeGroups */
/** @var array $advancedSettings */
/** @var array $generalSettings */
/** @var bool $showScriptField */
/** @var string $jsVariablesHtml */
/** @var string $modalsHtml */
/** @var string $selectormodeclass */

?>

<style>
    /* TODO: Move where? */
    .scoped-unset-pointer-events {
        pointer-events: none;
    }
</style>

<!-- NB: These must be inside #pjax-content to work with pjax. -->
<?php echo $jsVariablesHtml; ?>
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
            $visibilityEditor   = false;
        } else {
            $visibilityOverview = false;
            $visibilityEditor   = true;
        }
        ?>
    <?php endif; ?>

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

    <input type="hidden" name="sid" value="<?= $oSurvey->sid; ?>"/>
    <input type="hidden" name="question[qid]" value="<?= $oQuestion->qid; ?>"/>
    <input type="hidden" name="tabOverviewEditor" id='tab-overview-editor-input' value="<?= $this->aData['tabOverviewEditor'] ?>"/>
    <?php /** this btn is trigger by save&close topbar button in copyQuestiontobar_view  */ ?>
    <input
        type='submit'
        style="display:none"
        class="btn navbar-btn button white btn-primary"
        id='submit-create-question'
        name="savecreate"
    />
    <div id="advanced-question-editor" class="row"<?= empty($visibilityEditor) ? ' style="display:none;"' : '' ?>>
        <x-test id="action::addQuestion"></x-test>
        <div class="col-xl-8 pe-1">
            <div class="row">
                <div class="col-12">
                    <!-- Text elements -->
                    <?php
                    $this->renderPartial(
                        "textElements",
                        [
                            'oSurvey' => $oSurvey,
                            'question' => $oQuestion,
                            //'aStructureArray' => $aQuestionTypeGroups,
                            'showScriptField' => $showScriptField,
                        ]
                    ); ?>
                </div>
            </div>

            <div class="row">
                <?php
                $this->renderPartial(
                    "extraOptions",
                    [
                        'question' => $oQuestion,
                        'survey' => $oSurvey,
                    ]
                ); ?>
            </div>

        </div>
        <div class="col-xl-4 settings-accordion-container">
            <div class="accordion" id="accordion" role="tablist">
                <!-- General settings -->
                <?php
                $this->renderPartial(
                    "generalSettings",
                    [
                        'generalSettings' => $generalSettings,
                        'oSurvey' => $oSurvey,
                        'question' => $oQuestion,
                        'aQuestionTypeGroups' => $aQuestionTypeGroups,
                        'questionTheme' => $questionTheme,
                        'selectormodeclass' => $selectormodeclass,
                    ]
                );
                ?>

                <!-- Advanced settings -->
                <?php
                $this->renderPartial(
                    "advancedSettings",
                    [
                        'oSurvey' => $oSurvey,
                        'advancedSettings' => $advancedSettings,
                    ]
                ); ?>
            </div>
        </div>
    </div>
    <?php // Hidden field 'bFullPOST' is used to confirm the POST data is complete (it could be truncated if max_input_vars is exceeded) ?>
    <input type='hidden' id='bFullPOST' name='bFullPOST' value='1'/>
    <?php echo CHtml::endForm() ?>

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
