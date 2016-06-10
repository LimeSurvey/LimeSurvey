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

<div class="question answer-item geoloc-item <?php echo $extraclass; ?>">

    <div class="geoname_search col-xs-12" >
        <div class="pull-left search-icon">
            <span class="fa fa-search"></span>
        </div>
        <div class="geoname_search col-xs-6" >
            <input
                id="searchbox_<?php echo $name; ?>"
                placeholder="<?php eT("Search"); ?>"
                class="form-control"
                type="text"
             />
        </div>

        <div class="pull-left checkbox">
            <input
                type="checkbox"
                id="restrictToExtent_<?php echo $name; ?>"
                />
                <label for="restrictToExtent_<?php echo $name; ?>">
                    <?php eT("Restrict search place to map extent"); ?>
                </label>
        </div>
    </div>

    <div class="col-xs-12 if-no-js">
        <!-- No javascript need a way to answer -->

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

            <ul class="list-unstyled coordinates-list col-xs-12">
                <li class="coordinate-item">
                    <?php eT("Latitude:"); ?>
                    <input
                    class="coords text"
                    type="text"
                    name="<?php echo $name; ?>_c1"
                    id="answer_lat<?php echo $name; ?>_c"
                    value="<?php echo $currentLat; ?>"
                    />
                </li>

                <li class="coordinate-item">
                    <?php eT("Longitude:"); ?>
                    <input
                    class="coords text"
                    type="text"
                    name="<?php echo $name; ?>_c2"
                    id="answer_lng<?php echo $name; ?>_c"
                    value="<?php echo $currentLong; ?>"
                    />
                </li>
            </ul>

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

    <!-- Map -->
    <div id="map_<?php echo $name; ?>" style="width: 100%; height: <?php echo $location_mapheight; ?>px;">

    </div>

    <?php if($questionHelp):?>
        <div class="questionhelp">
            <?php echo $question_text_help; ?>
        </div>
    <?php endif;?>
</div>
