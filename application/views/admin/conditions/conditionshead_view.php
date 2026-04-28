<div class='side-body'>
    <div class="pagetitle h1 pt-3 pb-2">
        <?php eT("Conditions designer"); ?>

        <?php if ($scenariocount > 0): ?>
            <?php
            $this->widget('ext.ButtonWidget.ButtonWidget', [
                'name' => 'delete-all-conditions',
                'id' => 'delete-all-conditions',
                'text' => gT('Delete all conditions'),
                'icon' => 'ri-delete-bin-fill',
                'htmlOptions' => [
                    'class' => 'btn btn-danger float-end condition-header-button',
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#confirmation-modal',
                    'data-message' => gT('Are you sure you want to delete all conditions for this question?', 'js'),
                    'data-onclick' => '(function() { document.getElementById("deleteallconditions").submit(); })',
                    'onclick' => 'return false;',
                ],
            ]); ?>
        <?php endif; ?>

        <?php if ($scenariocount > 1): ?>
            <?php
            $this->widget('ext.ButtonWidget.ButtonWidget', [
                'name' => 'renumber-scenario',
                'id' => 'renumber-scenario',
                'text' => gT('Renumber scenarios'),
                'icon' => 'ri-list-ordered',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary float-end condition-header-button',
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#confirmation-modal',
                    'data-message' => gT('Are you sure you want to renumber the scenarios with incrementing numbers beginning from 1?', 'js'),
                    'data-onclick' => '(function() { document.getElementById("toplevelsubaction").value="renumberscenarios"; document.getElementById("deleteallconditions").submit();})',
                    'onclick' => 'return false;',
                ],
            ]); ?>
        <?php endif; ?>
    </div>
     <div class="row">
        <div class="col-12 content-right">


<?php echo $conditionsoutput_action_error;?>
<?php App()->getClientScript()->registerScript("conditionshead_prepared_javascript", $javascriptpre, LSYii_ClientScript::POS_BEGIN);?>
<?php App()->getClientScript()->registerScript("conditionshead_onrun_javascript", 'window.LS.doToolTip();', LSYii_ClientScript::POS_POSTSCRIPT);?>

<!-- Modal for quick add -->
<div id="quick-add-condition-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">  <?php // JS add not.type as panel-type, e.g. panel-default, panel-danger ?>
            <div class="modal-header">
                <h5 class="modal-title"><?php eT('Quick-add conditions'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Condition form is in file quickAddConditionForm.php -->
                <?php echo $quickAddConditionForm; ?>
            </div>
            <div class="modal-footer">
                <button type="button" id='quick-add-condition-close-button' class="btn btn-cancel" data-bs-dismiss="modal">
                    <?php eT("Cancel"); ?>
                </button>
                <button role="button" type='submit' id='quick-add-condition-save-button' class='btn btn-primary'>
                    <?php eT('Save'); ?>
                </button>
                <span id='quick-add-url' class="d-none">
                	<?php echo $quickAddConditionURL; ?>
                </span>
            </div>
        </div>
    </div>
</div>
