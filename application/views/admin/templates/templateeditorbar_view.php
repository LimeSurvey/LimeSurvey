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
            sendPost('<?php echo site_url('admin/templates/template'); ?>'+action,'<?php echo $this->session->userdata('checksessionpost'); ?>',new Array('action','newname','copydir'),new Array('template'+action,newtemplatename,copydirectory));
        }
    }
    function checkuploadfiletype(filename)
    {
        var allowedtypes=',<?php echo $this->config->item('allowedtemplateuploads'); ?>,';
        var lastdotpos=-1;
        var ext='';
        if ((lastdotpos=filename.lastIndexOf('.')) < 0)
            {
            alert('<?php echo $clang->gT('This file type is not allowed to be uploaded.','js'); ?>');
            return false;
        }
        else
            {
            ext = ',' + filename.substr(lastdotpos+1) + ',';
            ext = ext.toLowerCase();
            if (allowedtypes.indexOf(ext) < 0)
                {
                alert('<?php echo $clang->gT('This file type is not allowed to be uploaded.','js'); ?>');
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
        <strong><?php echo $clang->gT('Template editor'); ?> - <?php echo $clang->gT("Template:"); ?> <i><?php echo $templatename; ?></i></strong>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href='<?php echo site_url("admin"); ?>'>
                <img src='<?php echo $this->config->item('imageurl'); ?>/home.png' name='HomeButton' alt='<?php echo $clang->gT("Return to survey administration"); ?>' /></a>
            <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='60' height='10'  />
            <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt=''  />

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
                    <a href='#' onclick='javascript:window.open("<?php echo site_url('admin/templates/upload'); ?>", "_top")'
                        title="<?php echo $clang->gTview("Import template"); ?>" >
                        <img name='ImportTemplate' src='<?php echo $this->config->item('imageurl'); ?>/import.png' alt='<?php echo $clang->gT("Import template"); ?>' title='' /></a>
                    <a href='#' onclick='javascript:window.open("admin.php?action=templatezip&amp;editfile=$editfile&amp;screenname=<?php echo urlencode($screenname); ?>&amp;templatename=$templatename", "_top")'
                        title="<?php echo $clang->gTview("Export Template"); ?>" >
                        <img name='Export' src='<?php echo $this->config->item('imageurl'); ?>/export.png' alt='<?php echo $clang->gT("Export Template"); ?>' /></a>
                    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' border='0' />
                    <a href='#' title="<?php echo $clang->gTview("Copy Template"); ?>"
                        onclick="javascript: copyprompt('<?php echo $clang->gT("Please enter the name for the copied template:"); ?>', '<?php echo $clang->gT("copy_of_")."$templatename"; ?>',            '<?php echo $templatename; ?>', 'copy')">
                        <img name='MakeCopy' src='<?php echo $this->config->item('imageurl'); ?>/copy.png' alt='<?php echo $clang->gT("Copy Template"); ?>' /></a>
                  <?php
                }
                else
                { ?>

                    <img name='ImportTemplate' src='<?php echo $this->config->item('imageurl'); ?>/import_disabled.png' alt='<?php echo $clang->gT("Import template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
                    <img name='Export' src='<?php echo $this->config->item('imageurl'); ?>/export_disabled.png' alt='<?php echo $clang->gT("Export template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
                    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' border='0' />
                    <img name='MakeCopy' src='<?php echo $this->config->item('imageurl'); ?>/copy_disabled.png' alt='<?php echo $clang->gT("Copy template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
                    <?php
                }

                if (is_template_editable($templatename))
                { ?>
                    <a href='#' title='<?php echo $clang->gTview("Rename this template"); ?>' onclick="javascript: copyprompt('<?php echo $clang->gT("Rename this template to:"); ?>', '<?php echo $templatename; ?>', '<?php echo $templatename; ?>', 'rename');">
                        <img name='RenameTemplate' src='<?php echo $this->config->item('imageurl'); ?>/edit.png' alt='<?php echo $clang->gT("Rename this template"); ?>' /></a>
                    <a href='#' title='<?php echo $clang->gTview("Delete this template"); ?>'
                        onclick='if (confirm("<?php echo $clang->gT("Are you sure you want to delete this template?", "js"); ?>")) window.open("<?php echo site_url('admin/templates/delete/'.$templatename); ?>", "_top")' >
                        <img name='DeleteTemplate' src='<?php echo $this->config->item('imageurl'); ?>/delete.png' alt='<?php echo $clang->gT("Delete this template"); ?>'/></a>
                    <?php
                } ?>
                <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='20' height='10' />
                <?php
                if(!is_writable($usertemplaterootdir))
                { ?>
                    <img name='Export' src='<?php echo $this->config->item('imageurl'); ?>/export_disabled.png' alt='<?php echo $clang->gT("Export template").' - '.sprintf($clang->gT("Please change the directory permissions of the folder %s in order to enable this option"),$tempdir); ?>' />
                    <img name='ImportTemplate' src='<?php echo $this->config->item('imageurl'); ?>/import_disabled.png' alt='<?php echo $clang->gT("Import template").' - '.sprintf($clang->gT("Please change the directory permissions of the folder %s in order to enable this option"),$tempdir); ?>' title='' />
                    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' border='0' />
                    <a href='#' title="<?php echo $clang->gTview("Copy Template"); ?>"
                        onclick="javascript: copyprompt('<?php echo $clang->gT("Please enter the name for the copied template:"); ?>', '<?php echo $clang->gT("copy_of_")."$templatename"; ?>', '<?php echo $templatename; ?>', 'copy')">
                        <img name='MakeCopy' src='<?php echo $this->config->item('imageurl'); ?>/copy.png' alt='<?php echo $clang->gT("Copy Template"); ?>' /></a>
                    <?php
                }?>


        </div>
        <div class='menubar-right'>

            <label for='templatedir'><?php echo $clang->gT("Template:"); ?></label>
            <select class="listboxtemplates" id='templatedir' name='templatedir' onchange="javascript: window.open('<?php echo site_url("admin/templates/view/".$editfile."/".$screenname); ?>/'+escape(this.value), '_top')">
                <?php echo templateoptions($templates, $templatename); ?>
            </select>
            <label for='listboxtemplates'><?php echo $clang->gT("Screen:"); ?></label>
            <select class="listboxtemplates" id='listboxtemplates' name='screenname' onchange="javascript: window.open('<?php echo site_url("admin/templates/screenredirect/".$editfile."/".$templatename); ?>/'+escape(this.value), '_top')">
                <?php echo makeoptions($screens, "id", "name", html_escape($screenname) ); ?>
            </select>
            <a href='#' onclick="javascript: copyprompt('<?php echo $clang->gT("Create new template called:"); ?>', '<?php echo $clang->gT("NewTemplate"); ?>', 'default', 'copy')"
                title="<?php echo $clang->gTview("Create new template"); ?>" >
                <img src='<?php echo $this->config->item('imageurl'); ?>/add.png' alt='<?php echo $clang->gT("Create new template"); ?>' /></a>
            <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
            <a href="#" onclick="window.open('<?php echo site_url("admin/authentication/logout");?>', '_top')"
                title="<?php echo $clang->gTview("Logout"); ?>" >
                <img src='<?php echo $this->config->item('imageurl'); ?>/logout.png' name='Logout'
                    alt='<?php echo $clang->gT("Logout"); ?>' /></a>
            <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='20'  />
        </div>
    </div>
</div>
<font style='size:12px;line-height:2px;'>&nbsp;&nbsp;</font>