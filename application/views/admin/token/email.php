    <?php echo PrepareEditorScript(); ?>
    <div class='header ui-widget-header'>
    <?php echo $clang->gT("Send email invitations");?></div>
    <div><br/>

        <?php if ($thissurvey['active']!='Y')
        { ?>
            <div class='messagebox ui-corner-all'><div class='warningheader'><?php echo $clang->gT('Warning!');?></div><?php echo $clang->gT("This survey is not yet activated and so your participants won't be able to fill out the survey.");?></div>
       <?php } ?>
		<div id='tabs'>
		<ul>
		<?php
		$surveylangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($surveylangs,$baselang);
		foreach ($surveylangs as $language)
        {
		echo '<li><a href="#'.$language.'">'.getLanguageNameFromCode($language,false);
		if ($language==$baselang)
            {
                echo"(".$clang->gT("Base language").")";
            }


		echo "</a></li>";
		}
		?>
		</ul>
		<form id='sendinvitation' class='form30' method='post' action='<?php echo site_url("admin/tokens/email/$surveyid");?>'>

            <?php
        foreach ($surveylangs as $language)
        {
            //GET SURVEY DETAILS
            $thissurvey=getSurveyInfo($surveyid,$language);
            $bplang = new limesurvey_lang(array($language));

            if ($ishtml===true)
            {
               $aDefaultTexts=aTemplateDefaultTexts($bplang);
            }
            else
            {
                $aDefaultTexts=aTemplateDefaultTexts($bplang,'unescaped');
            }
            if (!$thissurvey['email_invite'])
            {
                if ($ishtml===true)
                {
                    $thissurvey['email_invite']=html_escape($aDefaultTexts['invitation']);
                }
                else
                {
                    $thissurvey['email_invite']=$aDefaultTexts['invitation'];
                }
            }
            if (!$thissurvey['email_invite_subj'])
            {
                $thissurvey['email_invite_subj']=$aDefaultTexts['invitation_subject'];
            }
            $fieldsarray["{ADMINNAME}"]= $thissurvey['adminname'];
            $fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
            $fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
            $fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
            $fieldsarray["{EXPIRY}"]=$thissurvey["expiry"];

            $subject=Replacefields($thissurvey['email_invite_subj'], $fieldsarray);
            $textarea=Replacefields($thissurvey['email_invite'], $fieldsarray);
            if ($ishtml!==true){$textarea=str_replace(array('<x>','</x>'),array(''),$textarea);}
?>
            <div id="<?php echo $language;?>">

            <ul>
            <li><label for='from_<?php echo $language;?>'><?php echo $clang->gT("From");?>:</label>
            <input type='text' size='50' id='from_<?php echo $language;?>' name='from_<?php echo $language;?>' value="<?php echo "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";?>" /></li>

            <li><label for='subject_<?php echo $language;?>'><?php echo $clang->gT("Subject");?>:</label>
            <input type='text' size='83' id='subject_<?php echo $language;?>' name='subject_<?php echo $language;?>' value="<?php echo $subject;?>" /></li>

            <li><label for='message_<?php echo $language;?>'><?php echo $clang->gT("Message");?>:</label>
            <textarea name='message_<?php echo $language;?>' id='message_<?php echo $language;?>' rows='20' cols='80'>
            <?php echo htmlspecialchars($textarea);?>
            </textarea>
             <?php echo getEditor("email-inv","message_$language","[".$clang->gT("Invitation email:", "js")."](".$language.")",$surveyid,'','',"tokens"); ?>
            </li>
            </ul></div>
			<?php
        }
?>

		  <p>
             <label for='bypassbademails'><?php echo $clang->gT("Bypass token with failing email addresses");?>:</label><select id='bypassbademails' name='bypassbademails'>
             <option value='Y'><?php echo $clang->gT("Yes");?></option>
             <option value='N'><?php echo $clang->gT("No");?></option>
             </select></p><p>
             <input type='submit' value='<?php echo $clang->gT("Send Invitations");?>' />
             <input type='hidden' name='ok' value='absolutely' />
             <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
             <input type='hidden' name='subaction' value='email' />
      <?php  if (isset($tokenid)) { ?> <input type='hidden' name='tid' value='$tokenid' /> <?php }
        if (isset($tokenids)) { ?> <input type='hidden' name='tids' value='|".implode("|", $tokenids);?>' /><?php } ?>
        </form></div>
</div>