<div id='publication'><ul>


            <li><label for='public'><?php echo $clang->gT("List survey publicly:");?></label>
            <select id='public' name='public'>
            <option value='Y'
        <?php if (!isset($esrow['listpublic']) || !$esrow['listpublic'] || $esrow['listpublic'] == "Y") { ?>
              selected='selected'
        <?php } ?>
             ><?php echo $clang->gT("Yes"); ?></option>
            <option value='N'
        <?php if (isset($esrow['listpublic']) && $esrow['listpublic'] == "N") { ?>
              selected='selected'
        <?php } ?>
             ><?php echo $clang->gT("No"); ?></option>
            </select>
            </li>
             <li><label for='startdate'><?php echo $clang->gT("Start date/time:"); ?></label>
            <input type='text' class='popupdatetime' id='startdate' size='20' name='startdate' value="<?php echo $startdate; ?>" /></li>

            <!--// Expiration date
            $expires='';
        if (trim($esrow['expires']) != '') {
                $items = array($esrow['expires'] , "Y-m-d H:i:s");
                $this->load->library('Date_Time_Converter',$items);
                $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($esrow['expires'] , "Y-m-d H:i:s");
                $expires=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            } -->
             <li><label for='expires'><?php echo $clang->gT("Expiry date/time:"); ?></label>
             <input type='text' class='popupdatetime' id='expires' size='20' name='expires' value="<?php echo $expires; ?>" /></li>


             <li><label for='usecookie'><?php echo $clang->gT("Set cookie to prevent repeated participation?"); ?></label>
            <select name='usecookie' id='usecookie'>
            <option value='Y'
        <?php if ($esrow['usecookie'] == "Y") { ?>
              selected='selected'
        <?php } ?>
             ><?php echo $clang->gT("Yes"); ?></option>
            <option value='N'
        <?php if ($esrow['usecookie'] != "Y") { ?>
              selected='selected'
        <?php } ?>
             ><?php echo $clang->gT("No"); ?></option>
             </select>
             </li>


             <li><label for='usecaptcha'><?php echo $clang->gT("Use CAPTCHA for"); ?>:</label>
             <select name='usecaptcha' id='usecaptcha'>
             <option value='A'
        <?php if ($esrow['usecaptcha'] == "A") { ?>
              selected='selected'
        <?php } ?>
             ><?php echo $clang->gT("Survey Access"); ?> / <?php echo $clang->gT("Registration"); ?> / <?php echo$clang->gT("Save & Load"); ?></option>
            <option value='B'
        <?php if ($esrow['usecaptcha'] == "B") { ?>
              selected='selected'
        <?php } ?>

             ><?php echo $clang->gT("Survey Access"); ?> / <?php echo $clang->gT("Registration"); ?> / ---------</option>
            <option value='C'
        <?php if ($esrow['usecaptcha'] == "C") { ?>
              selected='selected'
        <?php } ?>

             ><?php echo $clang->gT("Survey Access"); ?> / ------------ / <?php echo $clang->gT("Save & Load"); ?></option>
            <option value='D'
        <?php if ($esrow['usecaptcha'] == "D") { ?>
              selected='selected'
        <?php } ?>

             >------------- / <?php echo $clang->gT("Registration"); ?> / <?php echo $clang->gT("Save & Load"); ?></option>
            <option value='X'

        <?php if ($esrow['usecaptcha'] == "X") { ?>
              selected='selected'
        <?php } ?>

             ><?php echo $clang->gT("Survey Access"); ?> / ------------ / ---------</option>
            <option value='R'
        <?php if ($esrow['usecaptcha'] == "R") { ?>
              selected='selected'
        <?php } ?>
             >------------- / <?php echo $clang->gT("Registration"); ?> / ---------</option>
            <option value='S'
        <?php if ($esrow['usecaptcha'] == "S") { ?>
              selected='selected'
        <?php } ?>
             >------------- / ------------ / <?php echo $clang->gT("Save & Load"); ?></option>
            <option value='N'
        <?php if ($esrow['usecaptcha'] == "N") { ?>
              selected='selected'";
        <?php } ?>
             >------------- / ------------ / ---------</option>
            </select></li>

        </ul></div>
