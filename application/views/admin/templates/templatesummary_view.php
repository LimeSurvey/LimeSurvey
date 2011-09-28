<?php if (is_template_editable($templatename)==true)
{ ?>
          <script src="<?php echo base_url(); ?>scripts/admin/codemirror_ui/lib/CodeMirror-2.0/lib/codemirror.js" type="text/javascript"></script>
          <link rel="stylesheet" href="<?php echo base_url(); ?>scripts/admin/codemirror_ui/lib/CodeMirror-2.0/lib/codemirror.css" />
          <script src="<?php echo base_url(); ?>scripts/admin/codemirror_ui/lib/CodeMirror-2.0/mode/javascript/javascript.js"></script>
          <link rel="stylesheet" href="<?php echo base_url(); ?>scripts/admin/codemirror_ui/lib/CodeMirror-2.0/mode/javascript/javascript.css" />
          <script src="<?php echo base_url(); ?>scripts/admin/codemirror_ui/js/codemirror-ui.js" type="text/javascript"></script>
          <link rel="stylesheet" href="<?php echo base_url(); ?>scripts/admin/codemirror_ui/css/codemirror-ui.css" type="text/css" media="screen" /> 
									   <table class='templatecontrol'>
                                       <tr>
                                       <th colspan='3'>
                                       <strong><?php echo sprintf($clang->gT("Editing template '%s' - File '%s'"),$templatename,$editfile); ?></strong>
                                       </th>
                                       </tr>
                                       <tr><th class='subheader' width='150'>
                                       <?php echo $clang->gT("Standard Files:"); ?></th>
                                       <td align='center' valign='top' rowspan='3'>
                                       <form name='editTemplate' method='post' action='<?php echo site_url("admin/templates/templatesavechanges"); ?>'>
                                       <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
                                       <input type='hidden' name='screenname' value='<?php echo html_escape($screenname); ?>' />
                                       <input type='hidden' name='editfile' value='<?php echo $editfile; ?>' />
                                       <input type='hidden' name='action' value='templatesavechanges' />
                                       
                                        <textarea name='changes' id='changes' rows='15' cols='40' class='codepress html <?php echo $templateclasseditormode; ?>'>
                                        <?php if (isset($editfile)) { 
                                        echo textarea_encode(filetext($templatename,$editfile,$templates));
                                        } ?>                                           
                                        </textarea>
                 <script type="text/javascript">
                    var codemirropath = '<?php echo base_url(); ?>scripts/admin/codemirror_ui/js/';
                 </script>
                                       <br />
                                        <?php if (is_writable($templates[$templatename])) { ?>
                                           <input align='right' type='submit' value='<?php echo $clang->gT("Save Changes"); ?>'
                                           <?php if (!is_template_editable($templatename)) { ?>
                                                disabled='disabled' alt='<?php echo $clang->gT("Changes cannot be saved to a standard template."); ?>'
                                           <?php } ?>
                                            />
                                       <?php }
                                       else
                                       { ?>
                                           <span class="flashmessage"><?php echo $clang->gT("You can't save changes because the template directory is not writable."); ?></span>
                                       <?php } ?>
                                       <br />
                                       </form></td>
                                       <th class='subheader' colspan='2' align='right' width='200'><?php echo $clang->gT("Other Files:"); ?></th></tr>

                                       <tr><td valign='top' rowspan='2' class='subheader'><select size='6' name='editfile' onchange="javascript: window.open('<?php echo site_url("admin/templates/fileredirect/".$templatename."/".urlencode($screenname)); ?>/'+escape(this.value), '_top')">
                                       <?php echo makeoptions($files, "name", "name", $editfile); ?>
                                       </select><br /><br/>
                                       <?php echo $clang->gT("CSS & Javascript files:"); ?>
                                       <br/><select size='8' name='cssfiles' onchange="javascript: window.open('<?php echo site_url("admin/templates/fileredirect/".$templatename."/".urlencode($screenname)); ?>/'+escape(this.value), '_top')">
                                       <?php echo makeoptions($cssfiles, "name", "name", $editfile); ?>
                                       </select>
        
                                       </td>
                                       <td valign='top' align='right' width='20%'>
                                       <form action='<?php echo site_url("admin/templates/templatefiledelete"); ?>' method='post'>
                                       <table width='90' align='left' border='0' cellpadding='0' cellspacing='0'><tr><td></td></tr>
                                       <tr><td><select size='11' style='min-width:130px;' name='otherfile' id='otherfile'>
                                       <?php echo makeoptions($otherfiles, "name", "name", ""); ?>
                                       </select>
                                       </td></tr>
                                       <tr><td>
                                       <input type='submit' value='<?php echo $clang->gT("Delete"); ?>' onclick="javascript:return confirm('<?php echo $clang->gT("Are you sure you want to delete this file?","js"); ?>')"
                                       <?php if (!is_template_editable($templatename))  { ?>
                                            style='color: #BBBBBB;' disabled='disabled' alt='<?php echo $clang->gT("Files in a standard template cannot be deleted."); ?>'
                                       <?php } ?>
                                        />
                                       <input type='hidden' name='screenname' value='<?php echo html_escape($screenname); ?>' />
                                       <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
                                       <input type='hidden' name='editfile' value='<?php echo $editfile; ?>' />
                                       <input type='hidden' name='action' value='templatefiledelete' />
                                       </td></tr></table>
                                       </form></td>
                                       </tr>
                                       <tr>
                                       <td valign='top'>
                                       <form enctype='multipart/form-data' name='importtemplatefile' action='admin.php' method='post' onsubmit='return checkuploadfiletype(this.the_file.value);'>
                                       <table><tr> <th class='subheader' valign='top' style='border: solid 1 #000080'>
                                       <?php echo $clang->gT("Upload a file:"); ?></th></tr><tr><td><input name="the_file" type="file" size="30" /><br />
                                       <input type='submit' value='<?php echo $clang->gT("Upload"); ?>'
                                       <?php if (!is_template_editable($templatename))  { ?>
                                            disabled='disabled'
                                       <?php } ?>

                                         />
                                       <input type='hidden' name='editfile' value='<?php echo $editfile; ?>' />
                                       <input type='hidden' name='screenname' value='<?php echo html_escape($screenname); ?>' />
                                       <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
                                       <input type='hidden' name='action' value='templateuploadfile' />
                                       </td></tr></table>
                                       </form>
                                       </td>
                                       </tr>
                                       </table>
<?php } ?>

                                
                                   <div class='header ui-widget-header'>
                                   <strong><?php echo $clang->gT("Preview:"); ?></strong>
                                   <input type='button' value='iPhone' id='iphone' />
                                   <input type='button' value='640x480' id='x640' />
                                   <input type='button' value='800x600' id='x800' />
                                   <input type='button' value='1024x768' id='x1024' />
                                   <input type='button' value='<?php echo $clang->gt("Full"); ?>' id='full' />
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
<p><iframe id='previewiframe' src='<?php echo $tempurl; ?>/template_temp_<?php echo $time; ?>.html' width='95%' height='768' name='previewiframe' style='background-color: white;'>Embedded Frame</iframe>
</div>
<?php } ?>
