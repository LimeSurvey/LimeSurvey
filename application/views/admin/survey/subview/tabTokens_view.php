<div id='tokens'><ul>

        <li><label for='anonymized'><?php $clang->eT("Anonymized responses?"); ?>

                <script type="text/javascript"><!--
                    function alertPrivacy()
                    {
                        if (document.getElementById('tokenanswerspersistence').value == 'Y')
                            {
                            alert('<?php $clang->eT("You can't use Anonymized responses when Token-based answers persistence is enabled.","js"); ?>');
                            document.getElementById('anonymized').value = 'N';
                        }
                        else if (document.getElementById('anonymized').value == 'Y')
                            {
                            alert('<?php $clang->eT("Warning"); ?>: <?php $clang->eT("If you turn on the -Anonymized responses- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js"); ?>');
                        }
                    }
                    //--></script></label>

            <?php if ($esrow['active'] == "Y") {
                    if ($esrow['anonymized'] == "N") { ?>
                    <?php $clang->eT("Responses to this survey are NOT anonymized."); ?>
                    <?php } else {
                        $clang->eT("Responses to this survey are anonymized.");
                } ?>
                <span class='annotation'> <?php $clang->eT("Cannot be changed"); ?></span>
                <input type='hidden' name='anonymized' value="<?php echo $esrow['anonymized']; ?>" />
                <?php } else { ?>
                <select id='anonymized' name='anonymized' onchange='alertPrivacy();'>
                    <option value='Y'
                        <?php if ($esrow['anonymized'] == "Y") { ?>
                            selected='selected'
                            <?php } ?>
                        ><?php $clang->eT("Yes"); ?></option>
                    <option value='N'
                        <?php if ($esrow['anonymized'] != "Y") { ?>
                            selected='selected'
                            <?php } ?>
                        ><?php $clang->eT("No"); ?></option>
                </select>
                <?php } ?>
        </li>
        <li>
            <label for='alloweditaftercompletion'><?php $clang->eT("Allow editing responses after completion?"); ?></label>
            <select id='alloweditaftercompletion' name='alloweditaftercompletion' onchange="javascript: if (document.getElementById('private').value == 'Y') { alert('<?php $clang->eT("This option can't be set if Anonymous answers are used","js"); ?>'); this.value='N';}">
                <option value='Y'
                    <?php if ($esrow['alloweditaftercompletion'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['alloweditaftercompletion'] == "N") { ?>  selected='selected' <?php } ?>
                    ><?php $clang->eT("No"); ?></option>
            </select>
        </li>
        <li>
            <label for='tokenanswerspersistence'><?php $clang->eT("Enable token-based response persistence?"); ?></label>
            <select id='tokenanswerspersistence' name='tokenanswerspersistence' onchange="javascript: if (document.getElementById('anonymized').value == 'Y') { alert('<?php $clang->eT("This option can't be set if the `Anonymized responses` option is active.","js"); ?>'); this.value='N';}">
                <option value='Y'
                    <?php if ($esrow['tokenanswerspersistence'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['tokenanswerspersistence'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?></option>
            </select></li>
        <li><label for='allowregister'><?php $clang->eT("Allow public registration?"); ?></label>
            <select id='allowregister' name='allowregister'>
                <option value='Y'
                    <?php if ($esrow['allowregister'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['allowregister'] != "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?></option>
            </select>
        </li>

        <li>
            <label for='htmlemail'><?php $clang->eT("Use HTML format for token emails?"); ?></label>
            <select name='htmlemail' id='htmlemail' onchange="alert('<?php $clang->eT("If you switch email mode, you'll have to review your email templates to fit the new format","js"); ?>');">
                <option value='Y'
                    <?php if ($esrow['htmlemail'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['htmlemail'] == "N") { ?>
                        selected='selected'
                        <?php } ?>

                    ><?php $clang->eT("No"); ?></option>
            </select>
        </li>

        <li>
            <label for='sendconfirmation'><?php $clang->eT("Send confirmation emails?"); ?></label>
            <select name='sendconfirmation' id='sendconfirmation'>
                <option value='Y'
                    <?php if ($esrow['sendconfirmation'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['sendconfirmation'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php $clang->eT("No"); ?></option>
            </select>
        </li>


        <li><label for='tokenlength'><?php $clang->eT("Set token length to:"); ?></label>
            <input type='text' value="<?php echo $esrow['tokenlength']; ?>" name='tokenlength' id='tokenlength' size='12' maxlength='2' onkeypress="return goodchars(event,'0123456789')" />
        </li>


    </ul></div>