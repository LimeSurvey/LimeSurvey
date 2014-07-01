    <div class='header ui-widget-header'>
        <strong><?php printf($clang->ngT("Select which fields to import as attributes with your participant.","Select which fields to import as attributes with your %s participants.",$linecount), $linecount); ?></strong>
    </div>
    <div class="draggable-container">
        <div id="csvattribute" class="attribute-column">
            <div class="heading"><?php $clang->eT("CSV field names "); ?></div>
            <div class='instructions'><?php $clang->eT("The following additional fields were found in your CSV file."); ?></div>
            <div class="csvatt droppable">
                <?php
                foreach ($firstline as $key => $value)
                {
                    echo "<div id='cs_" . $value . "' data-name='" . $value . "' class=\"csv-attribute attribute-item draggable\">" . $value . "</div>";
                }
                ?>
            </div>
        </div>
        <div id="newcreated" class="attribute-column">
            <div class="heading"><?php $clang->eT("Attributes to be created") ?></div>
            <div class='instructions'><?php $clang->eT("Drop a CSV field into this area to create a new participant attribute and import your data into it."); ?></div>
            <div class="newcreate droppable" style ="height: 40px">
            </div>
        </div>
        <div id="centralattribute" class="attribute-column">
            <div class="heading"><?php $clang->eT("Existing attribute"); ?></div>
            <div class='instructions'><?php $clang->eT("Drop a CSV field into an existing participant attribute listed below to import your data into it."); ?></div>
            <div class="centralatt">
                <?php
                foreach ($attributes as $key => $value)
                {
                    echo "<div class=\"mappable-attribute-wrapper droppable\"><div id='c_" . $value['attribute_id'] . "' data-name='c_" . $key . "' class=\"mappable-attribute attribute-item\">" . $value['attribute_name'] . "</div></div>";
                }
                ?>
            </div>
            <div class='explanation'>
                <div class="explanation-row">
                    <input type='checkbox' id='overwrite' name='overwrite' />
                    <label for='overwrite'><?php $clang->eT("Overwrite existing token attribute values if a duplicate participant is found?") ?>
                    <br />
                    <?php
                    if($participant_id_exists) {
                        $clang->eT("Duplicates will be detected using the participant_id field in this CSV file.");
                    } else {
                        $clang->eT("Duplicates will be detected by a combination of firstname, lastname and email addresses.");
                    }
                    ?>
                    </label>
                </div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
    <p>
        <input type="button" name="attmapcancel" id="attmapcancel" value="<?php $clang->eT("Cancel") ?>" />
        <input type="button" name="attreset" id="attreset" value="<?php $clang->eT("Reset") ?>" onClick="window.location.reload();" />
        <input type="button" name="attmap" id="attmap" value="<?php $clang->eT("Continue"); ?>" />
    </p>
    <div id="processing" title="<?php $clang->eT("Processing...") ?>" style="display:none">
        <img src="<?php echo Yii::app()->getConfig('adminimageurl') . '/ajax-loader.gif'; ?>" alt="<?php $clang->eT('Loading...'); ?>" title="<?php $clang->eT('Loading...'); ?>" />
    </div>
