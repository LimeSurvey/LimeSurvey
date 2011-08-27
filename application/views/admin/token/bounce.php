<div class='header ui-widget-header'><?php echo $clang->gT("Bounce settings");?></div>
<div id='bouncesettings'>
<form id='bouncesettings' name='bouncesettings' action='<?php echo site_url("admin/tokens/bouncesettings/$surveyid");?>' method='post'>
<br><li><label for='bounce_email'><?php echo $clang->gT('Survey bounce email:');?></label>
<input type='text' size='50' id='bounce_email' name='bounce_email' value="<?php echo $settings['bounce_email'];?>" ></li>
<li><label for='bounceprocessing'><?php echo $clang->gT("Bounce settings to be used");?></label>
<select id='bounceprocessing' name='bounceprocessing'>
<option value='N'<?php 
if ($settings['bounceprocessing']=='N') {echo " selected='selected'";}
?>><?php echo $clang->gT("None");?></option>
<option value='L'<?php
if ($settings['bounceprocessing']=='L') {echo " selected='selected'";}
?>><?php echo $clang->gT("Use settings below");?></option>
<option value='G'<?php
if ($settings['bounceprocessing']=='G') {echo " selected='selected'";}
?>><?php echo $clang->gT("Use global settings");?></option>
</select></li>
<li><label for='bounceaccounttype'><?php echo $clang->gT("Server type:");?></label>
<select id='bounceaccounttype' name='bounceaccounttype'>
<option value='IMAP'<?php 
if ($settings['bounceaccounttype']=='IMAP') {echo " selected='selected'";}
?>><?php echo $clang->gT("IMAP");?></option>
<option value='POP'<?php
if ($settings['bounceaccounttype']=='POP') {echo " selected='selected'";}
?>><?php echo $clang->gT("POP");?></option>
</select></li>
<li><label for='bounceaccounthost'><?php echo $clang->gT("Server name & port:");?></label>
<input type='text' size='50' id='bounceaccounthost' name='bounceaccounthost' value="<?php echo $settings['bounceaccounthost'];?>" /><font size='1'><?php echo $clang->gT("Enter your hostname and port, e.g.: imap.gmail.com:995");?></font>
<li><label for='bounceaccountuser'><?php echo $clang->gT("User name:");?></label>
<input type='text' size='50' id='bounceaccountuser' name='bounceaccountuser' value="<?php echo $settings['bounceaccountuser'];?>" /></li>
<li><label for='bounceaccountpass'><?php echo $clang->gT("Password:");?></label>
<input type='password' size='50' id='bounceaccountpass' name='bounceaccountpass' value="<?php echo $settings['bounceaccountpass'];?>"/></li>
<li><label for='bounceencryption'><?php echo $clang->gT("Encryption type:");?></label>
<select id='bounceaccountencryption' name='bounceaccountencryption'>
<option value='Off'<?php
if ($settings['bounceaccountencryption']=='Off') {echo " selected='selected'";}
?>><?php echo $clang->gT("None");?></option>
<option value='SSL'<?php
if ($settings['bounceaccountencryption']=='SSL') {echo  " selected='selected'";}
?>><?php echo $clang->gT("SSL");?></option>
<option value='TLS'<?php
if ($settings['bounceaccountencryption']=='TLS') {echo " selected='selected'";}
?>><?php echo $clang->gT("TLS");?></option>
</select></li><br></div></form>
<p><input type='button' onclick='bouncesettings.submit()' class='standardbtn' value='<?php echo $clang->gT("Save settings");?>' /><br /></p>