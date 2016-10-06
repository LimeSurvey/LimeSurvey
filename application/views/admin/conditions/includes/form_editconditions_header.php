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

<?php
    //TODO: move to script
    $scenarioaddbtnOnClickAction = "$('#scenarioaddbtn').hide(); $('#defaultscenariotxt').hide('slow'); $('#scenario').show('slow');";
?>

<div class="row">
    <div class="col-lg-12">

        <!-- Form  editconditions -->
        <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/{$subaction}/surveyid/{$iSurveyID}/gid/{$gid}/qid/{$qid}/"),'post',array('id'=>"editconditions",'name'=>"editconditions"));?>
            <h4><?php echo $mytitle;?></h4>

            <!-- Condition -->
            <div class='condition-tbl-row'>
                <div class='condition-tbl-left'>
                    <?php if($showScenario):?>
                        <a id='scenarioaddbtn' href='#' onclick="<?php echo $scenarioaddbtnOnClickAction; ?>" >
                            <span class='icon-add'></span>
                        </a>
                    <?php endif;?>
                    <?php eT("Scenario"); ?>
                </div>
                <div class='condition-tbl-right'>
                    <input type='text' name='scenario' id='scenario' value='1' size='2' <?php if($showScenario):?> style = 'display: none;' <?php endif;?>/>
                    <?php if($showScenario):?>
                        <span id='defaultscenariotxt'>
                            <?php eT("Default scenario"); ?>
                        </span>
                    <?php endif;?>
                </div>
            </div>


            <div class='condition-tbl-row'>
                <div class='condition-tbl-left'>
                    <?php eT("Question"); ?>
                </div>

                <div class='condition-tbl-right'>
                    <div id="conditionsource">
                        <ul class='nav nav-tabs'>
                            <li  role='presentation' class='active'>
                                <a data-toggle='tab' href="#SRCPREVQUEST">
                                    <span>
                                        <?php eT("Previous questions"); ?>
                                    </span>
                                </a>
                            </li>

                            <li role='presentation'>
                                <a data-toggle='tab'href="#SRCTOKENATTRS">
                                    <span>
                                        <?php eT("Survey participant attributes"); ?>
                                    </span>
                                </a>
                            </li>
                        </ul>


                        <div class="tab-content">
                            <div id='SRCPREVQUEST' class='tab-pane fade in active'>
                                <select class='form-control' name='cquestions' id='cquestions' size='<?php echo $qcountI;?>'>
                                    <?php foreach ($cquestions as $cqn): ?>
                                        <option value='<?php echo $cqn[3]; ?>' title="<?php echo htmlspecialchars($cqn[0]); ?>">
                                            <?php echo $cqn[0]; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id='SRCTOKENATTRS' class='tab-pane fade in'>
                                <select class='form-control' name='csrctoken' id='csrctoken' >
                                    <?php foreach ($tokenFieldsAndNames as $tokenattr => $tokenattrName): ?>
                                        <option value='{TOKEN:<?php echo strtoupper($tokenattr); ?>}' <?php if ($p_csrctoken == '{TOKEN:'.strtoupper($tokenattr).'}'): echo ' selected="selected" '; endif; ?>>
                                            <?php echo HTMLEscape($tokenattrName['description']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='condition-tbl-row'>
            <div class='condition-tbl-left'><?php eT("Comparison operator"); ?></div>
                <div class='condition-tbl-right'>
                    <select class='form-control' name='method' id='method'>
                        <?php foreach ($method as $methodCode => $methodTxt): ?>
                            <option value='<?php echo $methodCode; ?>' <?php if ($methodCode == "=="): echo ' select="selected" '; endif; ?>>
                                <?php echo $methodTxt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class='condition-tbl-row'>
                <div class='condition-tbl-left'><?php echo gT("Answer"); ?></div>
                    <div class='condition-tbl-right'>
                        <div id="conditiontarget">
                            <ul class='nav nav-tabs'>
                                <li role='presentation' class='active'><a data-toggle='tab' href="#CANSWERSTAB"><span><?php eT("Predefined"); ?></span></a></li>
                                <li role='presentation'><a data-toggle='tab' href="#CONST"><span><?php eT("Constant"); ?></span></a></li>
                                <li role='presentation'><a data-toggle='tab' href="#PREVQUESTIONS"><span><?php eT("Questions"); ?></span></a></li>
                                <li role='presentation'><a data-toggle='tab' href="#TOKENATTRS"><span><?php eT("Token fields"); ?></span></a></li>
                                <li role='presentation'><a data-toggle='tab' href="#REGEXP"><span><?php eT("RegExp"); ?></span></a></li>
                            </ul>
                            <div class='tab-content'>
                                <div id='CANSWERSTAB'  class='tab-pane fade in active'>
                                    <select class='form-control'  name='canswers[]' <?php if ($subaction != 'editthiscondition'): echo ' multiple '; endif; ?> id='canswers' size='7'>
                                    </select>
                                    <br />
                                    <span id='canswersLabel'><?php eT("Predefined answer options for this question"); ?></span>
                                </div>
                                <div id='CONST' class='tab-pane fade in'>
                                <textarea name='ConditionConst' id='ConditionConst' rows='5' cols='113'><?php echo $EDITConditionConst; ?></textarea>
                                    <br />
                                    <div id='ConditionConstLabel'><?php eT("Constant value"); ?></div>
                                </div>
                                <div id='PREVQUESTIONS'  class='tab-pane fade in'>
                                    <label for='prevQuestionSGQA'><?php eT("Answer from previous question"); ?></label>
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
                                <div id='TOKENATTRS'  class='tab-pane fade in'>
                                    <label for='tokenAttr'><?php eT("Attributes of the survey participant"); ?></label>
                                    <select class='form-control' name='tokenAttr' id='tokenAttr' size='7'>
                                        <?php foreach ($tokenFieldsAndNames as $tokenattr => $tokenattrName): ?>
                                            <option value='{TOKEN:<?php echo strtoupper($tokenattr); ?>}'>
                                                <?php echo HTMLEscape($tokenattrName['description']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id='REGEXP' class='tab-pane fade in'>
                                    <textarea name='ConditionRegexp' id='ConditionRegexp' rows='5' cols='113'><?php echo $EDITConditionRegexp; ?></textarea>
                                    <br />
                                    <div id='ConditionRegexpLabel'>
                                        <a href=\"http://manual.limesurvey.org/wiki/Using_regular_expressions\" target=\"_blank\"><?php eT("Regular expression"); ?></a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='condition-tbl-full'>
                        <input type='reset' class='btn btn-default' id='resetForm' value='<?php eT("Clear"); ?>' />
                        <input type='submit' class='btn btn-default' value='<?php echo $submitLabel; ?>' />
                            <input type='hidden' name='sid' value='<?php echo $iSurveyID; ?>' />
                            <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
                            <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
                            <input type='hidden' name='subaction' value='<?php echo $submitSubaction; ?>' />
                            <input type='hidden' name='cqid' id='cqid' value='' />
                            <input type='hidden' name='cid' id='cid' value='<?php echo $submitcid; ?>' />
                            <input type='hidden' name='editTargetTab' id='editTargetTab' value='' />
                            <input type='hidden' name='editSourceTab' id='editSourceTab' value='' />
                            <input type='hidden' name='canswersToSelect' id='canswersToSelect' value='' />
                        </div>
                    </form>
