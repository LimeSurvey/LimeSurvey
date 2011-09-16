<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php echo $clang->gT("Data consistency check"); ?><br />
        <span style='font-size:7pt;'><?php echo $clang->gT("If errors are showing up you might have to execute this script repeatedly."); ?></span>
    </div>

    <ul>
    <?php
    if (isset($conditions))
    {?>
        <li><?php echo $clang->gT("The following conditions should be deleted:"); ?></li>
        <?php
        foreach ($conditions as $condition) {?>
            CID:<?php echo $condition['cid'].' '.$clang->gT("Reason:")." {$condition['reason']}";?><br /><?php
        }?>
        <br />
    <?php
    }
    else
    { ?>
        <li><?php echo $clang->gT("All conditions meet consistency standards."); ?></li><?php
    } ?>

    <?php
    if (isset($questionattributes)) { ?>
        <li><?php printf($clang->gT("There are %s orphaned question attributes."),count($questionattributes)); ?> </li>
    <? }
    else
    { ?>
        <li><?php echo $clang->gT("All question attributes meet consistency standards."); ?> </li> <?php
    } ?>

    <?php
    if ($defaultvalues) { ?>
        <li><?php printf($clang->gT("There are %s orphaned default value entries which can be deleted."),$dv); ?> </li>
    <? }
    else
    { ?>
        <li><?php echo $clang->gT("All default values meet consistency standards."); ?> </li> <?php
    } ?>

    <?php
    if ($quotas) { ?>
        <li><?php printf($clang->gT("There are %s orphaned quota entries which can be deleted."),$quotas); ?> </li>
    <? }
    else
    { ?>
        <li><?php echo $clang->gT("All quotas meet consistency standards."); ?> </li> <?php
    } ?>

    <?php
    if ($quotals) { ?>
        <li><?php printf($clang->gT("There are %s orphaned quota language settings which can be deleted."),$quotals); ?> </li>
    <? }
    else
    { ?>
        <li><?php echo $clang->gT("All quota language settings meet consistency standards."); ?> </li> <?php
    } ?>

    <?php
    if ($quotamembers) { ?>
        <li><?php printf($clang->gT("There are %s orphaned quota members which can be deleted."),$quotamembers); ?> </li>
    <? }
    else
    { ?>
        <li><?php echo $clang->gT("All quota quota members meet consistency standards."); ?> </li> <?php
    } ?>

    <?php
    if (isset($assessments))
    {?>
        <li><?php echo $clang->gT("The following assessments should be deleted:"); ?></li>
        <ul>
        <?php
        foreach ($assessments as $assessment) {?>
            <li>AID:<?php echo $assessment['id'];?> <?php echo $clang->gT("Assessment:");?> <?php echo $clang->gT("Reason:");?> <?php echo $assessment['reason'];?></li><?php
        }?>
        </ul>
    <?php
    }
    else
    { ?>
        <li><?php echo $clang->gT("All assessments meet consistency standards."); ?></li><?php
    } ?>

    <?php
    if (isset($answers))
    {?>
        <li><?php echo $clang->gT("The following answers should be deleted:"); ?></li>
        <ul>
        <?php
        foreach ($answers as $answer) {?>
            <li>QID:<?php echo $answer['qid'];?> <?php echo $clang->gT("Code:");?> <?php echo $clang->gT("Reason:");?> <?php echo $answer['reason'];?></li><?php
        }?>
        </ul>
    <?php
    }
    else
    { ?>
        <li><?php echo $clang->gT("All answers meet consistency standards."); ?></li><?php
    } ?>

    <?php
    if (isset($surveys))
    {?>
        <li><?php echo $clang->gT("The following surveys should be deleted:"); ?></li>
        <ul>
        <?php
        foreach ($surveys as $survey) {?>
            <li>SID:<?php echo $survey['sid'];?> <?php echo $clang->gT("Reason:");?> <?php echo $survey['reason'];?></li><?php
        }?>
        </ul>
    <?php
    }
    else
    { ?>
        <li><?php echo $clang->gT("All surveys meet consistency standards."); ?></li><?php
    } ?>

    <?php
    if (isset($surveylanguagesettings))
    {?>
        <li><?php echo $clang->gT("The following survey language settings should be deleted:"); ?></li>
        <ul>
        <?php
        foreach ($surveylanguagesettings as $surveylanguagesetting) {?>
            <li>SLID:<?php echo $surveylanguagesettings['slid'];?> <?php echo $clang->gT("Reason:");?> <?php echo $surveylanguagesettings['reason'];?></li><?php
        }?>
        </ul>
    <?php
    }
    else
    { ?>
        <li><?php echo $clang->gT("All survey language settings meet consistency standards."); ?></li><?php
    } ?>

    <?php
    if (isset($questions))
    {?>
        <li><?php echo $clang->gT("The following questions should be deleted:"); ?>
            <ul>
            <?php
            foreach ($questions as $question) {?>
                <li>QID:<?php echo $question['qid'];?> <?php echo $clang->gT("Reason:");?> <?php echo $question['reason'];?></li><?php
            }?>
            </ul>
        </li>
    <?php
    }
    else
    { ?>
        <li><?php echo $clang->gT("All questions meet consistency standards."); ?></li><?php
    } ?>

    <?php
    if (isset($groups))
    {?>
        <li><?php echo $clang->gT("The following groups should be deleted:"); ?></li>
        <ul>
        <?php
        foreach ($groups as $group) {?>
            <li>QID:<?php echo $group['gid'];?> <?php echo $clang->gT("Reason:");?> <?php echo $group['reason'];?></li><?php
        }?>
        </ul>
    <?php
    }
    else
    { ?>
        <li><?php echo $clang->gT("All groups meet consistency standards."); ?></li><?php
    } ?>

    <?php
    if (isset($orphansurveytables))
    {?>
        <li><?php echo $clang->gT("The following old survey tables should be deleted because they contain no records or their parent survey no longer exists:"); ?></li>
        <ul>
        <?php
        foreach ($orphansurveytables as $surveytable) {?>
            <li><?php echo $surveytable;?></li><?php
        }?>
        </ul>
    <?php
    }
    else
    { ?>
        <li><?php echo $clang->gT("All old survey tables meet consistency standards."); ?></li><?php
    } ?>

    <?php
    if (isset($orphantokentables))
    {?>
        <li><?php echo $clang->gT("The following old token tables should be deleted because they contain no records or their parent survey no longer exists:"); ?></li>
        <ul>
        <?php
        foreach ($orphantokentables as $tokentable) {?>
            <li><?php echo $tokentable;?></li><?php
        }?>
        </ul>
    <?php
    }
    else
    { ?>
        <li><?php echo $clang->gT("All old token tables meet consistency standards."); ?></li><?php
    } ?>
    </ul>

    <?php if ($integrityok) { ?>
          <br /> <?php echo $clang->gT("No database action required!"); ?>
    <?php } else
    {?>
        <br /><?php echo $clang->gT("Should we proceed with the delete?"); ?> <br />
        <form action='<?php echo site_url('admin/checkintegrity/fixintegrity'); ?>' method='post'>
            <input type='hidden' name='ok' value='Y' />
            <input type='submit' value='<?php echo $clang->gT("Yes - Delete Them!"); ?>' />
        </form>
        <?php
    } ?>
