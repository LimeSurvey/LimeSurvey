<?php /** @var QuestionTheme $questionTheme */ ?>

<div class="summary-table">

    <!-- Summary Table -->
        <!-- Question Group -->
        <div class="row">
            <div class="col-2">
                <strong>
                    <?php eT('Question group:');?>&nbsp;&nbsp;&nbsp;
                </strong>
            </div>
            <div class="col-10"><em><?php echo flattenText($question->group->group_name);?></em> (ID:<?php echo $question->group->gid;?>)</div>
        </div>

        <!-- Code -->
        <div class="row">
            <div class="col-2">
                <strong>
                    <?php eT("Code:"); ?>
                </strong>
            </div>

            <div class="col-10">
               <?= $question->title; ?>
                <?php if ($question->type != "X") : ?>
                    <?php if ($question->mandatory == "Y") :?>
                        : (<i><?php eT("Mandatory Question"); ?></i>)
                    <?php else : ?>
                            : (<i><?php eT("Optional Question"); ?></i>)
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Question -->
        <div class="row">
            <div class="col-2">
                <strong>
                    <?php eT("Question:"); ?>
                </strong>
            </div>
            <div class="col-10">
                <?php
                    templatereplace(
                        $question->questionl10ns[$question->survey->language]->question,
                        array('QID' => $question->qid),
                        $aReplacementData,
                        'Unspecified',
                        false,
                        $question->qid
                    );
                    echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                ?>
            </div>
        </div>

        <!-- Help -->
        <div class="row">
            <div class="col-2">
                <strong>
                    <?php eT("Help:"); ?>
                </strong>
            </div>
            <div class="col-10">

                <?php
                    if (trim((string) $question->questionl10ns[$question->survey->language]->help) != '')
                    {
                        templatereplace(
                            $question->questionl10ns[$question->survey->language]->help,
                            array('QID' => $question->qid),
                            $aReplacementData,
                            'Unspecified',
                            false,
                            $question->qid
                        );
                        echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                    }
                ?>
            </div>
        </div>

        <!-- Validation -->
        <?php if ($question->preg):?>
            <div class="row">
                <div class="col-2">
                    <strong>
                        <?php eT("Validation:"); ?>
                    </strong>
                </div>
                <div class="col-10">
                   <?= htmlspecialchars((string) $question->preg); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Type -->
        <div class="row">
            <div class="col-2">
                <strong>
                    <?php eT("Type:"); ?>
                </strong>
            </div>
            <div class="col-10">
                <?php
                    echo gT($questionTheme->title) . ' (Type: ' . $questionTheme->question_type . ')';
                ?>
            </div>
        </div>

        <!-- Warning : You need to add answer -->
        <?php if ($answersCount == 0 && (int) ($questionTheme->getDecodedSettings()->answerscales) > 0):?>
        <div class="row">
            <div class="col-2">
            </div>
            <div class="col-10">
                <span class='statusentryhighlight'>
                    <?php eT("Warning:"); ?>
                    <?php eT("You need to add answer options to this question."); ?>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <!--  Warning : You need to add subquestions to this question -->
        <?php  if ($subquestionsCount == 0 && (int) ($questionTheme->getDecodedSettings()->subquestions) > 0): ?>
            <div class="row">
                <div class="col-2"></div>
                <div class="col-10">
                    <span class='statusentryhighlight'>
                        <?php eT("Warning:"); ?>
                        <?php eT("You need to add subquestions to this question."); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Option 'Other' -->
        <?php if ($question->type == "M" or $question->type == "P"):?>
            <div class="row">
                <div class="col-2">
                    <strong>
                        <?php eT("Option 'Other':"); ?>
                    </strong>
                </div>
                <div class="col-10">
                    <?php if ($question->other == "Y"):?>
                        <?php eT("Yes"); ?>
                    <?php else:?>
                        <?php eT("No"); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Mandatory -->
        <?php if (isset($question->mandatory) and ($question->type != "X") and ($question->type != "|")):?>
            <div class="row">
                <div class="col-2">
                    <strong>
                        <?php eT("Mandatory:"); ?>
                    </strong>
                </div>
                <div class="col-10">
                    <?php if ($question->mandatory == "Y") : ?>
                        <?php eT("Yes"); ?>
                    <?php elseif ($question->mandatory == "S") : ?>
                        <?php eT("Soft"); ?>
                    <?php else : ?>
                        <?php eT("No"); ?>
                    <?php endif;  ?>
                </div>
            </div>
        <?php endif; ?>


        <!-- Encrypted -->
        <?php if (isset($question->encrypted)):?>
            <div class="row">
                <div class="col-2">
                    <strong>
                        <?php eT("Encrypted:"); ?>
                    </strong>
                </div>
                <div class="col-10">
                    <?php if ($question->encrypted == "Y") : ?>
                        <?php eT("Yes"); ?>
                    <?php else:?>
                        <?php eT("No"); ?>
                    <?php endif;  ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Condition for this question -->
        <?php if (trim((string) $question->relevance) != '') : ?>
            <div class="row">
                <div class="col-2">
                    <strong>
                    <?php eT("Condition:"); ?>
                    </strong>
                </div>
                <div class="col-10">
                    <?php
                    LimeExpressionManager::ProcessString(
                        "{" . trim((string) $question->relevance) . "}",
                        $question->qid
                    );
                    echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Group Relevance equation -->
        <?php if (trim((string) $question->group->grelevance) != '') : ?>
            <div class="row">
                <div class="col-2"><strong><?php eT("Group relevance:"); ?></strong></div>
                <div class="col-10">
                    <?php
                    LimeExpressionManager::ProcessString(
                        "{" . trim((string) $question->group->grelevance) . "}",
                        $question->qid
                    );
                    echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Advanced Settings -->
        <?php foreach ($advancedSettings as $settings){ ?>
            <?php foreach ($settings as $setting){
                $value = $setting['value'];
                if (!empty($setting['i18n'])) {
                    $value = $setting[$question->survey->language]['value'];
                }
                if($setting['default'] != $value){ ?>
                <div class="row">
                    <div class="col-2">
                        <strong>
                            <?php eT($setting['caption']);?>:
                        </strong>
                    </div>
                    <div class="col-10">
                        <?php

                            if (isset($setting['expression']) && $setting['expression'] > 0) {
                                if ($setting['expression'] == 1) {
                                    LimeExpressionManager::ProcessString($value, $question->qid);
                                } else {
                                    LimeExpressionManager::ProcessString('{' . $value . '}', $question->qid);
                                }
                                echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                            } else {
                                echo htmlspecialchars((string) $value);
                            }
                        ?>
                    </div>
                </div>
            <?php
                }
            }
        } ?>
</div>
