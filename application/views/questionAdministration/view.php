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
                    <div>
                            <x-test id="action::addQuestion"></x-test>
                            <!-- {{ (initCopy ? 'Copy question' : 'Create question') | translate }} -->
                            Create question
                    </div>
                    <!-- TODO -->
                    <!-- {{'Question'|translate}}: {{$store.state.currentQuestion.title}}&nbsp;&nbsp;<small>(ID: {{$store.state.currentQuestion.qid}})</small>-->
                </div>
                <!-- TODO: Move to partial -->
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
                    <div class="col-12">
                        <div class="panel panel-default col-12 question-option-general-container">
                            <div class="panel-heading">Text elements</div>
                            <div class="panel-body">
                                <div class="col-12 ls-space margin all-5 scope-contains-ckeditor">
                                    <div class="ls-flex-row">
                                        <div class="ls-flex-item grow-2 text-left">
                                            <label class="col-sm-12"><?= gT('Question'); ?></label>
                                        </div>
                                    </div>
                                    <div class="htmleditor input-group">
                                        <?= CHtml::textArea(
                                            "question_{$oSurvey->language}",
                                            $oQuestion->questionl10ns[$oSurvey->language]->question,
                                            array('class'=>'form-control','cols'=>'60','rows'=>'8','id'=>"question_{$oSurvey->language}")
                                        ); ?>
                                        <?= getEditor(
                                            "question-text",
                                            "question_".$oSurvey->language,
                                            "[".gT("Question:","js")."](".$oSurvey->language.")",
                                            $oSurvey->sid,
                                            $oQuestion->gid,
                                            $oQuestion->sid,
                                            $action = '');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-12 ls-space margin all-5 scope-contains-ckeditor">
                                    <div class="ls-flex-row">
                                        <div class="ls-flex-item grow-2 text-left">
                                            <label class="col-sm-12"><?= gT('Help:'); ?></label>
                                        </div>
                                    </div>
                                    <div class="htmleditor input-group">
                                        <?= CHtml::textArea(
                                            "help_".$oSurvey->language,
                                            $oQuestion->questionl10ns[$oSurvey->language]->help,
                                            array('class'=>'form-control','cols'=>'60','rows'=>'4','id'=>"help_{$oSurvey->language}")
                                        ); ?>
                                        <?= getEditor(
                                            "question-help",
                                            "help_".$oSurvey->language,
                                            "[".gT("Help:", "js")."](".$oSurvey->language.")",
                                            $oSurvey->sid,
                                            $oQuestion->gid,
                                            $oQuestion->qid,
                                            $action = ''
                                        ); ?>
                                    </div>
                                </div>
                                <div style="height: 300px;">
                                    <label class="col-sm-6">
                                        <?= gT('Script'); ?>
                                    </label>
                                    <div class="col-sm-6 text-right">
                                        <input 
                                            type="checkbox" 
                                            name="selector--scriptForAllLanguages" 
                                            id="selector--scriptForAllLanguages"
                                            v-model="scriptForAllLanugages"
                                        />&nbsp;
                                        <label for="selector--scriptForAllLanguages">
                                            <?= gT('Set for all languages'); ?>
                                        </label>
                                    </div>

                                    <?= CHtml::textArea(
                                        'editscript',
                                        !empty($editfile) ? file_get_contents($editfile) : '',
                                        array(
                                            'id' => 'editscript',
                                            'rows' => '10',
                                            'cols' => '20',
                                            'data-filetype' => 'javascript',
                                            'class' => 'ace default', // . $sTemplateEditorMode,
                                        )
                                    ); ?>
                                        <p class="alert well">
                                            <?= gt("This optional script field will be wrapped, so that the script is correctly executed after the question is on the screen. If you do not have the correct permissions, this will be ignored"); ?>
                                        </p>
                                </div>
                            </div>
                        </div>
                        <div class="row" key="divideRow">
                            <div class="col-sm-12 ls-space margin top-5 bottom-5">
                                <hr />
                            </div>
                        </div>
                    </div> 

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
                                    <!-- Advanced settings tabs -->
                                    <ul class="nav nav-tabs scoped-tablist-advanced-settings" role="tablist" v-if="!hideAdvancedOptions">
                                        <?php foreach ($advancedSettings as $category => $settings): ?>
                                            <li 
                                                v-for="advancedSettingCategory in tabs"
                                                :key="'tablist-'+advancedSettingCategory"
                                                :class="$store.state.questionAdvancedSettingsCategory == advancedSettingCategory && currentTabComponent == 'settings-tab' ? 'active' : ''"
                                            >
                                                <a href="#" @click.prevent.stop="selectCurrentTab('settings-tab', advancedSettingCategory)" >
                                                    <?= $category; ?>
                                                </a>
                                                <?php foreach ($settings as $setting): ?>
                                                    <?php $this->widget('ext.AdvancedSettingWidget.AdvancedSettingWidget', ['setting' => $setting]); ?>
                                                <?php endforeach; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
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
