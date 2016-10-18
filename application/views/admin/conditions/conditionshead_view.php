<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->renderPartial('/admin/survey/breadcrumb', array('oQuestion'=>$oQuestion, 'active'=>gT("Conditions designer") )); ?>
    <h3>
        <?php eT("Conditions designer"); ?>

        <?php if ($scenariocount > 0): ?>
            <button
                id='delete-all-conditions'
                data-toggle='modal'
                data-target='#confirmation-modal'
                data-message='<?php eT('Are you sure you want to delete all conditions for this question?', 'js'); ?>'
                data-onclick='(function() { document.getElementById("deleteallconditions").submit(); })'
                class='btn btn-warning pull-right'
                onclick='return false';
            >
                <span class="glyphicon glyphicon-trash"></span>
                &nbsp;
                <?php eT('Delete all conditions'); ?>
            </button>
        <?php endif; ?>

        <?php if ($scenariocount > 1): ?>
            <button
                id='renumber-scenario'
                class="btn btn-default pull-right"
                data-toggle='modal'
                data-target='#confirmation-modal'
                data-message='<?php eT('Are you sure you want to renumber the scenarios with incremented numbers beginning from 1?', 'js'); ?>'
                data-onclick='(function() { document.getElementById("toplevelsubaction").value="renumberscenarios"; document.getElementById("deleteallconditions").submit();})'
                onclick='return false;'
            >
                <span class="icon-renumber"></span>
                &nbsp;
                <?php eT("Renumber scenario automatically");?>
            </button>
        <?php endif; ?>
    </h3>
     <div class="row">
        <div class="col-lg-12 content-right">


<?php echo $conditionsoutput_action_error;?>
<?php echo $javascriptpre;?>
