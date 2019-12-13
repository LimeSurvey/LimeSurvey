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

// $oQuestionSelector = $this->beginWidget(
//     'ext.admin.PreviewModalWidget.PreviewModalWidget', 
//     array(
//         'widgetsJsName' => "questionTypeSelector",
//         'renderType' => (isset($selectormodeclass) && $selectormodeclass == "none") ? "group-simple" : "group-modal",
//         'modalTitle' => "Select question type",
//         'groupTitleKey' => "questionGroupName",
//         'groupItemsKey' => "questionTypes",
//         'debugKeyCheck' => "Type: ",
//         'previewWindowTitle' => gT("Preview question type"),
//         'groupStructureArray' => $aQuestionTypeGroups,
//         'value' => $oQuestion->type,
//         'debug' => YII_DEBUG,
//         'currentSelected' => $selectedQuestion['title'] ?? gT('Invalid Question'),
//         'buttonClasses' => ['btn-primary'],
//         'optionArray' => [
//             'secondaryInputElement' => '#question_type',
//             'onUpdate' => [
//                 'value',
//                 'options',
//                 "console.ls.log(value);"
//                 . "$('#question_type').val(value);"
//                 . "LS.EventBus.\$emit('questionTypeChanged', {"
//                 . "target: 'lsnextquestioneditor',"
//                 . "method: 'questionTypeChangeTriggered',"
//                 . "content: {value: value, options: options}"
//                 . "})"
//             ],
//             'onReady' => [
//                 'self',
//                 'LS.EventBus.$off("setQuestionType"); '
//                 . "LS.EventBus.\$on('setQuestionType', function(value){ "
//                 . "     var valueItem = self.preSelectFromValue(value); "
//                 . "     self.selectItem(valueItem.data('itemValue'));"
//                 . "});"
//             ],
//         ]
//     )
// );
// $oQuestionSelector->getModal();
// ?>
<?php $this->renderPartial("./survey/Question2/_jsVariables", ['data' => $jsData, 'aStructureArray' => $aQuestionTypeGroups]); ?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <div class="container-fluid">
        <?php echo CHtml::form(
            array("admin/questionedit/update"),
            'post',
            array(
                'class' => 'form30 ',
                'id' => 'frmeditquestion',
                'name' => 'frmeditquestion',
                'data-isvuecomponent' => 1
            )
        ); ?>
        <input type="submit" class="hidden" name="triggerSubmitQuestionEditor" id="triggerSubmitQuestionEditor"/>

        <div id="advancedQuestionEditor">
            <app/>
        </div>
        </form>
    </div>
</div>
