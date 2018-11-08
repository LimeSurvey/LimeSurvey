<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3>
        <?php eT("Conditions designer"); ?>

        <?php if ($scenariocount > 0): ?>
            <button
                id='delete-all-conditions'
                data-toggle='modal'
                data-target='#confirmation-modal'
                data-message='<?php eT('Are you sure you want to delete all conditions for this question?', 'js'); ?>'
                data-onclick='(function() { document.getElementById("deleteallconditions").submit(); })'
                class='btn btn-warning pull-right condition-header-button'
                onclick='return false;'
            >
                <span class="fa fa-trash"></span>
                &nbsp;
                <?php eT('Delete all conditions'); ?>
            </button>
        <?php endif; ?>

        <?php if ($scenariocount > 1): ?>
            <button
                id='renumber-scenario'
                class="btn btn-default pull-right condition-header-button"
                data-toggle='modal'
                data-target='#confirmation-modal'
                data-message='<?php eT('Are you sure you want to renumber the scenarios with incrementing numbers beginning from 1?', 'js'); ?>'
                data-onclick='(function() { document.getElementById("toplevelsubaction").value="renumberscenarios"; document.getElementById("deleteallconditions").submit();})'
                onclick='return false;'
            >
                <span class="icon-renumber"></span>
                &nbsp;
                <?php eT("Renumber scenarios");?>
            </button>
        <?php endif; ?>

        <button
            id='quick-add-condition-button'
            class='btn btn-default pull-right condition-header-button'
            data-toggle='modal'
            data-target='#quick-add-condition-modal'
            data-tooltip='true'
            data-title='<?php eT('Add multiple conditions without a page reload'); ?>'
        >
            <span class="fa fa-plus-circle"></span>
            &nbsp;
            <?php eT('Quick-add conditions'); ?>
        </button>
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
