

<div class="row">
    <div class="col-sm-11 col-sm-offset-1 content-right">
        <!-- Message box from super admin -->
        <div class="jumbotron message-box <?php echo isset($class) ? $class : ""; ?>">
            <div class="h2"><?php echo $title;?></div>
            <dl>
            <?php if(isset($aResult['success']) && is_array($aResult['success'])) {?>
                <dt class='success'><?php eT("Success"); ?></dt>
                <?php foreach($aResult['success'] as $sSucces) { ?>
                    <dd><?php echo $sSucces ?></dd>
                <?php }?>
            <?php } ?>
            <?php if(isset($aResult['errors']) && is_array($aResult['errors'])) {?>
                <dt class='error'><?php eT("Error"); ?></dt>
                <?php foreach($aResult['errors'] as $sError) { ?>
                    <dd><?php echo $sError ?></dd>
                <?php }?>
            <?php } ?>
            <?php if(isset($aResult['warnings']) && is_array($aResult['warnings'])) {?>
                <dt class='warning'><?php eT("Warning"); ?></dt>
                <?php foreach($aResult['warnings'] as $sWarning) { ?>
                    <dd><?php echo $sWarning ?></dd>
                <?php }?>
            <?php } ?>
            </dl>
            <?php //echo $message;?>
            <?php if(isset($aUrls) && count($aUrls)) {?>
                <?php foreach($aUrls as $url){ ?>
                    <a class='limebutton submit' href='<?php echo $url['link'] ?>'><?php echo $url['text'] ?></a>
                <?php } ?>
            <?php }else{ ?>
                    <a class='limebutton submit' href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$iSurveyId"); ?>'><?php eT("Browse responses") ?></a>
            <?php } ?>
        </div>
    </div>
</div>
