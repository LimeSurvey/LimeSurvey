<div class='header ui-widget-header'><?php echo $clang->gT("Export results");?></div>
<div class='wrap2columns'>
<form id='resultexport' action='<?php echo site_url("admin/export/exportresults/$surveyid");?>' method='post'><div class='left'>

<?php 	if (isset($_POST['sql'])) {echo" - ".$clang->gT("Filtered from statistics script");}
		if (returnglobal('id')<>'') {echo " - ".$clang->gT("Single response");} ?>

<fieldset><legend><?php echo $clang->gT("General");?></legend>

<ul><li><label><?php echo $clang->gT("Range:");?></label> <?php echo $clang->gT("From");?> <input type='text' name='export_from' size='8' value='1' />
<?php echo $clang->gT("to");?> <input type='text' name='export_to' size='8' value='<?php echo $max_datasets;?>' /></li>

<li><br /><label for='filterinc'><?php echo $clang->gT("Completion state");?></label> <select id='filterinc' name='filterinc'>
<option value='filter' $selecthide><?php echo $clang->gT("Completed responses only");?></option>
<option value='show' $selectshow><?php echo $clang->gT("All responses");?></option>
<option value='incomplete' $selectinc><?php echo $clang->gT("Incomplete responses only");?></option>
</select>
</li></ul></fieldset>

<fieldset><legend>
<?php echo $clang->gT("Questions");?></legend>
<ul>
<li><input type='radio' class='radiobtn' name='exportstyle' value='abrev' id='headabbrev' />
<label for='headabbrev'><?php echo $clang->gT("Abbreviated headings");?></label></li>
<li><input type='radio' class='radiobtn' checked name='exportstyle' value='full' id='headfull'  />
<label for='headfull'><?php echo $clang->gT("Full headings");?></label></li>
<li><input type='radio' class='radiobtn' checked name='exportstyle' value='headcodes' id='headcodes' />
<label for='headcodes'><?php echo $clang->gT("Question codes");?></label></li>
<li><br /><input type='checkbox' value='Y' name='convertspacetous' id='convertspacetous' />
<label for='convertspacetous'>
<?php echo $clang->gT("Convert spaces in question text to underscores");?></label></li>
</ul>
</fieldset>

<fieldset>
<legend><?php echo $clang->gT("Answers");?></legend>
<ul>
<li><input type='radio' class='radiobtn' name='answers' value='short' id='ansabbrev' />
<label for='ansabbrev'><?php echo $clang->gT("Answer Codes");?></label></li>

<li><input type='checkbox' value='Y' name='convertyto1' id='convertyto1' style='margin-left: 25px' />
<label for='convertyto1'><?php echo $clang->gT("Convert Y to");?></label> <input type='text' name='convertyto' size='3' value='1' maxlength='1' style='width:10px'  />
</li>
<li><input type='checkbox' value='Y' name='convertnto2' id='convertnto2' style='margin-left: 25px' />
<label for='convertnto2'><?php echo $clang->gT("Convert N to");?></label> <input type='text' name='convertnto' size='3' value='2' maxlength='1' style='width:10px' />
</li><li>
<input type='radio' class='radiobtn' checked name='answers' value='long' id='ansfull' />
<label for='ansfull'>
<?php echo $clang->gT("Full Answers");?></label></li>
</ul></fieldset>
<fieldset><legend><?php echo $clang->gT("Format");?></legend>
<ul>
<li>
<input type='radio' class='radiobtn' name='type' value='doc' id='worddoc' onclick='document.getElementById("ansfull").checked=true;document.getElementById("ansabbrev").disabled=true;' />
<label for='worddoc'>
<?php echo $clang->gT("Microsoft Word (Latin charset)");?></label></li>
<li><input type='radio' class='radiobtn' name='type' value='xls' checked id='exceldoc' <?php if (!function_exists('iconv')) echo ' disabled="disabled" ';?> onclick='document.getElementById("ansabbrev").disabled=false;' />
<label for='exceldoc'><?php echo $clang->gT("Microsoft Excel (All charsets)");?><?php if (!function_exists('iconv'))
{ echo '<font class="warningtitle">'.$clang->gT("(Iconv Library not installed)").'</font>'; } ?>
</label></li>
<li><input type='radio' class='radiobtn' name='type' value='csv' id='csvdoc' <?php if (!function_exists('iconv'))
{ echo 'checked="checked" ';} ?>onclick='document.getElementById(\"ansabbrev\").disabled=false;' />
<label for='csvdoc'><?php echo $clang->gT("CSV File (All charsets)");?></label></li>
<?php if(isset($usepdfexport) && $usepdfexport == 1) { ?>
    <li><input type='radio' class='radiobtn' name='type' value='pdf' id='pdfdoc' onclick='document.getElementById(\"ansabbrev\").disabled=false;' />"
    <label for='pdfdoc'><?php echo $clang->gT("PDF");?><br />
    </label></li>
<?php } ?>
</ul></fieldset>
</div>
<div class='right'>
<fieldset>
<legend><?php echo $clang->gT("Column control");?></legend>

