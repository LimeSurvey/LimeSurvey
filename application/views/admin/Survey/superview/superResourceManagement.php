<div id='resources'>
            <form enctype='multipart/form-data'  class='form30' id='importsurveyresources' name='importsurveyresources' action='someaction' method='post' onsubmit='return validatefilename(this,"<?php echo $clang->gT('Please select a file to import!','js'); ?>");'>
            <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            <input type='hidden' name='action' value='importsurveyresources' />
            <ul>
            <li><label>&nbsp;</label>
            <input type='button' onclick='window.open("<?php echo $sCKEditorURL; ?>/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php", "_blank")' value="<?php echo $clang->gT("Browse Uploaded Resources"); ?>" <?php echo $disabledIfNoResources; ?> /></li>
            <li><label>&nbsp;</label>
            <input type='button' onclick='window.open("<?php echo $scriptname; ?>?action=exportsurvresources&amp;sid=<?php echo $surveyid; ?>", "_blank")' value="<?php echo $clang->gT("Export Resources As ZIP Archive"); ?>" <?php echo $disabledIfNoResources; ?> /></li>
            <li><label for='the_file'><?php echo $clang->gT("Select ZIP File:"); ?></label>
            <input id='the_file' name='the_file' type='file' size='50' /></li>
            <li><label>&nbsp;</label>
            <input type='button' value='<?php echo $clang->gT("Import Resources ZIP Archive"); ?>' <?php echo $ZIPimportAction; ?> /></li>
            </ul></form>

        
</div>