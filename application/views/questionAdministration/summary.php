<?php /** @var QuestionTheme $questionTheme */ ?>

<div class="col-lg-12 content-right">

    <!-- Summary Table -->
    <table  id='questiondetails'>

        <!-- Question Group -->
        <tr>
            <td><strong><?php eT('Question group:');?></strong>&nbsp;&nbsp;&nbsp;</td>
            <td><em><?php echo flattenText($question->group->group_name);?></em> (ID:<?php echo $question->group->gid;?>)</td>
        </tr>

        <!-- Code -->
        <tr>
            <td>
                <strong>
                    <?php eT("Code:"); ?>
                </strong>
            </td>

            <td>
                <?php echo $question->title; ?>
                <?php if ($question->type != "X"): ?>
                    <?php if ($question->mandatory == "Y") :?>
                        : (<i><?php eT("Mandatory Question"); ?></i>)
                    <?php else: ?>
                            : (<i><?php eT("Optional Question"); ?></i>)
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>

        <!-- Question -->
        <tr>
            <td>
                <strong>
                    <?php eT("Question:"); ?>
                </strong>
            </td>
            <td>
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
            </td>
        </tr>

        <!-- Help -->
        <tr>
            <td>
                <strong>
                    <?php eT("Help:"); ?>
                </strong>
            </td>
            <td>

                <?php
                    if (trim($question->questionl10ns[$question->survey->language]->help) != '')
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
            </td>
        </tr>

        <!-- Validation -->
        <?php if ($question->preg):?>
            <tr >
                <td>
                    <strong>
                        <?php eT("Validation:"); ?>
                    </strong>
                </td>
                <td>
                    <?php echo htmlspecialchars($question->preg); ?>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Type -->
        <tr>
            <td>
                <strong>
                    <?php eT("Type:"); ?>
                </strong>
            </td>
            <td>
                <?php
                    echo gT($questionTheme->title) . ' (Type: ' . $questionTheme->question_type . ')';
                //echo $questionTypes[$question->type]['description'];
                ?>
            </td>
        </tr>

        <!-- Warning : You need to add answer -->
        <?php if ($answersCount == 0 && (int) ($questionTheme->getDecodedSettings()->answerscales) > 0):?>
        <tr>
            <td>
            </td>
            <td>
                <span class='statusentryhighlight'>
                    <?php eT("Warning"); ?>:
                    <?php eT("You need to add answer options to this question"); ?>
                    <span class="icon-answers text-success" title='<?php eT("Edit answer options for this question"); ?>'></span>
                </span>
            </td>
        </tr>
        <?php endif; ?>

        <!--  Warning : You need to add subquestions to this question -->
        <?php  if ($subquestionsCount == 0 && (int) ($questionTheme->getDecodedSettings()->subquestions) > 0): ?>
            <tr>
                <td></td>
                <td>
                    <span class='statusentryhighlight'>
                        <?php eT("Warning"); ?>:
                        <?php eT("You need to add subquestions to this question"); ?>
                        <span class="icon-defaultanswers text-success" title='<?php eT("Edit subquestions for this question"); ?>' ></span>
                    </span>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Option 'Other' -->
        <?php if ($question->type == "M" or $question->type == "P"):?>
            <tr>
                <td>
                    <strong>
                        <?php eT("Option 'Other':"); ?>
                    </strong>
                </td>
                <td>
                    <?php if ($question->other == "Y"):?>
                        <?php eT("Yes"); ?>
                    <?php else:?>
                        <?php eT("No"); ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Mandatory -->
        <?php if (isset($question->mandatory) and ($question->type != "X") and ($question->type != "|")):?>
            <tr>
                <td>
                    <strong>
                        <?php eT("Mandatory:"); ?>
                    </strong>
                </td>
                <td>
                    <?php if ($question->mandatory == "Y") : ?>
                        <?php eT("Yes"); ?>
                    <?php else:?>
                        <?php eT("No"); ?>
                    <?php endif;  ?>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Condition for this question -->
        <?php if (trim($question->relevance) != ''): ?>
            <tr>
                <td>
                    <strong>
                    <?php eT("Condition:"); ?>
                    </strong>
                </td>
                <td>
                    <?php
                    LimeExpressionManager::ProcessString("{" . $question->relevance . "}", $question->qid);    // tests Relevance equation so can pretty-print it
                    echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                    ?>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Group Relevance equation -->
        <?php if (trim($question->group->grelevance)!=''): ?>
            <tr>
                <td><strong><?php eT("Group relevance:"); ?></strong></td>
                <td>
                    <?php
                    LimeExpressionManager::ProcessString("{" . $question->group->grelevance . "}", $question->qid);
                    echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                    ?>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Advanced Settings -->
        <?php foreach ($advancedSettings as $settings){ ?>
            <?php foreach ($settings as $setting){

                if($setting['default'] != $setting['value']){ ?>
                <tr>
                    <td>
                        <strong>
                            <?php echo $setting['caption'];?>:
                        </strong>
                    </td>
                    <td>
                        <?php
                            if (isset($setting['expression']) && $setting['expression'] > 0) {
                                if ($setting['expression'] == 1) {
                                    LimeExpressionManager::ProcessString($setting['value'], $question->qid);
                                } else {
                                    LimeExpressionManager::ProcessString('{' . $setting['value'] . '}', $question->qid);
                                }
                                echo LimeExpressionManager::GetLastPrettyPrintExpression();
                            } else {
                                if (($setting['i18n'] ==null) || ($setting['i18n'] == false)) {
                                    echo htmlspecialchars($setting['value']);
                                } else {
                                    echo htmlspecialchars($setting[$question->survey->language]['value']);
                                }
                            }
                        ?>
                    </td>
                </tr>
            <?php
                }
            }
        } ?>
    </table>
</div>
