<?php
/**
 * Notificatin panel
 */
?>
<!-- Notification panel -->
<div id='notification'  class="tab-pane fade in">

    <!-- email basic to -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='emailnotificationto'><?php  eT("Send basic admin notification email to:"); ?></label>
        <div class="col-sm-6">
            <?php echo CHtml::textField('emailnotificationto',$esrow['emailnotificationto'],array('size'=>70, 'class'=>"form-control")); ?>
        </div>
    </div>

    <!-- email detail to  -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='emailresponseto'><?php  eT("Send detailed admin notification email to:"); ?></label>
        <div class="col-sm-6">
            <?php echo CHtml::textField('emailresponseto',$esrow['emailresponseto'],array('size'=>70, 'class'=>"form-control")) ?>
        </div>
    </div>

    <!-- Date Stamp -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='datestamp'><?php  eT("Date stamp:"); ?></label>
        <div class="col-sm-6">
            <?php if ($esrow['active'] == "Y") { ?>
                <?php if ($esrow['datestamp'] != "Y") {
                         eT("Responses will not be date stamped.");
                    } else {
                         eT("Responses will be date stamped.");
                } ?>
                <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('datestamp',$esrow['datestamp']); // Maybe use a readonly dropdown? ?>
                <?php } else { ?>
                    <?php echo CHtml::dropDownList('datestamp', $esrow['datestamp'],array("Y"=>gT("Yes",'unescaped'),"N"=>gT("No",'unescaped')),array('onchange'=>'alertDateStampAnonymization();', 'class'=>"form-control" )); ?>
            <?php } ?>
        </div>
    </div>

    <!-- Save IP Address -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='ipaddr'><?php  eT("Save IP Address:"); ?></label>
        <div class="col-sm-6">
            <?php if ($esrow['active'] == "Y") { ?>
                <?php  if ($esrow['ipaddr'] != "Y") {
                         eT("Responses will not have the IP address logged.");
                    } else {
                         eT("Responses will have the IP address logged");
                } ?>
                <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('ipaddr',$esrow['ipaddr']); // Maybe use a readonly dropdown ??>
                <?php } else { ?>
                    <?php echo CHtml::dropDownList('ipaddr', $esrow['ipaddr'],array("Y"=>gT("Yes",'unescaped'),"N"=>gT("No",'unescaped')), array('class'=>"form-control")); ?>
                <?php } ?>
        </div>
    </div>

    <!-- Save referrer URL -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='refurl'><?php  eT("Save referrer URL:"); ?></label>
        <div class="col-sm-6">
            <?php if ($esrow['active'] == "Y") { ?>
                <?php  if ($esrow['refurl'] != "Y") {
                         eT("Responses will not have their referring URL logged.");
                    } else {
                         eT("Responses will have their referring URL logged.");
                } ?>
                <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('refurl',$esrow['refurl']); // Maybe use a readonly dropdown ??>
                <?php } else { ?>
                    <?php echo CHtml::dropDownList('refurl', $esrow['refurl'],array("Y"=>gT("Yes",'unescaped'),"N"=>gT("No",'unescaped')), array('class'=>"form-control")); ?>
            <?php } ?>
        </div>
    </div>

    <!-- Save timings -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='savetimings'><?php  eT("Save timings:"); ?></label>
        <div class="col-sm-6">
            <?php
            if ($esrow['active']=="Y")
                { ?>
                <?php if ($esrow['savetimings'] != "Y") {   eT("Timings will not be saved.");}
                    else {  eT("Timings will be saved.");} ?>
                <span class='annotation'> <?php  eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('savetimings',$esrow['savetimings']); // Maybe use a readonly dropdown ??>
                <?php }
                else
                { ?>
                    <?php echo CHtml::dropDownList('savetimings', $esrow['savetimings'],array("Y"=>gT("Yes",'unescaped'),"N"=>gT("No",'unescaped')), array('class'=>"form-control")); ?>
            <?php } ?>
        </div>
    </div>

    <!-- Enable assessment mode -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='assessments'><?php  eT("Enable assessment mode:"); ?></label>
        <div class="col-sm-6">
            <?php echo CHtml::dropDownList('assessments', $esrow['assessments'],array("Y"=>gT("Yes",'unescaped'),"N"=>gT("No",'unescaped')), array('class'=>"form-control")); ?>
        </div>
    </div>

    <!-- Participant may save and resume  -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='allowsave'><?php  eT("Participant may save and resume later:"); ?></label>
        <div class="col-sm-6">
            <?php echo CHtml::dropDownList('allowsave', $esrow['allowsave'],array("Y"=>gT("Yes",'unescaped'),"N"=>gT("No",'unescaped')), array('class'=>"form-control")); ?>
        </div>
    </div>

    <!-- Google Analytics -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='googleanalyticsapikey'><?php  eT("Google Analytics API key for this survey:"); ?></label>
        <div class="col-sm-6">
            <?php echo CHtml::textField('googleanalyticsapikey',$esrow['googleanalyticsapikey'],array('size'=>20), array('class'=>"form-control")); ?>
        </div>
    </div>

    <!-- Google Analytics style -->
    <div class="form-group">
        <label class="col-sm-6 control-label" for='googleanalyticsstyle'><?php  eT("Google Analytics style for this survey:"); ?></label>
        <div class="col-sm-6">
            <?php echo CHtml::dropDownList('googleanalyticsstyle', $esrow['googleanalyticsstyle'],array("0"=>gT("Do not use Google Analytics",'unescaped'),"1"=>gT("Default Google Analytics",'unescaped'),"2"=>gT("Survey name-[SID]/Group name",'unescaped')), array('class'=>"form-control")); ?>
        </div>
    </div>
</div>
