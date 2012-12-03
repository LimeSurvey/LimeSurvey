<?php if (is_template_editable($templatename)==true)
    { ?>
    <script type="text/javascript" src="<?php echo Yii::app()->getConfig('adminscripts'); ?>codemirror_ui/lib/CodeMirror-2.0/lib/codemirror.js" ></script>
    <?php if ($sEditorFileType=='htmlmixed')
        {?>
        <script type="text/javascript" src="<?php echo Yii::app()->getConfig('adminscripts'); ?>codemirror_ui/lib/CodeMirror-2.0/mode/xml/xml.js" ></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getConfig('adminscripts'); ?>codemirror_ui/lib/CodeMirror-2.0/mode/javascript/javascript.js" ></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getConfig('adminscripts'); ?>codemirror_ui/lib/CodeMirror-2.0/mode/css/css.js" ></script>
        <?php }
    ?>
    <script type="text/javascript" src="<?php echo Yii::app()->getConfig('adminscripts'); ?>codemirror_ui/lib/CodeMirror-2.0/mode/<?php echo $sEditorFileType; ?>/<?php echo $sEditorFileType; ?>.js" ></script>
    <script type="text/javascript" src="<?php echo Yii::app()->getConfig('adminscripts'); ?>codemirror_ui/js/codemirror-ui.js" ></script>
    <script type="text/javascript">
        var editorfiletype='<?php echo $sEditorFileType; ?>';
    </script>
    <div class='header'>
        <?php echo sprintf($clang->gT("Editing template '%s' - File '%s'"),$templatename,$editfile); ?>
    </div>
    <div id='templateleft' style="float:left;padding-left:1em;width:12%;">
        <div >
            <?php $clang->eT("Standard files:"); ?><br>
            <select size='6' name='editfile' onchange="javascript: window.open('<?php echo $this->createUrl("admin/templates/sa/fileredirect/templatename/".$templatename."/screenname/".urlencode($screenname)); ?>/editfile/'+escape(this.value), '_top')">
                <?php echo makeoptions($files, "name", "name", $editfile); ?>
            </select>
        </div>
        <div style='margin-top:1em;'>
            <?php $clang->eT("CSS & Javascript files:"); ?>
            <br/><select size='8' name='cssfiles' onchange="javascript: window.open('<?php echo $this->createUrl("admin/templates/sa/fileredirect/templatename/".$templatename."/screenname/".urlencode($screenname)); ?>/editfile/'+escape(this.value), '_top')">
                <?php echo makeoptions($cssfiles, "name", "name", $editfile); ?>
            </select>
        </div>
    </div>

    <div style='float:left;width:70%; padding:1.3em;' >
        <?php echo CHtml::form(array('admin/templates/sa/templatesavechanges'), 'post', array('id'=>'editTemplate', 'name'=>'editTemplate')); ?>
        
            <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
            <input type='hidden' name='screenname' value='<?php echo HTMLEscape($screenname); ?>' />
            <input type='hidden' name='editfile' value='<?php echo $editfile; ?>' />
            <input type='hidden' name='action' value='templatesavechanges' />

            <textarea name='changes' id='changes' rows='20' cols='40' class='codepress html <?php echo $templateclasseditormode; ?>' style='width:100%'>
                <?php if (isset($editfile)) {
                        echo textarea_encode(filetext($templatename,$editfile,$templates));
                } ?>
            </textarea>
            <script type="text/javascript">
                var codemirropath = '<?php echo Yii::app()->getConfig('adminscripts'); ?>codemirror_ui/js/';
            </script>
            <p>
                <?php if (is_writable($templates[$templatename])) { ?>
                    <input type='submit' value='<?php $clang->eT("Save changes"); ?>'
                        <?php if (!is_template_editable($templatename)) { ?>
                            disabled='disabled' alt='<?php $clang->eT("Changes cannot be saved to a standard template."); ?>'
                            <?php } ?>
                        />
                    <?php }
                    else
                    { ?>
                    <span class="flashmessage"><?php $clang->eT("You can't save changes because the template directory is not writable."); ?></span>
                    <?php } ?>
            </p>
        </form>
    </div>

    <div style="float:left;">
        <div>
            <?php $clang->eT("Other files:"); ?>
            <?php echo CHtml::form(array('admin/templates/sa/templatefiledelete'), 'post'); ?>
                <select size='11' style='min-width:130px;' name='otherfile' id='otherfile'>
                    <?php echo makeoptions($otherfiles, "name", "name", ""); ?>
                </select><br>
                <input type='submit' value='<?php $clang->eT("Delete"); ?>' onclick="javascript:return confirm('<?php $clang->eT("Are you sure you want to delete this file?","js"); ?>')"/>
                <input type='hidden' name='screenname' value='<?php echo HTMLEscape($screenname); ?>' />
                <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
                <input type='hidden' name='editfile' value='<?php echo $editfile; ?>' />
                <input type='hidden' name='action' value='templatefiledelete' />
            </form>
        </div>
        <div style='margin-top:1em;'>
            <?php echo CHtml::form(array('admin/templates/uploadfile'), 'post', array('id'=>'importtemplatefile', 'name'=>'importtemplatefile', 'enctype'=>'multipart/form-data')); ?>
                <?php $clang->eT("Upload a file:"); ?><br><input style='width:50px;' size=10 name='upload_file' id="upload_file" type="file" /><br />
                <input type='submit' value='<?php $clang->eT("Upload"); ?>'
                    <?php if (!is_template_editable($templatename))  { ?>
                        disabled='disabled'
                        <?php } ?>

                    />
                <input type='hidden' name='editfile' value='<?php echo $editfile; ?>' />
                <input type='hidden' name='screenname' value='<?php echo HTMLEscape($screenname); ?>' />
                <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
                <input type='hidden' name='action' value='templateuploadfile' />
            </form>
        </div>
    </div>

    <?php } ?>


<div class='header ui-widget-header' style='clear:both;'>
    <?php $clang->eT("Preview:"); ?>
    <input type='button' value='iPhone' id='iphone' />
    <input type='button' value='640x480' id='x640' />
    <input type='button' value='800x600' id='x800' />
    <input type='button' value='1024x768' id='x1024' />
    <input type='button' value='<?php $clang->eT("Full"); ?>' id='full' />
</div>
<div style='width:90%; margin:0 auto;'>



    <?php if(isset($filenotwritten) && $filenotwritten==true)
        { ?>
        <p>
        <span class ='errortitle'><?php echo sprintf($clang->gT("Please change the directory permissions of the folder %s in order to preview templates."), $tempdir); ?></span>
    </div>
    <?php }
    else
    { ?>
    <p><iframe id='previewiframe' src='<?php echo $this->createUrl('admin/templates/sa/tmp/',array('id'=>$time)); ?>' height='768' name='previewiframe' style='width:95%;background-color: white;'>Embedded Frame</iframe></p>
    </div>
    <?php } ?>
