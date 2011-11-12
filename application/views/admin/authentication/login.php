<?php $this->_getAdminHeader(); ?>
<form name='loginform' id='loginform' method='post' action='<?php echo $this->createUrl("admin/authentication/login");?>' >
    <br /><p><strong><?php echo $summary;?></strong><br /><br /></p>
    <ul>
        <li><label for='user'><?php $clang->eT("Username");?></label>
            <input name='user' id='user' type='text' size='40' maxlength='40' value='' /></li>
        <li><label for='password'><?php $clang->eT("Password");?></label>
            <input name='password' id='password' type='password' size='40' maxlength='40' /></li>
        <li><label for='loginlang'><?php $clang->eT("Language");?></label>
            <select id='loginlang' name='loginlang'>
                <option value="default" selected="selected"><?php $clang->eT('Default');?></option>
                <?php
                    $x=0;
                    foreach (getLanguageDataRestricted(true) as $sLangKey=>$aLanguage)
                    {
                        //The following conditional statements select the browser language in the language drop down box and echoes the other options.
                    ?>
                    <option value='<?php echo $sLangKey; ?>'><?php echo $aLanguage['nativedescription']." - ".$aLanguage['description']; ?></option>
                    <?php
                    }
                ?>
            </select>
        </li>
    </ul>
    <p><input type='hidden' name='action' value='login' />
        <input class='action' type='submit' value='<?php $clang->eT("Login");?>' /><br />&nbsp;
        <br/>
        <?php
            if (Yii::app()->getConfig("display_user_password_in_email") === true)
            {
            ?>
            <a href='<?php echo $this->createUrl("admin/authentication/forgotpassword");?>'><?php $clang->eT("Forgot your password?");?></a><br />&nbsp;
            <?php
            }
        ?>
    </p></form><br />
<script type='text/javascript'>
    document.getElementById('user').focus();
       </script>
<?php $this->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual")) ?>