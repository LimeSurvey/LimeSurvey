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
    <strong><?php echo $clang->gT('Template Editor'); ?></strong>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href='<?php echo site_url("admin"); ?>'
             title="<?php echo $clang->gTview("Return to survey administration"); ?>">
            <img src='<?php echo $this->config->item('imageurl'); ?>/home.png' name='HomeButton' alt='<?php echo $clang->gT("Return to survey administration"); ?>' /></a>
            <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='60' height='10'  />
            <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt=''  />

<?php if (isset($flashmessage))
{ ?>
   <span class="flashmessage"><?php echo $flashmessage; ?></span>
<?php }
elseif (is_template_editable($templatename)==false)
{ ?>
   <span class="flashmessage"><?php echo sprintf($clang->gT('Note: This is a standard template. If you want to edit it %s please copy it first%s.'),"<a href='#' title=\"".$clang->gT("Copy Template")."\""
   ." onclick=\"javascript: copyprompt('".$clang->gT("Please enter the name for the copied template:")."', '".$clang->gT("copy_of_")."$templatename', '$templatename', 'copy')\">",'</a>'); ?></span>
<?php } ?>

        </div>
        <div class='menubar-right'>

            <font style='boxcaption'><strong><?php echo $clang->gT("Template:"); ?></strong> </font>
            <select class="listboxtemplates" name='templatedir' onchange="javascript: window.open('<?php echo site_url("admin/templates/view/".$editfile."/".$screenname); ?>/'+escape(this.value), '_top')">
            <?php echo templateoptions($templates, $templatename); ?>
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