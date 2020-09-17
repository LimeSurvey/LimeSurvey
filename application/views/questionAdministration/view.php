<?php
/**
 * @var $aQuestionTypeList array
 * @var $jsData            array
 * @var $aQuestionTypeStateList array
 * TODO: move logic from the view to controller
 */

Yii::app()->loadHelper("admin/htmleditor");
PrepareEditorScript(true, $this);

$aQuestionTypeGroups = array();

if (App()->session['questionselectormode'] !== 'default') {
    $selectormodeclass = App()->session['questionselectormode'];
} else {
    $selectormodeclass = App()->getConfig('defaultquestionselectormode');
}
uasort($aQuestionTypeList, "questionTitleSort");
foreach ($aQuestionTypeList as $questionType) {
    $htmlReadyGroup = str_replace(' ', '_', strtolower($questionType['group']));
    if (!isset($aQuestionTypeGroups[$htmlReadyGroup])) {
        $aQuestionTypeGroups[$htmlReadyGroup] = array(
            'questionGroupName' => $questionType['group']
        );
    }
        $imageName = $questionType['question_type'];
    if ($imageName == ":") {
        $imageName = "COLON";
    } elseif ($imageName == "|") {
        $imageName = "PIPE";
    } elseif ($imageName == "*") {
        $imageName = "EQUATION";
    }
        $questionType['type'] = $questionType['question_type'];
    $questionType['detailpage'] = '
        <div class="col-sm-12 currentImageContainer">
            <img src="' . $questionType['image_path'] . '" />
        </div>';
    if ($imageName == 'S') {
        $questionType['detailpage'] = '
            <div class="col-sm-12 currentImageContainer">
                <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '.png" />
                <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '2.png" />
            </div>';
    }
        $aQuestionTypeGroups[$htmlReadyGroup]['questionTypes'][] = $questionType;
}
?>
<?php
    $oQuestionSelector = $this->beginWidget('ext.admin.PreviewModalWidget.PreviewModalWidget', array(
        'widgetsJsName' => "questionTypeSelector",
        'renderType' =>  (isset($selectormodeclass) && $selectormodeclass == "none") ? "group-simple" : "group-modal",
        'modalTitle' => "Select question type",
        'groupTitleKey' => "questionGroupName",
        'groupItemsKey' => "questionTypes",
        'debugKeyCheck' => "Type: ",
        'previewWindowTitle' => gT("Preview question type"),
        'groupStructureArray' => $aQuestionTypeGroups,
        'value' => $oQuestion->type,
        'debug' => YII_DEBUG,
        'currentSelected' => Question::getQuestionTypeName($oQuestion->type),
        'optionArray' => [
            'selectedClass' => Question::getQuestionClass($oQuestion->type),
            'onUpdate' => [
                'value',
                "console.ls.log(value); $('#question_type').val(value); updatequestionattributes(''); updateQuestionTemplateOptions();"
            ]
        ]
    ));
