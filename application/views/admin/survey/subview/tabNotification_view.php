<div id='notification'>
    <ul>
        <li><label for='emailnotificationto'><?php eT("Send basic admin notification email to:"); ?></label>
            <?php echo CHtml::textField('emailnotificationto',$esrow['emailnotificationto'],array('size'=>70)) ?>
        </li>

        <li><label for='emailresponseto'><?php eT("Send detailed admin notification email to:"); ?></label>
            <?php echo CHtml::textField('emailresponseto',$esrow['emailresponseto'],array('size'=>70)) ?>
        </li>

        <li><label for='datestamp'><?php eT("Date Stamp?"); ?></label>
            <?php if ($esrow['active'] == "Y") { ?>
                <?php if ($esrow['datestamp'] != "Y") {
                        eT("Responses will not be date stamped.");
                    } else {
                        eT("Responses will be date stamped.");
                } ?>
                <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('datestamp',$esrow['datestamp']); // Maybe use a read-only dropdown ?>
                <?php } else { ?>
                    <?php echo CHtml::dropDownList('datestamp', $esrow['datestamp'],array("Y"=>gT("Yes"),"N"=>gT("No")),array('onchange'=>'alertDateStampAnonymization();')); ?>
                <?php } ?>
        </li>

        <li><label for='ipaddr'><?php eT("Save IP Address?"); ?></label>
            <?php if ($esrow['active'] == "Y") { ?>
                <?php  if ($esrow['ipaddr'] != "Y") {
                        eT("Responses will not have the IP address logged.");
                    } else {
                        eT("Responses will have the IP address logged");
                } ?>
                <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('ipaddr',$esrow['ipaddr']); // Maybe use a readonly dropdown ??>
                <?php } else { ?>
                    <?php echo CHtml::dropDownList('ipaddr', $esrow['ipaddr'],array("Y"=>gT("Yes"),"N"=>gT("No"))); ?>
                <?php } ?>
        </li>

        <li><label for='refurl'><?php eT("Save referrer URL?"); ?></label>

            <?php if ($esrow['active'] == "Y") { ?>
                <?php  if ($esrow['refurl'] != "Y") {
                        eT("Responses will not have their referring URL logged.");
                    } else {
                        eT("Responses will have their referring URL logged.");
                } ?>
                <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('refurl',$esrow['refurl']); // Maybe use a readonly dropdown ??>
                <?php } else { ?>
                    <?php echo CHtml::dropDownList('refurl', $esrow['refurl'],array("Y"=>gT("Yes"),"N"=>gT("No"))); ?>
                <?php } ?>
        </li>

        <li><label for='savetimings'><?php eT("Save timings?"); ?></label>
            <?php if ($esrow['active']=="Y") { ?>
                <?php if ($esrow['savetimings'] != "Y")
                    {
                        eT("Timings will not be saved.");
                    }
                    else
                    {
                        eT("Timings will be saved.");
                    }
                ?>
                <span class='annotation'> <?php eT("Cannot be changed"); ?></span>
                <?php echo CHtml::hiddenField('savetimings',$esrow['savetimings']); // Maybe use a readonly dropdown ??>
            <?php } else { ?>
                    <?php echo CHtml::dropDownList('savetimings', $esrow['savetimings'],array("Y"=>gT("Yes"),"N"=>gT("No"))); ?>
            <?php } ?>
        </li>

        <li><label for='assessments'><?php eT("Enable assessment mode?"); ?></label>
            <?php echo CHtml::dropDownList('assessments', $esrow['assessments'],array("Y"=>gT("Yes"),"N"=>gT("No"))); ?>
        </li>

        <li><label for='allowsave'><?php eT("Participant may save and resume later?"); ?></label>
            <?php echo CHtml::dropDownList('allowsave', $esrow['allowsave'],array("Y"=>gT("Yes"),"N"=>gT("No"))); ?>
        </li>

        <li><label for='googleanalyticsapikey'><?php eT("Google Analytics API key for this survey?"); ?></label>
            <?php echo CHtml::textField('googleanalyticsapikey',$esrow['googleanalyticsapikey'],array('size'=>20)) ?>
        </li>

        <li><label for='googleanalyticsstyle'><?php eT("Google Analytics style for this survey?"); ?></label>
            <?php echo CHtml::dropDownList('googleanalyticsstyle', $esrow['googleanalyticsstyle'],array("0"=>gT("Do not use Google Analytics"),"1"=>gT("Default Google Analytics"),"2"=>gT("Survey name-[SID]/Group name"))); ?>
        </li>

    </ul>
</div>
