<div id='notification'><ul>



             <li><label for='emailnotificationto'><?php echo $clang->gT("Send basic admin notification email to:"); ?></label>
            <input size='70' type='text' value="<?php echo $esrow['emailnotificationto']; ?>" id='emailnotificationto' name='emailnotificationto' />
            </li>


            <li><label for='emailresponseto'><?php echo $clang->gT("Send detailed admin notification email to:"); ?></label>
            <input size='70' type='text' value="<?php echo $esrow['emailresponseto']; ?>" id='emailresponseto' name='emailresponseto' />
            </li>




             <li><label for='datestamp'><?php echo $clang->gT("Date Stamp?"); ?></label>
        <?php if ($esrow['active'] == "Y") { ?>
            <?php if ($esrow['datestamp'] != "Y") {
                echo $clang->gT("Responses will not be date stamped.");
            } else {
                 echo $clang->gT("Responses will be date stamped.");
            } ?>
                 <font size='1' color='red'>&nbsp;(<?php echo $clang->gT("Cannot be changed"); ?>)
                </font>
                 <input type='hidden' name='datestamp' value="<?php echo $esrow['datestamp']; ?>" />
        <?php } else { ?>
                 <select id='datestamp' name='datestamp' onchange='alertPrivacy();'>
                 <option value='Y'
            <?php if ($esrow['datestamp'] == "Y") { ?>
                  selected='selected'
            <?php } ?>
                 ><?php echo $clang->gT("Yes"); ?></option>
                <option value='N'
            <?php if ($esrow['datestamp'] != "Y") { ?>
                  selected='selected'
            <?php } ?>
                 ><?php echo $clang->gT("No"); ?></option>
                </select>
            <?php } ?>
             </li>


             <li><label for='ipaddr'><?php echo $clang->gT("Save IP Address?"); ?></label>

        <?php if ($esrow['active'] == "Y") { ?>
          <?php  if ($esrow['ipaddr'] != "Y") {
                 echo $clang->gT("Responses will not have the IP address logged.");
            } else {
                 echo $clang->gT("Responses will have the IP address logged");
            } ?>
                 <font size='1' color='red'>&nbsp;(<?php echo $clang->gT("Cannot be changed"); ?>)
                </font>
                 <input type='hidden' name='ipaddr' value='<?php echo $esrow['ipaddr']; ?>' />
        <?php } else { ?>
                 <select name='ipaddr' id='ipaddr'>
                 <option value='Y'
            <?php if ($esrow['ipaddr'] == "Y") { ?>
                  selected='selected'
            <?php } ?>
                 ><?php echo $clang->gT("Yes"); ?></option>
                <option value='N'
            <?php if ($esrow['ipaddr'] != "Y") { ?>
                 selected='selected'
            <?php } ?>
                 ><?php echo $clang->gT("No"); ?></option>
                 </select>
            <?php } ?>

             </li>


             <li><label for='refurl'><?php echo $clang->gT("Save referrer URL?"); ?></label>

        <?php if ($esrow['active'] == "Y") { ?>
          <?php  if ($esrow['refurl'] != "Y") {
                echo $clang->gT("Responses will not have their referring URL logged.");
            } else {
                echo $clang->gT("Responses will have their referring URL logged.");
            } ?>
                 <font size='1' color='red'>&nbsp;(<?php echo $clang->gT("Cannot be changed"); ?>)
                 </font>
                 <input type='hidden' name='refurl' value='<?php echo $esrow['refurl']; ?>' />
        <?php } else { ?>
                 <select name='refurl' id='refurl'>
                 <option value='Y'
            <?php if ($esrow['refurl'] == "Y") { ?>
                  selected='selected'
            <?php } ?>
                 ><?php echo $clang->gT("Yes"); ?></option>
                 <option value='N'
            <?php if ($esrow['refurl'] != "Y") { ?>
                  selected='selected'
            <?php } ?>
                 ><?php echo $clang->gT("No"); ?></option>
                 </select>
            <?php } ?>
             </li>

         <li><label for='savetimings'><?php echo $clang->gT("Save timings?"); ?></label>
        <?php if ($esrow['active']=="Y")
        { ?>
           <?php if ($esrow['savetimings'] != "Y") {  echo $clang->gT("Timings will not be saved.");}
            else { $clang->gT("Timings will be saved.");} ?>
             <font size='1' color='red'>&nbsp;(<?php echo $clang->gT("Cannot be changed"); ?>)
             </font>
             <input type='hidden' name='savetimings' value='<?php echo $esrow['savetimings']; ?>' />
        <?php }
        else
        { ?>
             <select id='savetimings' name='savetimings'>
             <option value='Y'
            <?php if (!isset($esrow['savetimings']) || !$esrow['savetimings'] || $esrow['savetimings'] == "Y") { ?> selected='selected' <?php } ?>
             ><?php echo $clang->gT("Yes"); ?></option>
            <option value='N'
            <?php if (isset($esrow['savetimings']) && $esrow['savetimings'] == "N") { ?>  selected='selected' <?php } ?>
             ><?php echo $clang->gT("No"); ?></option>
            </select>
            </li>
        <?php } ?>


             <li><label for='assessments'><?php echo $clang->gT("Enable assessment mode?"); ?></label>
             <select id='assessments' name='assessments'>
             <option value='Y'
        <?php if ($esrow['assessments'] == "Y") { ?>
              selected='selected'
        <?php } ?>
             ><?php echo $clang->gT("Yes"); ?></option>
            <option value='N'
        <?php if ($esrow['assessments'] == "N") { ?>
              selected='selected'
        <?php } ?>
         ><?php echo $clang->gT("No"); ?></option>
        </select></li>


         <li><label for='allowsave'><?php echo $clang->gT("Participant may save and resume later?"); ?></label>
         <select id='allowsave' name='allowsave'>
         <option value='Y'
        <?php if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") { ?>
              selected='selected'
        <?php } ?>
             ><?php echo $clang->gT("Yes"); ?></option>
             <option value='N'
        <?php if ($esrow['allowsave'] == "N") { ?>
              selected='selected'
        <?php } ?>
         ><?php echo $clang->gT("No"); ?></option>
        </select></li>



        </ul></div>