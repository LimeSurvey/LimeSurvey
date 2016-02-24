<?php
Yii::app()->clientScript->registerScript('editorfiletype',"editorfiletype ='".$sEditorFileType."';",CClientScript::POS_HEAD); // Is this deprecated (2013-09-25) ?
?>


<?php if (is_template_editable($templatename)==true): ?>
<div class="row template-sum">
    <div class="col-lg-12">

        <?php App()->getClientScript()->registerPackage('jquery-ace'); ?>
        <h4><?php echo sprintf(gT("Editing template '%s' - File '%s'"),$templatename,$editfile); ?></h4>

        <?php if (!is_writable($templates[$templatename])):?>
            <div class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <?php eT("You can't save changes because the template directory is not writable."); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row template-sum">
    <div class="col-lg-2" id='templateleft'>
        <div style="padding-left:1em;">
            <?php eT("Standard files:"); ?><br>
            <select class="form-control" size='6' name='editfile' onchange="javascript: window.open('<?php echo $this->createUrl("admin/templates/sa/fileredirect/templatename/".$templatename."/screenname/".urlencode($screenname)); ?>/editfile/'+escape(this.value)+'/useindex/0/', '_top')">
                <?php echo makeoptions($files, "name", "name", $editfile); ?>
            </select>
        </div>

        <div style='margin-top:1em;padding-left:1em;'>
            <?php eT("CSS & Javascript files:"); ?>
            <br/>
            <select  class="form-control"  size='8' name='cssfiles' onchange="javascript: window.open('<?php echo $this->createUrl("admin/templates/sa/fileredirect/templatename/".$templatename."/screenname/".urlencode($screenname)); ?>/editfile/'+escape(this.value)+'/useindex/true/', '_top')">
                <?php echo makeoptionswithindex($cssfiles, "name", "name", $editfile, 'css'); ?>
                <?php echo makeoptionswithindex($jsfiles, "name", "name", $editfile, 'js'); ?>
            </select>
            <br/>
        </div>
    </div>
    <div class="col-lg-8 templateeditor">
        <?php echo CHtml::form(array('admin/templates/sa/templatesavechanges'), 'post', array('id'=>'editTemplate', 'name'=>'editTemplate')); ?>

        <?php if(isset($_GET['editfile'])):?>
            <input type='hidden' name='editfileindex' value='<?php echo $_GET['editfile']; ?>' />
        <?php endif;?>
        <?php if(isset($_GET['useindex'])):?>
            <input type='hidden' name='useindex' value='<?php echo $_GET['useindex']; ?>' />
        <?php endif;?>
        <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
        <input type='hidden' name='screenname' value='<?php echo HTMLEscape($screenname); ?>' />
        <input type='hidden' name='editfile' value='<?php echo $editfile; ?>' />
        <input type='hidden' name='action' value='templatesavechanges' />
        <textarea name='changes' id='changes' rows='20' cols='40' data-filetype="<?php echo $sEditorFileType; ?>" class="ace <?php echo $sTemplateEditorMode; ?>" style='width:100%'>
        <?php if (isset($editfile)) {
            echo textarea_encode(filetext($templatename,$editfile,$templates));
        } ?>
        </textarea>
        <p>
            <br/>
            <?php if (Permission::model()->hasGlobalPermission('templates','update')):?>
                <?php if (is_writable($templates[$templatename])):?>
                    <input type='submit' class='btn btn-default' value='<?php eT("Save changes"); ?>'
                            <?php if (!is_template_editable($templatename)):?>
                                disabled='disabled' alt='<?php eT("Changes cannot be saved to a standard template."); ?>'
                            <?php endif; ?>
                    />
                <?php endif; ?>
            <?php endif; ?>
        </p>
        </form>
    </div>



    <div class="col-lg-2" style="overflow-x: hidden">
        <div>
            <?php eT("Other files:"); ?>
            <br/>
            <?php // TODO printf(gT("(path for css: %s)"), $filespath) ?>
            <?php echo CHtml::form(array('admin/templates/sa/templatefiledelete'), 'post'); ?>
            <select size='11' class="form-control" name='otherfile' id='otherfile'>
                <?php echo makeoptions($otherfiles, "name", "name", ""); ?>
            </select><br>
            <?php
            if (Permission::model()->hasGlobalPermission('templates','delete'))
            { ?>

                <input type='submit' class='btn btn-default' value='<?php eT("Delete"); ?>' onclick="javascript:return confirm('<?php eT("Are you sure you want to delete this file?","js"); ?>')"/>
                <?php
            }
            ?>
            <input type='hidden' name='screenname' value='<?php echo HTMLEscape($screenname); ?>' />
            <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
            <input type='hidden' name='editfile' value='<?php echo $editfile; ?>' />
            <input type='hidden' name='action' value='templatefiledelete' />
            <?php if(isset($_GET['editfile'])):?>
                <input type='hidden' name='editfileindex' value='<?php echo $_GET['editfile']; ?>' />
            <?php endif;?>
            <?php if(isset($_GET['useindex'])):?>
                <input type='hidden' name='useindex' value='<?php echo $_GET['useindex']; ?>' />
            <?php endif;?>

            </form>
        </div>
        <div style='margin-top:1em;'>
            <?php
            if (Permission::model()->hasGlobalPermission('templates','update'))
            { ?>

                <?php echo CHtml::form(array('admin/templates/sa/uploadfile'), 'post', array('id'=>'importtemplatefile', 'name'=>'importtemplatefile', 'enctype'=>'multipart/form-data')); ?>
                <?php eT("Upload a file:"); ?>
                <br>
                <input name='upload_file' id="upload_file" type="file" required="required"/>
                <input type='submit' value='<?php eT("Upload"); ?>' class='btn btn-default'
                    <?php if (!is_template_editable($templatename)) : ?>
                        disabled='disabled'
                    <?php endif; ?>
                />
                <input type='hidden' name='editfile' value='<?php echo $editfile; ?>' />
                <input type='hidden' name='screenname' value='<?php echo HTMLEscape($screenname); ?>' />
                <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
                <input type='hidden' name='action' value='templateuploadfile' />
                <?php if(isset($_GET['editfile'])):?>
                    <input type='hidden' name='editfileindex' value='<?php echo $_GET['editfile']; ?>' />
                <?php endif;?>
                <?php if(isset($_GET['useindex'])):?>
                    <input type='hidden' name='useindex' value='<?php echo $_GET['useindex']; ?>' />
                <?php endif;?>

                </form>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<?php endif;?>

<div class="row template-sum" style="margin-bottom: 100px;">
    <div class="col-lg-12">
        <h4>
            <?php eT("Preview:"); ?>
        </h4>
        <div class="jumbotron message-box">
            <input type='button' value='<?php eT("Mobile"); ?>' id='iphone' class="btn btn-default"/>
            <input type='button' value='640x480' id='x640' class="btn btn-default" />
            <input type='button' value='800x600' id='x800' class="btn btn-default" />
            <input type='button' value='1024x768' id='x1024' class="btn btn-default" />
            <input type='button' value='<?php eT("Full"); ?>' id='full' class="btn btn-default" />
            <br><br><br><br>

                <div style='width:90%; margin:0 auto;'>
                    <?php if(isset($filenotwritten) && $filenotwritten==true)
                    { ?>
                        <p>
                            <span class ='errortitle'><?php echo sprintf(gT("Please change the directory permissions of the folder %s in order to preview templates."), $tempdir); ?></span>
                        </p>
                    </div>
                    <?php }
                else
                { ?>
                    <p>
                        <iframe id='previewiframe' src='<?php echo $this->createUrl('admin/templates/sa/tmp/',array('id'=>$time)); ?>' height='768' name='previewiframe' style='width:95%;background-color: white;'>Embedded Frame</iframe>
                    </p>
                    </div>
                    <?php
                } ?>
        </div>
    </div>
</div>
