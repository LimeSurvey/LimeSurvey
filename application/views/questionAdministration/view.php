<?php
/**
 * @var $jsData            array
 * @var $aQuestionTypeStateList array
 * TODO: move logic from the view to controller
 */

Yii::app()->loadHelper("admin/htmleditor");
PrepareEditorScript(true, $this);

Yii::app()->getClientScript()->registerPackage('jquery-ace'); 
Yii::app()->getClientScript()->registerScript('editorfiletype', "editorfiletype ='javascript';", CClientScript::POS_HEAD);

?>
<?php $this->renderPartial(
    "_jsVariables",
    [
        'data' => $jsData,
        'aStructureArray' => $aQuestionTypeGroups,
        'aQuestionTypes' => $aQuestionTypeStateList
    ]
); ?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <div class="container-fluid">
        <?php echo CHtml::form(
            array("admin/questionedit/update"),
            'post',
            array(
                'class' => 'form30 ',
                'id' => 'frmeditquestion',
                'name' => 'frmeditquestion',
                //'data-isvuecomponent' => 1
            )
        ); ?>
        <input type="submit" class="hidden" name="triggerSubmitQuestionEditor" id="triggerSubmitQuestionEditor"/>

        <div id="advancedQuestionEditor">
            <div class="container-center scoped-new-questioneditor">
                <div class="btn-group pull-right clear">
                    <button id="questionOverviewButton" class="btn btn-default"><?= gT('Question overview'); ?></button>
                    <button id="questionEditorButton" class="btn btn-default" ><?= gT('Question editor'); ?></button>
                </div>
                <div class="pagetitle h3 scoped-unset-pointer-events">
                    <!-- TODO: If create or edit or copy -->
                    <x-test id="action::addQuestion"></x-test>
                    <!-- {{ (initCopy ? 'Copy question' : 'Create question') | translate }} -->
                    <?= gT('Create question'); ?>
                    <!-- TODO -->
                    <!-- {{'Question'|translate}}: {{$store.state.currentQuestion.title}}&nbsp;&nbsp;<small>(ID: {{$store.state.currentQuestion.qid}})</small>-->
                </div>

                <!-- Question code and question type selector -->
                <div class="row">
                    <?php $this->renderPartial(
                        "codeAndType",
                        [
                            'data'                => $jsData,
                            'oQuestion'           => $oQuestion,
                            'oSurvey'             => $oSurvey,
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
                        [
                            'data' => $jsData,
                            'oQuestion'              => $oQuestion,
                            'oSurvey'                => $oSurvey,
                            'aStructureArray' => $aQuestionTypeGroups,
                            'questionTypes' => $aQuestionTypeStateList,
                            'answersCount'           => $answersCount,
                            'subquestionsCount'      => $subquestionsCount,
                            'advancedSettings'       => $advancedSettings
                        ]
                    ); ?>
                </div>

                <div class="col-lg-12">
                    <!-- Main editor -->
                    <?php $this->renderPartial(
                        "textElements",
                        [
                            'data' => $jsData,
                            'oQuestion'              => $oQuestion,
                            'oSurvey'                => $oSurvey,
                            'aStructureArray' => $aQuestionTypeGroups,
                            'questionTypes' => $aQuestionTypeStateList,
                            'answersCount'           => $answersCount,
                            'subquestionsCount'      => $subquestionsCount,
                            'advancedSettings'       => $advancedSettings
                        ]
                    ); ?>

                    <!-- Question summary, TODO: Put in partial view -->
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
                                'data' => $jsData,
                                'oQuestion'              => $oQuestion,
                                'oSurvey'                => $oSurvey,
                                'aStructureArray' => $aQuestionTypeGroups,
                                'questionTypes' => $aQuestionTypeStateList,
                                'answersCount'           => $answersCount,
                                'subquestionsCount'      => $subquestionsCount,
                                'advancedSettings'       => $advancedSettings
                            ]
                        ); ?>
                    </div>

                    <!-- General settings -->
                    <div class="ls-flex scope-set-min-height scoped-general-settings">
                        <div class="panel panel-default question-option-general-container col-12" id="uncollapsed-general-settings" v-if="!loading && !collapsedMenu">
                            <div class="panel-heading"> 
                                <?= gT('General Settings'); ?>
                                <button class="pull-right btn btn-default btn-xs" @click="collapsedMenu=true">
                                    <i class="fa fa-chevron-right" /></i>
                                </button>
                            </div>
                            <div class="panel-body">
                                <div class="list-group">
                                    <?php foreach ($generalSettings as $generalOption): ?>
                                        <?php $this->widget('ext.GeneralOptionWidget.GeneralOptionWidget', ['generalOption' => $generalOption]); ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ls-flex ls-flex-row scoped-advanced-settings-block">
                        <div class="col-12 scope-apply-base-style scope-min-height">
                            <div class="container-fluid" v-if="!loading && showAdvancedOptions" id="advanced-options-container">
                                <div class="row scoped-tablist-container">
                                    <!--
                                    <template v-if="showSubquestionEdit || showAnswerOptionEdit">
                                        <ul class="nav nav-tabs scoped-tablist-subquestionandanswers" role="tablist">
                                            <li 
                                                v-if="showSubquestionEdit"
                                                :class="currentTabComponent == 'subquestions' ? 'active' : ''"
                                            >
                                                <a href="#" @click.prevent.stop="selectCurrentTab('subquestions')" >{{"subquestions" | translate }}</a>
                                            </li>
                                            <li 
                                                v-if="showAnswerOptionEdit"
                                                :class="currentTabComponent == 'answeroptions' ? 'active' : ''"
                                            >
                                                <a href="#" @click.prevent.stop="selectCurrentTab('answeroptions')" >{{"answeroptions" | translate }}</a>
                                            </li>
                                        </ul>
                                    </template>
                                    -->
                                    <!-- Advanced settings tabs -->
                                    <ul class="nav nav-tabs scoped-tablist-advanced-settings" role="tablist">
                                        <?php foreach ($advancedSettings as $category => $_) : ?>
                                            <li role="presentation">
                                                <a
                                                    href="#<?= $category; ?>"
                                                    aria-controls="<?= $category; ?>"
                                                    role="tab"
                                                    data-toggle="tab"
                                                    >
                                                    <?= $category; ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="tab-content">
                                    <?php foreach ($advancedSettings as $category => $settings): ?>
                                        <div role="tabpanel" class="tab-pane" id="<?= $category; ?>">
                                            <?php foreach ($settings as $setting): ?>
                                                <?php $this->widget('ext.AdvancedSettingWidget.AdvancedSettingWidget', ['setting' => $setting]); ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>

<script>
jQuery(document).on('ready', function () {
    $('.ace:not(.none)').ace({
        'mode' : 'javascript'
    });
});
</script>
