<?php
/**
 * @var QuestionGroupsAdministrationController $this
 * @var array $jsData
 * @var int $gid
 */

App()->getClientScript()->registerScript(
    'activatePanelClickable',
    'LS.pageLoadActions.panelClickable()',
    LSYii_ClientScript::POS_POSTSCRIPT
)
?>
<?php $this->renderPartial("_jsVariables", ['data' => $jsData]); ?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <div class="container-fluid">
    <?php echo CHtml::form(array("questionGroupsAdministration/update/gid/{$gid}"), 'post', array('id'=>'frmeditgroup',
        'name'=>'frmeditgroup', 'class'=>'form30 ', 'data-isvuecomponent' => 1)); ?>
        <input type="submit" class="hidden" name="triggerSubmitQuestionGroupEditor"
               id="triggerSubmitQuestionGroupEditor" />
        <div id="advancedQuestionGroupEditor"><lsnextquestiongroupeditor /></div>
        </form>
    </div>
</div>


