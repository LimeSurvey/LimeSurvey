<?php echo $conditionsoutput;?>

<table class='conditionlist'>
<tr>
    <?php if ($subaction== "editconditionsform" || $subaction=='insertcondition' ||
        $subaction == "editthiscondition" || $subaction == "delete" ||
        $subaction == "updatecondition" || $subaction == "deletescenario" ||
        $subaction == "updatescenario" ||
        $subaction == "renumberscenarios")  { ?>
        <td><strong><?php echo $onlyshow;?></strong>
        </td>
        <td>
        <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/deleteallconditions/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'margin-bottom:0;','id'=>'deleteallconditions','name'=>'deleteallconditions'));?>
        <input type='hidden' name='qid' value='<?php echo $qid;?>' />
        <input type='hidden' name='gid' value='<?php echo $gid;?>' />
        <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
        <input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />

        <?php if ($scenariocount > 0)
            { // show the Delete all conditions for this question button ?>
            <a href='#' onclick="if ( confirm('<?php $clang->eT("Are you sure you want to delete all conditions set to the questions you have selected?","js");?>')) { document.getElementById('deleteallconditions').submit();}">
                <img src='<?php echo $sImageURL;?>conditions_deleteall_16.png'  alt='<?php $clang->eT("Delete all conditions");?>' /></a>
            <?php }
            if ($scenariocount > 1)
            { // show the renumber scenario button for this question ?>
            <a href='#' onclick="if ( confirm('<?php $clang->eT("Are you sure you want to renumber the scenarios with incremented numbers beginning from 1?","js");?>')) { document.getElementById('toplevelsubaction').value='renumberscenarios'; document.getElementById('deleteallconditions').submit();}">
                <img src='<?php echo $sImageURL;?>scenario_renumber.png'  alt='<?php $clang->eT("Renumber scenario automatically");?>' /></a>
            <?php }
        }
        else
        { ?>
        <td><strong><?php echo $onlyshow;?></strong>
            <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/deleteallconditions/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'margin-bottom:0;','id'=>'deleteallconditions','name'=>'deleteallconditions'));?>
                <input type='hidden' name='qid' value='<?php echo $qid;?>' />
                <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
                <input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />
                <?php }
            ?>
        </form>
    </td></tr>
