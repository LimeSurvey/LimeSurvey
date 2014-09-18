<div class='messagebox ui-corner-all data-integrity consistency'>
    <div class='header ui-widget-header'><?php $clang->eT("Data consistency check"); ?><br />
        <span class='hint'><?php $clang->eT("If errors are showing up you might have to execute this script repeatedly."); ?></span>
    </div>

    <ul class='data-consistency-list'>
        <?php
            // TMSW Conditions->Relevance:  Update this to use relevance processing results
            if (isset($conditions))
            {?>
            <li><?php $clang->eT("The following conditions should be deleted:"); ?>
                <ul>
                    <?php
                        foreach ($conditions as $condition) {?>
                         <li>CID:<?php echo $condition['cid'].' '.$clang->gT("Reason:")." {$condition['reason']}";?></li><?php
                    }?>
                </ul>
            </li>
            <?php
            }
            else
            { ?>
            <li><?php $clang->eT("All conditions meet consistency standards."); ?></li><?php
        } ?>

        <?php
            if (isset($questionattributes)) { ?>
            <li><?php printf($clang->gT("There are %s orphaned question attributes."),count($questionattributes)); ?> </li>
            <?php }
            else
            { ?>
            <li><?php $clang->eT("All question attributes meet consistency standards."); ?> </li> <?php
        } ?>

        <?php
            if ($defaultvalues) { ?>
            <li><?php printf($clang->gT("There are %s orphaned default value entries which can be deleted."),$defaultvalues); ?> </li>
            <?php }
            else
            { ?>
            <li><?php $clang->eT("All default values meet consistency standards."); ?> </li> <?php
        } ?>

        <?php
            if ($quotas) { ?>
            <li><?php printf($clang->gT("There are %s orphaned quota entries which can be deleted."),$quotas); ?> </li>
            <?php }
            else
            { ?>
            <li><?php $clang->eT("All quotas meet consistency standards."); ?> </li> <?php
        } ?>

        <?php
            if ($quotals) { ?>
            <li><?php printf($clang->gT("There are %s orphaned quota language settings which can be deleted."),$quotals); ?> </li>
            <?php }
            else
            { ?>
            <li><?php $clang->eT("All quota language settings meet consistency standards."); ?> </li> <?php
        } ?>

        <?php
            if ($quotamembers) { ?>
            <li><?php printf($clang->gT("There are %s orphaned quota members which can be deleted."),$quotamembers); ?> </li>
            <?php }
            else
            { ?>
            <li><?php $clang->eT("All quota quota members meet consistency standards."); ?> </li> <?php
        } ?>

        <?php
            if (isset($assessments))
            {?>
            <li><?php $clang->eT("The following assessments should be deleted:"); ?>
                <ul>
                    <?php
                        foreach ($assessments as $assessment) {?>
                        <li>AID:<?php echo $assessment['id'];?> <?php $clang->eT("Assessment:");?> <?php $clang->eT("Reason:");?> <?php echo $assessment['reason'];?></li><?php
                    }?>
                </ul>
            </li>
            <?php
            }
            else
            { ?>
            <li><?php $clang->eT("All assessments meet consistency standards."); ?></li><?php
        } ?>

        <?php
            if (isset($answers))
            {?>
            <li><?php $clang->eT("The following answers should be deleted:"); ?>
                <ul>
                    <?php
                        foreach ($answers as $answer) {?>
                        <li>QID:<?php echo $answer['qid'];?> <?php $clang->eT("Code:");?> <?php $clang->eT("Reason:");?> <?php echo $answer['reason'];?></li><?php
                    }?>
                </ul>
            </li>
            <?php
            }
            else
            { ?>
            <li><?php $clang->eT("All answers meet consistency standards."); ?></li><?php
        } ?>

        <?php
            if (isset($surveys))
            {?>
            <li><?php $clang->eT("The following surveys should be deleted:"); ?>
                <ul>
                    <?php
                        foreach ($surveys as $survey) {?>
                        <li>SID:<?php echo $survey['sid'];?> <?php $clang->eT("Reason:");?> <?php echo $survey['reason'];?></li><?php
                    }?>
                </ul>
            </li>
            <?php
            }
            else
            { ?>
            <li><?php $clang->eT("All surveys meet consistency standards."); ?></li><?php
        } ?>

        <?php
            if (isset($surveylanguagesettings))
            {?>
            <li><?php $clang->eT("The following survey language settings should be deleted:"); ?>
                <ul>
                    <?php
                        foreach ($surveylanguagesettings as $surveylanguagesetting) {?>
                        <li>SLID:<?php echo $surveylanguagesetting['slid'];?> <?php $clang->eT("Reason:");?> <?php echo $surveylanguagesetting['reason'];?></li><?php
                    }?>
                </ul>
            </li>
            <?php
            }
            else
            { ?>
            <li><?php $clang->eT("All survey language settings meet consistency standards."); ?></li><?php
        } ?>

        <?php
            if (isset($questions))
            {?>
            <li><?php $clang->eT("The following questions should be deleted:"); ?>
                <ul>
                    <?php
                        foreach ($questions as $question) {?>
                        <li>QID:<?php echo $question['qid'];?> <?php $clang->eT("Reason:");?> <?php echo $question['reason'];?></li><?php
                    }?>
                </ul>
            </li>
            <?php
            }
            else
            { ?>
            <li><?php $clang->eT("All questions meet consistency standards."); ?></li><?php
        } ?>

        <?php
            if (isset($groups))
            {?>
            <li><?php $clang->eT("The following groups should be deleted:"); ?>
                <ul>
                    <?php
                        foreach ($groups as $group) {?>
                        <li>GID:<?php echo $group['gid'];?> <?php $clang->eT("Reason:");?> <?php echo $group['reason'];?></li><?php
                    }?>
                </ul>
            </li>
            <?php
            }
            else
            { ?>
            <li><?php $clang->eT("All groups meet consistency standards."); ?></li><?php
        } ?>

        <?php
            if (isset($orphansurveytables))
            {?>
            <li><?php $clang->eT("The following old survey tables should be deleted because they contain no records or their parent survey no longer exists:"); ?>
                <ul>
                    <?php
                        foreach ($orphansurveytables as $surveytable) {?>
                        <li><?php echo $surveytable;?></li><?php
                    }?>
                </ul>
            </li>
            <?php
            }
            else
            { ?>
            <li><?php $clang->eT("All old survey tables meet consistency standards."); ?></li><?php
        } ?>

        <?php
            if (isset($orphantokentables))
            {?>
            <li><?php $clang->eT("The following old token tables should be deleted because they contain no records or their parent survey no longer exists:"); ?>
                <ul>
                    <?php
                        foreach ($orphantokentables as $tokentable) {?>
                        <li><?php echo $tokentable;?></li><?php
                    }?>
                </ul>
            </li>
            <?php
            }
            else
            { ?>
            <li><?php $clang->eT("All old token tables meet consistency standards."); ?></li><?php
        } ?>
    </ul>

    <?php if ($integrityok) { ?>
        <br /> <?php $clang->eT("No database action required!"); ?>
        <?php } else
        {?>
        <br /><?php $clang->eT("Should we proceed with the delete?"); ?> <br />
        <?php echo CHtml::form(array("admin/checkintegrity/fixintegrity"), 'post');?>
            <input type='hidden' name='ok' value='Y' />
            <input type='submit' value='<?php $clang->eT("Yes - Delete Them!"); ?>' />
        </form>
        <?php
    } ?>
