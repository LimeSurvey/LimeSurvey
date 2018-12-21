<?php
    // TODO : move all this to controller
    $okfiles = 0;
    $errfiles = 0;
    if (count($aErrorFilesInfo) == 0 && count($aImportedFilesInfo) > 0)
    {
        $status = gT("Success");
        $class = '';
        $statusClass = 'text-success';
        $okfiles = count($aImportedFilesInfo);
    }
    elseif (count($aErrorFilesInfo) > 0 && count($aImportedFilesInfo) > 0)
    {
        $status = gT("Partial");
        $class = 'message-box-warning';
        $statusClass = 'text-warning';
        $okfiles = count($aImportedFilesInfo);
        $errfiles = count($aErrorFilesInfo);
    }
    else
    {
        $status = gT("Error");
        $statusClass = 'text-error';
        $class = 'message-box-error';
        $errfiles = count($aErrorFilesInfo);
    }
?>
<div class="row">
    <div class="col-sm-11 col-sm-offset-1 content-right">
        <!-- Message box from super admin -->
        <div class="jumbotron message-box <?php echo $class;?>">
            <div class="h2"><?php eT("Import theme result:") ?></div>

            <p class='lead <?php echo $statusClass;?>'>
                <?php echo $status ?>
            </p>

            <p>
                <strong><u><?php eT("Resources import summary") ?></u></strong><br />
                <?php echo gT("Files imported:") . " $okfiles" ?><br />
                <?php echo gT("Files skipped:") . " $errfiles" ?><br />
            </p>
            <p>
                <?php
                    if (count($aImportedFilesInfo) > 0)
                    {
                    ?>
                    <br /><strong><u><?php eT("Imported files:") ?></u></strong><br />
                    <ul style="max-height: 250px; overflow-y:scroll;" class="list-unstyled">
                        <?php
                            foreach ($aImportedFilesInfo as $entry)
                            {
                                if ($entry['is_folder']){
                                ?>
                                <li><?php printf(gT("Folder: %s"),CHtml::encode($entry["filename"])); ?></li>
                                <?php
                                }
                                else
                                { ?>
                                <li><?php printf(gT("File: %s"),CHtml::encode($entry["filename"])); ?></li>


                                <?php
                                }
                            }
                        }
                        if (count($aErrorFilesInfo) > 0)
                        {
                        ?>
                    </ul>
                    <br /><strong><u><?php eT("Skipped files:") ?></u></strong><br />
                    <ul style="max-height: 250px; overflow-y:scroll;" class="list-unstyled">
                        <?php
                            foreach ($aErrorFilesInfo as $entry)
                            {
                            ?>
                            <li><?php printf(gT("File: %s"),CHtml::encode($entry["filename"])); ?></li>
                            <?php
                            }
                        }
                    ?>
                </ul>
            </p>
            <p>
                <input type='submit' class="btn btn-default btn-lg" id="button-open-theme" value='<?php eT("Open imported theme") ?>' onclick="window.open('<?php echo $this->createUrl('admin/themes/sa/view/templatename/' . $newdir) ?>', '_top')" />
            </p>
        </div>
    </div>
</div>
