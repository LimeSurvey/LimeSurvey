<?php

/** @var Survey $oSurvey */
/** @var Question $oQuestion */
/** @var string $questionTemplate */
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

    <?php if ($oQuestion->qid !== 0): ?>
        <?php
            if ($this->aData['tabOverviewEditor'] === 'overview') {
                $visibilityOverview = ''; //should be displayed
                $visibilityEditor = 'style="display:none;"';
            } else {
                $visibilityOverview = 'style="display:none;"';
                $visibilityEditor = '';
            }
        ?>
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
                                'aQuestionTypeGroups' => $aQuestionTypeGroups,
                                'questionThemeTitle'  => $questionTheme['title'],
                                'questionThemeName'   => $questionTheme['name'],
                                'questionThemeClass'  => ($questionTheme['settings'])->class,
                                'selectormodeclass'   => $selectormodeclass,
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
                <div class="pagetitle h3">
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
                            'questionTheme'    => $questionTheme,
                            'answersCount'      => count($oQuestion->answers),
                            'subquestionsCount' => count($oQuestion->subquestions),
                            'advancedSettings'  => $advancedSettings
                        ]
                    ); ?>
                </div>
                <?php if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'update')): ?>
                    <div id="survey-action-title" class="pagetitle h3"><?php eT('Question quick actions'); ?></div>
                    <div class="row welcome survey-action">
                        <div class="col-lg-12 content-right">

                            <!-- create question in this group -->
                            <div class="col-lg-3">
                                <div class="panel panel-primary <?php if ($oSurvey->isActive) { echo 'disabled'; } else { echo 'panel-clickable'; } ?>" id="panel-1" data-url="<?php echo $this->createUrl('questionAdministration/create/surveyid/'.$oSurvey->sid.'/gid/'.$oQuestion->gid); ?>">
                                    <div class="panel-heading">
                                        <div class="panel-title h4"><?php eT("Add new question to group");?></div>
                                    </div>
                                    <div class="panel-body">
                                        <span class="icon-add text-success"  style="font-size: 3em;"></span>
                                        <p class='btn-link'>
                                                <?php eT("Add new question to group");?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            </form>
        </div>
    <?php endif; ?>
</div>
