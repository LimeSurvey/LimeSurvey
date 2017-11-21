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

<div class='panel panel-primary'>

    <div class='panel-heading scenario-heading'>

        <div class='row'>
            <div class='col-sm-2'>
            <!-- Scenario header -->
            <?php if ($showScenarioText == 'normal'): ?>
                <h5>Scenario <?php echo $scenarionr['scenario']; ?></h5>
            <?php elseif ($showScenarioText == 'withOr'): ?>
                <h5><?php eT('OR'); ?> Scenario <?php echo $scenarionr['scenario']; ?></h5>
            <?php else: ?>
                <h5><?php eT('Default scenario'); ?></h5>
            <?php endif; ?>
            </div>

            <div class='col-sm-10'>
                <div class="container-fluid">
                <!-- Small form to change scenario number -->
                <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/updatescenario/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'display: none','id'=>'editscenario'.$scenarionr['scenario']));?>
                    <label>
                        <?php eT("New scenario number:"); ?>&nbsp;
                        <input type='text' name='newscenarionum' size='3'/>
                    </label>
                    <input type='hidden' name='scenario' value='<?php echo $scenarionr['scenario']; ?>'/>
                    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                    <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
                    <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
                    <input type='hidden' name='subaction' value='updatescenario' />&nbsp;&nbsp;
                    <input type='submit' class="btn btn-default" name='scenarioupdated' value='<?php eT("Update scenario"); ?>' />
                    <input type='button' class="btn btn-default" name='cancel' value='<?php eT("Cancel"); ?>' onclick="$('#editscenario<?php echo $scenarionr['scenario']; ?>').hide('slow');" />
                </form>

                <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/deletescenario/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'margin-bottom:0;', 'class' => 'delete-scenario-form', 'id'=>'deletescenario'.$scenarionr['scenario'],'name'=>'deletescenario'.$scenarionr['scenario']));?>

                    <?php if ($showScenarioButtons): ?>
                        <button 
                            class='btn btn-default btn-xs'
                            data-tooltip='true'
                            data-title='<?php eT('Delete all conditions in this scenario'); ?>'
                            data-toggle='modal'
                            data-target='#confirmation-modal'
                            data-message='<?php eT('Are you sure you want to delete all conditions set in this scenario?', 'js'); ?>'
                            data-onclick='(function() { document.getElementById("deletescenario<?php echo $scenarionr["scenario"]; ?>").submit(); })'
                            onclick='return false;'
                        >
                            <span class="fa fa-trash text-danger"></span>
                        </button>

                        <button
                            class='btn btn-default btn-xs'
                            data-toggle='tooltip'
                            data-title='<?php eT('Change scenario number'); ?>'
                            id='editscenariobtn<?php echo $scenarionr['scenario']; ?>'
                            onclick="$('#editscenario<?php echo $scenarionr['scenario']; ?>').toggle('slow'); return false"
                        >
                            <span class="fa fa-pencil"></span>
                        </button>

                        <button
                            class='btn btn-default btn-xs'
                            data-toggle='tooltip'
                            data-title='<?php eT('Add condition to this scenario'); ?>'
                            onclick='addConditionToScenario("<?php echo $addConditionToScenarioURL; ?>"); return false'
                        >
                            <span class='fa fa-plus-circle'></span>
                        </button>


                    <?php endif; ?>

                    <input type='hidden' name='scenario' value='<?php echo $scenarionr['scenario'];?>' />
                    <input type='hidden' name='qid' value='<?php echo $qid;?>' />
                    <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
                    <input type='hidden' name='subaction' value='deletescenario' />
                </form>

                </div>
            </div>
        </div>
    </div>

    <div class='panel-body'>
        <?php echo $conditionHtml; ?>
    </div>
</div>
