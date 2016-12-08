<div class="col-lg-12 list-surveys" id="comfortUpdateGeneralWrap">
    <h3>
        <span id="comfortUpdateIcon" class="icon-shield text-success"></span>
        <?php eT('ComfortUpdate'); ?>
        <?php if(YII_DEBUG):?>
            <small>server:<em class="text-warning"> <?php echo Yii::app()->getConfig("comfort_update_server_url");?></em></small>
        <?php endif;?>
    </h3>

    <?php if($updateKey): ?>
        ok
    <?php else:?>
        <div class="jumbotron message-box ">
            <h2 class="text-success">Pwet</h2>
            <p class="lead">
            <?php eT('The LimeSurvey ComfortUpdate is a great feature to easily update to the latest version of LimeSurvey. To use it you will need an update key.');?>
            </p>
            <p>
                <?php
                    $aopen  = '<a href="https://www.limesurvey.org/en/your-account/your-details" target="_blank">';
                    $aclose = '</a>';
                ?>
                <?php echo sprintf(gT("You can get a free trial update key from %syour account on the limesurvey.org website%s."),$aopen, $aclose); ?>
                <?php
                    $aopen  = '<a href="https://www.limesurvey.org/en/cb-registration/registers">';
                    $aclose = '</a>';
                    ?><br>
                <?php echo sprintf(gT("If you don't have an account on limesurvey.org, please %sregister first%s."),$aopen, $aclose);?></p>

            </p>
        </div>
    <?php endif;?>
</div>
