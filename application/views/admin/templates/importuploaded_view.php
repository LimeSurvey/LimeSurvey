<div class='header ui-widget-header'><?php $clang->eT("Import template") ?></div>
<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php $clang->eT("Success") ?></div><br />
    <?php $clang->eT("File upload succeeded.") ?><br /><br />
    <?php $clang->eT("Reading file...") ?><br /><br />
    <strong><?php $clang->eT("Imported template files for") ?></strong> <?php echo $lid ?><br /><br />
    <?php
        $okfiles = 0;
        $errfiles = 0;
        if (count($aErrorFilesInfo) == 0 && count($aImportedFilesInfo) > 0)
        {
            $status = $clang->gT("Success");
            $statusClass = 'successheader';
            $okfiles = count($aImportedFilesInfo);
        }
        elseif (count($aErrorFilesInfo) > 0 && count($aImportedFilesInfo) > 0)
        {
            $status = $clang->gT("Partial");
            $statusClass = 'partialheader';
            $okfiles = count($aImportedFilesInfo);
            $errfiles = count($aErrorFilesInfo);
        }
        else
        {
            $status = $clang->gT("Error");
            $statusClass = 'warningheader';
            $errfiles = count($aErrorFilesInfo);
        }
    ?>
    <div class="<?php echo $statusClass ?>"><?php echo $status ?></div><br />
    <strong><u><?php $clang->eT("Resources import summary") ?></u></strong><br />
    <?php echo $clang->gT("Total files imported") . ": $okfiles" ?><br />
    <?php echo $clang->gT("Total errors") . ": $errfiles" ?><br />
    <?php
        if (count($aImportedFilesInfo) > 0)
        {
        ?>
        <br /><strong><u><?php $clang->eT("Imported Files List") ?>:</u></strong><br />
        <ul>
            <?php
                foreach ($aImportedFilesInfo as $entry)
                {
                    if ($entry['is_folder']){
                    ?> 
                    <li><?php echo $clang->gT("Folder") . ": " . htmlspecialchars($entry["filename"],ENT_QUOTES,'utf-8'); ?></li>
                    <?php
                    }
                    else
                    { ?>
                    <li><?php echo $clang->gT("File") . ": " . htmlspecialchars($entry["filename"],ENT_QUOTES,'utf-8'); ?></li>


                    <?php
                    }
                }
            }
            if (count($aErrorFilesInfo) > 0)
            {
            ?>
        </ul>
        <br /><strong><u><?php $clang->eT("Error files list") ?>:</u></strong><br />
        <ul>
            <?php
                foreach ($aErrorFilesInfo as $entry)
                {
                ?>
                <li><?php echo $clang->gT("File") . ": " . $entry["filename"] ?></li>
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
        <div class="header"><?php $clang->eT("Template upgrade summary") ?></div>
        <?php
            if(!$templateFixes['success'])
            {
                $status = $clang->gT("Error");
                $statusClass = 'warningheader';
            }
            else
            {
                $status = $clang->gT("Success");
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
    <input type='submit' value='<?php $clang->eT("Open imported template") ?>' onclick="window.open('<?php echo $this->createUrl('admin/templates/sa/view/editfile/startpage.pstpl/screenname/welcome/templatename/' . $newdir) ?>', '_top')" />
</div>