<input type='hidden' name='sid' value='$surveyid' />
<?php if (isset($_POST['sql'])) { ?>
    <input type='hidden' name='sql' value="<?php echo stripcslashes($_POST['sql']);?>" />
<?php }
if (returnglobal('id')<>'') { ?>
    <input type='hidden' name='answerid' value="<?php echo stripcslashes(returnglobal('id'));?>" />
<?php } 
echo $clang->gT("Choose Columns");?>:

<?php if ($afieldcount > 255) { 
    echo "\t<img src='$imageurl/help.gif' alt='".$clang->gT("Help")."' onclick='javascript:alert(\""
    .$clang->gT("Your survey contains more than 255 columns of responses. Spreadsheet applications such as Excel are limited to loading no more than 255. Select the columns you wish to export in the list below.","js")
    ."\")' />";
}
else
{
    echo "\t<img src='$imageurl/help.gif' alt='".$clang->gT("Help")."' onclick='javascript:alert(\""
    .$clang->gT("Choose the columns you wish to export.","js")
    ."\")' />";
} ?>
<br /><select name='colselect[]' multiple size='20'>
<?php $i=1;
foreach($excesscols as $ec)
{
    echo "<option value='$ec'";
    if (isset($_POST['summary']))
    {
        if (in_array($ec, $_POST['summary']))
        {
            echo "selected";
        }
    }
    elseif ($i<256)
    {
        echo " selected";
    }
    echo ">$i: $ec</option>\n";
    $i++;
} ?>
</select>
<br />&nbsp;</fieldset>
<?php if ($thissurvey['anonymized'] == "N" && tableExists("tokens_$surveyid")) { ?>
        <fieldset><legend><?php echo $clang->gT("Token control");?></legend>
        <?php echo $clang->gT("Choose token fields");?>:
        <img src='<?php echo $imageurl;?>/help.gif' alt='<?php echo $clang->gT("Help");?>' onclick='javascript:alert("<?php
        $clang->gT("Your survey can export associated token data with each response. Select any additional fields you would like to export.","js");
        ?>")' /><br />
        <select name='attribute_select[]' multiple size='20'>
        <option value='first_name' id='first_name' /><?php echo $clang->gT("First name");?></option>
        <option value='last_name' id='last_name' /><?php echo $clang->gT("Last name");?></option>
        <option value='email_address' id='email_address' /><?php echo $clang->gT("Email address");?></option>
        <option value='token' id='token' /><?php echo $clang->gT("Token");?></option>

        <?php $attrfieldnames=GetTokenFieldsAndNames($surveyid,true);
        foreach ($attrfieldnames as $attr_name=>$attr_desc)
        {
           echo "<option value='$attr_name' id='$attr_name' />".$attr_desc."</option>\n";
        } ?>
        </select></fieldset>
 <?php } ?>
</div>
<div style='clear:both;'><p><input type='submit' value='<?php echo $clang->gT("Export data");?>' /></div></form></div>