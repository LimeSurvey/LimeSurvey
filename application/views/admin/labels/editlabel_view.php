<div class='header'>
    <input type='image' src='<?php echo $sImageURL; ?>close.png' style='float:right;'
        onclick="window.open('<?php echo $this->createUrl("admin/labels/sa/view/lid/".$lid); ?>', '_top')" alt='<?php $clang->eT("Close"); ?>'/>
    <?php if ($action == "newlabelset") { $clang->eT("Create or import new label set(s)");}
        else {$clang->eT("Edit label set"); } ?>
</div>

<div id='tabs'>
    <ul>
        <li><a href='#neweditlblset0'><?php echo $tabitem; ?>
            </a></li>
        <?php if ($action == "newlabelset"){ ?>
            <li><a href='#neweditlblset1'><?php $clang->eT("Import label set(s)"); ?></a></li>
            <?php } ?>
    </ul>
    <div id='neweditlblset0'>
        <?php echo CHtml::form(array("admin/labels/sa/process"), 'post',array('class'=>'form30','id'=>'labelsetform','onsubmit'=>"return isEmpty(document.getElementById('label_name'), '".$clang->gT("Error: You have to enter a name for this label set.","js")."')")); ?>
            <ul>
                <li><label for='label_name'><?php $clang->eT("Set name:"); ?></label>
                    <input type='hidden' name='languageids' id='languageids' value='<?php echo $langids; ?>' />
                    <input type='text' id='label_name' name='label_name' maxlength='100' size='50' value='<?php if (isset($lbname)) { echo $lbname;} ?>' />
                </li>

                <li><label><?php $clang->eT("Languages:"); ?></label>
                    <table><tr><td><select multiple='multiple' style='min-width:220px;' size='5' id='additional_languages' name='additional_languages'>
                                    <?php foreach ($langidsarray as $langid)
                                        { ?>
                                        <option id='<?php echo $langid; ?>' value='<?php echo $langid; ?>'
                                            ><?php echo getLanguageNameFromCode($langid,false); ?></option>
                                        <?php } ?>


                                </select></td>
                            <td><input type="button" value="<< <?php $clang->eT("Add"); ?>" onclick="DoAdd()" id="AddBtn" /><br /> <input type="button" value="<?php $clang->eT("Remove"); ?> >>" onclick="DoRemove(1,'<?php $clang->eT("You cannot remove this item since you need at least one language in a labelset.", "js"); ?>')" id="RemoveBtn"  /></td>


                            <td><select size='5' style='min-width:220px;' id='available_languages' name='available_languages'>
                                    <?php foreach (getLanguageDataRestricted(false, Yii::app()->session['adminlang']) as  $langkey=>$langname)
                                        {
                                            if (in_array($langkey,$langidsarray)==false)  // base languag must not be shown here
                                            { ?>
                                            <option id='<?php echo $langkey; ?>' value='<?php echo $langkey; ?>'
                                                ><?php echo $langname['description']; ?></option>
                                            <?php }
                                    } ?>

                                </select></td>
                        </tr></table></li></ul>
            <p><input type='submit' value='<?php if ($action == "newlabelset") {$clang->eT("Save");}
                    else {$clang->eT("Update");} ?>' />
            <input type='hidden' name='action' value='<?php if ($action == "newlabelset") {echo "insertlabelset";} else {echo "updateset";} ?>' />

            <?php if ($action == "editlabelset") { ?>
                <input type='hidden' name='lid' value='<?php echo $lblid; ?>' />
                <?php } ?>

        </form>

    </div>
    <?php if ($action == "newlabelset"){ ?>
        <div id='neweditlblset1'>
            <?php echo CHtml::form(array("admin/labels/sa/import"), 'post',array('enctype'=>'multipart/form-data','id'=>'importlabels','name'=>"importlabels")); ?>
                <div class='header ui-widget-header'>
                    <?php $clang->eT("Import label set(s)"); ?>
                </div><ul>
                    <li><label for='the_file'>
                        <?php $clang->eT("Select label set file (*.lsl,*.csv):"); ?></label>
                        <input id='the_file' name='the_file' type='file'/>
                    </li>
                    <li><label for='checkforduplicates'>
                        <?php $clang->eT("Don't import if label set already exists:"); ?></label>
                        <input name='checkforduplicates' id='checkforduplicates' type='checkbox' checked='checked' />
                    </li>
                    <li><label for='translinksfields'>
                        <?php $clang->eT("Convert resources links?"); ?></label>
                        <input name='translinksfields' id='translinksfields' type='checkbox' checked='checked' />
                    </li></ul>
                <p><input type='submit' value='<?php $clang->eT("Import label set(s)"); ?>' />
                <input type='hidden' name='action' value='importlabels' />
            </form></div>



    </div>
    <?php } ?>
        </div>
