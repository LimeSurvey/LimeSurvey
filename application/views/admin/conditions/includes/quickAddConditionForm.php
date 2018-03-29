<div class="container-fluid">
    <?php echo CHtml::form(array("/admin/conditions/sa/index/subaction/{$subaction}/surveyid/{$iSurveyID}/gid/{$gid}/qid/{$qid}/"),'post',array('id'=>"quick-add-conditions-form",'name'=>"quick-add-conditions-form", 'class' => 'form '));?>
    
        <div class="row">
        <!-- Form quick-add condition -->
            <div class='form-group'>
                <div class='col-sm-2'></div>
                <div class='col-sm-10'>
                </div>
            </div>

            <!-- Condition -->
            <div class='form-group'>
                <label class='control-label col-sm-2'><?php eT('Scenario'); ?></label>
                <div class='col-sm-2 add-scenario-column'>
                    <input class='form-control' type='number' name='quick-add-scenario' id='quick-add-scenario' value='1' />
                </div>
            </div>
        </div>
        <div class="row ls-space margin top-10 bottom-5">
            <div class='form-group row'>
                <label class='control-label col-sm-2'><?php eT("Question"); ?></label>
                <div class='col-sm-10'>
                    <ul class='nav nav-tabs'>
                        <li role='presentation' class='active src-tab'>
                            <a href='#QUICKADD-SRCPREVQUEST' aria-controls='SRCPREVQUEST' role='tab' data-toggle='tab'><?php eT('Previous questions'); ?></a>
                        </li>
                        <li role='presentation' class='src-tab'>
                            <a href='#QUICKADD-SRCTOKENATTRS' aria-controls='SRCTOKENATTRS' role='tab' data-toggle='tab'><?php eT('Survey participant attributes'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class='tab-content'>
                <div role='tabpanel' class='tab-pane active' id='QUICKADD-SRCPREVQUEST'>
                    <div class='form-group question-option'>
                        <div class='col-sm-2'></div>
                        <div class='col-sm-10'>
                            <select class='form-control' name='quick-add-cquestions' id='quick-add-cquestions' size='7'>
                                <?php foreach ($cquestions as $cqn): ?>
                                    <option value='<?php echo $cqn[3]; ?>' title="<?php echo htmlspecialchars($cqn[0]); ?>">
                                        <?php echo $cqn[0]; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div role='tabpanel' class='tab-pane ' id='QUICKADD-SRCTOKENATTRS'>
                    <div class='form-group question-option'>
                        <div class='col-sm-2'></div>
                        <div class='col-sm-10'>
                            <select class='form-control' name='quick-add-csrctoken' id='quick-add-csrctoken' size='7'>
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
        <div class="row ls-space margin top-10">
            <div class='form-group'>
                <label class='control-label col-sm-2'><?php eT("Comparison operator"); ?></label>
                <div class='col-sm-4'>
                    <select class='form-control' name='quick-add-method' id='quick-add-method'>
                        <?php foreach ($method as $methodCode => $methodTxt): ?>
                            <option value='<?php echo $methodCode; ?>' <?php if ($methodCode == "=="): echo ' selected="selected" '; endif; ?>>
                                <?php echo $methodTxt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class='form-group row'>
                <label class='control-label col-sm-2'><?php echo gT("Answer"); ?></label>
                <div class='col-sm-10'>
                    <ul class='nav nav-tabs'>
                        <li role='presentation' class='active target-tab'>
                            <a href='#QUICKADD-CANSWERSTAB' aria-controls='CANSWERSTAB' role='tab' data-toggle='tab'><?php eT('Predefined'); ?></a>
                        </li>
                        <li role='presentation' class='target-tab'>
                            <a href='#QUICKADD-CONST' aria-controls='CONST' role='tab' data-toggle='tab'><?php eT('Constant'); ?></a>
                        </li>
                        <li role='presentation' class='target-tab'>
                            <a href='#QUICKADD-PREVQUESTIONS' aria-controls='PREVQUESTIONS' role='tab' data-toggle='tab'><?php eT('Questions'); ?></a>
                        </li>
                        <li role='presentation' class='target-tab'>
                            <a href='#QUICKADD-TOKENATTRS' aria-controls='TOKENATTRS' role='tab' data-toggle='tab'><?php eT('Token fields'); ?></a>
                        </li>
                        <li role='presentation' class='target-tab disabled'>
                            <a href='#QUICKADD-REGEXP' aria-controls='REGEXP' role='tab' data-toggle='tab'><?php eT('RegExp'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class='tab-content'>
                <div role='tabpanel' class='tab-pane active' id='QUICKADD-CANSWERSTAB'>
                    <div class='form-group answer-option'>
                        <div class='col-sm-2'></div>
                        <div class='col-sm-10'>
                            <select
                                class='form-control'
                                name='quick-add-canswers[]'
                                <?php if ($subaction != 'editthiscondition'): echo ' multiple '; endif; ?>
                                id='quick-add-canswers'
                                size='7'
                            >
                            </select>
                        </div>
                    </div>
                </div>

                <div role='tabpanel' class='tab-pane active' id='QUICKADD-CONST'>
                    <div class='form-group answer-option'>
                        <div class='col-sm-2'></div>
                        <div class='col-sm-10'>
                            <textarea class='form-control' name='quick-add-ConditionConst' id='quick-add-ConditionConst' rows='5' cols='113'></textarea>
                        </div>
                    </div>
                </div>

                <div role='tabpanel' class='tab-pane active' id='QUICKADD-PREVQUESTIONS'>
                    <div class='form-group answer-option'>
                        <div class='col-sm-2'></div>
                        <div class='col-sm-10'>
                            <select class='form-control' name='quick-add-prevQuestionSGQA' id='quick-add-prevQuestionSGQA' size='7'>
                                <?php foreach ($cquestions as $cqn): ?>
                                    <?php if ($cqn[2] != 'M' && $cqn[2] != 'P'): ?>
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

                <div role='tabpanel' class='tab-pane' id='QUICKADD-TOKENATTRS'>
                    <div class='form-group answer-option'>
                        <div class='col-sm-2'></div>
                        <div class='col-sm-10'>
                            <select class='form-control' name='quick-add-tokenAttr' id='quick-add-tokenAttr' size='7'>
                                <?php foreach ($tokenFieldsAndNames as $tokenattr => $tokenattrName): ?>
                                    <option value='{TOKEN:<?php echo strtoupper($tokenattr); ?>}'>
                                        <?php echo HTMLEscape($tokenattrName['description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div role='tabpanel' class='tab-pane active' id='QUICKADD-REGEXP'>
                    <div class='form-group answer-option'>
                        <div class='col-sm-2'></div>
                        <div class='col-sm-10'>
                            <textarea name='quick-add-ConditionRegexp' class='form-control' id='quick-add-ConditionRegexp' rows='5' cols='113'></textarea>
                            <div id='quick-add-ConditionRegexpLabel'>
                                <a href="http://manual.limesurvey.org/Using_regular_expressions" target="_blank">
                                    <?php eT("Regular expression"); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class='form-group '>
                <div class='col-sm-2'></div>
                <div class='col-sm-10'>
                    <input type='hidden' name='quick-add-sid' value='<?php echo $iSurveyID; ?>' />
                    <input type='hidden' name='quick-add-gid' value='<?php echo $gid; ?>' />
                    <input type='hidden' name='quick-add-qid' value='<?php echo $qid; ?>' />
                    <input type='hidden' name='quick-add-cqid' id='quick-add-cqid' value='' />
                    <input type='hidden' name='quick-add-canswersToSelect' id='quick-add-canswersToSelect' value='' />
                    <input type='hidden' name='quick-add-editSourceTab' id='quick-add-editSourceTab' value='#QUICKADD-SRCPREVQUEST' />
                    <input type='hidden' name='quick-add-editTargetTab' id='quick-add-editTargetTab' value='#QUICKADD-CANSWERSTAB' />
                </div>
            </div>
        </form>
    </div>
</div>
