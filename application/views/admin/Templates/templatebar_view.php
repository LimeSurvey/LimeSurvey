<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
    <strong><?php echo $clang->gT("Template:"); ?> <i><?php echo $templatename; ?></i></strong>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
        <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='104' height='40'/>
        <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt=''  />

    <?php if (!is_template_editable($templatename))
    { ?>
       <img name='RenameTemplate' src='<?php echo $this->config->item('imageurl'); ?>/edit_disabled.png' alt='<?php echo $clang->gT("You can't rename a standard template."); ?>' title='<?php echo $clang->gTview("You can't rename a standard template."); ?>' />
       <img name='EditName' src='<?php echo $this->config->item('imageurl'); ?>/delete_disabled.png' alt='<?php echo $clang->gT("You can't delete a standard template."); ?>' title='<?php echo $clang->gTview("You can't delete a standard template."); ?>' />
    <?php }
    else
    { ?>
       <a href='#' title='<?php echo $clang->gTview("Rename this template"); ?>' onclick="javascript: copyprompt('<?php echo $clang->gT("Rename this template to:"); ?>', '<?php echo $templatename; ?>', '<?php echo $templatename; ?>', 'rename');">
        <img name='RenameTemplate' src='<?php echo $this->config->item('imageurl'); ?>/edit.png' alt='<?php echo $clang->gT("Rename this template"); ?>' /></a>
       <a href='#' title='<?php echo $clang->gTview("Delete this template"); ?>'
       onclick='if (confirm("<?php echo $clang->gT("Are you sure you want to delete this template?", "js"); ?>")) window.open("admin.php?action=templates&amp;subaction=delete&amp;templatename=$templatename", "_top")' >
        <img name='DeleteTemplate' src='<?php echo $this->config->item('imageurl'); ?>/delete.png' alt='<?php echo $clang->gT("Delete this template"); ?>'/></a>
    <?php } ?>
        <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='20' height='10' />
    <?php if(is_writable($tempdir) && is_writable($usertemplaterootdir))
    { ?>
        <a href='#' onclick='javascript:window.open("admin.php?action=templatezip&amp;editfile=$editfile&amp;screenname=<?php echo urlencode($screenname); ?>&amp;templatename=$templatename", "_top")'
         title="<?php echo $clang->gTview("Export Template"); ?>" >
        <img name='Export' src='<?php echo $this->config->item('imageurl'); ?>/export.png' alt='<?php echo $clang->gT("Export Template"); ?>' /></a>
        <a href='#' onclick='javascript:window.open("admin.php?action=templates&amp;subaction=templateupload", "_top")'
         title="<?php echo $clang->gTview("Import template"); ?>" >
        <img name='ImportTemplate' src='<?php echo $this->config->item('imageurl'); ?>/import.png' alt='<?php echo $clang->gT("Import template"); ?>' title='' /></a>
        <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' border='0' />
        <a href='#' title="<?php echo $clang->gTview("Copy Template"); ?>" 
        onclick="javascript: copyprompt('<?php echo $clang->gT("Please enter the name for the copied template:"); ?>', '<?php echo $clang->gT("copy_of_")."$templatename"; ?>', 	   	'<?php echo $templatename; ?>', 'copy')">
        <img name='MakeCopy' src='<?php echo $this->config->item('imageurl'); ?>/copy.png' alt='<?php echo $clang->gT("Copy Template"); ?>' /></a>
    <?php } 
    elseif(is_writable($usertemplaterootdir))
    { ?>
        <img name='Export' src='<?php echo $this->config->item('imageurl'); ?>/export_disabled.png' alt='<?php echo $clang->gT("Export template").' - '.sprintf($clang->gT("Please change the directory permissions of the folder %s in order to enable this option"),$tempdir); ?>' />
        <img name='ImportTemplate' src='<?php echo $this->config->item('imageurl'); ?>/import_disabled.png' alt='<?php echo $clang->gT("Import template").' - '.sprintf($clang->gT("Please change the directory permissions of the folder %s in order to enable this option"),$tempdir); ?>' title='' />
        <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' border='0' />
        <a href='#' title="<?php echo $clang->gTview("Copy Template"); ?>" 
        onclick="javascript: copyprompt('<?php echo $clang->gT("Please enter the name for the copied template:"); ?>', '<?php echo $clang->gT("copy_of_")."$templatename"; ?>', '<?php echo $templatename; ?>', 'copy')">
        <img name='MakeCopy' src='<?php echo $this->config->item('imageurl'); ?>/copy.png' alt='<?php echo $clang->gT("Copy Template"); ?>' /></a>
    <?php }
    else
    { ?>
    
        <img name='Export' src='<?php echo $this->config->item('imageurl'); ?>/export_disabled.png' alt='<?php echo $clang->gT("Export template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
    	<img name='ImportTemplate' src='<?php echo $this->config->item('imageurl'); ?>/import_disabled.png' alt='<?php echo $clang->gT("Import template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
    	<img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' border='0' />
    	<img name='MakeCopy' src='<?php echo $this->config->item('imageurl'); ?>/copy_disabled.png' alt='<?php echo $clang->gT("Copy template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
    <?php } ?>
        </div>
        <div class='menubar-right'>
            <font style='boxcaption'><strong><?php echo $clang->gT("Screen:"); ?></strong> </font>
            <select class="listboxtemplates" name='screenname' onchange="javascript: window.open('<?php echo site_url("admin/templates/screenredirect/".$editfile."/".$templatename); ?>/'+escape(this.value), '_top')">
            <?php echo makeoptions($screens, "id", "name", html_escape($screenname) ); ?>
            </select>
            <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' width='45' height='10' alt='' />
            <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
            <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' width='62' height='10' alt=''/>
        </div>
    </div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>