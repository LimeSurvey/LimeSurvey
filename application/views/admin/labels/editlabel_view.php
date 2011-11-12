<div class='header header_statistics'>
        <input type='image' src='<?php echo $this->config->item('imageurl'); ?>/close.gif' align='right' 
        onclick="window.open('<?php echo site_url("admin/labels/view/".$lid); ?>', '_top')" />
        <?php if ($action == "newlabelset") { echo $clang->gT("Create or import new label set(s)");}
        else {echo $clang->gT("Edit Label Set"); } ?>
</div>

<div id='tabs'>
        <ul>
        <li><a href='#neweditlblset0'><?php echo $tabitem; ?>
        </a></li>
        <?php if ($action == "newlabelset"){ ?>
            <li><a href='#neweditlblset1'><?php echo $clang->gT("Import label set(s)"); ?></a></li>
        <?php } ?>
        </ul>
        <div id='neweditlblset0'>
            <form method='post' class='form30' id='labelsetform' action='<?php echo site_url("admin/labels/process"); ?>' onsubmit="return isEmpty(document.getElementById('label_name'), '<?php echo $clang->gT("Error: You have to enter a name for this label set.","js"); ?>')">

        <ul>
        <li><label for='languageids'><?php echo $clang->gT("Set name:"); ?></label>
        <input type='hidden' name='languageids' id='languageids' value='<?php echo $langids; ?>' />
        <input type='text' id='label_name' name='label_name' maxlength='100' size='50' value='<?php if (isset($lbname)) { echo $lbname;} ?>' />
        </li>
        
        <li><label><?php echo $clang->gT("Languages:"); ?></label>
        <table><tr><td align='left'><select multiple='multiple' style='min-width:220px;' size='5' id='additional_languages' name='additional_languages'>
        <?php foreach ($langidsarray as $langid)
        { ?>
            <option id='<?php echo $langid; ?>' value='<?php echo $langid; ?>'
            ><?php echo getLanguageNameFromCode($langid,false); ?></option>
        <?php } ?>

        
        </select></td>
        <td align='left'><input type="button" value="<< <?php echo $clang->gT("Add"); ?>" onclick="DoAdd()" id="AddBtn" /><br /> <input type="button" value="<?php echo $clang->gT("Remove"); ?> >>" onclick="DoRemove(1,'<?php echo $clang->gT("You cannot remove this item since you need at least one language in a labelset.", "js"); ?>')" id="RemoveBtn"  /></td>

        
        <td align='left'><select size='5' style='min-width:220px;' id='available_languages' name='available_languages'>
        <?php foreach (getLanguageData() as  $langkey=>$langname)
        {
            if (in_array($langkey,$langidsarray)==false)  // base languag must not be shown here
            { ?>
                <option id='<?php echo $langkey; ?>' value='<?php echo $langkey; ?>'
                ><?php echo $langname['description']; ?></option>
            <?php }
        } ?>

        </select></td>
        </tr></table></li></ul>
        <p><input type='submit' value='<?php if ($action == "newlabelset") {echo $clang->gT("Save");} 
        else {echo $clang->gT("Update");} ?>' />
        <input type='hidden' name='action' value='<?php if ($action == "newlabelset") {echo "insertlabelset";} else {echo "updateset";} ?>' />

        <?php if ($action == "editlabelset") { ?>
            <input type='hidden' name='lid' value='<?php echo $lblid; ?>' />
        <?php } ?>

        </form>
        
        </div> 
        <?php if ($action == "newlabelset"){ ?>
            <div id='neweditlblset1'>
                <form enctype='multipart/form-data' id='importlabels' name='importlabels' action='<?php echo site_url('admin/labels/import'); ?>' method='post'>
                <div class='header ui-widget-header'>
                <?php echo $clang->gT("Import label set(s)"); ?>
                </div><ul>
                <li><label for='the_file'>
                <?php echo $clang->gT("Select label set file (*.lsl,*.csv):"); ?></label>
                <input id='the_file' name='the_file' type='file' size='35' />
                </li>
                <li><label for='checkforduplicates'>
                <?php echo $clang->gT("Don't import if label set already exists:"); ?></label>
                <input name='checkforduplicates' id='checkforduplicates' type='checkbox' checked='checked' />
                </li>
                <li><label for='translinksfields'>
                <?php echo $clang->gT("Convert resources links?"); ?></label>
                <input name='translinksfields' id='translinksfields' type='checkbox' checked='checked' />
                </li></ul>
                <p><input type='submit' value='<?php echo $clang->gT("Import label set(s)"); ?>' />
                <input type='hidden' name='action' value='importlabels' />
                </form></div>
            
            
            
            </div>
        <?php } ?>
        </div>