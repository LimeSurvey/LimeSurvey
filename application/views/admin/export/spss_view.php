<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Export result data to SPSS");?></h3>
        <div class="row">
            <div class="col-lg-12 content-right">
                

<?php echo CHtml::form(array("admin/export/sa/exportspss/sid/{$surveyid}/"), 'post', array('id'=>'exportspss', 'class'=>'wrap2columns'));?>
    <fieldset>
    <ul class="list-unstyled">
        <li><label for='completionstate'><?php eT("Data selection:");?></label><select class="form-control" id='completionstate' name='completionstate' onchange='this.form.submit();'>
                <option value='complete' <?php echo $selecthide;?>><?php eT("Completed responses only");?></option>
                <option value='all' <?php echo $selectshow;?>><?php eT("All responses");?></option>
                <option value='incomplete' <?php echo$selectinc;?>><?php eT("Incomplete responses only");?></option>
            </select></li>

        <li><label for='spssver'><?php eT("SPSS version:");?></label><select class="form-control"  id='spssver' name='spssver' onchange='this.form.submit();'>
                <?php if ($spssver == 1) $selected = "selected='selected'"; else $selected = "";?>
                <option value='1' <?php echo $selected;?>><?php eT("Prior to 16");?></option>
                <?php if ($spssver == 2) $selected = "selected='selected'"; else $selected = ""; ?>
                <option value='2' <?php echo $selected;?>><?php eT("16 or up");?></option>
            </select></li>
        <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
        <input type='hidden' name='action' value='exportspss' /></li><br/>
        <li><label for='dlstructure'><?php eT("Step 1:");?></label> &nbsp;&nbsp;&nbsp; <input class="btn btn-default" type='submit' name='dlstructure' id='dlstructure' value='<?php eT("Export syntax");?>'/></li><br/>
        <li><label for='dldata'/><?php eT("Step 2:");?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="btn btn-default" type='submit' name='dldata' id='dldata' value='<?php eT("Export data");?>'/></li>
    </ul>
    </fieldset>
    <fieldset>
        <legend><?php eT("Optional");?></legend>
        <ul class="list-unstyled">
            <li><label></label></li>
            <li><label for='limit'><?php eT("Limit:");?></label><input type='text' name='limit' value='<?php echo App()->getRequest()->getParam('limit');?>' /></li>
            <li><label for='offset'><?php eT("Offset:");?></label><input type='text' name='offset' value='<?php echo App()->getRequest()->getParam('offset');?>' /></li>
        </ul>
    </fieldset>
</form>

<p>
    <div class="alert alert-info" role="alert"><?php eT("Instructions for the impatient");?> : 
    <br/><br/>
    <ol>
        <li><?php eT("Download the data and the syntax file");?></li>
        <li><?php eT("Open the syntax file in SPSS in Unicode mode");?></li>
        <li><?php echo sprintf(gT("Edit the %s line and complete the filename with a full path to the downloaded data file"),"'FILE='");?></li>
        <li><?php eT("Choose 'Run/All' from the menu to run the import");?></li>
    </ol>
    <?php eT("Your data should be imported now");?></div>
    </div>
<p>
	

</div></div></div>
