<?php echo PrepareEditorScript(false, $this); ?>
<script type="text/javascript">
<!--
    var strnogroup='<?php $clang->eT("There are no groups available.", "js");?>';
--></script>
<div class='menubar'>
	<div class='menubar-title ui-widget-header'>
		<strong><?php $clang->eT("Assessments");?></strong>
	</div>
	<div class='menubar-main'>
		<div class='menubar-left'>
			<a href="#" onclick="window.open('<?php echo $this->createUrl("admin/survey/view/surveyid/$surveyid");?>', '_top')" title='<?php $clang->eTview("Return to survey administration");?>'>
			<img src='<?php echo $imageurl;?>/home.png' alt='<?php $clang->eT("Return to survey administration");?>' /></a>
			<img src='$imageurl/blank.gif' alt='' width='11'  />
			<img src='$imageurl/seperator.gif' alt='' />

			<?php if ($surveyinfo['assessments']!='Y') { ?>
				<span style="font-size:11px;"><?php echo sprintf($clang->gT("Notice: Assessment mode for this survey is not activated. You can activate it in the %s survey settings %s (tab 'Notification & data management')."),'<a href="admin.php?action=editsurvey&amp;sid='.$surveyid.'">','</a>');?></span>
			<?php } ?>
		</div>
	</div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p> <!-- CSS Firefox 2 transition fix -->
<div class='header ui-widget-header'><?php $clang->eT("Assessment rules");?></div>

<table class='assessmentlist'><thead>
<tr><th><?php $clang->eT("ID");?></th><th><?php $clang->eT("Actions");?></th><th><?php $clang->eT("SID");?></th>
<?php foreach ($headings as $head) {
	echo "<th>$head</th>\n";
} ?>
<th><?php $clang->eT("Title");?></th><th><?php $clang->eT("Message");?></th>
</tr></thead><tbody>
<?php $flipflop=true;
foreach($assessments as $assess) {
	$flipflop=!$flipflop;
	if ($flipflop==true){echo "<tr class='oddrow'>\n";}
else {echo "<tr class='evenrow'>\n";} ?>
<td><?php echo $assess['id'];?></td>
<td>
<?php if (bHasSurveyPermission($surveyid, 'assessments','update')) { ?>
    <form method='post' action='<?php echo $this->createUrl("admin/assessments/index/surveyid/$surveyid");?>'>
        <input type='image' src='<?php echo $imageurl;?>/token_edit.png' alt='<?php $clang->eT("Edit");?>' />
        <input type='hidden' name='action' value='assessmentedit' />
        <input type='hidden' name='id' value="<?php echo $assess['id'];?>" />
    </form>
<?php } ?>

<?php if (bHasSurveyPermission($surveyid, 'assessments','delete')) { ?>
     <form method='post' action='<?php echo $this->createUrl("admin/assessments/index/surveyid/$surveyid");?>'>
     <input type='image' src='<?php echo $imageurl;?>/token_delete.png' alt='<?php $clang->eT("Delete");?>' onclick='return confirm("<?php $clang->eT("Are you sure you want to delete this entry?","js");?>")' />
     <input type='hidden' name='action' value='assessmentdelete' />
     <input type='hidden' name='id' value='<?php echo $assess['id'];?>' />
     </form>
<?php } ?>
</td><td><?php echo $assess['sid'];?></td>
<?php if ($assess['scope'] == "T") { ?>
	<td><?php $clang->eT("Total");?></td>
	<td>-</td>
<?php } else { ?>
	<td><?php $clang->eT("Question group");?></td>
	<td><?php echo $groups[$assess['gid']]." (".$assess['gid'].")";?></td>
<?php } ?>


<td><?php echo $assess['minimum'];?></td>
<td><?php echo $assess['maximum'];?></td>
<td><?php echo stripslashes($assess['name']);?></td>
<td><?php echo strip_tags(strip_javascript($assess['message']));?></td>

</tr>
<?php } ?>
</tbody></table>

<?php if ((bHasSurveyPermission($surveyid, 'assessments','update') && $actionvalue=="assessmentupdate") || (bHasSurveyPermission($surveyid, 'assessments','create')&& $actionvalue=="assessmentadd")) { ?>
<br /><form method='post' class='form30' id='assessmentsform' name='assessmentsform' action='<?php echo $this->createUrl("admin/assessments/index/surveyid/$surveyid");?>'>
	<div class='header ui-widget-header'><?php echo $actiontitle;?></div>
	<ul><li><label><?php $clang->eT("Scope");?></label>
	<input type='radio' id='radiototal' name='scope' value='T' <?php
    if (!isset($editdata) || $editdata['scope'] == "T") {echo "checked='checked' ";} ?>/>
    <label for='radiototal'><?php $clang->eT("Total");?></label>
    <input type='radio' id='radiogroup' name='scope' value='G' <?php
    if (isset($editdata) && $editdata['scope'] == "G") {echo " checked='checked' ";} ?>/>
    <label for='radiogroup'><?php $clang->eT("Group");?></label></li>
    <li><label for='gid'><?php $clang->eT("Question group");?></label>
        <select name='gid' id='gid'>
            <?php
            foreach ($groups as $groupId => $groupName) {
                echo '<option value="' . $groupId . '"'.($editId == $groupId ? ' selected' : '').'>' . $groupName . '</option>';
            }
            ?>
        </select></li>
    <li><label for='minimum'><?php $clang->eT("Minimum");?></label><input type='text' id='minimum' name='minimum' class='numbersonly'<?php
    if (isset($editdata)) {echo " value='{$editdata['minimum']}' ";} ?>/></li>
    <li><label for='maximum'><?php $clang->eT("Maximum");?></label><input type='text' id='maximum' name='maximum' class='numbersonly'<?php
    if (isset($editdata)) {echo " value='{$editdata['maximum']}' ";} ?>/></li>

	</ul><div id="languagetabs">
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
	    <?php $clang->eT("Heading");?><br/>
	    <input type='text' name='name_<?php echo $assessmentlang;?>' size='80' value='<?php echo $heading;?>'/><br /><br />
	    <?php $clang->eT("Message");?>
	    <textarea name='assessmentmessage_<?php echo $assessmentlang;?>' id='assessmentmessage_<?php echo $assessmentlang;?>' rows='10' cols='80'><?php echo $message;?></textarea >

	    </div>
    <?php } ?>
    </div>

    <div><input type='submit' value='<?php $clang->eT("Save");?>' />
    <?php if ($action == "assessmentedit") echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='".$clang->gT("Cancel")."' onclick=\"document.assessmentsform.action.value='assessments'\" />\n ";?>
    <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
    <input type='hidden' name='action' value='<?php echo $actionvalue;?>' />
    <input type='hidden' name='id' value='<?php echo $editId;?>' />
    </div>
    </form>
    <?php foreach ($assessmentlangs as $assessmentlang) {
	    echo getEditor("assessment-text","assessmentmessage_$assessmentlang", "[".$clang->gT("Message:", "js")."]",$surveyid,$gid,null,$action);
    }
} ?>