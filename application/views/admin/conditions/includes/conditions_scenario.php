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

<div class='card card-primary mb-4'>

    <div class='card-header '>

        <div class='row mb-4'>
            <div class='col-md-4'>
            <!-- Scenario header -->
            <?php if ($showScenarioText == 'normal'): ?>
                <h5 class="card-title">Scenario <?php echo $scenarionr['scenario']; ?></h5>
            <?php elseif ($showScenarioText == 'withOr'): ?>
                <h5 class="card-title"><?php eT('OR'); ?> Scenario <?php echo $scenarionr['scenario']; ?></h5>
            <?php else: ?>
                <h5 class="card-title"><?php eT('Default scenario'); ?></h5>
            <?php endif; ?>
            </div>

            <div class='col-md-8'>
                <div class="text-end">
                <!-- Small form to change scenario number -->
                <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/updatescenario/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'display: none','id'=>'editscenario'.$scenarionr['scenario']));?>
                    <label>
                        <?php eT("New scenario number:"); ?>&nbsp;
                        <input type='text' name='newscenarionum' size='3' value='<?php echo $scenarionr['scenario']; ?>' required/>
                    </label>
                    <input type='hidden' name='scenario' value='<?php echo $scenarionr['scenario']; ?>'/>
                    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                    <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
                    <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
                    <input type='hidden' name='subaction' value='updatescenario' />&nbsp;&nbsp;
                    <input type='submit' class="btn btn-outline-secondary" name='scenarioupdated' value='<?php eT("Update scenario"); ?>' />
                    <input type='button' class="btn btn-cancel" name='cancel' value='<?php eT("Cancel"); ?>' onclick="$('#editscenario<?php echo $scenarionr['scenario']; ?>').hide('slow');" />
                </form>

                <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/deletescenario/surveyid/{$surveyid}/gid/{$gid}/qid/{$qid}/"), 'post', array('style'=>'margin-bottom:0;', 'class' => 'delete-scenario-form', 'id'=>'deletescenario'.$scenarionr['scenario'],'name'=>'deletescenario'.$scenarionr['scenario']));?>

                    <?php if ($showScenarioButtons): ?>
                        <div class="mt-1">
                            <span data-bs-toggle="tooltip" title='<?php eT('Delete all conditions in this scenario'); ?>'>
                                <button
                                    class='btn btn-outline-secondary btn-xs'
                                    data-bs-toggle='modal'
                                    data-bs-target='#confirmation-modal'
                                    data-message='<?php eT('Are you sure you want to delete all conditions set in this scenario?', 'js'); ?>'
                                    data-onclick='(function() { document.getElementById("deletescenario<?php echo $scenarionr["scenario"]; ?>").submit(); })'
                                    onclick='return false;'
                                >
                                    <span class="ri-delete-bin-fill text-danger"></span>
                                </button>
                            </span>
                            <button
                                class='btn btn-outline-secondary btn-xs'
                                data-bs-toggle='tooltip'
                                title='<?php eT('Change scenario number'); ?>'
                                id='editscenariobtn<?php echo $scenarionr['scenario']; ?>'
                                onclick="$('#editscenario<?php echo $scenarionr['scenario']; ?>').toggle('slow'); return false"
                            >
                                <span class="ri-pencil-fill"></span>
                            </button>

                            <button
                                class='btn btn-outline-secondary btn-xs'
                                data-bs-toggle='tooltip'
                                title='<?php eT('Add condition to this scenario'); ?>'
                                onclick='addConditionToScenario("<?php echo $addConditionToScenarioURL; ?>"); return false'
                            >
                                <span class='ri-add-circle-fill'></span>
                            </button>
                        </div>

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

    <div class='card-body'>
        <?php echo $conditionHtml; ?>
    </div>
</div>
