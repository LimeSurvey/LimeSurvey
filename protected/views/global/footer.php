<div class= "row footer">
    <div style='display: inline-block; width: 33%;'>
    </div>

    <ul class='subtitle' style="display: inline-block; width: 33%; text-align: center; list-style-type: none;">
        <li>
            <a class='subtitle' title='<?php eT("Visit our website!"); ?>' href='http://www.limesurvey.org' target='_blank'>LimeSurvey</a>
        </li>
        <li>
            <?php echo gT('Version') . " " .  App()->params["version"]; ?>
        </li>
        <li>
            <?php echo App()->getConfig("buildnumber"); ?>
        </li>
         
    </ul>
    <div style='display: inline-block; width: 33%; text-align: right;'>
        <a href='http://donate.limesurvey.org'><img alt='<?php eT("Support this project - Donate to "); ?>LimeSurvey' title='<?php eT("Support this project - Donate to "); ?>LimeSurvey!' src='<?php echo App()->theme->baseUrl ?>/images/donate.png'/></a>
    </div>
</div>