<?php
/**
 * Shortfreetext, location map service style, item Html
 *
 * @var $freeTextId                  answer{$ia[1]}
 * @var $extraclass
 * @var $name                        $ia[1]
 * @var $value                       $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]}
 * @var $kpclass
 * @var $currentLocation
 * @var $strBuild
 * @var $checkconditionFunction      $checkconditionFunction(this.value, this.name, this.type)
 * @var $location_mapservice         $aQuestionAttributes['location_mapservice']
 * @var $location_mapzoom            $aQuestionAttributes['location_mapzoom']
 * @var $location_mapheight          $aQuestionAttributes['location_mapheight']
 * @var $questionHelp
 * @var $question_text_help          $question_text['help']
 */
?>

<script type="text/javascript">
    zoom['<?php echo $name;?>'] = <?php echo $location_mapzoom;?>;
</script>

<div class="question answer-item geoloc-item <?php echo $extraclass;?>">
    <!-- Input Text Location -->
    <input
        class="text location <?php echo $kpclass; ?> if-no-js"
        type="text"
        size="20"
        name="<?php echo $name; ?>_c"
        id="answer<?php echo $name; ?>_c"
        value="<?php echo $currentLocation; ?>"
        onchange="<?php echo $checkconditionFunction; ?>"
        />

    <!-- Map -->
    <div
        id="gmap_canvas_<?php echo $name; ?>_c"
        class='col-xs-12'
        style='height: <?php echo $location_mapheight;?>px'>
    </div>

    <!-- hidden input -->
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
        value = "<?php echo $location_mapservice;?>"
        />

    <input
        type="hidden"
        name="<?php echo $name;?>"
        id="<?php echo $freeTextId;?>"
        value="<?php echo $value; ?>"
        />

    <?php if($questionHelp):?>
        <div class="questionhelp">
            <?php echo $question_text_help; ?>
        </div>
    <?php endif;?>

</div>
