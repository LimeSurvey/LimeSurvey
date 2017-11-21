<?php echo $conditionsoutput;?>

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
