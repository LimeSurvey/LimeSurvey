<?php
/**
 * @var AdminController $this
 * @var Survey $oSurvey
 */
?>
<?php
App()->getClientScript()->registerScript(
    'activatePanelClickable', 
    'LS.pageLoadActions.panelClickable()', 
    LSYii_ClientScript::POS_POSTSCRIPT 
)
?>
<?php $this->renderPartial("./survey/QuestionGroups/_jsVariables", ['data' => $jsData]); ?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <div class="container-fluid">
    <?php echo CHtml::form(array("admin/questiongroups/sa/update/gid/{$gid}"), 'post', array('id'=>'frmeditgroup', 'name'=>'frmeditgroup', 'class'=>'form30 ', 'data-isvuecomponent' => 1)); ?>
        <input type="submit" class="hidden" name="triggerSubmitQuestionGroupEditor" id="triggerSubmitQuestionGroupEditor" />
        <div id="advancedQuestionGroupEditor"><lsnextquestiongroupeditor /></div>
        </form>
    </div>
</div>


