<div class='header ui-widget-header'><?php echo $clang->gT("Uploaded template file") ?></div>
<form enctype='multipart/form-data' id='importtemplate' name='importtemplate' action='<?php echo $this->createUrl('admin/templates/sa/upload') ?>' method='post' onsubmit='return validatefilename(this, "<?php echo $clang->gT('Please select a file to import!', 'js') ?>");'>
    <input type='hidden' name='lid' value='$lid' />
    <input type='hidden' name='action' value='templateupload' />
    <ul>
        <li>
            <label for='the_file'><?php echo $clang->gT("Select template ZIP file:") ?></label>
            <input id='the_file' name='the_file' type="file" size="50" />
        </li>
        <li>
            <label>&nbsp;</label>
            <input type='button' value='<?php echo $clang->gT("Import template ZIP archive") ?>'
<?php
        if (!function_exists("zip_open"))
        {?>
                   onclick='alert("<?php echo $clang->gT("zip library not supported by PHP, Import ZIP Disabled", "js") ?>");'
<?php
        }
        else
        {?>
                   onclick='if (validatefilename(this.form,"<?php echo $clang->gT('Please select a file to import!', 'js') ?>")) { this.form.submit();}'
<?php
        }?>
                   />
        </li>
    </ul>
</form>
