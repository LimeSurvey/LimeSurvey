<?php echo $conditionsoutput;?>

<div class="row">
    <?php if ($subaction== "editconditionsform" || $subaction=='insertcondition' ||
        $subaction == "editthiscondition" || $subaction == "delete" ||
        $subaction == "updatecondition" || $subaction == "deletescenario" ||
        $subaction == "updatescenario" ||
        $subaction == "renumberscenarios")  : ?>

    <div class="col-sm-12 lead">
            <?php eT("Only show question"); ?>
            <strong>
            <?php echo ' ' . $sCurrentQuestionText;?>
            </strong>
        <?php eT('if:');?>
    </div>

    <div class="col-sm-4">
        <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/deleteallconditions/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'margin-bottom:0;','id'=>'deleteallconditions','name'=>'deleteallconditions'));?>
            <input type='hidden' name='qid' value='<?php echo $qid;?>' />
            <input type='hidden' name='gid' value='<?php echo $gid;?>' />
            <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
            <input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />


            <?php if ($scenariocount > 0): ?>
            <?php endif; ?>


        </form>
    </div>

<?php else :?>
            <strong><?php echo $onlyshow;?></strong>
            <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/deleteallconditions/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'margin-bottom:0;','id'=>'deleteallconditions','name'=>'deleteallconditions'));?>
                <input type='hidden' name='qid' value='<?php echo $qid;?>' />
                <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
                <input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />
            </form>
<?php endif;?>
</div>