?>
<?=$oQuestionSelector->getModal();?>
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
                <div class="btn-group pull-right clear" v-if="allowSwitchEditing && !loading">
                        <button
                            id="questionOverviewButton"
                            key="questionOverviewButton"
                            @click.prevent="triggerEditQuestion(false)"
                            :class="editQuestion ? 'btn-default' : 'btn-primary'"
                            class="btn ">
                            Question overview
                        </button>
                        <button
                            id="questionEditorButton"
                            key="questionEditorButton"
                            @click.prevent="triggerEditQuestion(true)"
                            :class="editQuestion ? 'btn-primary' : 'btn-default'"
                            class="btn "
                        >
                            Question editor
                        </button>
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
                    <div class="row" key="questioncopy-block" v-if="initCopy">
                        <div class="form-group col-lg-3 col-sm-6">
                            <label class="ls-space margin right-5" for="copySubquestions">Copy subquestions</label>
                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => 'copySubquestions',
                                'id'=>'copySubquestions',
                                'value' => Yii::app()->getConfig('copySubquestions'),
                                'onLabel'=>gT('On'),
                                'offLabel' => gT('Off')));
                            ?>
                        </div>
                        <div class="form-group col-lg-3 col-sm-6">
                            <label class="ls-space margin right-5" for="copyAnswerOptions">Copy answer options</label>
                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => 'copyAnswerOptions',
                                'id'=>'copyAnswerOptions',
                                'value' => Yii::app()->getConfig('copyAnswerOptions'),
                                'onLabel'=>gT('On'),
                                'offLabel' => gT('Off')));
                            ?>
                        </div>
                        <div class="form-group col-lg-3 col-sm-6">
                            <label class="ls-space margin right-5" for="copyDefaultAnswers">Copy default answers</label>
                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => 'copyDefaultAnswers',
                                'id'=>'copyDefaultAnswers',
                                'value' => Yii::app()->getConfig('copyDefaultAnswers'),
                                'onLabel'=>gT('On'),
                                'offLabel' => gT('Off')));
                            ?>
                        </div>
                        <div class="form-group col-lg-3 col-sm-6">
                            <label class="ls-space margin right-5" for="copyAdvancedOptions">Copy advanced options</label>
                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => 'copyAdvancedOptions',
                                'id'=>'copyAdvancedOptions',
                                'value' => Yii::app()->getConfig('copyAdvancedOptions'),
                                'onLabel'=>gT('On'),
                                'offLabel' => gT('Off')));
                            ?>
                        </div>
                    </div>
                    <div class="row" key="questioncode-block">
                        <div class="form-group col-sm-6 scoped-responsive-fix-height">
                            <label for="questionCode">Code</label>
                            <div class="scoped-keep-in-line">
                                <input
                                    text="text"
                                    class="form-control"
                                    id="questionCode"
                                    :maxlength="this.maxQuestionCodeLength"
                                    :required="true"
                                    :readonly="!(editQuestion || isCreateQuestion || initCopy)"
                                    v-model="currentQuestionCode" 
                                    @dblclick="triggerEditQuestion" 
                                />
                                <type-counter 
                                    :countable="currentQuestionCode.length"
                                    :max-value="this.maxQuestionCodeLength"
                                    :valid="inputValid"
                                />
                            </div>
                            <p class="well bg-warning scoped-highten-z" v-if="noCodeWarning!=null">{{noCodeWarning}}</p>
                        </div>
                        <div class="form-group col-sm-6 contains-question-selector">
                            <label for="questionCode">Question type</label>
                            <div v-if="$store.getters.surveyObject.active !=='Y'"
                                 v-show="(editQuestion || isCreateQuestion)"
                                 class="btn-group">
                                <?=$oQuestionSelector->getButtonOrSelect();?>
                                <?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>
                            </div>
                            <input
                                v-show="!((editQuestion || isCreateQuestion) && $store.getters.surveyObject.active !=='Y')"
                                type="text"
                                class="form-control" id="questionTypeVisual"
                                :readonly="true"
                                :value="$store.state.currentQuestion.typeInformation.description+' ('+$store.state.currentQuestion.type+')'"
                            />
                            <input
                                v-if="$store.getters.surveyObject.active !=='Y'"
                                type="hidden"
                                id="question_type"
                                name="type"
                                :value="$store.state.currentQuestion.type"
                            />
                        </div>
                    </div>
                    <div class="row" key="languageselector-block" v-if="this.containsMultipleLanguages">
                        <languageselector
                            :elId="'question-language-changer'"
                            :aLanguages="$store.state.languages"
                            :parentCurrentLanguage="$store.state.activeLanguage"
                            @change="selectLanguage"
                        />
                        <div class="col-xs-12" >
                            <div class="button-toolbar" :id="elId+'-language-selector'">
                                <div
                                    class="btn-group"
                                >
                                    <?php foreach($this->aData['languagelist'] as $lang): ?>
                                        <button
                                            :key="language+'-button'"
                                            class="btn btn-default"
                                            @click.prevent="setCurrentLanguage(language)"
                                        >
                                            <!-- TODO: Mark active class="'btn btn-'+(language==currentLanguage ? 'primary active' : 'default')"-->
                                            <?= getLanguageNameFromCode($lang, false); ?>
                                        </button>
                                    <?php endforeach; ?>
                                    <!-- TODO: Chunk languages
                                    <button
                                        v-if="getInChunks.length > 1"
                                        class="btn btn-default dropdown-toggle"
                                        data-toggle="dropdown"
                                    >
                                        More Languages
                                        <span class="caret"></span>
                                    </button>
                                     <ul class="dropdown-menu">
                                        <li
                                            v-for="(languageTerm, language) in getInChunks[1]"
                                            :key="language+'-dropdown'"
                                            @click.prevent="setCurrentLanguage(language)"
                                        >
                                            <a href="#">{{ languageTerm }}</a>
                                        </li>
                                    </ul>
                                    -->
                                </div>
                            </div>
                            <hr/>
                        </div>
                    </div>
                    <div key="editorcontent-block" class="col-12">
                        <div class="ls-flex ls-flex-row scope-create-gutter">

                            <!-- Main editor -->
                            <div class="ls-flex grow-2">
                                <div class="col-12">
                                    <div
                                        class="panel panel-default col-12 question-option-general-container"
                                        key="mainPanel"
                                    >
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
                                            <div
                                                class="col-12 ls-space margin all-5 scope-contains-ckeditor"
                                                v-if="!!$store.state.currentQuestionPermissions.script"
                                            >
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
                                                <aceeditor
                                                    v-model="currentQuestionScript"
                                                    :show-lang-selector="false"
                                                    base-lang="javascript"
                                                    :thisId="'helpEditScript'"
                                                    :showLangSelector="true"
                                                    v-on:input="runDebouncedChange"
                                                ></aceeditor>
                                                <p class="alert well">{{"__SCRIPTHELP"|translate}}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" key="divideRow">
                                        <div class="col-sm-12 ls-space margin top-5 bottom-5">
                                            <hr />
                                        </div>
                                    </div>
                                </div> 
                            </div>

                            <questionoverview
                                v-show="!(editQuestion || isCreateQuestion)"
                                :loading="loading"
                                :event="event"
                                @triggerEvent="triggerEvent"
                                @eventSet="eventSet"
                            ></questionoverview>
                            <generalsettings
                                :event="event"
                                :readonly="!(editQuestion || isCreateQuestion)"
                                @triggerEvent="triggerEvent"
                                @eventSet="eventSet"
                            ></generalsettings>
                        </div>
                        <div class="ls-flex ls-flex-row scoped-advanced-settings-block">
                            <advancedsettings 
                                :event="event" 
                                v-on:triggerEvent="triggerEvent" 
                                v-on:eventSet="eventSet" 
                                :readonly="!(editQuestion || isCreateQuestion)"
                                :hide-advanced-options="initCopy && copyAdvancedOptions"
                                :hide-subquestions="initCopy && copyAdvancedOptions"
                                :hide-answeroptions="initCopy && copyAdvancedOptions"
                            />
                        </div>
                    </div>
                <modals-container @modalEvent="setModalEvent"/>
            </div>
        </div>
        </form>
    </div>
</div>
