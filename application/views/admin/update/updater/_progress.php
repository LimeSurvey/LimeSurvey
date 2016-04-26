<?php
/**
 * This view display the left progress "menus" (steps such as welcome, or pre-installation check, etc.)
 * The ajax code change the active step.
 */
?>
<div id="progressContainer">
    <h2 class="maintitle"><?php eT("Progress"); ?></h2>
    <ol>
        <li id ="step0Updt" class="on">
            <span id="welcome"><?php eT("Welcome"); ?></span>
            <span id="newKey" style="display : none;"><?php eT("New key"); ?></span>
        </li>

        <li id="step1Updt" class="off">
            <?php eT("Pre-installation check"); ?>
        </li>
        <li id="step2Updt" class="off">
            <?php eT("Change log"); ?>
        </li>
        <li id="step3Updt" class="off">
            <?php eT("File system"); ?>
        </li>
        <li id="step4Updt" class="off">
            <?php eT("Backup"); ?>
        </li>
        <li id="step5Updt" class="off">
            <?php eT("End"); ?>
        </li>
    </ol>
</div>
