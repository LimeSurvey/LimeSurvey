<?php
/**
 * @var $aQuestionTypeList array
 * @var $jsData            array
 * @var $aQuestionTypeStateList array
 * TODO: move logic from the view to controller
 */

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
                    </div>
                    <div key="editorcontent-block" class="col-12">
                        <div class="ls-flex ls-flex-row scope-create-gutter">
                                <maineditor
                                    v-show="(editQuestion || isCreateQuestion)"
                                    :loading="loading"
                                    :event="event"
                                    @triggerEvent="triggerEvent"
                                    @eventSet="eventSet"
                                ></maineditor>
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
