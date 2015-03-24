<div class='header ui-widget-header'><?php eT("Export result data to SPSS");?></div>

<?php echo CHtml::form(array("admin/export/sa/exportspss/sid/{$surveyid}/"), 'post', array('id'=>'exportspss', 'class'=>'wrap2columns'));?>
    <fieldset>
    <ul>
        <li><label for='completionstate'><?php eT("Data selection:");?></label><select id='completionstate' name='completionstate' onchange='this.form.submit();'>
                <option value='complete' <?php echo $selecthide;?>><?php eT("Completed responses only");?></option>
                <option value='all' <?php echo $selectshow;?>><?php eT("All responses");?></option>
                <option value='incomplete' <?php echo$selectinc;?>><?php eT("Incomplete responses only");?></option>
            </select></li>

        <li><label for='spssver'><?php eT("SPSS version:");?></label><select id='spssver' name='spssver' onchange='this.form.submit();'>
                <?php if ($spssver == 1) $selected = "selected='selected'"; else $selected = "";?>
                <option value='1' <?php echo $selected;?>><?php eT("Prior to 16");?></option>
                <?php if ($spssver == 2) $selected = "selected='selected'"; else $selected = ""; ?>
                <option value='2' <?php echo $selected;?>><?php eT("16 or up");?></option>
            </select></li>
        <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
        <input type='hidden' name='action' value='exportspss' /></li>
        <li><label for='dlstructure'><?php eT("Step 1:");?></label><input type='submit' name='dlstructure' id='dlstructure' value='<?php eT("Export syntax");?>'/></li>
        <li><label for='dldata'/><?php eT("Step 2:");?></label><input type='submit' name='dldata' id='dldata' value='<?php eT("Export data");?>'/></li>
    </ul>
    </fieldset>
    <fieldset>
        <legend><?php eT("Optional");?></legend>
        <ul>
            <li><label></label></li>
            <li><label for='limit'><?php eT("Limit:");?></label><input type='text' name='limit' value='<?php echo App()->getRequest()->getParam('limit');?>' /></li>
            <li><label for='offset'><?php eT("Offset:");?></label><input type='text' name='offset' value='<?php echo App()->getRequest()->getParam('offset');?>' /></li>
        </ul>
    </fieldset>
</form>

<p><div class='messagebox ui-corner-all'><div class='header ui-widget-header'><?php eT("Instructions for the impatient");?></div>
    <br/><ol>
        <li><?php eT("Download the data and the syntax file");?></li>
        <li><?php eT("Open the syntax file in SPSS in Unicode mode");?></li>
        <li><?php echo sprintf(gT("Edit the %s line and complete the filename with a full path to the downloaded data file"),"'FILE='");?></li>
        <li><?php eT("Choose 'Run/All' from the menu to run the import");?></li>
    </ol><p>
	<?php eT("Your data should be imported now");?></div>
