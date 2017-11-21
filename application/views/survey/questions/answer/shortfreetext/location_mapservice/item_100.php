<?php
/**
 * Shortfreetext, location map service style, item 100 Html
 *
 * @var $extraclass
 * @var $name                        $ia[1]
 * @var $value                       $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]; ?>
 * @var $strBuild
 * @var $checkconditionFunction      $checkconditionFunction(this.value, this.name, this.type)
 * @var $location_mapservice         $aQuestionAttributes['location_mapservice']
 * @var $location_mapheight          $aQuestionAttributes['location_mapheight']
 * @var $questionHelp
 * @var $question_text_help          $question_text['help']
 * @var $location_value          $currentLatLong[0]; ?> <?php echo $currentLatLong[1]; ?>" />
 * @var $currentLat          $currentLatLong[0]
 * @var $currentLong     $currentLatLong[1]
 */
?>

<div class="<?php echo $coreClass; ?> <?php echo $extraclass; ?> row" role="group" aria-labelledby="ls-question-text-<?php echo $basename; ?>">

    <div class="geoname_search col-sm-12 " >
        <div class="input-group">
            <div class="input-group-addon search-icon">
                <span class="fa fa-search"></span>
            </div>
                <input
                    id="searchbox_<?php echo $name; ?>"
                    placeholder="<?php eT("Search (3 characters minimum)"); ?>"
                    class="form-control"
                    type="text"
                 />

            <div class="input-group-addon checkbox-item">
                <input
                    type="checkbox"
                    id="restrictToExtent_<?php echo $name; ?>"
                    />
                    <label for="restrictToExtent_<?php echo $name; ?>">
                        <?php eT("Restrict search place to map extent"); ?>
                    </label>
            </div>
        </div>
    </div>

    <div class="col-sm-12 form-inline">
        <!-- No javascript need a way to answer -->
        <?php /* Where is the answer-item ? answer-item geoloc-item */ ?>
        <input
            type="hidden"
            name="<?php echo $name; ?>"
            id="answer<?php echo $name; ?>"
            value="<?php echo $value; ?>"
            >

        <input
            type="hidden"
            class="location"
            name="<?php echo $name; ?>_c"
            id="answer<?php echo $name; ?>_c"
            value="<?php echo $location_value; ?>"
            />
                <!-- readonly untill update a value can update the coordinate -->
                <div class="coordinate-item form-group">
                    <label for="answer_lat<?php echo $name; ?>_c" class="control-label">
                        <?php eT("Latitude:"); ?>
                    </label>
                    <input
                    class="coords text form-control"
                    type="number"
                    step="any"
                    name="<?php echo $name; ?>_c1"
                    id="answer_lat<?php echo $name; ?>_c"
                    value="<?php echo $currentLat; ?>"
                    />
                </div>

                <div class="coordinate-item form-group">
                    <label for="answer_lng<?php echo $name; ?>_c" class="control-label">
                        <?php eT("Longitude:"); ?>
                    </label>
                    <input
                    class="coords text form-control"
                    type="number"
                    step="any"
                    name="<?php echo $name; ?>_c2"
                    id="answer_lng<?php echo $name; ?>_c"
                    value="<?php echo $currentLong; ?>"
                    />
                </div>

            <input
            type="hidden"
            name="boycott_<?php echo $name; ?>"
            id="boycott_<?php echo $name; ?>"
            value = "<?php echo $strBuild; ?>"
            />

            <input
            type="hidden"
            name="mapservice_<?php echo $name; ?>"
            id="mapservice_<?php echo $name; ?>"
            class="mapservice"
            value="<?php echo $location_mapservice; ?>"
            />
    </div>
    <div class="col-sm-12">
    <!-- Map -->
        <div id="map_<?php echo $name; ?>" style="width: 100%; height: <?php echo $location_mapheight; ?>px;">
    </div>
    </div>

    <?php if($questionHelp):?>
        <div class="questionhelp col-sm-12">
            <?php echo $question_text_help; ?>
        </div>
    <?php endif;?>
</div>
