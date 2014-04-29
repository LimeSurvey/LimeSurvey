<div class='header ui-widget-header'><?php eT("Import template") ?></div>
<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php eT("Success") ?></div><br />
    <?php eT("File upload succeeded.") ?><br /><br />
    <?php eT("Reading file...") ?><br /><br />
    <strong><?php eT("Imported template files for") ?></strong> <?php echo $lid ?><br /><br />
    <?php
        $okfiles = 0;
        $errfiles = 0;
        if (count($aErrorFilesInfo) == 0 && count($aImportedFilesInfo) > 0)
        {
            $status = gT("Success");
            $statusClass = 'successheader';
            $okfiles = count($aImportedFilesInfo);
        }
        elseif (count($aErrorFilesInfo) > 0 && count($aImportedFilesInfo) > 0)
        {
            $status = gT("Partial");
            $statusClass = 'partialheader';
            $okfiles = count($aImportedFilesInfo);
            $errfiles = count($aErrorFilesInfo);
        }
        else
        {
            $status = gT("Error");
            $statusClass = 'warningheader';
            $errfiles = count($aErrorFilesInfo);
        }
    ?>
    <div class="<?php echo $statusClass ?>"><?php echo $status ?></div><br />
    <strong><u><?php eT("Resources import summary") ?></u></strong><br />
    <?php echo gT("Total files imported") . ": $okfiles" ?><br />
    <?php echo gT("Total errors") . ": $errfiles" ?><br />
    <?php
        if (count($aImportedFilesInfo) > 0)
        {
        ?>
        <br /><strong><u><?php eT("Imported Files List") ?>:</u></strong><br />
        <ul>
            <?php
                foreach ($aImportedFilesInfo as $entry)
                {
                    if ($entry['is_folder']){
                    ?> 
                    <li><?php echo gT("Folder") . ": " . htmlspecialchars($entry["filename"],ENT_QUOTES,'utf-8'); ?></li>
                    <?php
                    }
                    else
                    { ?>
                    <li><?php echo gT("File") . ": " . htmlspecialchars($entry["filename"],ENT_QUOTES,'utf-8'); ?></li>


                    <?php
                    }
                }
            }
            if (count($aErrorFilesInfo) > 0)
            {
            ?>
        </ul>
        <br /><strong><u><?php eT("Error files list") ?>:</u></strong><br />
        <ul>
            <?php
                foreach ($aErrorFilesInfo as $entry)
                {
                ?>
                <li><?php echo gT("File") . ": " . $entry["filename"] ?></li>
                <?php
                }
            }
        ?>
    </ul>
    <?php
        if(count($templateFixes['details'])>0)
        {
        ?>
        <br />
        <div class="header"><?php eT("Template upgrade summary") ?></div>
        <?php
            if(!$templateFixes['success'])
            {
                $status = gT("Error");
                $statusClass = 'warningheader';
            }
            else
            {
                $status = gT("Success");
                $statusClass = 'successheader';
            }
        ?>
        <div class="<?php echo $statusClass ?>"><?php echo $status ?></div>
        <ul>
            <?php
                foreach ($templateFixes['details'] as $detail)
                {
                ?>
                <li><?php echo $detail ?></li>
                <?php
                }
            ?>
        </ul>
        <?php
        }
    ?>
    <input type='submit' value='<?php eT("Open imported template") ?>' onclick="window.open('<?php echo $this->createUrl('admin/templates/sa/view/editfile/startpage.pstpl/screenname/welcome/templatename/' . $newdir) ?>', '_top')" />
</div>
