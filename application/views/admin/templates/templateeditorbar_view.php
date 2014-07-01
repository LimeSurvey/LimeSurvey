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
            sendPost('<?php echo $this->createUrl('admin/templates/sa/template'); ?>'+action,'',new Array('action','newname','copydir'),new Array('template'+action,newtemplatename,copydirectory));
        }
    }

    $(document).ready(function(){
        $("#importtemplatefile").submit(function(){

            filename = $("#upload_file").val();
            if(filename==""){
                return false; // False click
            }
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
        });
    });
    //-->
</script>
<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT('Template editor'); ?> - <?php $clang->eT("Template:"); ?> <i><?php echo $templatename; ?></i></strong>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href='<?php echo $this->createUrl("/admin"); ?>'>
                <img src='<?php echo $sImageURL; ?>home.png' alt='<?php $clang->eT("Return to survey administration"); ?>' /></a>
            <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt=''  />

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
                <?php 

                if (Permission::model()->hasGlobalPermission('templates','import'))
                {

                    if (function_exists("zip_open")) {?>
                        <a href='<?php echo $this->createUrl('admin/templates/sa/upload'); ?>'>
                            <img src='<?php echo $sImageURL; ?>import.png' alt='<?php $clang->eT("Import template"); ?>' title='' /></a>
                        <?php }else{ ?>
                        <img src='<?php echo $sImageURL; ?>import_disabled.png' alt='<?php $clang->eT("zip library not supported by PHP, Import ZIP Disabled"); ?>' /></a>
                        <?php } 
                }
                if (Permission::model()->hasGlobalPermission('templates','export'))
                {
                    ?>
                    <a href='<?php echo $this->createUrl('admin/templates/sa/templatezip/templatename/' . $templatename) ?>'>
                        <img src='<?php echo $sImageURL; ?>export.png' alt='<?php $clang->eT("Export Template"); ?>' /></a>
                    <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
                    <?php 
                    if (Permission::model()->hasGlobalPermission('templates','create'))
                    { ?>
                        <a href='#' onclick="javascript: copyprompt('<?php $clang->eT("Please enter the name for the copied template:"); ?>', '<?php echo $clang->gT("copy_of_")."$templatename"; ?>',            '<?php echo $templatename; ?>', 'copy')">
                            <img src='<?php echo $sImageURL; ?>copy.png' alt='<?php $clang->eT("Copy Template"); ?>' /></a>
                        <?php
                    }
                }
            }
            else
            { 

                if (Permission::model()->hasGlobalPermission('templates','import'))
                { ?>
                    <img src='<?php echo $sImageURL; ?>import_disabled.png' alt='<?php echo $clang->gT("Import template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
                    <?php }
                if (Permission::model()->hasGlobalPermission('templates','export'))
                { ?>
                    <img src='<?php echo $sImageURL; ?>export_disabled.png' alt='<?php echo $clang->gT("Export template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
                    <?php 
                } ?>
                <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
                <?php 
                if (Permission::model()->hasGlobalPermission('templates','create'))
                { ?>
                    <img src='<?php echo $sImageURL; ?>copy_disabled.png' alt='<?php echo $clang->gT("Copy template").' - '.$clang->gT("Please change the directory permissions of the folders /tmp and /upload/templates in order to enable this option."); ?>' />
                    <?php
                }
            }

            if (is_template_editable($templatename))
            { 
                if (Permission::model()->hasGlobalPermission('templates','update'))
                { ?>
                    <a href='#' onclick="javascript: copyprompt('<?php $clang->eT("Rename this template to:"); ?>', '<?php echo $templatename; ?>', '<?php echo $templatename; ?>', 'rename');">
                        <img src='<?php echo $sImageURL; ?>edit.png' alt='<?php $clang->eT("Rename this template"); ?>' /></a>
                    <?php  
                }
                if (Permission::model()->hasGlobalPermission('templates','delete'))
                { ?>
                    <a href='#' onclick='if (confirm("<?php $clang->eT("Are you sure you want to delete this template?", "js"); ?>")) window.open("<?php echo $this->createUrl('admin/templates/sa/delete/templatename/'.$templatename); ?>", "_top")' >
                        <img src='<?php echo $sImageURL; ?>delete.png' alt='<?php $clang->eT("Delete this template"); ?>'/></a>
                    <?php
                }
            } ?>
            <img src='<?php echo $sImageURL; ?>blank.gif' alt='' width='20' height='10' />

        </div>
        <div class='menubar-right'>

            <label for='templatedir'><?php $clang->eT("Template:"); ?></label>
            <select class="listboxtemplates" id='templatedir' name='templatedir' onchange="javascript: window.open('<?php echo $this->createUrl("admin/templates/sa/view/editfile/".$editfile."/screenname/".$screenname); ?>/templatename/'+escape(this.value), '_top')">
                <?php echo templateoptions($templates, $templatename); ?>
            </select>
            <label for='listboxtemplates'><?php $clang->eT("Screen:"); ?></label>
            <select class="listboxtemplates" id='listboxtemplates' name='screenname' onchange="javascript: window.open('<?php echo $this->createUrl("admin/templates/sa/screenredirect/editfile/".$editfile."/templatename/".$templatename); ?>/screenname/'+escape(this.value), '_top')">
                <?php echo makeoptions($screens, "id", "name", HTMLEscape($screenname) ); ?>
            </select>
            <?php
            if (Permission::model()->hasGlobalPermission('templates','create'))
            { ?>
                <a href='#' onclick="javascript: copyprompt('<?php $clang->eT("Create new template called:"); ?>', '<?php $clang->eT("NewTemplate"); ?>', 'default', 'copy')">
                    <img src='<?php echo $sImageURL; ?>add.png' alt='<?php $clang->eT("Create new template"); ?>' /></a>
                <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
                <?php 
            }
            ?>
            <a href="<?php echo $this->createUrl("admin/authentication/sa/logout"); ?>">
                <img src='<?php echo $sImageURL; ?>logout.png' alt='<?php $clang->eT("Logout"); ?>' /></a>
            <img src='<?php echo $sImageURL; ?>blank.gif' alt='' width='20'  />
        </div>
    </div>
</div>
