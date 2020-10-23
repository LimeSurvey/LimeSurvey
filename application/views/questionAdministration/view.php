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
                <div class="btn-group pull-right clear" role="group" data-toggle="buttons">
                    <label class="btn btn-default active">
                        <input 
                            type="radio" 
                            name="question-editor-switch"
                            checked="checked"
                        />
                        <?= gt('Question overview'); ?>
                    </label>
                    <label class="btn btn-default">
                        <input
                            type="radio"
                            name="question-editor-switch"
                        />
                        <?= gT('Question editor'); ?>
                    </label>
                </div>

                <div class="row">
                    <!-- Question summary -->
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
                                'oQuestion'         => $oQuestion,
                                'questionTypes'     => $aQuestionTypeStateList,
                                'answersCount'      => $answersCount,
                                'subquestionsCount' => $subquestionsCount,
                                'advancedSettings'  => $advancedSettings
                            ]
                        ); ?>
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
