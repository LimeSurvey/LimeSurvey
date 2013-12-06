<?php echo PrepareEditorScript(false, $this); ?>
<script type="text/javascript">
<!--
    var strnogroup='<?php $clang->eT("There are no groups available.", "js");?>';
--></script>
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
<?php if (Permission::model()->hasSurveyPermission($surveyid, 'assessments','update')) { ?>
    <?php echo CHtml::form(array("admin/assessments/sa/index/surveyid/{$surveyid}"), 'post');?>
        <input type='image' src='<?php echo $imageurl;?>edit_16.png' alt='<?php $clang->eT("Edit");?>' />
        <input type='hidden' name='action' value='assessmentedit' />
        <input type='hidden' name='id' value="<?php echo $assess['id'];?>" />
    </form>
<?php } ?>

<?php if (Permission::model()->hasSurveyPermission($surveyid, 'assessments','delete')) { ?>
     <?php echo CHtml::form(array("admin/assessments/sa/index/surveyid/{$surveyid}"), 'post');?>
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
<td><?php 
    $aReplacementData=array();
    templatereplace($assess['name'],array(),$aReplacementData,'Unspecified', false ,$assess['sid']);
    echo FlattenText(LimeExpressionManager::GetLastPrettyPrintExpression(), true);
    ?></td>
<td><?php 
    $aReplacementData=array();
    templatereplace($assess['message'],array(),$aReplacementData,'Unspecified', false ,$assess['sid']);
    echo FlattenText(LimeExpressionManager::GetLastPrettyPrintExpression(), true);
    ?></td>

</tr>
<?php } ?>
</tbody></table>

<?php if ((Permission::model()->hasSurveyPermission($surveyid, 'assessments','update') && $actionvalue=="assessmentupdate") || (Permission::model()->hasSurveyPermission($surveyid, 'assessments','create')&& $actionvalue=="assessmentadd")) { ?>
<br />
<?php echo CHtml::form(array("admin/assessments/sa/index/surveyid/{$surveyid}"), 'post', array('class'=>'form30','id'=>'assessmentsform','name'=>'assessmentsform'));?>
	<div class='header ui-widget-header'><?php echo $actiontitle;?></div>
	<ul class="assessmentscope"><li><label><?php $clang->eT("Scope");?></label>
	<input type='radio' id='radiototal' name='scope' value='T' <?php
    if (!isset($editdata) || $editdata['scope'] == "T") {echo "checked='checked' ";} ?>/>
    <label for='radiototal'><?php $clang->eT("Total");?></label>
    <input type='radio' id='radiogroup' name='scope' value='G' <?php
    if (isset($editdata) && $editdata['scope'] == "G") {echo " checked='checked' ";} ?>/>
    <label for='radiogroup'><?php $clang->eT("Group");?></label></li>
    <li><label for='gid'><?php $clang->eT("Question group");?></label>
        <?php 
        if (isset($groups))
        { ?>
	        <select name='gid' id='gid'>
	            <?php
	            foreach ($groups as $groupId => $groupName) {
	                echo '<option value="' . $groupId . '"'.(isset($editdata['gid']) && $editdata['gid']== $groupId ? ' selected' : '').'>' . $groupName . '</option>';
	            }
	            ?>
	        </select>
    	<?php
		}
		else
			echo $clang->eT("No question group found."); 	 
    	?> 
    </li>
    <li><label for='minimum'><?php $clang->eT("Minimum");?></label><input type='text' id='minimum' name='minimum' class='numbersonly'<?php
    if (isset($editdata)) {echo " value='{$editdata['minimum']}' ";} ?>/></li>
    <li><label for='maximum'><?php $clang->eT("Maximum");?></label><input type='text' id='maximum' name='maximum' class='numbersonly'<?php
    if (isset($editdata)) {echo " value='{$editdata['maximum']}' ";} ?>/></li>

	</ul><div id="languagetabs">
    <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <?php foreach ($assessmentlangs as $assessmentlang)
    {
	    $position=0;
	    echo '<li class="ui-state-default ui-corner-top" style="clear: none;"><a href="#tablang'.$assessmentlang.'">'.getLanguageNameFromCode($assessmentlang, false);
	    if ($assessmentlang==$baselang) {echo ' ('.$clang->gT("Base language").')';}
	    echo '</a></li>';
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
	    <ul><li><label for='name_<?php echo $assessmentlang;?>'><?php $clang->eT("Heading");?>:</label>
	    <input type='text' name='name_<?php echo $assessmentlang;?>' id='name_<?php echo $assessmentlang;?>' size='80' value='<?php echo $heading;?>'/></li>
	    <li><label for='assessmentmessage_<?php echo $assessmentlang;?>'><?php $clang->eT("Message");?>:</label>
	    <textarea name='assessmentmessage_<?php echo $assessmentlang;?>' id='assessmentmessage_<?php echo $assessmentlang;?>' rows='10' cols='80'><?php echo $message;?></textarea>
        <?php echo getEditor("assessment-text","assessmentmessage_$assessmentlang", "[".$clang->gT("Message:", "js")."]",$surveyid,$gid,null,$action); ?>
        </li>
        <li style="text-align:center;"><input type='submit' value='<?php $clang->eT("Save");?>'/></li></ul>
	    </div>
    <?php } ?>
    </div>
    <div>
    <?php if ($action == "assessmentedit") echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='".$clang->gT("Cancel")."' onclick=\"document.assessmentsform.action.value='assessments'\" />\n ";?>
    <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
    <input type='hidden' name='action' value='<?php echo $actionvalue;?>' />
    <input type='hidden' name='id' value='<?php echo $editId;?>' />
    </div>
    </form>
    <?php 
} ?>
