<?php
    /**
     * @var $aQuestionTypeList array
     * @var $questionType      QuestionTheme
     * @var $oQuestion         Question
     * @var $jsData            array
     * @var $selectedQuestion  array
     * @var $oQuestionSelector PreviewModalWidget
     * @var $this              AdminController
     * TODO: move logic from the view to controller
     */

    $aQuestionTypeGroups = array();

    if (App()->session['questionselectormode'] !== 'default') {
        $selectormodeclass = App()->session['questionselectormode'];
    } else {
        $selectormodeclass = App()->getConfig('defaultquestionselectormode');
    }

    foreach ($aQuestionTypeList as $questionType) {

        $blame = $questionType['type'];
        $htmlReadyGroup = str_replace(' ', '_', strtolower($questionType['group']));
        if (!isset($aQuestionTypeGroups[$htmlReadyGroup])) {
            $aQuestionTypeGroups[$htmlReadyGroup] = array(
                'questionGroupName' => $questionType['group']
            );
        }
        $imageName = $questionType['type'];
        if ($imageName == ":") {
            $imageName = "COLON";
        } elseif ($imageName == "|") {
            $imageName = "PIPE";
        } elseif ($imageName == "*") {
            $imageName = "EQUATION";
        }

        $questionType['detailpage'] = '
        <div class="col-sm-12 currentImageContainer">
            <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '.png" />
        </div>';
        if ($imageName == 'S') {
            $questionType['detailpage'] = '
            <div class="col-sm-12 currentImageContainer">
                <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '.png" />
                <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '2.png" />
            </div>';
        }
        $aQuestionTypeGroups[$htmlReadyGroup]['questionTypes'][$questionType['type']] = $questionType;
    }

    $oQuestionSelector = $this->beginWidget('ext.admin.PreviewModalWidget.PreviewModalWidget', array(
        'widgetsJsName' => "questionTypeSelector",
        'renderType' => (isset($selectormodeclass) && $selectormodeclass == "none") ? "group-simple" : "group-modal",
        'modalTitle' => "Select question type",
        'groupTitleKey' => "questionGroupName",
        'groupItemsKey' => "questionTypes",
        'debugKeyCheck' => "Type: ",
        'previewWindowTitle' => gT("Preview question type"),
        'groupStructureArray' => $aQuestionTypeGroups,
        'value' => $oQuestion->type,
        'debug' => YII_DEBUG,
        'currentSelected' => $selectedQuestion['title'] ?? gT('Invalid Question'),
        'buttonClasses' => ['btn-primary'],
        'optionArray' => [
            'selectedClass' => $selectedQuestion['settings']->class ?? 'invalid_question',
            'onUpdate' => [
                'value',
                "console.ls.log(value);"
                . "$('#question_type').val(value);"
                . "var event = jQuery.Event('jquery:trigger');"
                . "event.emitter = JSON.stringify({"
                . "target: 'lsnextquestioneditor',"
                . "method: 'questionTypeChangeTriggered',"
                . "content: value"
                . "});"
                . "$('#advancedQuestionEditor').trigger('jquery:trigger', event);"
            ]
        ]
    ));
?>
<?= $oQuestionSelector->getModal(); ?>
<?php $this->renderPartial("./survey/Question2/_jsVariables", ['data' => $jsData, 'oQuestionSelector' => $oQuestionSelector]); ?>
<?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <div class="container-fluid">
        <?php echo CHtml::form(array("admin/questionedit/update"), 'post', array('class' => 'form30 ', 'id' => 'frmeditquestion', 'name' => 'frmeditquestion')); ?>
        <input type="submit" class="hidden" name="triggerSubmitQuestionEditor" id="triggerSubmitQuestionEditor"/>

        <div id="advancedQuestionEditor">
            <app/>
        </div>
        </form>
    </div>
</div>
