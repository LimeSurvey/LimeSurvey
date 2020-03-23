<?php
/** @var Question $oQuestion */
?>
<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <div class="pagetitle h3"><?php eT('Question summary'); ?>  <small><em><?php echo  $qrrow['title'];?></em> (ID: <?php echo (int) $qid;?>)</small></div>
    <div class="row">
        <div class="col-lg-12 content-right">

            <!-- Summary Table -->
            <table  id='questiondetails' <?php echo $qshowstyle; ?>>

                <!-- Question Group -->
                <tr>
                    <td><strong><?php eT('Question group:');?></strong>&nbsp;&nbsp;&nbsp;</td>
                    <td><em><?php echo flattenText($oQuestion->group->questiongroupl10ns[$baselang]->group_name);?></em> (ID:<?php echo $oQuestion->group->gid;?>)</td>
                </tr>

                <!-- Code -->
                <tr>
                    <td>
                        <strong>
                            <?php eT("Code:"); ?>
                        </strong>
                    </td>

                    <td>
                        <?php echo $qrrow['title']; ?>
                        <?php if ($qrrow['type'] != Question::QT_X_BOILERPLATE_QUESTION): ?>
                            <?php if ($qrrow['mandatory'] == "Y") :?>
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
                            templatereplace($oQuestion->questionl10ns[$baselang]->question,array(),$aReplacementData,'Unspecified', false ,$qid);
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
                            if (trim($oQuestion->questionl10ns[$baselang]->help)!='')
                            {
                                templatereplace($oQuestion->questionl10ns[$baselang]->help,array(),$aReplacementData,'Unspecified', false ,$qid);
                                echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                            }
                        ?>
                    </td>
                </tr>

                <!-- Validation -->
                <?php if ($qrrow['preg']):?>
                    <tr >
                        <td>
                            <strong>
                                <?php eT("Validation:"); ?>
                            </strong>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($qrrow['preg']); ?>
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
                        <?php echo $qtypes[$qrrow['type']]['description']; ?>
                    </td>
                </tr>

                <!-- Warning : You need to add answer -->
                <?php if ($qct == 0 && $qtypes[$qrrow['type']]['answerscales'] >0):?>
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
                <?php  if($sqct == 0 && $qtypes[$qrrow['type']]['subquestions'] >0): ?>
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
                <?php if ($qrrow['type'] == Question::QT_M_MULTIPLE_CHOICE or $qrrow['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS):?>
                    <tr>
                        <td>
                            <strong>
                                <?php eT("Option 'Other':"); ?>
                            </strong>
                        </td>
                        <td>
                            <?php if ($qrrow['other'] == "Y"):?>
                                <?php eT("Yes"); ?>
                            <?php else:?>
                                <?php eT("No"); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <!-- Mandatory -->
                <?php if (isset($qrrow['mandatory']) and ($qrrow['type'] != Question::QT_X_BOILERPLATE_QUESTION) and ($qrrow['type'] != Question::QT_VERTICAL_FILE_UPLOAD)):?>
                    <tr>
                        <td>
                            <strong>
                                <?php eT("Mandatory:"); ?>
                            </strong>
                        </td>
                        <td>
                            <?php if ($qrrow['mandatory'] == "Y") : ?>
                                <?php eT("Yes"); ?>
                            <?php elseif ($qrrow['mandatory'] == "S") : ?>
                                <?php eT("Soft"); ?>
                            <?php else:?>
                                <?php eT("No"); ?>
                            <?php endif;  ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <!-- Encrypted -->
                <?php if (isset($qrrow['mandatory'])):?>
                    <tr>
                        <td>
                            <strong>
                                <?php eT("Encrypted:"); ?>
                            </strong>
                        </td>
                        <td>
                            <?php if ($qrrow['encrypted'] == "Y") : ?>
                                <?php eT("Yes"); ?>
                            <?php else:?>
                                <?php eT("No"); ?>
                            <?php endif;  ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <!-- Relevance equation -->
                <?php if (trim($qrrow['relevance']) != '' && trim($qrrow['relevance']) != '1'): ?>
                    <tr>
                        <td><?php eT("Condition:"); ?></td>
                        <td>
                            <?php
                            LimeExpressionManager::ProcessString("{" . $qrrow['relevance'] . "}", $qid);    // tests Relevance equation so can pretty-print it
                            echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <!-- Group Relevance equation -->
                <?php if (trim($oQuestion->group->grelevance)!='' && trim($oQuestion->group->grelevance)!='1'): ?>
                    <tr>
                        <td><?php eT("Group relevance:"); ?></td>
                        <td>
                            <?php
                            LimeExpressionManager::ProcessString("{" . $oQuestion->group->grelevance . "}", $qid);
                            echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <!-- Advanced Settings -->
                <?php
                    $sCurrentCategory='';
                    foreach ($advancedsettings as $aAdvancedSetting): ?>
                        <tr>
                            <td>
                                <?php echo $aAdvancedSetting['caption'];?>:
                            </td>
                            <td>
                                <?php
                                    if (isset($aAdvancedSetting['expression']) && $aAdvancedSetting['expression']==2){
                                        LimeExpressionManager::ProcessString('{' . $aAdvancedSetting['value'] . '}', $qid);
                                        echo LimeExpressionManager::GetLastPrettyPrintExpression();
                                    } else {
                                        if ($aAdvancedSetting['i18n']==false){
                                            echo htmlspecialchars($aAdvancedSetting['value']);
                                        } else {
                                            echo htmlspecialchars($aAdvancedSetting[$baselang]['value']);
                                        }
                                    }
                                ?>
                            </td>
                        </tr>
                <?php endforeach; ?>
            </table>

            <!-- Quick Actions -->
            <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'update')): ?>
                <div id="survey-action-title" class="pagetitle h3"><?php eT('Question quick actions'); ?></div>
                <div class="row welcome survey-action">
                    <div class="col-lg-12 content-right">

                        <!-- create question in this group -->
                        <div class="col-lg-3">
                            <div class="panel panel-primary <?php if ($surveyIsActive) { echo 'disabled'; } else { echo 'panel-clickable'; } ?>" id="panel-1" data-url="<?php echo $this->createUrl('admin/questions/sa/newquestion/surveyid/'.$surveyid.'/gid/'.$gid); ?>">
                                <div class="panel-heading">
                                    <div class="panel-title h4"><?php eT("Add new question to group");?></div>
                                </div>
                                <div class="panel-body">
                                    <span class="icon-add text-success"  style="font-size: 3em;"></span>
                                    <p class='btn-link'>
                                            <?php eT("Add new question to group");?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
App()->getClientScript()->registerScript(
    'activatePanelClickable', 
    'LS.pageLoadActions.panelClickable()', 
    LSYii_ClientScript::POS_POSTSCRIPT 
)
?>

