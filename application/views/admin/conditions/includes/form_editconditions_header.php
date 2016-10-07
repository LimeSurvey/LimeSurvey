<?php
/**
 * View for the form 'editconditions' header.
 *
 * @var $subaction
 * @var $iSurveyID
 * @var $gid
 * @var $qid
 * @var $mytitle
 * @var $showScenario                   ( $subaction != "editthiscondition" && isset($scenariocount) && ($scenariocount == 1 || $scenariocount==0)) ||( $subaction == "editthiscondition" && isset($scenario) && $scenario == 1)
 * @var $qcountI                        $qcount+1
 */
?>

<div class="row">
    <div class="col-lg-12">

        <hr/>
        <!-- Form  editconditions -->
        <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/{$subaction}/surveyid/{$iSurveyID}/gid/{$gid}/qid/{$qid}/"),'post',array('id'=>"editconditions",'name'=>"editconditions", 'class' => 'form form-horizontal'));?>
            <div class='form-group'>
                <div class='col-sm-2'></div>
                <div class='col-sm-10'>
                    <h4><?php echo $mytitle;?></h4>
                </div>
            </div>

            <!-- Condition -->
            <div class='form-group'>
                <label class='control-label col-sm-2'><?php eT('Scenario'); ?></label>
                <div class='<?php if ($showScenario): ?> col-sm-4 <?php else: ?> col-sm-2 <?php endif; ?> add-scenario-column'>
                    <input class='form-control' type='number' name='scenario' id='scenario' value='1' <?php if($showScenario):?> style='display: none;' <?php endif;?>/>
                    <?php if($showScenario):?>
                        <span id='defaultscenariotxt'>
                            <?php eT("Default scenario"); ?>
                        </span>
                    <?php endif;?>
                    <?php if($showScenario):?>
                        &nbsp;
                        <a class='btn btn-default' id='scenarioaddbtn' href='#' onclick="scenarioaddbtnOnClickAction();" >
                            <span class='icon-add'></span>&nbsp;<?php eT('Add scenario'); ?>
                        </a>
                    <?php endif;?>
                </div>
            </div>

            <div class='form-group'>
                <label class='control-label col-sm-2'><?php eT("Question"); ?></label>
                <div class='col-sm-5'>
                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'editSourceTab',
                        'value'=> '#SRCPREVQUEST',
                        'selectOptions'=>array(
                            '#SRCPREVQUEST' => gT('Previous questions'),
                            '#SRCTOKENATTRS' => gT('Survey participant attributes')
                    )));?>
                </div>
            </div>

            <div id='SRCPREVQUEST' class='form-group question-option'>
                <label class='control-label col-sm-2'><?php eT("Previous questions"); ?></label>
                <div class='col-sm-5'>
                    <select class='form-control' name='cquestions' id='cquestions' size='<?php echo $qcountI;?>'>
                        <?php foreach ($cquestions as $cqn): ?>
                            <option value='<?php echo $cqn[3]; ?>' title="<?php echo htmlspecialchars($cqn[0]); ?>">
                                <?php echo $cqn[0]; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id='SRCTOKENATTRS' class='form-group question-option'>
                <label class='control-label col-sm-2'><?php eT("Survey participant attributes"); ?></label>
                <div class='col-sm-5'>
                    <select class='form-control' name='csrctoken' id='csrctoken' >
                        <?php foreach ($tokenFieldsAndNames as $tokenattr => $tokenattrName): ?>
                            <option value='{TOKEN:<?php echo strtoupper($tokenattr); ?>}' <?php if ($p_csrctoken == '{TOKEN:'.strtoupper($tokenattr).'}'): echo ' selected="selected" '; endif; ?>>
                                <?php echo HTMLEscape($tokenattrName['description']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class='form-group'>
                <label class='control-label col-sm-2'><?php eT("Comparison operator"); ?></label>
                <div class='col-sm-2'>
                    <select class='form-control' name='method' id='method'>
                        <?php foreach ($method as $methodCode => $methodTxt): ?>
                            <option value='<?php echo $methodCode; ?>' <?php if ($methodCode == "=="): echo ' select="selected" '; endif; ?>>
                                <?php echo $methodTxt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class='form-group'>
                <label class='control-label col-sm-2'><?php echo gT("Answer"); ?></label>
                <div class='col-sm-8'>
                    <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                        'name' => 'editTargetTab',
                        'value'=> '#CANSWERSTAB',
                        'selectOptions'=>array(
                            '#CANSWERSTAB' => gT('Predefined'),
                            '#CONST' => gT('Constant'),
                            '#PREVQUESTIONS' => gT('Questions'),
                            '#TOKENATTRS' => gT('Token fields'),
                            //'#REGEXP' => gT('RegExp')
                    )));?>
                </div>
            </div>

            <div id='CANSWERSTAB'  class='form-group answer-option'>
                <label class='control-label col-sm-2' id='canswersLabel'><?php eT("Predefined answer options for this question"); ?></label>
                <div class='col-sm-5'>
                    <select
                        class='form-control'
                        name='canswers[]'
                        <?php if ($subaction != 'editthiscondition'): echo ' multiple '; endif; ?>
                        id='canswers'
                        size='7'
                    >
                    </select>
                </div>
            </div>

            <div id='CONST' class='form-group answer-option'>
                <label class='control-label col-sm-2' id='ConditionConstLabel'><?php eT("Constant value"); ?></label>
                <div class='col-sm-5'>
                    <textarea class='form-control' name='ConditionConst' id='ConditionConst' rows='5' cols='113'><?php echo $EDITConditionConst; ?></textarea>
                </div>
            </div>

            <div id='PREVQUESTIONS'  class='form-group answer-option'>
                <label class='control-label col-sm-2' for='prevQuestionSGQA'><?php eT("Answer from previous question"); ?></label>
                <div class='col-sm-5'>
                    <select class='form-control' name='prevQuestionSGQA' id='prevQuestionSGQA' size='7'>
                        <?php foreach ($cquestions as $cqn): ?>
                            <?php if ($cqn[2] != 'M' && $cqn[2] != 'P'): ?>
                                <!-- Type M or P aren't real fieldnames and thus can't be used in @SGQA@ placehodlers -->
                                <option
                                    value='<?php echo '@' . $cqn[3] . '@'; ?>'
                                    title="<?php echo htmlspecialchars($cqn[0]); ?>"
                                    <?php if ($p_prevquestionsgqa == '@' . $cqn[3] . '@'): echo ' selected="selected" '; endif; ?>
                                    >
                                    <?php echo $cqn[0]; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id='TOKENATTRS'  class='form-group answer-option'>
                <label class='control-label col-sm-2' for='tokenAttr'><?php eT("Attributes of the survey participant"); ?></label>
                <div class='col-sm-5'>
                    <select class='form-control' name='tokenAttr' id='tokenAttr' size='7'>
                        <?php foreach ($tokenFieldsAndNames as $tokenattr => $tokenattrName): ?>
                            <option value='{TOKEN:<?php echo strtoupper($tokenattr); ?>}'>
                                <?php echo HTMLEscape($tokenattrName['description']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id='REGEXP' class='form-group answer-option'>
                <label class='control-label col-sm-2' for='ConditionRegexp'><?php eT("RegExp"); ?></label>
                <div class='col-sm-5'>
                    <textarea name='ConditionRegexp' class='form-control' id='ConditionRegexp' rows='5' cols='113'><?php echo $EDITConditionRegexp; ?></textarea>
                    <div id='ConditionRegexpLabel'>
                        <a href=\"http://manual.limesurvey.org/wiki/Using_regular_expressions\" target=\"_blank\">
                            <?php eT("Regular expression"); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class='form-group'>
                <div class='col-sm-2'></div>
                <div class='col-sm-5'>
                    <input type='reset' class='btn btn-default' id='resetForm' value='<?php eT("Clear"); ?>' />
                    <input type='submit' class='btn btn-default' value='<?php echo $submitLabel; ?>' />

                    <input type='hidden' name='sid' value='<?php echo $iSurveyID; ?>' />
                    <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
                    <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
                    <input type='hidden' name='subaction' value='<?php echo $submitSubaction; ?>' />
                    <input type='hidden' name='cqid' id='cqid' value='' />
                    <input type='hidden' name='cid' id='cid' value='<?php echo $submitcid; ?>' />
                    <input type='hidden' name='canswersToSelect' id='canswersToSelect' value='' />
                </div>
            </div>
        </form>
    </div>
</div>
