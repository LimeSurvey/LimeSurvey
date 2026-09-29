<div class='header pt-2'>
    <h3 class='pagetitle'><?php neT("Select which fields to import as attributes with your {n} participant.|Select which fields to import as attributes with your {n} participants.", $linecount); ?></h3>
</div>

<div class="row justify-content-start">
    <div class='col-4'>
        <div id="csvattribute" class="card card-primary h-100">
            <div class="card-header ">
                <?php eT("CSV field names "); ?>
                <div class='float-end'>
                    <span id='move-all' class='btn ri-arrow-right-fill no-padding' data-bs-toggle='tooltip' data-title='<?php eT('Move all fields to create column'); ?>'></span>
                </div>
            </div>
            <div class='card-body'>
                <p class='help-block'><?php eT("The following additional fields were found in your CSV file."); ?></p>
                <div class="csvatt droppable-csv">
                    <?php
                    foreach ($firstline as $value) {
                        echo CHtml::tag(
                            "div",
                            array(
                                'id'        => "cs_{$value}",
                                'data-name' => $value,
                                'class'     => "draggable card csv-attribute-item"
                            ),
                            '<div class="card-body">' . $value . '</div>'
                        );
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class='col-4'>
        <div id="newcreated" class="card card-primary h-100">
            <div class="card-header ">
                <?php eT("Attributes to be created") ?>
            </div>
            <div class='card-body'>
                <p class='help-block'><?php eT("Drop a CSV field into this area to create a new participant attribute and import your data into it."); ?></p>
                <div class="newcreate droppable-new">
                </div>
            </div>
        </div>
    </div>
    <div class='col-4'>
        <div id="centralattribute" class="card card-primary h-100">
            <div class="card-header "><?php eT("Existing attribute"); ?></div>
            <div class='card-body'>
                <p class='help-block'><?php eT("Drop a CSV field into an existing participant attribute listed below to import your data into it."); ?></p>
                <div class="centralatt">
                    <?php foreach ($attributes as $key => $value) : ?>
                        <div class='col-12 row droppable-map'>
                            <div class='col-6'>
                                <div id='c_<?php echo $value['attribute_id']; ?>' data-name='c_<?php echo $key; ?>' class='card csv-attribute-item'>
                                    <div class='card-body'>
                                        <?php echo $value['attribute_name']; ?>
                                        <span class='ri-arrow-left-right-fill tokenatt-arrow'></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class='explanation'>
                    <div class="explanation-row">
                        <input type='checkbox' id='overwrite' name='overwrite' />
                        <label for='overwrite'><?php eT("Overwrite existing participant attribute values if a duplicate participant is found?") ?>
                        <br />
                        <?php
                        if ($participant_id_exists) {
                            eT("Duplicates will be detected using the participant_id field in this CSV file.");
                        } else {
                            eT("Duplicates will be detected by a combination of firstname, lastname and email addresses.");
                        }
                        ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class='m-3 col-12 text-center'>
        <input class='btn btn-cancel' type="button" name="attmapcancel" id="attmapcancel" value="<?php eT("Cancel") ?>" />
        <input class='btn btn-outline-secondary' type="button" name="attreset" id="attreset" value="<?php eT("Reset") ?>" onClick="window.location.reload();" />
        <input class='btn btn-outline-secondary' type="button" name="attmap" id="attmap" value="<?php eT("Continue"); ?>" />
    </div>
    <div id="processing" title="<?php eT("Processing...") ?>"></div>
</div>
