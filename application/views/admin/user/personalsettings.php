<?php // prepare data for the htmleditormode preference
$edmod1='';
$edmod2='';
$edmod3='';
$edmod4='';
switch ($this->session->userdata('htmleditormode'))
{
    case 'none':
        $edmod2="selected='selected'";
        break;
    case 'inline':
        $edmod3="selected='selected'";
        break;
    case 'popup':
        $edmod4="selected='selected'";
        break;
    default:
        $edmod1="selected='selected'";
        break;
} ?>

<div class='formheader'>
<strong><?php echo $clang->gT("Your personal settings");?></strong>
</div>
<div>
<form action='<?php echo site_url("admin/user/personalsettings/");?>' id='personalsettings' method='post'>
<ul>

<li><label for='lang'><?php echo $clang->gT("Interface language");?>:</label>
<select id='lang' name='lang'>
<option value='auto'<?php if ($sSavedLanguage == 'auto') {echo " selected='selected'";} ?>>
	<?php echo $clang->gT("(Autodetect)");?></option>
<?php foreach (getlanguagedata(true) as $langkey=>$languagekind) { ?>
    <option value='<?php echo $langkey;?>'<?php if ($langkey == $sSavedLanguage) {echo " selected='selected'";}?>>
    	<?php echo $languagekind['nativedescription'];?> - <?php echo $languagekind['description'];?></option>
<?php } ?>
</select>
</li>

<li>
<label for='htmleditormode'><?php echo $clang->gT("HTML editor mode");?>:</label>
<select id='htmleditormode' name='htmleditormode'>
<option value='default' <?php echo $edmod1;?>><?php echo $clang->gT("Default");?></option>
<option value='inline' <?php echo $edmod3;?>><?php echo $clang->gT("Inline HTML editor");?></option>
<option value='popup' <?php echo $edmod4;?>><?php echo $clang->gT("Popup HTML editor");?></option>
<option value='none' <?php echo $edmod2;?>><?php echo $clang->gT("No HTML editor");?></option>
</select>
</li>

<li>
<label for='questionselectormode'><?php echo $clang->gT("Question selector mode");?>:</label>
<select id='questionselectormode' name='questionselectormode'>
<option value='default'><?php echo $clang->gT("Default");?></option>
<option value='full'<?php if ($this->session->userdata('questionselectormode')=="full"){
	echo "selected='selected'";}?>><?php echo $clang->gT("Full question selector");?></option>
<option value='none'<?php if ($this->session->userdata('questionselectormode')=="none"){
	echo "selected='selected'";}?>><?php echo $clang->gT("Simple question selector");?></option>
</select>
  </li>

<li>
<label for='templateeditormode'><?php echo $clang->gT("Template editor mode");?>:</label>
<select id='templateeditormode' name='templateeditormode'>
	<option value='default'><?php echo $clang->gT("Default");?></option>
<option value='full'<?php if ($this->session->userdata('templateeditormode')=="full"){
	echo "selected='selected'";};?>><?php echo $clang->gT("Full template editor");?></option>
<option value='none'<?php if ($this->session->userdata('templateeditormode')=="none"){
	echo "selected='selected'";}?>><?php echo $clang->gT("Simple template editor");?></option>
</select>
</li>
    
<li>
<label for='dateformat'><?php echo $clang->gT("Date format");?>:</label>
<select name='dateformat' id='dateformat'>
<?php foreach (getDateFormatData() as $index=>$dateformatdata)
{
    echo "<option value='{$index}'";
    if ($index==$this->session->userdata('dateformat'))
    {
        echo "selected='selected'";
    }
     
    echo ">".$dateformatdata['dateformat'].'</option>';
} ?>
</select>
</li>
</ul>
<p><input type='hidden' name='action' value='savepersonalsettings' /><input class='submit' type='submit' value='<?php echo $clang->gT("Save settings");
?>' /></p></form></div>
