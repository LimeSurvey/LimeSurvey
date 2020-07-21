<?php echo $conditionsoutput;?>

<?php if ($hasUpdatePermission): ?>
<div class="pull-right text-right">
    <p>        
        <button
            id='quick-add-condition-button'
            class='btn btn-default condition-header-button'
            data-toggle='modal'
            data-target='#quick-add-condition-modal'
            data-tooltip='true'
            data-title='<?php eT('Add multiple conditions without a page reload'); ?>'
            >
            <span class="fa fa-plus-circle"></span>
            &nbsp;
            <?php eT('Quick-add conditions'); ?>
        </button>

        <?php if ($scenariocount > 1): ?>
        <button
            id='renumber-scenario'
            class="btn btn-default condition-header-button"
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
    </p>
</div>
<?php endif; ?>

<p class="lead">
    <?php eT("Only show question"); ?>
    <strong><?php echo ' ' . $sCurrentQuestionText;?></strong>
    <?php eT('if:');?>
</p>


<?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/deleteallconditions/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'margin-bottom:0;','id'=>'deleteallconditions','name'=>'deleteallconditions'));?>
    <input type='hidden' name='qid' value='<?php echo $qid;?>' />
    <input type='hidden' name='gid' value='<?php echo $gid;?>' />
    <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
    <input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />
</form>
