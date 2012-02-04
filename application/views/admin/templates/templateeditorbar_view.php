<script type="text/javascript">
    var adminlanguage='<?php echo $codelanguage; ?>';
    var highlighter='<?php echo $highlighter; ?>';
</script>
<script type='text/javascript'>
    <!--
    function copyprompt(text, defvalue, copydirectory, action)
    {
        if (newtemplatename=window.prompt(text, defvalue))
            {
            sendPost('<?php echo $this->createUrl('admin/templates/template'); ?>'+action,'<?php echo Yii::app()->session['checksessionpost']; ?>',new Array('action','newname','copydir'),new Array('template'+action,newtemplatename,copydirectory));
        }
    }
    function checkuploadfiletype(filename)
    {
        var allowedtypes=',<?php echo Yii::app()->getConfig('allowedtemplateuploads'); ?>,';
        var lastdotpos=-1;
        var ext='';
        if ((lastdotpos=filename.lastIndexOf('.')) < 0)
            {
            alert('<?php $clang->eT('This file type is not allowed to be uploaded.','js'); ?>');
            return false;
        }
        else
            {
            ext = ',' + filename.substr(lastdotpos+1) + ',';
            ext = ext.toLowerCase();
            if (allowedtypes.indexOf(ext) < 0)
                {
                alert('<?php $clang->eT('This file type is not allowed to be uploaded.','js'); ?>');
                return false;
            }
            else
                {
                return true;
            }
        }
    }
    //-->
