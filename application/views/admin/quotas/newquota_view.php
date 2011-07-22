<div class='header ui-widget-header'><?php echo $clang->gT("New quota");?></div>
<form class="form30" action="<?php echo site_url("admin/quotas/$surveyid");?>" method="post" id="addnewquotaform" name="addnewquotaform">
<ul>
		<li>
			<label for="quota_name"><?php echo $clang->gT("Quota name");?>:</label>
			<input id="quota_name" name="quota_name" type="text" size="30" maxlength="255" />
		</li>
		<li>
            <label for="quota_limit"><?php echo $clang->gT("Quota limit");?>:</label>
			<input id="quota_limit" name="quota_limit" type="text" size="12" maxlength="8" />
		</li>
		<li>
            <label for="quota_action"><?php echo $clang->gT("Quota action");?>:</label>
			<select id="quota_action" name="quota_action">
				<option value ="1"><?php echo $clang->gT("Terminate survey");?></option>
				<option value ="2"><?php echo $clang->gT("Terminate survey with warning");?></option>
			</select>
		</li>
		<li>
            <label for="autoload_url"><?php echo $clang->gT("Autoload URL");?>:</label>
			<input id="autoload_url" name="autoload_url" type="checkbox" value="1" />
		</li>
</ul>
    <div class="tab-pane" id="tab-pane-quota-<?php echo $surveyid;?>">
    	
<?php foreach ($langs as $lang) { ?>
		<div class="tab-page">
				  	 	 <h2 class="tab"><?php echo GetLanguageNameFromCode($lang,false);
    if ($lang==$baselang) {echo '('.$clang->gT("Base language").')';} ;?>
    </h2>
			<ul>
  				<li>
  					<label for="quotals_message_<?php echo $lang;?>"><?php echo $clang->gT("Quota message");?>:</label>
  					<textarea id="quotals_message_<?php echo $lang;?>" name="quotals_message_<?php echo $lang;?>" cols="60" rows="6"><?php echo $clang->gT("Sorry your responses have exceeded a quota on this survey.");?></textarea>
  				</li>
  				<li>
    				<label for="quotals_url_<?php echo $lang;?>"><?php echo $clang->gT("URL");?>:</label>
    				<input id="quotals_url_<?php echo $lang;?>" name="quotals_url_<?php echo $lang;?>" type="text" size="50" maxlength="255" value="<?php echo $thissurvey['url'];?>" />
  				</li>
                <li>
                    <label for="quotals_urldescrip_<?php echo $lang;?>"><?php echo $clang->gT("URL description");?>:</label>
                    <input id="quotals_urldescrip_<?php echo $lang;?>" name="quotals_urldescrip_<?php echo $lang;?>" type="text" size="50" maxlength="255" value="<?php echo $thissurvey['urldescrip'];?>" />
                </li>
			</ul>
		</div>
<?php } ?>
		<input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
		<input type="hidden" name="action" value="quotas" />
		<input type="hidden" name="subaction" value="insertquota" />
		</div>
		<p><input name="submit" type="submit" value="<?php echo $clang->gT("Add New Quota");?>" />
	</form>