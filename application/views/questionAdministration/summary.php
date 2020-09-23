<div class="col-lg-12 content-right">

    <!-- Summary Table -->
    <table  id='questiondetails'>

        <!-- Question Group -->
        <tr>
            <td><strong><?php eT('Question group:');?></strong>&nbsp;&nbsp;&nbsp;</td>
            <td><em><?php echo flattenText($oQuestion->group->group_name);?></em> (ID:<?php echo $oQuestion->group->gid;?>)</td>
        </tr>

        <!-- Code -->
        <tr>
            <td>
                <strong>
                    <?php eT("Code:"); ?>
                </strong>
            </td>

            <td>
                <?php echo $oQuestion->title; ?>
                <?php if ($oQuestion->type != "X"): ?>
                    <?php if ($oQuestion->mandatory == "Y") :?>
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
                        $oQuestion->questionl10ns[$oSurvey->language]->question,
                        array('QID' => $oQuestion->qid),
                        $aReplacementData,
                        'Unspecified',
                        false,
                        $oQuestion->qid
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
                    if (trim($oQuestion->questionl10ns[$oSurvey->language]->help) != '')
                    {
                        templatereplace(
                            $oQuestion->questionl10ns[$oSurvey->language]->help,
                            array('QID' => $oQuestion->qid),
                            $aReplacementData,
                            'Unspecified',
                            false,
                            $oQuestion->qid
                        );
                        echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                    }
                ?>
            </td>
        </tr>

        <!-- Validation -->
        <?php if ($oQuestion->preg):?>
            <tr >
                <td>
                    <strong>
                        <?php eT("Validation:"); ?>
                    </strong>
                </td>
                <td>
                    <?php echo htmlspecialchars($oQuestion->preg); ?>
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
                <?php echo $questionTypes[$oQuestion->type]['description']; ?>
            </td>
        </tr>

        <!-- Warning : You need to add answer -->
        <?php if ($answersCount == 0 && $questionTypes[$oQuestion->type]['answerscales'] > 0):?>
        <tr>
            <td>
            </td>
            <td>
                <span class='statusentryhighlight'>
                    <?php eT("Warning"); ?>:
                    <a href='<?php echo $this->createUrl("admin/questions/sa/answeroptions/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>'>
                        <?php eT("You need to add answer options to this question"); ?>
                        <span class="icon-answers text-success" title='<?php eT("Edit answer options for this question"); ?>'></span>
                    </a>
                </span>
            </td>
        </tr>
        <?php endif; ?>

        <!--  Warning : You need to add subquestions to this question -->
        <?php  if ($subquestionsCount == 0 && $questionTypes[$oQuestion->type]['subquestions'] > 0): ?>
            <tr>
                <td></td>
                <td>
                    <span class='statusentryhighlight'>
                        <?php eT("Warning"); ?>:
                        <a href='<?php echo $this->createUrl("admin/questions/sa/subquestions/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>'>
                            <?php eT("You need to add subquestions to this question"); ?>
                            <span class="icon-defaultanswers text-success" title='<?php eT("Edit subquestions for this question"); ?>' ></span>
                        </a>
                    </span>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Option 'Other' -->
        <?php if ($oQuestion->type == "M" or $oQuestion->type == "P"):?>
            <tr>
                <td>
                    <strong>
                        <?php eT("Option 'Other':"); ?>
                    </strong>
                </td>
                <td>
                    <?php if ($oQuestion->other == "Y"):?>
                        <?php eT("Yes"); ?>
                    <?php else:?>
                        <?php eT("No"); ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Mandatory -->
        <?php if (isset($oQuestion->mandatory) and ($oQuestion->type != "X") and ($oQuestion->type != "|")):?>
            <tr>
                <td>
                    <strong>
                        <?php eT("Mandatory:"); ?>
                    </strong>
                </td>
                <td>
                    <?php if ($oQuestion->mandatory == "Y") : ?>
                        <?php eT("Yes"); ?>
                    <?php else:?>
                        <?php eT("No"); ?>
                    <?php endif;  ?>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Relevance equation -->
        <?php if (trim($oQuestion->relevance) != ''): ?>
            <tr>
                <td><?php eT("Relevance equation:"); ?></td>
                <td>
                    <?php
                    LimeExpressionManager::ProcessString("{" . $oQuestion->relevance . "}", $oQuestion->qid);    // tests Relevance equation so can pretty-print it
                    echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                    ?>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Group Relevance equation -->
        <?php if (trim($oQuestion->group->grelevance)!=''): ?>
            <tr>
                <td><?php eT("Group relevance:"); ?></td>
                <td>
                    <?php
                    LimeExpressionManager::ProcessString("{" . $oQuestion->group->grelevance . "}", $oQuestion->qid);
                    echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                    ?>
                </td>
            </tr>
        <?php endif; ?>

        <!-- Advanced Settings -->
        <?php foreach ($advancedSettings as $settings): ?>
            <?php foreach ($settings as $setting): ?>
                <tr>
                    <td>
                        <?php echo $setting['title']; ?>:
                    </td>
                    <td>
                        <?php
                            if (isset($setting->expression) && $setting->expression == 2) {
                                LimeExpressionManager::ProcessString('{' . $setting->value . '}', $oQuestion->qid);
                                echo LimeExpressionManager::GetLastPrettyPrintExpression();
                            } else {
                                //if ($setting->aFormElementOptions->i18n == false) {
                                    //echo htmlspecialchars($setting->aFormElementOptions->value);
                                //} else {
                                    //echo htmlspecialchars($setting['aFormElementOptions'][$oSurvey->language]['value']);
                                //}
                            }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>
</div>
