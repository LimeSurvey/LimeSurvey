<h2 class="h4" id="preview-size-label">
    <?php eT("Preview:"); ?>
</h2>
<div class="jumbotron message-box">
    <div id="preview-size-tablist" role="tablist" aria-labelledby="preview-size-label">
        <button type="button" id="iphone" class="btn btn-outline-secondary preview-size-tab" role="tab" aria-selected="false" tabindex="-1"><?php eT("Mobile"); ?></button>
        <button type="button" id="x640" class="btn btn-outline-secondary preview-size-tab" role="tab" aria-selected="false" tabindex="-1">640x480</button>
        <button type="button" id="x800" class="btn btn-outline-secondary preview-size-tab" role="tab" aria-selected="false" tabindex="-1">800x600</button>
        <button type="button" id="x1024" class="btn btn-outline-secondary preview-size-tab" role="tab" aria-selected="false" tabindex="-1">1024x768</button>
        <button type="button" id="full" class="btn btn-outline-secondary preview-size-tab active" role="tab" aria-selected="true" tabindex="0"><?php eT("Full"); ?></button>
    </div>
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
