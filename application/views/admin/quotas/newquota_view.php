<div class='header ui-widget-header'><?php $clang->eT("New quota");?></div>
<?php echo CHtml::form(array("admin/quotas/sa/insertquota/surveyid/{$iSurveyId}"), 'post', array('class'=>'form30', 'id'=>'addnewquotaform', 'name'=>'addnewquotaform')); ?>
    <ul>
        <li>
            <label for="quota_name"><?php $clang->eT("Quota name");?>:</label>
            <input id="quota_name" name="quota_name" type="text" size="30" maxlength="255" />
        </li>
        <li>
            <label for="quota_limit"><?php $clang->eT("Quota limit");?>:</label>
            <input id="quota_limit" name="quota_limit" type="text" size="12" maxlength="8" />
        </li>
        <li>
            <label for="quota_action"><?php $clang->eT("Quota action");?>:</label>
            <select id="quota_action" name="quota_action">
                <option value ="1"><?php $clang->eT("Terminate survey");?></option>
                <option value ="2"><?php $clang->eT("Terminate survey with warning");?></option>
            </select>
        </li>
        <li>
            <label for="autoload_url"><?php $clang->eT("Autoload URL");?>:</label>
            <input id="autoload_url" name="autoload_url" type="checkbox" value="1" />
        </li>
    </ul>
    <div id="tabs"><ul>
            <?php foreach ($langs as $lang) { ?>
                <li><a href="#tabpage_<?php echo $lang ?>"><?php echo getLanguageNameFromCode($lang,false);
                        if ($lang==$baselang) {echo '('.$clang->gT("Base language").')';} ;?></a></li>
                <?php } ?>
        </ul>


        <?php foreach ($langs as $lang) { ?>
            <div id="tabpage_<?php echo $lang ?>">
                <ul>
                    <li>
                        <label for="quotals_message_<?php echo $lang;?>"><?php $clang->eT("Quota message");?>:</label>
                        <textarea id="quotals_message_<?php echo $lang;?>" name="quotals_message_<?php echo $lang;?>" cols="60" rows="6"><?php $clang->eT("Sorry your responses have exceeded a quota on this survey.");?></textarea>
                    </li>
                    <li>
                        <label for="quotals_url_<?php echo $lang;?>"><?php $clang->eT("URL");?>:</label>
                        <input id="quotals_url_<?php echo $lang;?>" name="quotals_url_<?php echo $lang;?>" type="text" size="50" maxlength="255" value="<?php echo $thissurvey['url'];?>" />
                    </li>
                    <li>
                        <label for="quotals_urldescrip_<?php echo $lang;?>"><?php $clang->eT("URL description");?>:</label>
                        <input id="quotals_urldescrip_<?php echo $lang;?>" name="quotals_urldescrip_<?php echo $lang;?>" type="text" size="50" maxlength="255" value="<?php echo $thissurvey['urldescrip'];?>" />
                    </li>
                </ul>
            </div>
            <?php } ?>
        <input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
        <input type="hidden" name="action" value="quotas" />
        <input type="hidden" name="subaction" value="insertquota" />
    </div>
    <p><input name="submit" type="submit" value="<?php $clang->eT("Add New Quota");?>" />
	</form>
