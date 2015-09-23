<div class='header ui-widget-header'><?php eT("Import Label Set") ?></div>
<div class='messagebox ui-corner-all'>
    <?php
        if (isset($aImportResults['fatalerror']))
        {
        ?>       <div class='successheader'><?php eT("Error") ?></div><br />

        <p><?php echo $aImportResults['fatalerror']; ?> </p><br/>
        <?php
        }
        else
        {
        ?>


        <div class='successheader'><?php eT("Success") ?></div><br />
        <?php eT("File upload succeeded.") ?><br /><br />
        <?php eT("Reading file..") ?><br /><br />
        <?php
            if (count($aImportResults['warnings']) > 0)
            {
            ?>
            <br />
            <div class='warningheader'><?php eT("Warnings") ?></div>
            <ul>
                <?php
                    foreach ($aImportResults['warnings'] as $warning)
                    {
                    ?>
                    <li><?php echo $warning ?></li>
                    <?php
                    }
                ?>
            </ul>
            <?php
            }
        ?>
        <br />
        <div class='successheader'><?php eT("Success") ?></div><br />
        <strong><u><?php eT("Label set import summary") ?></u></strong><br />
        <ul style="text-align:left;">
            <li><?php echo gT("Label sets") . ": {$aImportResults['labelsets']}" ?></li>
            <li><?php echo gT("Labels") . ": {$aImportResults['labels']}" ?></li>
        </ul>
        <strong><?php eT("Import of label set(s) is completed.") ?></strong><br /><br />
        <?php
        }
    ?>
    <input type='submit' value='<?php eT("Return to label set administration"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/labels/sa/view') ?>', '_top')" />
</div><br />