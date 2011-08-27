<form name='loginform' id='loginform' method='post' action='<?php echo site_url("admin/authentication/login");?>' ><br /><p><strong><?php echo $summary;?></strong><br />	<br />
<ul>
                                        <li><label for='user'><?php echo $clang->gT("Username");?></label>
                                        <input name='user' id='user' type='text' size='40' maxlength='40' value='' /></li>
                                        <li><label for='password'><?php echo $clang->gT("Password");?></label>
                                        <input name='password' id='password' type='password' size='40' maxlength='40' /></li>
                                        <li><label for='loginlang'><?php echo $clang->gT("Language");?></label>
                                        <select id='loginlang' name='loginlang' style='width:216px;'>
            <option value="default" selected="selected"><?php echo $clang->gT('Default');?></option>
<?php
			$x=0;
			foreach (getlanguagedata(true) as $langkey=>$languagekind)
            {
				//The following conditional statements select the browser language in the language drop down box and echoes the other options.
					?>
                <option value='<?php echo $langkey; ?>'><?php echo $languagekind['nativedescription']." - ".$languagekind['description']; ?></option>
                <?php
            }
?>
</select>
</li>
</ul>
                <p><input type='hidden' name='action' value='login' />
                <input type='hidden' name='refererargs' value='<?php echo $refererargs;?>' />
                <input class='action' type='submit' value='<?php echo $clang->gT("Login");?>' /><br />&nbsp;
                <br/>
                <?php
        if ($this->config->item("display_user_password_in_email") === true)
        {
            ?>
            <p><a href='<?php echo site_url("admin/authentication/forgotpassword");?>'><?php echo $clang->gT("Forgot Your Password?");?></a><br />&nbsp;
            <?php
        }
		?>
                                                </form><br /></p>
                                                <script type='text/javascript'>
                                                  document.getElementById('user').focus();
                                               </script>