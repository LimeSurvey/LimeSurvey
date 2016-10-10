<?php echo $conditionsoutput;?>
<div class="row">
    <?php if ($subaction== "editconditionsform" || $subaction=='insertcondition' ||
        $subaction == "editthiscondition" || $subaction == "delete" ||
        $subaction == "updatecondition" || $subaction == "deletescenario" ||
        $subaction == "updatescenario" ||
        $subaction == "renumberscenarios")  : ?>

    <div class="col-sm-8">
        <strong>
            <?php
                // echo $onlyshow;
                eT("Only show question:");
            ?>
        </strong>
        <br/><br/>
            <blockquote>
            <em>
            <?php echo $sCurrentQuestionText;?>
            </em>
        </blockquote>
        <br/>
        <strong><?php eT('IF:');?></strong>
        <br/>
        <br/>
    </div>

    <div class="col-sm-4">
        <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/deleteallconditions/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'margin-bottom:0;','id'=>'deleteallconditions','name'=>'deleteallconditions'));?>
            <input type='hidden' name='qid' value='<?php echo $qid;?>' />
            <input type='hidden' name='gid' value='<?php echo $gid;?>' />
            <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
            <input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />


            <?php if ($scenariocount > 0): ?>
                <button
                    data-toggle='modal'
                    data-target='#confirmation-modal'
                    data-message='<?php eT('Are you sure you want to delete all conditions?', 'js'); ?>'
                    data-onclick='(function() { document.getElementById("deleteallconditions").submit(); })'
                    class='btn btn-warning'
                    onclick='return false';
                >
                    <span class="glyphicon glyphicon-trash"></span>&nbsp;
                    <?php eT('Delete all conditions'); ?>
                </button>
            <?php endif; ?>

            <?php if ($scenariocount > 1): ?>
                <a class="btn btn-default" href='#' onclick="if ( confirm('<?php eT("Are you sure you want to renumber the scenarios with incremented numbers beginning from 1?","js");?>')) { document.getElementById('toplevelsubaction').value='renumberscenarios'; document.getElementById('deleteallconditions').submit();}">
                    <span class="icon-renumber"></span>
                    <?php eT("Renumber scenario automatically");?>
                </a>
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
