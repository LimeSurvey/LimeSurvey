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
<?= $oQuestionSelector->getModal(); ?>

<div class="form-group col-sm-6 scoped-responsive-fix-height">
    <label for="questionCode"><?= gT('Code'); ?></label>
    <div class="scoped-keep-in-line">
        <input
            text="text"
            class="form-control"
            id="questionCode"
            :maxlength="this.maxQuestionCodeLength"
            :required="true"
            :readonly="!(editQuestion || isCreateQuestion || initCopy)"
        />
        <!--
        <type-counter 
            :countable="currentQuestionCode.length"
            :max-value="this.maxQuestionCodeLength"
            :valid="inputValid"
        />
        -->
    </div>
    <p class="well bg-warning scoped-highten-z" v-if="noCodeWarning!=null">{{noCodeWarning}}</p>
</div>
<div class="form-group col-sm-6 contains-question-selector">
    <label for="questionCode">Question type</label>
    <div v-if="$store.getters.surveyObject.active !=='Y'"
         v-show="(editQuestion || isCreateQuestion)"
         class="btn-group">
         <?= $oQuestionSelector->getButtonOrSelect(); ?>
        <?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>
    </div>
    <input type="text" class="form-control" id="questionTypeVisual" />
    <input type="hidden" id="question_type" name="type" />
</div>
