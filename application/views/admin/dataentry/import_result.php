

<div class="row">
    <div class="col-md-11 offset-md-1 content-right">
        <!-- Message box from super admin -->
        <div class="card card-body bg-light message-box <?php echo isset($class) ? $class : ""; ?>">
            <div class="h2"><?php eT("Import old table result"); ?></div>
            <dl>
            <?php if($imported) {?>
                <dt class='success text-success h2'><?php eT("Success"); ?></dt>
                <dd> <?php echo sprintf(gT("%s old response(s) were successfully imported."), $imported); ?></dd>
                <?php if (!is_null($iRecordCountT)) { ?>
                    <dd> <?php echo sprintf(gT("%s old response(s) and according %s timings were successfully imported."), $imported, $iRecordCountT); ?></dd>
                <?php } ?>
            <?php } ?>
            <?php if(count($aWarnings)) {?>
                <dt class='warning text-warning h2'><?php eT("Warning"); ?></dt>
                <?php foreach($aWarnings as $srid => $sWarning) { ?>
                    <dd><strong><?php echo sprintf(gT("Error on response ID %s"), $srid); ?></strong>
                        <div><?php echo $sWarning ?></div>
                    </dd>
                <?php }?>
            <?php } ?>
            </dl>
            <a class='limebutton btn btn-outline-secondary' href='<?php echo $this->createUrl("responses/browse/", ['surveyId' => $iSurveyId]); ?>'><?php eT("Browse responses") ?></a>
        </div>
    </div>
</div>
