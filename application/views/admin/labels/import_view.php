<div class='header ui-widget-header'><?php $clang->eT("Import Label Set") ?></div>
<div class='messagebox ui-corner-all'>
    <?php
        if (isset($aImportResults['fatalerror']))
        {
        ?>       <div class='successheader'><?php $clang->eT("Error") ?></div><br />

        <p><?php echo $aImportResults['fatalerror']; ?> </p><br/>
        <?php
        }
        else
        {
        ?>


        <div class='successheader'><?php $clang->eT("Success") ?></div><br />
        <?php $clang->eT("File upload succeeded.") ?><br /><br />
        <?php $clang->eT("Reading file..") ?><br /><br />
        <?php
            if (count($aImportResults['warnings']) > 0)
            {
            ?>
            <br />
            <div class='warningheader'><?php $clang->eT("Warnings") ?></div>
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
        <div class='successheader'><?php $clang->eT("Success") ?></div><br />
        <strong><u><?php $clang->eT("Label set import summary") ?></u></strong><br />
        <ul style="text-align:left;">
            <li><?php echo $clang->gT("Label sets") . ": {$aImportResults['labelsets']}" ?></li>
            <li><?php echo $clang->gT("Labels") . ": {$aImportResults['labels']}" ?></li>
        </ul>
        <strong><?php $clang->eT("Import of label set(s) is completed.") ?></strong><br /><br />
        <?php
        }
    ?>
    <input type='submit' value='<?php $clang->eT("Return to label set administration"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/labels/sa/view') ?>', '_top')" />
</div><br />