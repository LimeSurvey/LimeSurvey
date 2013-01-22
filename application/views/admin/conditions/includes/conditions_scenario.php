<tr>
    <?php echo $initialCheckbox; ?>
    <td><?php echo $scenariotext; ?>&nbsp;
        <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/updatescenario/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'display: none','id'=>'editscenario'.$scenarionr['scenario']));?>
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
        <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/deletescenario/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'margin-bottom:0;','id'=>'deletescenario'.$scenarionr['scenario'],'name'=>'deletescenario'.$scenarionr['scenario']));?>
            <?php if(isset($additional_content)) echo $additional_content; ?>
            <input type='hidden' name='scenario' value='<?php echo $scenarionr['scenario'];?>' />
            <input type='hidden' name='qid' value='<?php echo $qid;?>' />
            <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
            <input type='hidden' name='subaction' value='deletescenario' />
        </form>
    </td>
</tr>
