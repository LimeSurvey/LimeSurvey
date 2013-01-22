<div id='notification'><ul>



        <li><label for='emailnotificationto'><?php $clang->eT("Send basic admin notification email to:"); ?></label>
            <input size='70' type='email' value="<?php echo $esrow['emailnotificationto']; ?>" id='emailnotificationto' name='emailnotificationto' />
        </li>


        <li><label for='emailresponseto'><?php $clang->eT("Send detailed admin notification email to:"); ?></label>
            <input size='70' type='email' value="<?php echo $esrow['emailresponseto']; ?>" id='emailresponseto' name='emailresponseto' />
        </li>




        <li><label for='datestamp'><?php $clang->eT("Date Stamp?"); ?></label>
            <?php if ($esrow['active'] == "Y") { ?>
                <?php if ($esrow['datestamp'] != "Y") {
                        $clang->eT("Responses will not be date stamped.");
                    } else {
                        $clang->eT("Responses will be date stamped.");
                } ?>
                <span class='annotation'> <?php $clang->eT("Cannot be changed"); ?></span>
                <input type='hidden' name='datestamp' value="<?php echo $esrow['datestamp']; ?>" />
                <?php } else { ?>
                <select id='datestamp' name='datestamp' onchange='alertPrivacy();'>
                    <option value='Y'
                        <?php if ($esrow['datestamp'] == "Y") { ?>
                            selected='selected'
                            <?php } ?>
                        ><?php $clang->eT("Yes"); ?></option>
                    <option value='N'
                        <?php if ($esrow['datestamp'] != "Y") { ?>
                            selected='selected'
                            <?php } ?>
                        ><?php $clang->eT("No"); ?></option>
                </select>
                <?php } ?>
        </li>


        <li><label for='ipaddr'><?php $clang->eT("Save IP Address?"); ?></label>

            <?php if ($esrow['active'] == "Y") { ?>
                <?php  if ($esrow['ipaddr'] != "Y") {
                        $clang->eT("Responses will not have the IP address logged.");
                    } else {
                        $clang->eT("Responses will have the IP address logged");
                } ?>
                <span class='annotation'> <?php $clang->eT("Cannot be changed"); ?></span>
                <input type='hidden' name='ipaddr' value='<?php echo $esrow['ipaddr']; ?>' />
                <?php } else { ?>
                <select name='ipaddr' id='ipaddr'>
                    <option value='Y'
                        <?php if ($esrow['ipaddr'] == "Y") { ?>
                            selected='selected'
                            <?php } ?>
                        ><?php $clang->eT("Yes"); ?></option>
                    <option value='N'
                        <?php if ($esrow['ipaddr'] != "Y") { ?>
                            selected='selected'
                            <?php } ?>
                        ><?php $clang->eT("No"); ?></option>
                </select>
                <?php } ?>

        </li>


        <li><label for='refurl'><?php $clang->eT("Save referrer URL?"); ?></label>

            <?php if ($esrow['active'] == "Y") { ?>
                <?php  if ($esrow['refurl'] != "Y") {
                        $clang->eT("Responses will not have their referring URL logged.");
                    } else {
                        $clang->eT("Responses will have their referring URL logged.");
                } ?>
                <span class='annotation'> <?php $clang->eT("Cannot be changed"); ?></span>
                <input type='hidden' name='refurl' value='<?php echo $esrow['refurl']; ?>' />
                <?php } else { ?>
                <select name='refurl' id='refurl'>
                    <option value='Y'
                        <?php if ($esrow['refurl'] == "Y") { ?>
                            selected='selected'
                            <?php } ?>
                        ><?php $clang->eT("Yes"); ?></option>
                    <option value='N'
                        <?php if ($esrow['refurl'] != "Y") { ?>
                            selected='selected'
                            <?php } ?>
                        ><?php $clang->eT("No"); ?></option>
                </select>
                <?php } ?>
        </li>

        <li><label for='savetimings'><?php $clang->eT("Save timings?"); ?></label>
            <?php 
            if ($esrow['active']=="Y")
                { ?>
                <?php if ($esrow['savetimings'] != "Y") {  $clang->eT("Timings will not be saved.");}
                    else { $clang->eT("Timings will be saved.");} ?>
                <span class='annotation'> <?php $clang->eT("Cannot be changed"); ?></span>
                <input type='hidden' name='savetimings' value='<?php echo $esrow['savetimings']; ?>' />
                <?php }
                else
                { ?>
                <select id='savetimings' name='savetimings'>
                    <option value='Y'
                        <?php if (!isset($esrow['savetimings']) || !$esrow['savetimings'] || $esrow['savetimings'] == "Y") { ?> selected='selected' <?php } ?>
                        ><?php $clang->eT("Yes"); ?></option>
                    <option value='N'
                        <?php if (isset($esrow['savetimings']) && $esrow['savetimings'] == "N") { ?>  selected='selected' <?php } ?>
                        ><?php $clang->eT("No"); ?></option>
                </select>
            </li>
            <?php } ?>


        <li><label for='assessments'><?php $clang->eT("Enable assessment mode?"); ?></label>
            <select id='assessments' name='assessments'>
                <option value='Y'
                    <?php if ($esrow['assessments'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['assessments'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?></option>
            </select></li>


        <li><label for='allowsave'><?php $clang->eT("Participant may save and resume later?"); ?></label>
            <select id='allowsave' name='allowsave'>
                <option value='Y'
                    <?php if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['allowsave'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?></option>
            </select></li>

        <li><label for='googleanalyticsapikey'><?php $clang->eT("Google Analytics API key for this survey?"); ?></label>
            <input type='text' value='<?php echo $esrow['googleanalyticsapikey']; ?>' name='googleanalyticsapikey' id='googleanalyticsapikey' size='20'/>
        </li>

        <li><label for='googleanalyticsstyle'><?php $clang->eT("Google Analytics style for this survey?"); ?></label>
            <select id='googleanalyticsstyle' name='googleanalyticsstyle'>
                <option value='0'
                    <?php if (!$esrow['googleanalyticsstyle'] || $esrow['googleanalyticsstyle'] == "0") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Do not use Google Analytics"); ?></option>
                <option value='1'
                    <?php if ($esrow['googleanalyticsstyle'] == "1") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Default Google Analytics"); ?></option>
                <option value='2'
                    <?php if ($esrow['googleanalyticsstyle'] == "2") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Survey name-[SID]/Group name"); ?></option>
            </select></li>

    </ul></div>