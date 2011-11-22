<?php echo PrepareEditorScript(false, $this); ?>
<script type="text/javascript">
<!--
    var strnogroup='<?php echo $clang->gT("There are no groups available.", "js");?>';
--></script>
<div class='menubar'>
	<div class='menubar-title ui-widget-header'>
		<strong><?php echo $clang->gT("Assessments");?></strong>
	</div>
	<div class='menubar-main'>
		<div class='menubar-left'>
			<a href="#" onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid");?>', '_top')" title='<?php echo $clang->gTview("Return to survey administration");?>'>
			<img name='Administration' src='<?php echo $imageurl;?>/home.png' alt='<?php echo $clang->gT("Return to survey administration");?>' /></a>
			<img src='$imageurl/blank.gif' alt='' width='11'  />
			<img src='$imageurl/seperator.gif' alt='' />

			<?php if ($surveyinfo['assessments']!='Y') { ?>
				<span style="font-size:11px;"><?php echo sprintf($clang->gT("Notice: Assessment mode for this survey is not activated. You can activate it in the %s survey settings %s (tab 'Notification & data management')."),'<a href="admin.php?action=editsurvey&amp;sid='.$surveyid.'">','</a>');?></span>
			<?php } ?>
		</div>
	</div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p> <!-- CSS Firefox 2 transition fix -->
<div class='header ui-widget-header'><?php echo $clang->gT("Assessment rules");?></div>

<table class='assessmentlist'><thead>
<tr><th><?php echo $clang->gT("ID");?></th><th><?php echo $clang->gT("Actions");?></th><th><?php echo $clang->gT("SID");?></th>
<?php foreach ($headings as $head) {
	echo "<th>$head</th>\n";
} ?>
<th><?php echo $clang->gT("Title");?></th><th><?php echo $clang->gT("Message");?></th>
</tr></thead><tbody>
<?php $flipflop=true;
foreach($assessments as $assess) {
	$flipflop=!$flipflop;
	if ($flipflop==true){echo "<tr class='oddrow'>\n";}
else {echo "<tr class='evenrow'>\n";} ?>
<td><?php echo $assess['id'];?></td>
<td>
<?php if (bHasSurveyPermission($surveyid, 'assessments','update')) { ?>
    <form method='post' action='<?php echo $this->createUrl("admin/assessments/surveyid/$surveyid");?>'>
        <input type='image' src='<?php echo $imageurl;?>/token_edit.png' alt='<?php echo $clang->gT("Edit");?>' />
        <input type='hidden' name='action' value='assessmentedit' />
        <input type='hidden' name='id' value="<?php echo $assess['id'];?>" />
    </form>
<?php } ?>

<?php if (bHasSurveyPermission($surveyid, 'assessments','delete')) { ?>
     <form method='post' action='<?php echo $this->createUrl("admin/assessments/surveyid/$surveyid");?>'>
     <input type='image' src='<?php echo $imageurl;?>/token_delete.png' alt='<?php echo $clang->gT("Delete");?>' onclick='return confirm("<?php echo $clang->gT("Are you sure you want to delete this entry?","js");?>")' />
     <input type='hidden' name='action' value='assessmentdelete' />
     <input type='hidden' name='id' value='<?php echo $assess['id'];?>' />
     </form>
<?php } ?>
</td><td><?php echo $assess['sid'];?></td>
<?php if ($assess['scope'] == "T") { ?>
	<td><?php echo $clang->gT("Total");?></td>
	<td>-</td>
<?php } else { ?>
	<td><?php echo $clang->gT("Question group");?></td>
	<td><?php echo $groups[$assess['gid']]['group_name']." (".$assess['gid'].")";?></td>
<?php } ?>


<td><?php echo $assess['minimum'];?></td>
<td><?php echo $assess['maximum'];?></td>
<td><?php echo stripslashes($assess['name']);?></td>
<td><?php echo strip_tags(strip_javascript($assess['message']));?></td>

</tr>
<?php } ?>
</tbody></table>

