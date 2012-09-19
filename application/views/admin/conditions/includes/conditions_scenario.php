<tr>
    <?php echo $initialCheckbox; ?>
    <td><?php echo $scenariotext; ?>&nbsp;
        <form action='<?php echo $this->createUrl("/admin/conditions/index/subaction/updatescenario/surveyid/$surveyid/gid/$gid/qid/$qid/"); ?>' method='post' id='editscenario<?php echo $scenarionr['scenario']; ?>' style='display: none'>
            <label><?php $clang->eT("New scenario number:"); ?>&nbsp;
                <input type='text' name='newscenarionum' size='3'/></label>
            <input type='hidden' name='scenario' value='<?php echo $scenarionr['scenario']; ?>'/>
            <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
            <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
            <input type='hidden' name='subaction' value='updatescenario' />&nbsp;&nbsp;
            <input type='submit' name='scenarioupdated' value='<?php $clang->eT("Update scenario"); ?>' />
            <input type='button' name='cancel' value='<?php $clang->eT("Cancel"); ?>' onclick="$('#editscenario<?php echo $scenarionr['scenario']; ?>').hide('slow');" />
        </form>
    </td>
    <td>
        <form id='deletescenario<?php echo $scenarionr['scenario']; ?>' action='<?php echo $this->createUrl("/admin/conditions/index/subaction/deletescenario/surveyid/$surveyid/gid/$gid/qid/$qid/"); ?>' method='post' name='deletescenario<?php echo $scenarionr['scenario']; ?>' style='margin-bottom:0;'>
            <?php if(isset($additional_content)) echo $additional_content; ?>
            <input type='hidden' name='scenario' value='<?php echo $scenarionr['scenario'];?>' />
            <input type='hidden' name='qid' value='<?php echo $qid;?>' />
            <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
            <input type='hidden' name='subaction' value='deletescenario' />
        </form>
    </td>
</tr>
