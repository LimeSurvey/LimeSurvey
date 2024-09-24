<div class="h4">
    <?php eT("Preview:"); ?>
</div>
<div class="jumbotron message-box">
    <input type='button' value='<?php eT("Mobile"); ?>' id='iphone' class="btn btn-outline-secondary"/>
    <input type='button' value='640x480' id='x640' class="btn btn-outline-secondary"/>
    <input type='button' value='800x600' id='x800' class="btn btn-outline-secondary"/>
    <input type='button' value='1024x768' id='x1024' class="btn btn-outline-secondary"/>
    <input type='button' value='<?php eT("Full"); ?>' id='full' class="btn btn-outline-secondary"/>
    <br>
    <br>
    <br>
    <br>

    <div class="overflow-auto" style='width:90%; margin:0 auto;'>
        <?php if (isset($filenotwritten) && $filenotwritten == true) { ?>
            <p>
                <span
                    class='errortitle'><?php echo sprintf(gT("Please change the directory permissions of the folder %s in order to preview themes."),
                        $tempdir); ?></span>
            </p>
        <?php } else { ?>
            <p>
                <iframe id='previewiframe' title='Preview'
                        src='<?php echo $this->createUrl('admin/themes/sa/tmp/', ['id' => $time]); ?>' height='768'
                        name='previewiframe' style='width:95%;background-color: white;'>Embedded Frame
                </iframe>
            </p>
        <?php } ?>
    </div>
</div>