</script>
<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT('Template editor'); ?> - <?php $clang->eT("Template:"); ?> <i><?php echo $templatename; ?></i></strong>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href='<?php echo $this->createUrl("/admin"); ?>'>
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/home.png' name='HomeButton' alt='<?php $clang->eT("Return to survey administration"); ?>' /></a>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='60' height='10'  />
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt=''  />

            <?php
                if (!is_template_editable($templatename))
                { ?>
                <div class="menubar-right" style='padding-left:15px;padding-top:5px;'><span style='font-size:10px; font-weight: bold;'><?php $clang->eT('Note: This is a standard template.');?><br />
                <?php printf($clang->gT('If you want to edit it %s please copy it first%s.'),"<a href='#' title=\"".$clang->gT("Copy Template")."\""
                    ." onclick=\"javascript: copyprompt('".$clang->gT("Please enter the name for the copied template:")."', '".$clang->gT("copy_of_")."$templatename', '$templatename', 'copy')\">",'</a>'); ?></span></div>
                <?php
                } ?>
                <?php if(is_writable($tempdir) && is_writable($usertemplaterootdir))
                {?>
                    <a href='#' onclick='javascript:window.open("<?php echo $this->createUrl('admin/templates/upload'); ?>", "_top")'
                        title="<?php $clang->eTview("Import template"); ?>" >
                        <img name='ImportTemplate' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/import.png' alt='<?php $clang->eT("Import template"); ?>' title='' /></a>
                    <a href='#' onclick='javascript:window.open("<?php echo $this->createUrl('admin/templates/templatezip/templatename/' . $templatename) ?>", "_top")'
                        title="<?php $clang->eTview("Export Template"); ?>" >
                        <img name='Export' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/export.png' alt='<?php $clang->eT("Export Template"); ?>' /></a>
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' border='0' />
                    <a href='#' title="<?php $clang->eTview("Copy Template"); ?>"
                        onclick="javascript: copyprompt('<?php $clang->eT("Please enter the name for the copied template:"); ?>', '<?php echo $clang->gT("copy_of_")."$templatename"; ?>',            '<?php echo $templatename; ?>', 'copy')">
                        <img name='MakeCopy' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/copy.png' alt='<?php $clang->eT("Copy Template"); ?>' /></a>
                  <?php
                }
                else
                { ?>

                    <img name='ImportTemplate' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/import_disabled.png' alt='<?php echo $clang->gT("Import template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
                    <img name='Export' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/export_disabled.png' alt='<?php echo $clang->gT("Export template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' border='0' />
                    <img name='MakeCopy' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/copy_disabled.png' alt='<?php echo $clang->gT("Copy template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
                    <?php
                }

                if (is_template_editable($templatename))
                { ?>
                    <a href='#' title='<?php $clang->eTview("Rename this template"); ?>' onclick="javascript: copyprompt('<?php $clang->eT("Rename this template to:"); ?>', '<?php echo $templatename; ?>', '<?php echo $templatename; ?>', 'rename');">
                        <img name='RenameTemplate' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/edit.png' alt='<?php $clang->eT("Rename this template"); ?>' /></a>
                    <a href='#' title='<?php $clang->eTview("Delete this template"); ?>'
                        onclick='if (confirm("<?php $clang->eT("Are you sure you want to delete this template?", "js"); ?>")) window.open("<?php echo $this->createUrl('admin/templates/delete/templatename/'.$templatename); ?>", "_top")' >
                        <img name='DeleteTemplate' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/delete.png' alt='<?php $clang->eT("Delete this template"); ?>'/></a>
                    <?php
                } ?>
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='20' height='10' />
                <?php
                if(!is_writable($usertemplaterootdir))
                { ?>
                    <img name='Export' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/export_disabled.png' alt='<?php echo $clang->gT("Export template").' - '.sprintf($clang->gT("Please change the directory permissions of the folder %s in order to enable this option"),$tempdir); ?>' />
                    <img name='ImportTemplate' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/import_disabled.png' alt='<?php echo $clang->gT("Import template").' - '.sprintf($clang->gT("Please change the directory permissions of the folder %s in order to enable this option"),$tempdir); ?>' title='' />
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' border='0' />
                    <a href='#' title="<?php $clang->eTview("Copy Template"); ?>"
                        onclick="javascript: copyprompt('<?php $clang->eT("Please enter the name for the copied template:"); ?>', '<?php echo $clang->gT("copy_of_")."$templatename"; ?>', '<?php echo $templatename; ?>', 'copy')">
                        <img name='MakeCopy' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/copy.png' alt='<?php $clang->eT("Copy Template"); ?>' /></a>
                    <?php
                }?>


        </div>
        <div class='menubar-right'>

            <label for='templatedir'><?php $clang->eT("Template:"); ?></label>
            <select class="listboxtemplates" id='templatedir' name='templatedir' onchange="javascript: window.open('<?php echo $this->createUrl("admin/templates/view/editfile/".$editfile."/screenname/".$screenname); ?>/templatename/'+escape(this.value), '_top')">
                <?php echo templateoptions($templates, $templatename); ?>
            </select>
            <label for='listboxtemplates'><?php $clang->eT("Screen:"); ?></label>
            <select class="listboxtemplates" id='listboxtemplates' name='screenname' onchange="javascript: window.open('<?php echo $this->createUrl("admin/templates/screenredirect/editfile/".$editfile."/templatename/".$templatename); ?>/screenname/'+escape(this.value), '_top')">
                <?php echo makeoptions($screens, "id", "name", HTMLEscape($screenname) ); ?>
            </select>
            <a href='#' onclick="javascript: copyprompt('<?php $clang->eT("Create new template called:"); ?>', '<?php $clang->eT("NewTemplate"); ?>', 'default', 'copy')"
                title="<?php $clang->eTview("Create new template"); ?>" >
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/add.png' alt='<?php $clang->eT("Create new template"); ?>' /></a>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' />
            <a href="<?php echo $this->createUrl("admin/authentication/logout"); ?>"
                title="<?php $clang->eTview("Logout"); ?>" >
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/logout.png' name='Logout'
                    alt='<?php $clang->eT("Logout"); ?>' /></a>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='20'  />
        </div>
    </div>
</div>
<font style='size:12px;line-height:2px;'>&nbsp;&nbsp;</font>