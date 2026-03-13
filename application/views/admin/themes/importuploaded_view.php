<?php
// TODO : move all this to controller
$okfiles = 0;
$errfiles = 0;
if ($result === 'success') {
    $status = gT("Success");
    $class = '';
    $statusClass = 'text-success';
    $okfiles = count($aImportedFilesInfo);
} elseif ($result === 'partial') {
    $status = gT("Partial");
    $class = 'message-box-warning';
    $statusClass = 'text-danger';
    $okfiles = count($aImportedFilesInfo);
    $errfiles = count($aErrorFilesInfo);
} else {
    $status = gT("Error");
    $statusClass = 'text-error';
    $class = 'message-box-error';
    $errfiles = count($aErrorFilesInfo);
}
?>
<div class="row">
    <div class="col-md-11 offset-md-1 content-right">
        <!-- Message box from super admin -->
        <div class="jumbotron message-box <?php echo $class;?>">
            <div class="h2"><?php eT("Import theme result:") ?></div>

            <p class='lead <?php echo $statusClass;?>'>
                <?php echo $status ?>
            </p>

            <p>
                <?php if (count($aImportedFilesInfo) > 0 || count($aErrorFilesInfo) > 0): ?>
                    <strong><u><?php eT("Resources import summary") ?></u></strong><br />
                <?php endif; ?>
                <?php if (count($aImportedFilesInfo) > 0): ?>
                    <?php echo gT("Files imported:") . " $okfiles" ?><br />
                <?php endif; ?>
                <?php if (count($aErrorFilesInfo) > 0): ?>
                    <?php echo gT("Files skipped:") . " $errfiles" ?><br />
                <?php endif; ?>
            </p>
            <?php if (count($aImportedFilesInfo) > 0): ?>
                <p>
                    <br><strong><u><?php eT("Imported files list:") ?></u></strong><br>
                </p>
                <ul style="max-height: 250px; overflow-y:scroll;" class="list-unstyled">
                    <?php foreach ($aImportedFilesInfo as $entry): ?>
                        <?php if ($entry['is_folder']): ?>
                            <li><?php printf(gT("Folder: %s"),CHtml::encode($entry["filename"])); ?></li>
                        <?php else: ?>
                            <li><?php printf(gT("File: %s"),CHtml::encode($entry["filename"])); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if (count($aErrorFilesInfo) > 0): ?>
                <p>
                    <br><strong><u><?php eT("Skipped files:") ?></u></strong><br>
                </p>
                <ul style="max-height: 250px; overflow-y:scroll;" class="list-unstyled">
                    <?php foreach ($aErrorFilesInfo as $entry): ?>
                        <li><?php printf(gT("File: %s"),CHtml::encode($entry["filename"])); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if (!empty($aImportErrors)): ?>
                <p>
                    <br><strong><u><?php eT("Error details:") ?></u></strong><br>
                </p>
                <ul style="max-height: 250px; overflow-y:scroll;" class="list-unstyled">
                    <?php foreach ($aImportErrors as $sThemeDirectoryName => $error): ?>
                        <li><?php echo sprintf(gT("Error importing folder: %s"),CHtml::encode($sThemeDirectoryName)) . ": " . CHtml::encode($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <p>
                <input type='submit' class="btn btn-outline-secondary btn-lg" id="button-open-theme"
                       value='<?php eT("Open imported theme") ?>'
                       onclick="window.open('<?php
                       if ($theme == 'question') {
                           echo $this->createUrl('themeOptions/index#questionthemes') . '\', ' . '\'_top\'';
                       } elseif ($theme == 'survey') {
                           echo $this->createUrl('admin/themes/sa/view/templatename/' . $newdir) . '\', ' . '\'_top\'';
                       }
                       ?>)"
                />
            </p>
        </div>
    </div>
</div>
