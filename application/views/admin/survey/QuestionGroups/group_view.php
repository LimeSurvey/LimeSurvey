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
    <?php echo CHtml::form(array("admin/questiongroups/sa/update/gid/{$gid}"), 'post', array('id'=>'frmeditgroup', 'name'=>'frmeditgroup', 'class'=>'form30 ')); ?>
        <input type="submit" class="hidden" name="triggerSubmitQuestionGroupEditor" id="triggerSubmitQuestionGroupEditor" />
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <?=gT("EXPERIMENTAL EDITOR! - Please keep an eye out for bugs and report them on ")?>
            <a href="https://bugs.limesurvey.org">https://bugs.limesurvey.org</a>
        </div>
        <div id="advancedQuestionGroupEditor"><lsnextquestiongroupeditor /></div>
        </form>
    </div>
</div>


