<div class='header ui-widget-header'><?php $clang->eT("Bounce settings"); ?></div>
<div id='bouncesettingsdiv'>
    <?php echo CHtml::form(array("admin/tokens/sa/bouncesettings/surveyid/{$surveyid}"), 'post', array('id'=>'bouncesettings', 'name'=>'bouncesettings', 'class'=>'form30')); ?>
        <br>
        <ul><li><label for='bounce_email'><?php $clang->eT('Survey bounce email:'); ?></label>
                <input type='email' size='50' id='bounce_email' name='bounce_email' value="<?php echo $settings['bounce_email']; ?>" ></li>
            <li><label for='bounceprocessing'><?php $clang->eT("Bounce settings to be used"); ?></label>
                <select id='bounceprocessing' name='bounceprocessing'>
                    <option value='N'<?php
                            if ($settings['bounceprocessing'] == 'N')
                            {
                                echo " selected='selected'";
                            }
                        ?>><?php $clang->eT("None"); ?></option>
                    <option value='L'<?php
                            if ($settings['bounceprocessing'] == 'L')
                            {
                                echo " selected='selected'";
                            }
                        ?>><?php $clang->eT("Use settings below"); ?></option>
                    <option value='G'<?php
                            if ($settings['bounceprocessing'] == 'G')
                            {
                                echo " selected='selected'";
                            }
                        ?>><?php $clang->eT("Use global settings"); ?></option>
                </select></li>
            <li><label for='bounceaccounttype'><?php $clang->eT("Server type:"); ?></label>
                <select id='bounceaccounttype' name='bounceaccounttype'>
                    <option value='IMAP'<?php
                            if ($settings['bounceaccounttype'] == 'IMAP')
                            {
                                echo " selected='selected'";
                            }
                        ?>><?php $clang->eT("IMAP"); ?></option>
                    <option value='POP'<?php
                            if ($settings['bounceaccounttype'] == 'POP')
                            {
                                echo " selected='selected'";
                            }
                        ?>><?php $clang->eT("POP"); ?></option>
                </select></li>
            <li><label for='bounceaccounthost'><?php $clang->eT("Server name & port:"); ?></label>
            <input type='text' size='50' id='bounceaccounthost' name='bounceaccounthost' value="<?php echo $settings['bounceaccounthost']; ?>" /> <span class='annotation'><?php $clang->eT("Enter your hostname and port, e.g.: imap.gmail.com:995"); ?></span>
            <li><label for='bounceaccountuser'><?php $clang->eT("User name:"); ?></label>
                <input type='text' size='50' id='bounceaccountuser' name='bounceaccountuser' value="<?php echo $settings['bounceaccountuser']; ?>" /></li>
            <li><label for='bounceaccountpass'><?php $clang->eT("Password:"); ?></label>
                <input type='password' size='50' id='bounceaccountpass' name='bounceaccountpass' value="<?php echo $settings['bounceaccountpass']; ?>"/></li>
            <li><label for='bounceaccountencryption'><?php $clang->eT("Encryption type:"); ?></label>
                <select id='bounceaccountencryption' name='bounceaccountencryption'>
                    <option value='Off'<?php
                            if ($settings['bounceaccountencryption'] == 'Off')
                            {
                                echo " selected='selected'";
                            }
                        ?>><?php $clang->eT("None"); ?></option>
                    <option value='SSL'<?php
                            if ($settings['bounceaccountencryption'] == 'SSL')
                            {
                                echo " selected='selected'";
                            }
                        ?>><?php $clang->eT("SSL"); ?></option>
                    <option value='TLS'<?php
                            if ($settings['bounceaccountencryption'] == 'TLS')
                            {
                                echo " selected='selected'";
                            }
                        ?>><?php $clang->eT("TLS"); ?></option>
                </select></li></ul><br></form></div>
    <p><input type='button' onclick='bouncesettings.submit()' class='standardbtn' value='<?php $clang->eT("Save settings"); ?>' /><br /></p>
