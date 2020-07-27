<div class="row">
    <div class="col-sm-12">
        <?php if ($hasUpdatePermission && $scenariocount > 0): ?>
            <button
            id='delete-all-conditions'
            data-toggle='modal'
            data-target='#confirmation-modal'
            data-message='<?php eT('Are you sure you want to delete all conditions for this question?', 'js'); ?>'
            data-onclick='(function() { document.getElementById("deleteallconditions").submit(); })'
            class='btn btn-xs btn-warning pull-right condition-header-button'
            onclick='return false;'
            >
            <span class="fa fa-trash"></span>
            &nbsp;
            <?php eT('Delete all conditions'); ?>
        </button>
        <?php endif; ?>
    </div>
</div>