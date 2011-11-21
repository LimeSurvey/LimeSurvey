<form id='vvexport' method='post' action='<?php echo $this->createUrl("admin/export/sa/vvexport/surveyid/$surveyid/subaction/export");?>'>
<div class='header ui-widget-header'><?php echo $clang->gT("Export a VV survey file");?></div>
<ul>
<li>
<label for='sid'><?php echo $clang->gT("Export Survey");?>:</label>
<input type='text' size='10' value='<?php echo $surveyid;?>' id='sid' name='sid' readonly='readonly' />
</li>
<li>
 <label for='filterinc'><?php echo $clang->gT("Export");?>:</label>
 <select name='filterinc' id='filterinc'>
  <option value='filter' $selecthide><?php echo $clang->gT("Completed responses only");?></option>
  <option value='show' $selectshow><?php echo $clang->gT("All responses");?></option>
  <option value='incomplete' $selectinc><?php echo $clang->gT("Incomplete responses only");?></option>
 </select>
</li>
<li>
 <label for='extension'><?php echo $clang->gT("File Extension");?>: </label>
 <input type='text' id='extension' name='extension' size='3' value='csv' /><span style='font-size: 7pt'>*</span>
</li>
</ul>
<p><input type='submit' value='<?php echo $clang->gT("Export results");?>' />&nbsp;
<input type='hidden' name='subaction' value='export' />
</form>

<p><span style='font-size: 7pt'>* <?php echo $clang->gT("For easy opening in MS Excel, change the extension to 'tab' or 'txt'");?></span><br />