<?php if ($subaction == "copyconditionsform" || $subaction == "copyconditions"): ?>
    <input type='checkbox' id='scenarioCbx<?php echo $scenarionr['scenario']; ?>' checked='checked'/>
    <script type='text/javascript'>
        $(document).ready(function () {
            $('#scenarioCbx<?php echo $scenarionr['scenario']; ?>').checkgroup({
                groupName: 'aConditionFromScenario<?php echo $scenarionr['scenario']; ?>'
            }); 
        });
    </script>
<?php endif; ?>

<?php if ($showScenarioText == 'normal'): ?>
    -------- <i>Scenario <?php echo $scenarionr['scenario']; ?></i> --------
<?php elseif ($showScenarioText == 'withOr'): ?>
    -------- <i><?php eT('OR'); ?> Scenario <?php echo $scenarionr['scenario']; ?></i> --------
<?php endif; ?>

<?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/updatescenario/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'display: none','id'=>'editscenario'.$scenarionr['scenario']));?>
    <label><?php eT("New scenario number:"); ?>&nbsp;
        <input type='text' name='newscenarionum' size='3'/></label>
    <input type='hidden' name='scenario' value='<?php echo $scenarionr['scenario']; ?>'/>
    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
    <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
    <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
    <input type='hidden' name='subaction' value='updatescenario' />&nbsp;&nbsp;
    <input type='submit' class="btn btn-default" name='scenarioupdated' value='<?php eT("Update scenario"); ?>' />
    <input type='button' class="btn btn-default" name='cancel' value='<?php eT("Cancel"); ?>' onclick="$('#editscenario<?php echo $scenarionr['scenario']; ?>').hide('slow');" />
</form>

<?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/deletescenario/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'margin-bottom:0;','id'=>'deletescenario'.$scenarionr['scenario'],'name'=>'deletescenario'.$scenarionr['scenario']));?>

    <?php if ($showScenarioButtons): ?>
        <a 
            href='#'
            onclick="if (confirm('<?php eT("Are you sure you want to delete all conditions set in this scenario?", "js"); ?>')) { document.getElementById('deletescenario<?php echo $scenarionr['scenario']; ?>').submit();}"
        >
            <span class="glyphicon glyphicon-trash"></span>
        </a>

        <a
            href='#'
            id='editscenariobtn<?php echo $scenarionr['scenario']; ?>'
            onclick="$('#editscenario<?php echo $scenarionr['scenario']; ?>').toggle('slow');"
        >
            <span class="glyphicon glyphicon-pencil"></span>
        </a>
    <?php endif; ?>

    <input type='hidden' name='scenario' value='<?php echo $scenarionr['scenario'];?>' />
    <input type='hidden' name='qid' value='<?php echo $qid;?>' />
    <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
    <input type='hidden' name='subaction' value='deletescenario' />
</form>
