<?php
/**
 * View for the form 'editconditions' header.
 *
 * @var $subaction
 * @var $iSurveyID
 * @var $gid
 * @var $qid
 * @var $title
 * @var $showScenario                   ( $subaction != "editthiscondition" && isset($scenariocount) && ($scenariocount == 1 || $scenariocount==0)) ||( $subaction == "editthiscondition" && isset($scenario) && $scenario == 1)
 */
?>

<hr class="ls-space margin top-35 bottom-25"/>
<?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/{$subaction}/surveyid/{$iSurveyID}/gid/{$gid}/qid/{$qid}/"),'post',array('id'=>"editconditions",'name'=>"editconditions", 'class' => 'form'));?>
    <div class="row ">
    <!-- Form  editconditions -->
        <div class='col-12 h4'>
            <u><?php echo $title;?></u>
        </div>
    </div>
    <!-- Scenario selection -->
    <div class="row ">
        <!-- Condition -->
        <div class='mb-3'>
            <label class='form-label col-12'><?php eT('Scenario'); ?></label>
            <div class='add-scenario-column col-12  ls-space padding bottom-15'>
                <input class='form-control' type='number' name='scenario' id='scenario' value='<?php echo ($addConditionToScenarioNr ? $addConditionToScenarioNr : '1'); ?>' <?php if($showScenario):?> style='display: none;' <?php endif;?>/>
                <?php if($showScenario):?>
                    <div id="defaultscenarioshow" class="col-12">
                        <span>
                            <?php eT("Default scenario"); ?>
                        </span>
                        &nbsp;
                        <button class='btn btn-outline-secondary' onclick="scenarioaddbtnOnClickAction(); return false;" >
                            <span class='ri-add-circle-fill'></span>&nbsp;<?php eT('Add scenario'); ?>
                        </button>
                    </div>
                <?php endif;?>
            </div>
        </div>
    </div>


    <!-- Comparison operator -->
    <div class="row">
        <div class='mb-3 col-12'>
            <label class='form-label'><?php eT("Comparison operator"); ?></label>
            <div class=''>
                <select class='form-select' name='method' id='method'>
                    <?php foreach ($method as $methodCode => $methodTxt): ?>
                        <option value='<?php echo $methodCode; ?>' <?php if ($methodCode == "=="): echo ' selected="selected" '; endif; ?>>
                            <?php echo $methodTxt; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Question and answer selection  -->
    <div class="row">
        <!-- Question section -->
        <div class="col-xl-6">
            <div class='mb-3'>
                <label class='form-label '><?php eT("Question"); ?></label>
                <div class=''>
                    <ul class='nav nav-tabs'>
                        <li class='nav-item src-tab' role='presentation'>
                            <a class='nav-link active' href="#SRCPREVQUEST" aria-controls='SRCPREVQUEST' role='tab' data-bs-toggle='tab'><?php eT('Previous questions'); ?></a>
                        </li>
                        <li class='nav-item src-tab' role='presentation'>
                            <a class="nav-link" href='#SRCTOKENATTRS' aria-controls='SRCTOKENATTRS' role='tab' data-bs-toggle='tab'><?php eT('Survey participant attributes'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class='tab-content'>
                <div role='tabpanel' class='tab-pane show active' id='SRCPREVQUEST'>
                    <div class='mb-3 question-option'>
                        <div class=''>
                            <select class='form-select' name='cquestions' id='cquestions' size='7'>
                                <?php foreach ($cquestions as $cqn): ?>
                                    <option value='<?php echo $cqn[3]; ?>' title="<?php echo htmlspecialchars((string) $cqn[0]); ?>">
                                        <?php echo $cqn[0]; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div role='tabpanel' class='tab-pane ' id='SRCTOKENATTRS'>
                    <?php if($surveyIsAnonymized) {
                        $this->widget('ext.AlertWidget.AlertWidget', [
                            'text' => gT("This is an anonymized survey. Participant attributes can only be used in non-anonymised surveys."),
                            'type' => 'warning',
                        ]);
                    }?>
                    <div class='mb-3 question-option'>
                        <div class=''>
                            <select class='form-select' name='csrctoken' id='csrctoken' size='7'>
                                <?php foreach ($tokenFieldsAndNames as $tokenattr => $tokenattrName): ?>
                                    <option value='{TOKEN:<?php echo strtoupper((string) $tokenattr); ?>}' <?php if ($p_csrctoken == '{TOKEN:'.strtoupper((string) $tokenattr).'}'): echo ' selected="selected" '; endif; ?>>
                                        <?php echo HTMLEscape($tokenattrName['description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Answer section -->
        <div class="col-xl-6">
            <div class='mb-3'>
                <label class='form-label'><?php echo gT("Answer"); ?></label>
                <div class=''>
                    <ul class='nav nav-tabs'>
                        <li role='presentation' class='nav-item target-tab'>
                            <a class="nav-link active" href='#CANSWERSTAB' aria-controls='CANSWERSTAB' role='tab' data-bs-toggle='tab'><?php eT('Predefined'); ?></a>
                        </li>
                        <li role='presentation' class='nav-item target-tab'>
                            <a class="nav-link" href='#CONST' aria-controls='CONST' role='tab' data-bs-toggle='tab'><?php eT('Constant'); ?></a>
                        </li>
                        <li role='presentation' class='nav-item target-tab'>
                            <a class="nav-link" href='#PREVQUESTIONS' aria-controls='PREVQUESTIONS' role='tab' data-bs-toggle='tab'><?php eT('Questions'); ?></a>
                        </li>
                        <li role='presentation' class='nav-item target-tab'>
                            <a class="nav-link" href='#TOKENATTRS' aria-controls='TOKENATTRS' role='tab' data-bs-toggle='tab'><?php eT('Participant fields'); ?></a>
                        </li>
                        <li role='presentation' class='nav-item target-tab'>
                            <a class="nav-link disabled" href='#REGEXP' aria-controls='REGEXP' role='tab' data-bs-toggle='tab'><?php eT('RegExp'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class='tab-content'>
                <div role='tabpanel' class='tab-pane show active' id='CANSWERSTAB'>
                    <div class='mb-3 answer-option'>
                        <div class=''></div>
                        <div class=''>
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
                </div>

                <div role='tabpanel' class='tab-pane' id='CONST'>
                    <div class='mb-3 answer-option'>
                        <div class=''></div>
                        <div class=''>
                            <textarea class='form-control' name='ConditionConst' id='ConditionConst' rows='5' cols='113'><?php echo $EDITConditionConst; ?></textarea>
                        </div>
                    </div>
                </div>

                <div role='tabpanel' class='tab-pane' id='PREVQUESTIONS'>
                    <div class='mb-3 answer-option'>
                        <div class=''></div>
                        <div class=''>
                            <select class='form-select' name='prevQuestionSGQA' id='prevQuestionSGQA' size='7'>
                                <?php foreach ($cquestions as $cqn): ?>
                                <?php if ($cqn[2] != Question::QT_M_MULTIPLE_CHOICE && $cqn[2] != Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS): ?>
                                        <!-- Type M or P aren't real fieldnames and thus can't be used in @SGQA@ placehodlers -->
                                        <option
                                            value='<?php echo '@' . $cqn[3] . '@'; ?>'
                                            title="<?php echo HTMLEscape($cqn[0]); ?>"
                                            <?php if ($p_prevquestionsgqa == '@' . $cqn[3] . '@'): echo ' selected="selected" '; endif; ?>
                                            >
                                            <?php echo HTMLEscape($cqn[0]); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div role='tabpanel' class='tab-pane' id='TOKENATTRS'>
                    <div class='mb-3 answer-option'>
                        <div class=''></div>
                        <div class=''>
                            <select class='form-select' name='tokenAttr' id='tokenAttr' size='7'>
                                <?php foreach ($tokenFieldsAndNames as $tokenattr => $tokenattrName): ?>
                                    <option value='{TOKEN:<?php echo strtoupper((string) $tokenattr); ?>}'>
                                        <?php echo HTMLEscape($tokenattrName['description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div role='tabpanel' class='tab-pane' id='REGEXP'>
                    <div class='mb-3 answer-option'>
                        <div class=''></div>
                        <div class=''>
                            <textarea name='ConditionRegexp' class='form-control' id='ConditionRegexp' rows='5' cols='113'><?php echo $EDITConditionRegexp; ?></textarea>
                            <div id='ConditionRegexpLabel'>
                                <a href="http://manual.gitit-tech.com/Using_regular_expressions" target="_blank">
                                    <?php eT("Regular expression"); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class='mb-3 col-12'>
            <div class=''></div>
            <div class=''>
                <input type='reset' class='btn btn-outline-secondary' id='resetForm' value='<?php eT("Clear"); ?>' />
                <input type='submit' class='btn btn-outline-secondary' value='<?php echo $submitLabel; ?>' />

                <input type='hidden' name='sid' value='<?php echo $iSurveyID; ?>' />
                <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
                <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
                <input type='hidden' name='subaction' value='<?php echo $submitSubaction; ?>' />
                <input type='hidden' name='cqid' id='cqid' value='' />
                <input type='hidden' name='cid' id='cid' value='<?php echo $submitcid; ?>' />
                <input type='hidden' name='canswersToSelect' id='canswersToSelect' value='' />
                <input type='hidden' name='editSourceTab' id='editSourceTab' value='<?php echo $editSourceTab; ?>' />
                <input type='hidden' name='editTargetTab' id='editTargetTab' value='<?php echo $editTargetTab; ?>' />
            </div>
        </div>
    </div>
</form>
