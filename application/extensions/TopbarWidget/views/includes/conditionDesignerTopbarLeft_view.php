<a role="button" class="btn btn-outline-secondary pjax <?php if(isset($currentMode) && $currentMode == 'conditions'){echo 'active';}?>" href="<?php echo Yii::App()->createUrl("/admin/conditions/sa/index/subaction/conditions/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
    <span class="fa fa-info-sign"></span>
    <?php eT("Show conditions for this question");?>
</a>

<a role="button" class="btn btn-outline-secondary pjax <?php if(isset($currentMode) && $currentMode == 'edit'){ echo 'active'; }?>" href="<?php echo Yii::App()->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
    <span class="icon-conditions_add"></span>
    <?php eT("Add and edit conditions");?>
</a>

<a role="button" class="btn btn-outline-secondary pjax <?php if(isset($currentMode) && $currentMode == 'copyconditionsform'){echo 'active';}?>" href="<?php echo Yii::App()->createUrl("admin/conditions/sa/index/subaction/copyconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
    <span class="icon-copy"></span>
    <?php eT("Copy conditions");?>
</a>

<!-- Quick Add Contitions Button -->
<button
    id='quick-add-condition-button'
    role="button"
    type="button"
    class='btn btn-outline-secondary'
    data-bs-toggle='modal'
    data-bs-target='#quick-add-condition-modal'
    data-tooltip='true'
    data-title='<?php eT('Add multiple conditions without a page reload'); ?>'>
        <span class="fa fa-plus-circle"></span>
        &nbsp;
        <?php eT('Quick-add conditions'); ?>
</button>