</div><br />
<div class='messagebox ui-corner-all data-integrity redundancy'>
    <div class='header ui-widget-header'><?php $clang->eT("Data redundancy check"); ?><br />
        <span class='hint'><?php $clang->eT("The redundancy check looks for tables leftover after deactivating a survey. You can delete these if you no longer require them."); ?></span>
    </div>
    <?php if ($redundancyok) { ?>
        <br /> <?php $clang->eT("No database action required!"); ?>
        <?php } else
        {?>
        <?php echo CHtml::form(array("admin/checkintegrity/fixredundancy"), 'post');?>
            <ul class='data-redundancy-list'>
                <?php
                    if (isset($redundantsurveytables))
                    {?>
                    <li><?php $clang->eT("The following old survey response tables exist and may be deleted if no longer required:"); ?>
                        <ul class='response-tables-list'>
                            <?php
                                foreach ($redundantsurveytables as $surveytable) {?>
                                <li><input type='checkbox' id='cbox_<?php echo $surveytable['table']?>' value='<?php echo $surveytable['table']?>' name='oldsmultidelete[]' /><label for='cbox_<?php echo $surveytable['table']?>'><?php echo $surveytable['details']?></label></li><?php
                            }?>
                        </ul>
                    </li>
                    <?php
                } ?>

                <?php
                    if (isset($redundanttokentables) && count($redundanttokentables)>0)
                    {?>
                    <li><?php $clang->eT("The following old token list tables exist and may be deleted if no longer required:"); ?>
                        <ul class='token-tables-list'>
                            <?php
                                foreach ($redundanttokentables as $tokentable) {?>
                                <li><input type='checkbox' id='cbox_<?php echo $tokentable['table']?>' value='<?php echo $tokentable['table']?>' name='oldsmultidelete[]' /><label for='cbox_<?php echo $tokentable['table']?>'><?php echo $tokentable['details']?></label></li><?php
                            }?>
                        </ul>
                    </li>
                    <?php
                } ?>
            </ul><p>
                <input type='hidden' name='ok' value='Y' />
                <input type='submit' value='<?php $clang->eT("Delete checked items!"); ?>' /> <br />
                <span class='hint warning'><?php $clang->eT("Note that you cannot undo a delete if you proceed. The data will be gone."); ?></span></p>
        </form><?php
    } ?>

</div>
