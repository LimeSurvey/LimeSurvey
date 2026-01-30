<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('checkIntegrity');
?>

<div class="row">
    <div class="col-12">
        <div class="jumbotron message-box">
            <h2><?php eT("Data consistency check"); ?></h2>
            <p class="lead"><?php eT("If errors are showing up you might have to execute this script repeatedly."); ?></p>
            <p>
            <ul class='data-consistency-list list-unstyled'>
                <?php
                // TMSW Conditions->Relevance:  Update this to use relevance processing results
                if (isset($conditions)) { ?>
                    <li><?php eT("The following conditions should be deleted:"); ?>
                    <ul class="list-unstyled">
                        <?php
                        foreach ($conditions as $condition) { ?>
                            <li>CID:<?php echo $condition['cid'] . ' ' . gT("Reason:") . " {$condition['reason']}"; ?></li><?php
                        } ?>
                    </ul>
                    <?php
                } else { ?>
                    <li><?php eT("All conditions meet consistency standards."); ?></li><?php
                } ?>

                <?php
                if (isset($questionattributes)) { ?>
                    <li><?php printf(gT("There are %s orphaned question attributes."), count($questionattributes)); ?> </li>
                <?php } else { ?>
                    <li><?php eT("All question attributes meet consistency standards."); ?> </li> <?php
                } ?>

                <?php
                if ($defaultvalues) { ?>
                    <li><?php printf(gT("There are %s orphaned default value entries which can be deleted."), $defaultvalues); ?> </li>
                <?php } else { ?>
                    <li><?php eT("All default values meet consistency standards."); ?> </li> <?php
                } ?>

                <?php
                if ($quotas) { ?>
                    <li><?php printf(gT("There are %s orphaned quota entries which can be deleted."), $quotas); ?> </li>
                <?php } else { ?>
                    <li><?php eT("All quotas meet consistency standards."); ?> </li> <?php
                } ?>

                <?php
                if ($quotals) { ?>
                    <li><?php printf(gT("There are %s orphaned quota language settings which can be deleted."), $quotals); ?> </li>
                <?php } else { ?>
                    <li><?php eT("All quota language settings meet consistency standards."); ?> </li> <?php
                } ?>

                <?php
                if ($quotamembers) { ?>
                    <li><?php printf(gT("There are %s orphaned quota members which can be deleted."), $quotamembers); ?> </li>
                <?php } else { ?>
                    <li><?php eT("All quota quota members meet consistency standards."); ?> </li> <?php
                } ?>

                <?php
                if (isset($assessments)) { ?>
                    <li><?php eT("The following assessments should be deleted:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($assessments as $assessment) { ?>
                                <li>AID:<?php echo $assessment['id']; ?><?php eT("Assessment:"); ?><?php eT("Reason:"); ?> <?php echo $assessment['reason']; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All assessments meet consistency standards."); ?></li><?php
                } ?>

                <?php
                if (isset($answers)) { ?>
                    <li><?php eT("The following answers should be deleted:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($answers as $answer) { ?>
                                <li>QID:<?php echo $answer['qid']; ?> <?php eT("Code:"); ?> <?php echo $answer['code']; ?> <?php eT("Reason:"); ?><?php echo $answer['reason']; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All answers meet consistency standards."); ?></li><?php
                } ?>

                <?php
                if (isset($answer_l10ns)) { ?>
                    <li><?php eT("The following answer texts should be deleted:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($answer_l10ns as $answer) { ?>
                                <li>AID:<?php echo $answer['aid']; ?> <?php printf(gT("ID: %s"), $answer['id']); ?> <?php eT("Reason:"); ?><?php echo $answer['reason']; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All answers texts meet consistency standards."); ?></li><?php
                } ?>

                <?php
                if (isset($surveys)) { ?>
                    <li><?php eT("The following surveys should be deleted:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($surveys as $survey) { ?>
                                <li>SID:<?php echo $survey['sid']; ?> <?php eT("Reason:"); ?><?php echo $survey['reason']; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All surveys meet consistency standards."); ?></li><?php
                } ?>

                <?php
                if (isset($surveylanguagesettings)) { ?>
                    <li><?php eT("The following survey language settings should be deleted:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($surveylanguagesettings as $surveylanguagesetting) { ?>
                                <li>SLID:<?php echo $surveylanguagesetting['slid']; ?> <?php eT("Reason:"); ?><?php echo $surveylanguagesetting['reason']; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All survey language settings meet consistency standards."); ?></li><?php
                } ?>

                <?php
                if (isset($questions)) { ?>
                    <li><?php eT("The following questions should be deleted:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($questions as $question) { ?>
                                <li>QID:<?php echo $question['qid']; ?> <?php eT("Reason:"); ?><?php echo $question['reason']; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All questions meet consistency standards."); ?></li><?php
                } ?>

                <?php
                if (isset($question_l10ns)) { ?>
                    <li><?php eT("The following question texts should be deleted:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($question_l10ns as $question) { ?>
                                <li>QID:<?php echo $question['qid']; ?><?php printf(gT("ID: %s"), $question['id']); ?> <?php eT("Reason:"); ?><?php echo $question['reason']; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All question texts meet consistency standards."); ?></li><?php
                } ?>


                <?php if (isset($questionOrderDuplicates) && !empty($questionOrderDuplicates)) : ?>
                    <li><?php eT("The following surveys have an erroneous question order. That could lead to errors during the design and/or processing of the survey. Please go to each question and group respectively, check the question order and save it."); ?>
                    <ul>
                        <?php foreach ($questionOrderDuplicates as $info) : ?>
                            <li>
                                SID: <a href="<?php echo $info['viewSurveyLink']; ?>"><?php echo $info['sid']; ?></a>
                                GID: <a href="<?php echo $info['viewGroupLink']; ?>"><?php echo $info['gid']; ?></a>
                                <?php if ($info['parent_qid'] != 0) : ?>
                                    Parent QID: <a href="<?php echo $info['questionSummaryLink']; ?>"><?php echo $info['parent_qid']; ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <li><?php eT("No issues with question order found."); ?></li>
                <?php endif; ?>

                <?php
                if (isset($groups)) { ?>
                    <li><?php eT("The following question groups should be deleted:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($groups as $group) { ?>
                                <li>GID:<?php echo $group['gid']; ?> <?php eT("Reason:"); ?><?php echo $group['reason']; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All question groups meet consistency standards."); ?></li><?php
                } ?>

                <?php
                if (isset($group_l10ns)) { ?>
                    <li><?php eT("The following question group texts should be deleted:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($group_l10ns as $group) { ?>
                                <li>GID:<?php echo $group['gid']; ?><?php printf(gT("ID: %s"), $group['id']); ?> <?php eT("Reason:"); ?><?php echo $group['reason']; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All question group texts meet consistency standards."); ?></li><?php
                } ?>

                <?php
                if (isset($user_in_groups)) { ?>
                    <li><?php eT("The following user group assignments should be deleted:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($user_in_groups as $user_in_group) { ?>
                                <li>UID:<?php echo $user_in_group['uid']; ?> UGID:<?php echo $user_in_group['ugid']; ?> <?php eT("Reason:"); ?><?php echo $user_in_group['reason']; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All groups meet consistency standards."); ?></li><?php
                } ?>

                <?php if (isset($groupOrderDuplicates) && !empty($groupOrderDuplicates)) : ?>
                    <li><?php eT("The following surveys have an errorneous question group order. Please go to each survey respectively, check the group order and save it."); ?>
                    <ul>
                        <?php foreach ($groupOrderDuplicates as $info) : ?>
                            <li>
                                SID: <a href="<?php echo $info['organizerLink']; ?>"><?php echo $info['sid']; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <li><?php eT("No issues with question group order found."); ?></li>
                <?php endif; ?>

                <?php
                if (isset($orphansurveytables)) { ?>
                    <li><?php eT("The following old survey tables should be deleted because they contain no records or their parent survey no longer exists:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($orphansurveytables as $surveytable) { ?>
                                <li><?php echo $surveytable; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All old survey tables meet consistency standards."); ?></li><?php
                } ?>

                <?php
                if (isset($orphantokentables)) { ?>
                    <li><?php eT("The following old survey participant lists should be deleted because they contain no records or their parent survey no longer exists:"); ?>
                        <ul class="list-unstyled">
                            <?php
                            foreach ($orphantokentables as $tokentable) { ?>
                                <li><?php echo $tokentable; ?></li><?php
                            } ?>
                        </ul>
                    </li>
                    <?php
                } else { ?>
                    <li><?php eT("All old survey participant lists meet consistency standards."); ?></li><?php
                } ?>
            </ul>

            <?php if ($integrityok) { ?>
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT("No database action required!"),
                    'type' => 'success',
                ]);
                ?>
            <?php } else { ?>
                <br/><?php eT("Should we proceed with the delete?"); ?> <br/>
                <?php echo CHtml::form(["admin/checkintegrity", "sa" => 'fixintegrity'], 'post'); ?>
                <button
                    type='submit'
                    value='Y'
                    name='ok'
                    class="btn btn-danger">
                    <?php eT("Yes - Delete Them!"); ?>
                </button>
                </form>
                <?php
            } ?>
        </div>

        <!-- Data redundancy check -->
        <div class="jumbotron message-box">
            <h2><?php eT("Data redundancy check"); ?></h2>
            <p class="lead">
                <?php eT("The redundancy check looks for tables leftover after deactivating a survey. You can delete these if you no longer require them."); ?>
            </p>
            <p>
                <?php if ($redundancyok) { ?>
                    <?php
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'text' => gT("No database action required!"),
                        'type' => 'success',
                    ]);
                    ?>
                <?php } else { ?>
                    <?php echo CHtml::form(["admin/checkintegrity", 'sa' => 'fixredundancy'], 'post'); ?>
            <ul id="data-redundancy-list" class='data-redundancy-list list-unstyled'>
                    <?php
                    if (isset($redundantsurveytables)) { ?>
                    <li><?php eT("The following old survey response tables exist and may be deleted if no longer required:"); ?>
                        <ul class='response-tables-list list-unstyled'>
                                <?php
                                foreach ($redundantsurveytables as $surveytable) { ?>
                                <li>
                                    <input type='checkbox' id='cbox_<?php echo $surveytable['table'] ?>' value='<?php echo $surveytable['table'] ?>' name='oldsmultidelete[]' onclick="toggleDisableState(this)"/>
                                    <label for='cbox_<?php echo $surveytable['table'] ?>'><?php echo $surveytable['details'] ?></label>
                                </li>
                                    <?php
                                } ?>
                        </ul>
                    </li>
                        <?php
                    } ?>

                    <?php
                    if (isset($redundanttokentables) && count($redundanttokentables) > 0) { ?>
                    <li><?php eT("The following old participant lists exist and may be deleted if no longer required:"); ?>
                        <ul class='token-tables-list list-unstyled'>
                                <?php
                                foreach ($redundanttokentables as $tokentable) { ?>
                                <li>
                                    <input type='checkbox' id='cbox_<?php echo $tokentable['table'] ?>' value='<?php echo $tokentable['table'] ?>' name='oldsmultidelete[]'/>
                                    <label for='cbox_<?php echo $tokentable['table'] ?>'><?php echo $tokentable['details'] ?></label>
                                </li>
                                    <?php
                                } ?>
                        </ul>
                    </li>
                        <?php
                    } ?>
            </ul>
             <input type='hidden' name='ok' value='Y' />
            <button id='delete-checked-items-button' type='submit' name='ok' value='Y'
                    class="btn btn-danger mb-2"><?php
                    eT("Delete checked items!"); ?>
            </button>
                    <?php
                    $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT("Note that you cannot undo a delete if you proceed. The data will be gone."),
                    'type' => 'warning',
                    ]);
                    ?>
            </form><?php
                } ?>
        </div>
    </div>
</div>
