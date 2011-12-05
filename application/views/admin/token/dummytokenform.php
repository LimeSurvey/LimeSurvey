	    <div class='header ui-widget-header'>
	    <?php echo $clang->gT("Create dummy tokens"); ?>
	    </div>
	    <form id='edittoken' class='form30' method='post' action='<?php echo $this->createUrl("admin/tokens/sa/adddummys/surveyid/$surveyid/subaction/add");?>'>
	    <ul>
	    <li><label>ID:</label>
	    <?php echo $clang->gT("Auto");?>
	    </li>
	    <li><label for='amount'><?php echo $clang->gT("Number of tokens");?>:</label>
	    <input type='text' size='20' id='amount' name='amount' value="100" /></li>
	    <li><label for='tokenlen'><?php echo $clang->gT("Token length");?>:</label>
	    <input type='text' size='20' id='tokenlen' name='tokenlen' value="<?php echo $tokenlength;?>" /></li>
	    <li><label for='firstname'><?php echo $clang->gT("First name");?>:</label>
	    <input type='text' size='30' id='firstname' name='firstname' value="" /></li>
	    <li><label for='lastname'><?php echo $clang->gT("Last name");?>:</label>
	    <input type='text' size='30'  id='lastname' name='lastname' value="" /></li>
	    <li><label for='email'><?php echo $clang->gT("Email");?>:</label>
	    <input type='text' maxlength='320' size='50' id='email' name='email' value="" /></li>
	    </li>
	    <li><label for='language'><?php echo $clang->gT("Language");?>:</label>
	    <?php echo languageDropdownClean($surveyid,GetBaseLanguageFromSurveyID($surveyid)); ?>
	    </li>
	    <li><label for='usesleft'><?php echo $clang->gT("Uses left:");?></label>
	    <input type='text' size='20' id='usesleft' name='usesleft' value="1" /></li>
	    <li><label for='validfrom'><?php echo $clang->gT("Valid from");?>:</label>
	    <input type='text' class='popupdatetime' size='20' id='validfrom' name='validfrom' value="<?php
	    if (isset($validfrom)){
	        $datetimeobj = new Date_Time_Converter($validfrom , "Y-m-d H:i:s");
	        echo $datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
	    } ?>" /> <label for='validuntil'><?php echo $clang->gT('until');?></label>
	    <input type='text' size='20' id='validuntil' name='validuntil' class='popupdatetime' value="<?php
	    if (isset($validuntil)){
	        $datetimeobj = new Date_Time_Converter($validuntil , "Y-m-d H:i:s");
	       echo $datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
	    } ?>" /> <span class='annotation'><?php echo sprintf($clang->gT('Format: %s'),$dateformatdetails['dateformat'].' '.$clang->gT('hh:mm'));?></span>
	    </li>
	<?php
	    // now the attribute fieds
	    $attrfieldnames=GetTokenFieldsAndNames($surveyid,true);
	    foreach ($attrfieldnames as $attr_name=>$attr_description)
	    {
	        ?><li>
	        <label for='$attr_name'><?php echo $attr_description;?>:</label>
	        <input type='text' size='55' id='<?php echo $attr_name;?>' name='<?php echo $attr_name;?>' value='<?php
	        if (isset($$attr_name)) { echo htmlspecialchars($$attr_name,ENT_QUOTES,'UTF-8');}
	        ?>' /></li>
	   <?php }
	?>
	    </ul><p>
	    <input type='submit' value='<?php echo $clang->gT("Add dummy tokens");?>' />
	    <input type='hidden' name='sid' value='$surveyid' /></p>
	    </form>