<?php if ((bHasSurveyPermission($surveyid, 'assessments','update') && $actionvalue=="assessmentupdate") || (bHasSurveyPermission($surveyid, 'assessments','create')&& $actionvalue=="assessmentadd")) { ?>
<br /><form method='post' class='form30' id='assessmentsform' name='assessmentsform' action='<?php echo $this->createUrl("admin/assessments/surveyid/$surveyid");?>'>
	<div class='header ui-widget-header'><?php echo $actiontitle;?></div>
	<ul><li><label><?php echo $clang->gT("Scope");?></label>
	<input type='radio' id='radiototal' name='scope' value='T' <?php
    if (!isset($editdata) || $editdata['scope'] == "T") {echo "checked='checked' ";} ?>/>
    <label for='radiototal'><?php echo $clang->gT("Total");?></label>
    <input type='radio' id='radiogroup' name='scope' value='G' <?php
    if (isset($editdata) && $editdata['scope'] == "G") {echo " checked='checked' ";} ?>/>
    <label for='radiogroup'><?php echo $clang->gT("Group");?></label></li>
    <li><label for='gid'><?php echo $clang->gT("Question group");?></label>
    <?php echo $groupselect?></li>
    <li><label for='minimum'><?php echo $clang->gT("Minimum");?></label><input type='text' id='minimum' name='minimum' class='numbersonly'<?php
    if (isset($editdata)) {echo " value='{$editdata['minimum']}' ";} ?>/></li>
    <li><label for='maximum'><?php echo $clang->gT("Maximum");?></label><input type='text' id='maximum' name='maximum' class='numbersonly'<?php
    if (isset($editdata)) {echo " value='{$editdata['maximum']}' ";} ?>/></li>

    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	</table><div id="languagetabs">
    <ul>
    <?php foreach ($assessmentlangs as $assessmentlang)
    {
	    $position=0;
	    echo '<li><a href="#tablang'.$assessmentlang.'"><span>'.getLanguageNameFromCode($assessmentlang, false);
	    if ($assessmentlang==$baselang) {echo ' ('.$clang->gT("Base language").')';}
	    echo '</span></a></li>';
    } ?>
    </ul>
    <?php foreach ($assessmentlangs as $assessmentlang)
    {
	    $heading=''; $message='';
	    if ($action == "assessmentedit")
	    {
	    	$results = Assessment::model()->findAllByAttributes(array('id' => $_POST['id'], 'language' => $assessmentlang));
		    foreach ($results as $row) {
		        $editdata=$row->attributes;
		    }
		    $heading=htmlspecialchars($editdata['name'],ENT_QUOTES);
		    $message=htmlspecialchars($editdata['message']);
	    } ?>
	    <div id="tablang<?php echo $assessmentlang;?>">
	    <?php echo $clang->gT("Heading");?><br/>
	    <input type='text' name='name_<?php echo $assessmentlang;?>' size='80' value='<?php echo $heading;?>'/><br /><br />
	    <?php echo $clang->gT("Message");?>
	    <textarea name='assessmentmessage_<?php echo $assessmentlang;?>' id='assessmentmessage_<?php echo $assessmentlang;?>' rows='10' cols='80'><?php echo $message;?></textarea >

	    </div>
    <?php } ?>
    </div>

    <p><input type='submit' value='<?php echo $clang->gT("Save");?>' />
    <?php if ($action == "assessmentedit") echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='".$clang->gT("Cancel")."' onclick=\"document.assessmentsform.action.value='assessments'\" />\n ";?>
    <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
    <input type='hidden' name='action' value='<?php echo $actionvalue;?>' />
    <input type='hidden' name='id' value='<?php echo $thisid;?>' />
    </div>
    </form>
    <?php foreach ($assessmentlangs as $assessmentlang) {
	    echo getEditor("assessment-text","assessmentmessage_$assessmentlang", "[".$clang->gT("Message:", "js")."]",$surveyid,$gid,null,$action);
    }
} ?>