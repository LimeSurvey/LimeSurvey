<div id='tokens'><ul>
        <li><label for='anonymized' title='<?php eT("If you set 'Yes' then no link will exist between token table and survey responses table. You won't be able to identify responses by their token."); ?>'><?php eT("Anonymized responses?"); ?>

                <script type="text/javascript"><!--
                    function alertPrivacy()
                    {
                        if (document.getElementById('tokenanswerspersistence').value == 'Y')
                            {
                            alert(<?=json_encode(gT("You can't use Anonymized responses when Token-based answers persistence is enabled.")); ?>);
                            document.getElementById('anonymized').value = 'N';
                        }
                        else if (document.getElementById('anonymized').value == 'Y')
                            {
                            alert(<?=json_encode(gT("Warning: If you turn on the -Anonymized responses- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.")); ?>);
                        }
                    }
                    function alertDateStampAnonymization()
                    {
                        if (document.getElementById('anonymized').value == 'Y')
                        {
                            alert('<?php eT("Warning: If the option -Anonymized responses- is activated only a dummy date stamp (1980-01-01) will be used for all responses to ensure the anonymity of your participants.","js"); ?>');
                        }
                    }
                    function alertDateStampAnonymization()
                    {
                        if (document.getElementById('anonymized').value == 'Y')
                        {
                            alert('<?php eT("Warning"); ?>: <?php eT("If the option -Anonymized responses- is activated only a dummy date stamp (1980-01-01) will be used for all responses to ensure the anonymity of your participants.","js"); ?>');
                        }
                    }
                    //--></script></label>
            <?php if ($esrow['active'] == "Y") {
                    if ($esrow['anonymized'] == "N") { ?>
                    <?php eT("Responses to this survey are NOT anonymized."); ?>
                    <?php } else {
                        eT("Responses to this survey are anonymized.");
                } ?>
                <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                <input type='hidden' id='anonymized' name='anonymized' value="<?php echo $esrow['anonymized']; ?>" />
                <?php } else { ?>
                <select id='anonymized' name='anonymized' onchange='alertPrivacy();'>
                    <option value='Y'
                        <?php if ($esrow['anonymized'] == "Y") { ?>
                            selected='selected'
                            <?php } ?>
                        ><?php eT("Yes"); ?></option>
                    <option value='N'
                        <?php if ($esrow['anonymized'] != "Y") { ?>
                            selected='selected'
                            <?php } ?>
                        ><?php eT("No"); ?></option>
                </select>
                <?php } ?>
        </li>

        <li>
            <label for='alloweditaftercompletion' title='<?php eT("With not anonymous survey: user can update his answer after completion, else user can add new answers without restriction."); ?>'><?php eT("Allow multiple responses or update responses with one token?"); ?></label>
            <select id='alloweditaftercompletion' name='alloweditaftercompletion'>
                <option value='Y'
                    <?php if ($esrow['tokenanswerspersistence'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['alloweditaftercompletion'] == "N") { ?>  selected='selected' <?php } ?>
                    ><?php eT("No"); ?></option>
            </select>
        </li>

        <li>
            <label for='tokenanswerspersistence'><?php eT("Enable token-based response persistence?"); ?></label>
            <select id='tokenanswerspersistence' name='tokenanswerspersistence' onchange="javascript: if (document.getElementById('anonymized').value == 'Y') { alert('<?php eT("This option can't be set if the `Anonymized responses` option is active.","js"); ?>'); this.value='N';}">
                <option value='Y'
                    <?php if ($esrow['alloweditaftercompletion'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['tokenanswerspersistence'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php eT("No"); ?></option>
            </select></li>
        <li><label for='allowregister'><?php eT("Allow public registration?"); ?></label>
            <select id='allowregister' name='allowregister'>
                <option value='Y'
                    <?php if ($esrow['allowregister'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['allowregister'] != "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php eT("No"); ?></option>
            </select>
        </li>

        <li>
            <label for='htmlemail'><?php eT("Use HTML format for token emails?"); ?></label>
            <select name='htmlemail' id='htmlemail' onchange="alert('<?php eT("If you switch email mode, you'll have to review your email templates to fit the new format","js"); ?>');">
                <option value='Y'
                    <?php if ($esrow['htmlemail'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['htmlemail'] == "N") { ?>
                        selected='selected'
                        <?php } ?>

                    ><?php eT("No"); ?></option>
            </select>
        </li>

        <li>
            <label for='sendconfirmation'><?php eT("Send confirmation emails?"); ?></label>
            <select name='sendconfirmation' id='sendconfirmation'>
                <option value='Y'
                    <?php if ($esrow['sendconfirmation'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['sendconfirmation'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php eT("No"); ?></option>
            </select>
        </li>
        <li><label for='tokenlength'><?php eT("Set token length to:"); ?></label>
            <input type="number" min="5" max="36" step="1"  pattern="\d*" value="<?php echo $esrow['tokenlength']; ?>" name='tokenlength' id='tokenlength' style='width:4em' />
        </li>
    </ul></div>