</div><br />
<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php echo $clang->gT("Data redundancy check"); ?><br />
        <span style='font-size:7pt;'><?php echo $clang->gT("The redundancy check looks for tables leftover after deactivating a survey. You can delete these if you no longer require them."); ?></span>
    </div>
    <?php if ($redundancyok) { ?>
          <br /> <?php echo $clang->gT("No database action required!"); ?>
    <?php } else
    {?>
      <form action='<?php echo site_url('admin/checkintegrity/fixredundancy'); ?>' method='post'>
          <ul>
          <?php
            if (isset($redundantsurveytables))
            {?>
                <li><?php echo $clang->gT("The following old survey response tables exist and may be deleted if no longer required:"); ?>
                    <ul>
                    <?php
                    foreach ($redundantsurveytables as $surveytable) {?>
                        <li><input type='checkbox' value='<?php echo $surveytable['table']?>' name='oldsmultidelete[]' /><?php echo $surveytable['details']?></li><?php
                    }?>
                    </ul>
                </li>
            <?php
            } ?>

          <?php
            if (isset($redundanttokentables))
            {?>
                <li><?php echo $clang->gT("The following old token list tables exist and may be deleted if no longer required:"); ?>
                    <ul>
                    <?php
                    foreach ($redundanttokentables as $tokentable) {?>
                        <li><input type='checkbox' value='<?php echo $tokentable['table']?>' name='oldsmultidelete[]' /><?php echo $tokentable['details']?></li><?php
                    }?>
                    </ul>
                </li>
            <?php
            } ?>
          </ul><p>
          <input type='hidden' name='ok' value='R' />
          <input type='submit' value='<?php echo $clang->gT("Delete checked items!"); ?>' /><p>
          <span style='color: red; font-size:0.8em;'><?php echo $clang->gT("Note that you cannot undo a delete if you proceed. The data will be gone."); ?></span>
      </form><?php
    } ?>

</div>