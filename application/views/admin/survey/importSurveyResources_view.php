<?php if (!count($aErrorFilesInfo) &&count($aImportedFilesInfo)): ?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row welcome survey-action">
        <div class="col-sm-12 content-right">
            <div class="jumbotron message-box">
                <h2><?php eT("Import survey resources"); ?></h2>
                <p class="lead text-success">
                    <?php eT("Success");?>
                </p>
                <p>
                    <?php eT("Resources Import Summary"); ?>
                </p>
                <p>
                    <?php eT("Total Imported files"); ?>: <?php echo count($aImportedFilesInfo); ?><br />
                </p>
                <p>
                    <strong><?php eT("Imported Files List") ?>:</strong>
                </p>
                <p>
                    <ul>
                        <?php
                        foreach ($aImportedFilesInfo as $entry) {
                            echo CHtml::tag('li', array(), sprintf(gT("File: %s"),CHtml::encode($entry["filename"])));
                        }
                        ?>
                    </ul>
                </p>
                <p>
                    <input class="btn btn-default btn-lg" type='submit' value='<?php eT("Back"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/editlocalsettings/surveyid/' . $surveyid); ?>', '_top')" />
                </p>
            </div>
        </div>
    </div>
</div>
<?php elseif(count($aErrorFilesInfo) &&count($aImportedFilesInfo)): ?>
    <div class='side-body <?php echo getSideBodyClass(false); ?>'>
        <div class="row welcome survey-action">
            <div class="col-sm-12 content-right">
                <div class="jumbotron message-box message-box-warning">
                    <h2><?php eT("Import survey resources"); ?></h2>
                    <p class="lead text-warning">
                        <?php eT("Partial");?>
                    </p>
                    <p>
                        <?php eT("Resources Import Summary"); ?>
                    </p>
                    <p>
                        <?php eT("Total Imported files"); ?>: <?php echo count($aImportedFilesInfo); ?><br />
                        <?php eT("Total Errors"); ?>: <?php echo count($aErrorFilesInfo); ?><br />
                    </p>
                    <p>
                        <strong><?php eT("Imported Files List"); ?>:</strong>
                    </p>
                    <p>
                        <ul>
                            <?php
                            foreach ($aImportedFilesInfo as $entry) {
                                echo CHtml::tag('li', array(), sprintf(gT("File: %s"),CHtml::encode($entry["filename"])));
                            }
                            ?>
                        </ul>
                    </p>
                    <p>
                        <strong class="text-warning"><?php eT("Error Files List"); ?>:</strong>
                    </p>
                    <p>
                        <?php
                            foreach ($aErrorFilesInfo as $entry) {
                                echo CHtml::tag('li', array(), sprintf(gT("File: %s (%s)"),CHtml::encode($entry["filename"]),$entry['status']));
                            }
                        ?>
                        </ul>
                    </p>
                    <p>
                        <input class="btn btn-default btn-lg" type='submit' value='<?php eT("Back"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/editlocalsettings/surveyid/' . $surveyid); ?>', '_top')" />
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php else:?>
    <div class='side-body <?php echo getSideBodyClass(false); ?>'>
        <div class="row welcome survey-action">
            <div class="col-sm-12 content-right">
                <div class="jumbotron message-box message-box-error">
                    <h2><?php eT("Import survey resources"); ?></h2>
                    <p class="lead text-danger">
                        <?php eT("Error");?>
                    </p>
                    <p>
                        <?php eT("Resources Import Summary"); ?>
                    </p>
                    <p>
                        <?php eT("Total Imported files"); ?>: 0<br />
                        <?php eT("Total Errors"); ?>: <?php echo count($aErrorFilesInfo); ?><br />
                    </p>
                    <p>
                        <strong class="text-warning"><?php eT("Error Files List"); ?>:</strong>
                    </p>
                    <p>
                        <?php
                            foreach ($aErrorFilesInfo as $entry) {
                                echo CHtml::tag('li', array(), sprintf(gT("File: %s (%s)"),CHtml::encode($entry["filename"]),$entry['status']));
                            }
                        ?>
                        </ul>
                    </p>
                    <p>
                        <input class="btn btn-default btn-lg" type='submit' value='<?php eT("Back"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/editlocalsettings/surveyid/' . $surveyid); ?>', '_top')" />
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
