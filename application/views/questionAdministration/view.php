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
