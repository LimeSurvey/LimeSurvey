<div class='header ui-widget-header'>
    <h3 class='pagetitle'><?php neT("Select which fields to import as attributes with your {n} participant.|Select which fields to import as attributes with your {n} participants.", $linecount); ?></h3>
</div>

<div class='row draggable-container' style='z-index: 1;'>
    <div class='col-sm-4'>
        <div id="csvattribute" class="panel panel-primary">
            <div class="panel-heading">
                <?php eT("CSV field names "); ?>
                <div class='pull-right'>
                    <span id='move-all' class='btn fa fa-arrow-right no-padding' data-toggle='tooltip' data-title='<?php eT('Move all fields to create column'); ?>'></span>
                </div>
            </div>
            <div class='panel-body'>
                <p class='help-block'><?php eT("The following additional fields were found in your CSV file."); ?></p>
                <div class="csvatt droppable-csv">
                    <?php
                    foreach ($firstline as $value)
                    {
                        echo CHtml::tag(
                            "div",
                            array('id'=>"cs_{$value}",'data-name'=>$value,'class'=>"draggable well well-sm csv-attribute-item"),
                            $value
                        );
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class='col-sm-4'>
        <div id="newcreated" class="panel panel-primary">
            <div class="panel-heading">
                <?php eT("Attributes to be created") ?>
            </div>
            <div class='panel-body'>
                <p class='help-block'><?php eT("Drop a CSV field into this area to create a new participant attribute and import your data into it."); ?></p>
                <div class="newcreate droppable-new">
                </div>
            </div>
        </div>
    </div>
    <div class='col-sm-4'>
        <div id="centralattribute" class="panel panel-primary">
            <div class="panel-heading"><?php eT("Existing attribute"); ?></div>
            <div class='panel-body'>
                <p class='help-block'><?php eT("Drop a CSV field into an existing participant attribute listed below to import your data into it."); ?></p>
                <div class="centralatt">
                    <?php foreach ($attributes as $key => $value): ?>
                        <div class='col-sm-12 droppable-map'>
                            <div class='col-sm-6'>
                                <div id='c_<?php echo $value['attribute_id']; ?>' data-name='c_<?php echo $key; ?>' class='well well-sm csv-attribute-item'>
                                    <?php echo $value['attribute_name']; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class='explanation'>
                    <div class="explanation-row">
                        <input type='checkbox' id='overwrite' name='overwrite' />
                        <label for='overwrite'><?php eT("Overwrite existing token attribute values if a duplicate participant is found?") ?>
                        <br />
                        <?php
                        if($participant_id_exists) {
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
    <div class='form-group col-sm-12 text-center'>
        <input class='btn btn-default' type="button" name="attmapcancel" id="attmapcancel" value="<?php eT("Cancel") ?>" />
        <input class='btn btn-default' type="button" name="attreset" id="attreset" value="<?php eT("Reset") ?>" onClick="window.location.reload();" />
        <input class='btn btn-default' type="button" name="attmap" id="attmap" value="<?php eT("Continue"); ?>" />
    </div>
    <div id="processing" title="<?php eT("Processing...") ?>" style="display:none">
        <img src="<?php echo Yii::app()->getConfig('adminimageurl') . '/ajax-loader.gif'; ?>" alt="<?php eT('Loading...'); ?>" title="<?php eT('Loading...'); ?>" />
    </div>
</div>
