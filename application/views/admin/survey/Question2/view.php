<?php
    $aQuestionTypeGroups = array();
    $aQuestionTypeList = Question::typeList();
    $selected = null;

    if (Yii::app()->session['questionselectormode'] !== 'default') {
        $selectormodeclass = Yii::app()->session['questionselectormode'];
    } else {
        $selectormodeclass = getGlobalSetting('defaultquestionselectormode');
    }
    
    foreach ($aQuestionTypeList as $key=> $questionType) {
        $htmlReadyGroup = str_replace(' ', '_', strtolower($questionType['group']));
        if (!isset($aQuestionTypeGroups[$htmlReadyGroup])) {
            $aQuestionTypeGroups[$htmlReadyGroup] = array(
                'questionGroupName' => $questionType['group']
            );
        }
        $imageName = $key;
        if ($imageName == ":") {
            $imageName = "COLON";
        } elseif ($imageName == "|") {
            $imageName = "PIPE";
        } elseif ($imageName == "*") {
            $imageName = "EQUATION";
        }

        $questionType['detailpage'] = '
        <div class="col-sm-12 currentImageContainer">
            <img src="'.Yii::app()->getConfig('imageurl').'/screenshots/'.$imageName.'.png" />
        </div>';
        if ($imageName == 'S') {
            $questionType['detailpage'] = '
            <div class="col-sm-12 currentImageContainer">
                <img src="'.Yii::app()->getConfig('imageurl').'/screenshots/'.$imageName.'.png" />
                <img src="'.Yii::app()->getConfig('imageurl').'/screenshots/'.$imageName.'2.png" />
            </div>';
        }
        $aQuestionTypeGroups[$htmlReadyGroup]['questionTypes'][$key] = $questionType;
    }

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
        'buttonClasses' => ['btn-primary'],
        'optionArray' => [
            'selectedClass' => Question::getQuestionClass($oQuestion->type),
            'onUpdate' => [
                'value',
                "console.ls.log(value);"
                ."$('#question_type').val(value);"
                ."var event = jQuery.Event('jquery:trigger');"
                ."event.emitter = JSON.stringify({"
                    ."target: 'lsnextquestioneditor',"
                    ."method: 'questionTypeChangeTriggered',"
                    ."content: value"
                ."});"
                ."$('#advancedQuestionEditor').trigger('jquery:trigger', event);"
            ]
        ]
    ));
?>
<?=$oQuestionSelector->getModal();?>
<?php $this->renderPartial("./survey/Question2/_jsVariables", ['data' => $jsData, 'oQuestionSelector' => $oQuestionSelector]); ?>
<?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <div class="container-fluid">
        <?php echo CHtml::form(array("admin/questionedit/update"), 'post', array('class'=>'form30 ','id'=>'frmeditquestion','name'=>'frmeditquestion')); ?>
        <input type="submit" class="hidden" name="triggerSubmitQuestionEditor" id="triggerSubmitQuestionEditor" />

        <div id="advancedQuestionEditor"><app /></div>
        </form>
    </div>
</div>
