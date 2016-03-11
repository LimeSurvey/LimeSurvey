<?php
/**
 * Tokens panel
 */
?>
<!-- tokens panel -->
<div id='tokens' class="tab-pane fade in">

    <!-- Anonymized responses -->
    <div class="form-group">
        <label  class="col-sm-6 control-label"  for='anonymized' title='<?php eT("If you set 'Yes' then no link will exist between token table and survey responses table. You won't be able to identify responses by their token."); ?>'>
            <?php  eT("Anonymized responses:"); ?>
            <script type="text/javascript"><!--
                function alertPrivacy()
                {
                    if (document.getElementById('tokenanswerspersistence').value == 'Y')
                        {
                        alert('<?php  eT("You can't use Anonymized responses when Token-based answers persistence is enabled.","js"); ?>');
                        document.getElementById('anonymized').value = 'N';
                    }
                    else if (document.getElementById('anonymized').value == 'Y')
                        {
                        alert('<?php  eT("Warning"); ?>: <?php  eT("If you turn on the -Anonymized responses- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js"); ?>');
                    }
                }
                function alertDateStampAnonymization()
                {
                    if (document.getElementById('anonymized').value == 'Y')
                    {
                        alert('<?php  eT("Warning"); ?>: <?php  eT("If the option -Anonymized responses- is activated only a dummy date stamp (1980-01-01) will be used for all responses to ensure the anonymity of your participants.","js"); ?>');
                    }
                }
                //--></script>
        </label>
        <div class="col-sm-6">
            <?php if ($esrow['active'] == "Y") {
                if ($esrow['anonymized'] == "N") { ?>
                <?php  eT("Responses to this survey are NOT anonymized."); ?>
                <?php } else {
                     eT("Responses to this survey are anonymized.");
            } ?>
            <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
            <input type='hidden' id='anonymized' name='anonymized' value="<?php echo $esrow['anonymized']; ?>" />
            <?php } else { ?>
            <select  class="form-control" id='anonymized' name='anonymized' onchange='alertPrivacy();'>
                <option value='Y'
                    <?php if ($esrow['anonymized'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php  eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['anonymized'] != "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php  eT("No"); ?></option>
            </select>
            <?php } ?>
        </div>
    </div>

    <!-- Enable token-based response persistence -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='tokenanswerspersistence' title='<?php  eT("With non-anonymized responses (and the token table field 'Uses left' set to 1) if the participant closes the survey and opens it again (by using the survey link) his previous answers will be reloaded."); ?>'>
            <?php  eT("Enable token-based response persistence:"); ?>
        </label>
        <div class="col-sm-6">
            <select class="form-control" id='tokenanswerspersistence' name='tokenanswerspersistence' onchange="javascript: if (document.getElementById('anonymized').value == 'Y') { alert('<?php  eT("This option can't be set if the `Anonymized responses` option is active.","js"); ?>'); this.value='N';}">
                <option value='Y'
                    <?php if ($esrow['tokenanswerspersistence'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php  eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['tokenanswerspersistence'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php  eT("No"); ?></option>
            </select>
        </div>
    </div>

    <!-- Allow multiple responses or update responses with one token -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='alloweditaftercompletion' title='<?php  eT("If token-based response persistence is enabled a participant can update his response after completion, else a participant can add new responses without restriction."); ?>'>
            <?php  eT("Allow multiple responses or update responses with one token:"); ?>
        </label>
        <div class="col-sm-6">
            <select id='alloweditaftercompletion' name='alloweditaftercompletion' class="form-control">
                <option value='Y'
                    <?php if ($esrow['alloweditaftercompletion'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php  eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['alloweditaftercompletion'] == "N") { ?>  selected='selected' <?php } ?>
                    ><?php  eT("No"); ?></option>
            </select>
        </div>
    </div>

    <!-- Allow public registration -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='allowregister'><?php  eT("Allow public registration:"); ?></label>
        <div class="col-sm-6">
            <select id='allowregister' name='allowregister' class="form-control">
                <option value='Y'
                    <?php if ($esrow['allowregister'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php  eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['allowregister'] != "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php  eT("No"); ?></option>
            </select>
        </div>
    </div>

    <!-- Use HTML format for token emails -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='htmlemail'><?php  eT("Use HTML format for token emails:"); ?></label>
        <div class="col-sm-6">
            <select name='htmlemail' id='htmlemail' onchange="alert('<?php  eT("If you switch email mode, you'll have to review your email templates to fit the new format","js"); ?>');" class="form-control">
                <option value='Y'
                    <?php if ($esrow['htmlemail'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php  eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['htmlemail'] == "N") { ?>
                        selected='selected'
                        <?php } ?>

                    ><?php  eT("No"); ?></option>
            </select>
        </div>
    </div>

    <!-- Send confirmation emails -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='sendconfirmation'><?php  eT("Send confirmation emails:"); ?></label>
        <div class="col-sm-6">
            <select name='sendconfirmation' id='sendconfirmation'  class="form-control">
                <option value='Y'
                    <?php if ($esrow['sendconfirmation'] == "Y") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php  eT("Yes"); ?></option>
                <option value='N'
                    <?php if ($esrow['sendconfirmation'] == "N") { ?>
                        selected='selected'
                        <?php } ?>
                    ><?php  eT("No"); ?></option>
            </select>
        </div>
    </div>

    <!--  Set token length to -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='tokenlength'><?php  eT("Set token length to:"); ?></label>
        <div class="col-sm-6">
            <input type='text' value="<?php echo $esrow['tokenlength']; ?>" name='tokenlength' id='tokenlength' size='4' maxlength='2' onkeypress="return goodchars(event,'0123456789')"  class="form-control" />
        </div>
    </div>
</div>
