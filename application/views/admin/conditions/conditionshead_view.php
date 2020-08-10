<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3>
        <?php eT("Conditions designer"); ?>
        
        <?php if ($hasUpdatePermission): ?>
            <a class="btn btn-default pjax pull-right condition-header-button <?php if(isset($questionbar['buttons']['condition']['edit']) && $questionbar['buttons']['condition']['edit']){ echo 'active'; }?>" href="<?php echo $this->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button">
                <span class="icon-conditions_add"></span>
                <?php eT("Add and edit conditions");?>
            </a>

            <a class="btn btn-default pjax pull-right condition-header-button <?php if(isset($questionbar['buttons']['condition']['copyconditionsform'])){echo 'active';}?>" href="<?php echo $this->createUrl("admin/conditions/sa/index/subaction/copyconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button">
                <span class="icon-copy"></span>
                <?php eT("Copy conditions");?>
            </a>
        <?php endif ?>
    </h3>
     <div class="row">
        <div class="col-lg-12 content-right">


<?php echo $conditionsoutput_action_error;?>
<?php App()->getClientScript()->registerScript("conditionshead_prepared_javascript", $javascriptpre, LSYii_ClientScript::POS_BEGIN);?>
<?php App()->getClientScript()->registerScript("conditionshead_onrun_javascript", 'window.LS.doToolTip();', LSYii_ClientScript::POS_POSTSCRIPT);?>

<!-- Modal for quick add -->
<div id="quick-add-condition-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">  <?php // JS add not.type as panel-type, e.g. panel-default, panel-danger ?>
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php eT('Quick-add conditions'); ?></h4>
            </div>
            <div class="modal-body">
                <!-- Condition form is in file quickAddConditionForm.php -->
                <?php echo $quickAddConditionForm; ?>
            </div>
            <div class="modal-footer">
                <button type='submit' id='quick-add-condition-save-button' class='btn btn-primary'><?php eT('Save'); ?></button>
                <button type='submit' id='quick-add-condition-save-and-close-button' class='btn btn-default'><?php eT('Save and close'); ?></button>
                <button type="button" id='quick-add-condition-close-button' class="btn btn-danger" data-dismiss="modal">&nbsp;<?php eT("Close"); ?></button>
                <span id='quick-add-url' class='hidden'><?php echo $quickAddConditionURL; ?></span>
            </div>
        </div>
    </div>
</div>
