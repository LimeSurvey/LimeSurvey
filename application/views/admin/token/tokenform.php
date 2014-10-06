<div class='header ui-widget-header'>
    <?php
        if ($subaction == "edit")
        {
            $clang->eT("Edit token entry");
            foreach ($tokendata as $Key => $Value)
            {
                $$Key = $Value;
            }
        }
        else
        {
            $clang->eT("Add token entry");
            $tokenid = "";
        }
    ?>
</div>
<?php echo CHtml::form(array("admin/tokens/sa/{$subaction}/surveyid/{$surveyid}/tokenid/{$tokenid}"), 'post', array('id'=>'edittoken', 'class'=>'form30')); ?>
    <ul>
        <li><label>ID:</label>
            <?php if ($subaction == "edit")
                {
                    echo $tokenid;
                }
                else
                {
                    $clang->eT("Auto");
            } ?>
        </li>
        <li><label for='firstname'><?php $clang->eT("First name"); ?>:</label>
            <input type='text' size='30' id='firstname' name='firstname' value="<?php if (isset($firstname))
                    {
                        echo $firstname;
                } ?>" /></li>
        <li><label for='lastname'><?php $clang->eT("Last name"); ?>:</label>
            <input type='text' size='30'  id='lastname' name='lastname' value="<?php if (isset($lastname))
                    {
                        echo $lastname;
                } ?>" /></li>
        <li><label for='email'><?php $clang->eT("Email"); ?>:</label>
            <input type='email' maxlength='320' size='50' id='email' name='email' value="<?php if (isset($email))
                    {
                        echo $email;
                } ?>" /></li>
        <li><label for='emailstatus'><?php $clang->eT("Email Status"); ?>:</label>
            <input type='text' maxlength='320' size='50' id='emailstatus' name='emailstatus' placeholder='OK' value="<?php
                    if (isset($emailstatus))
                    {
                        echo $emailstatus;
                    }
                    else
                    {
                        echo "OK";
                    }
                ?>" /></li>
        <li><label for='token'><?php $clang->eT("Token"); ?>:</label>
            <input type='text' size='20' name='token' id='token' value="<?php if (isset($token))
                    {
                        echo $token;
                } ?>" />
            <?php if ($subaction == "addnew")
                { ?>
                <span class='annotation'><?php $clang->eT("You can leave this blank, and automatically generate tokens using 'Generate Tokens'"); ?></span>
                <?php } ?>
        </li>
        <li><label for='language'><?php $clang->eT("Language"); ?>:</label>
            <?php
                if (isset($language))
                {
                    echo languageDropdownClean($surveyid, $language);
                }
                else
                {
                    echo languageDropdownClean($surveyid, Survey::model()->findByPk($surveyid)->language);
                }
            ?>
        </li>

        <li><label for='sent'><?php $clang->eT("Invitation sent?"); ?></label>
            <input type='text' size='20' id='sent' name='sent' value="<?php
                    if (isset($sent))
                    {
                        echo $sent;
                    }
                    else
                    {
                        echo "N";
                    }
                ?>" /></li>

        <li><label for='remindersent'><?php $clang->eT("Reminder sent?"); ?></label>
            <input type='text' size='20' id='remindersent' name='remindersent' value="<?php
                    if (isset($remindersent))
                    {
                        echo $remindersent;
                    }
                    else
                    {
                        echo "N";
                    }
                ?>" /></li>

        <?php if ($subaction == "edit")
            { ?>
            <li><label for='remindercount'><?php $clang->eT("Reminder count:"); ?></label>
                <input type='text' size='6' id='remindercount' name='remindercount' value="<?php echo $remindercount; ?>" /></li>
            <?php } ?>

        <li><label for='completed'><?php $clang->eT("Completed?"); ?></label>
            <input type='text' size='20' id='completed' name='completed' value="<?php
                    if (isset($completed))
                    {
                        echo $completed;
                    }
                    else
                    {
                        echo "N";
                    }
                ?>" /></li>

        <li><label for='usesleft'><?php $clang->eT("Uses left:"); ?></label>
            <input type='text' size='20' id='usesleft' name='usesleft' value="<?php
                    if (isset($usesleft))
                    {
                        echo $usesleft;
                    }
                    else
                    {
                        echo "1";
                    }
                ?>" /></li>

        <li><label for='validfrom'><?php $clang->eT("Valid from"); ?>:</label>
            <input type='text' class='popupdatetime' size='20' id='validfrom' name='validfrom' value="<?php
                    if (isset($validfrom))
                    {
                        Yii::import('application.libraries.Date_Time_Converter', true);
                        $datetimeobj = new Date_Time_Converter($validfrom, "Y-m-d H:i:s");
                        echo $datetimeobj->convert($dateformatdetails['phpdate'] . ' H:i');
                    }
                ?>" /> <label for='validuntil'><?php $clang->eT('until'); ?>
            </label><input type='text' size='20' id='validuntil' name='validuntil' class='popupdatetime' value="<?php
                    if (isset($validuntil))
                    {
                        $datetimeobj = new Date_Time_Converter($validuntil, "Y-m-d H:i:s");
                        echo $datetimeobj->convert($dateformatdetails['phpdate'] . ' H:i');
                    }
                ?>" /> <span class='annotation'><?php echo sprintf($clang->gT('Format: %s'), $dateformatdetails['dateformat'] . ' ' . $clang->gT('hh:mm')); ?></span>
        </li>

        <?php
            // now the attribute fieds
            foreach ($attrfieldnames as $attr_name => $attr_description)
            {
                            ?>
            <li>
                <label for='<?php echo $attr_name; ?>'><?php echo $attr_description['description'] . ($attr_description['mandatory'] == 'Y' ? '*' : '') ?>:</label>
                <input type='text' size='55' id='<?php echo $attr_name; ?>' name='<?php echo $attr_name; ?>' value='<?php
                        if (isset($$attr_name))
                        {
                            echo htmlspecialchars($$attr_name, ENT_QUOTES, 'UTF-8');
                        }
                    ?>' /></li>
            <?php } ?>

    </ul><p>
        <?php
            switch ($subaction)
            {
                case "edit":
                ?>
                <input type='submit' value='<?php $clang->eT("Update token entry"); ?>' />
                <input type='hidden' name='subaction' value='updatetoken' />
                <input type='hidden' name='tid' value='<?php echo $tokenid; ?>' />
                <?php break;
                case "addnew": ?>
                <input type='submit' value='<?php $clang->eT("Add token entry"); ?>' />
                <input type='hidden' name='subaction' value='inserttoken' />
                <?php break;
        } ?>
        <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' /></p>
</form>
