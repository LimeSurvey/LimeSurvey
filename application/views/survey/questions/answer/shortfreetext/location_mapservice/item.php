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

<?php
App()->getClientScript()->registerScript('MAP_VARS_'.$basename,  
    " var zoom = zoom || [];
    zoom['".$name."'] = ".$location_mapzoom.";"
    , LSYii_ClientScript::POS_END); 
?>


<div class="<?php echo $coreClass; ?> <?php echo $extraclass;?>" row" role="group" aria-labelledby="ls-question-text-<?php echo $basename; ?>">
    <!-- Input Text Location -->
    <div class="col-sm-12" >
            <input
                class="text location <?php echo $kpclass; ?>"
                type="text"
                size="20"
                name="<?php echo $name; ?>_c"
                id="answer<?php echo $name; ?>_c"
                value="<?php echo $currentLocation; ?>"
                onchange="<?php echo $checkconditionFunction; ?>"
                />
    </div>
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
        <div class="questionhelp col-sm-12">
            <?php echo $question_text_help; ?>
        </div>
    <?php endif;?>

</div>
