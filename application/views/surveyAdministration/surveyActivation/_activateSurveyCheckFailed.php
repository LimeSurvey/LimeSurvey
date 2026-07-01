<?php

App()->getClientScript()->registerScriptFile(
    '/application/views/surveyAdministration/javascript/fix-solvable-errors.js',
    LSYii_ClientScript::POS_BEGIN
);
?>

<p class="lead text-danger"><strong><?php eT("Error"); ?> !</strong></p>
<p class="lead text-danger"><strong><?php eT("Survey does not pass consistency check"); ?></strong></p>
<p>
    <?php eT("The following problems have been found:"); ?>
</p>
<ul class="list-unstyled">
    <?php
    if (isset($failedcheck) && $failedcheck) {
        foreach ($failedcheck as $fc) { ?>

            <li> Question qid-<?php echo $fc[0]; ?> ("<a
                    href='<?php echo App()->getController()->createUrl(
                        'questionAdministration/view/surveyid/' . $surveyid . '/gid/' . $fc[3] . '/qid/' . $fc[0]
                    ); ?>'><?php echo $fc[1]; ?></a>")<?php echo $fc[2]; ?>
            </li>
        <?php }
    }

    if (isset($failedgroupcheck) && $failedgroupcheck) {
        foreach ($failedgroupcheck as $fg) { ?>

            <li> Group gid-<?php echo $fg[0]; ?> ("<a
                    href='<?php echo Yii::app()->getController()->createUrl(
                        'questionGroupsAdministration/view/surveyid/' . $surveyid . '/gid/' . $fg[0]
                    ); ?>'><?php echo flattenText($fg[1]); ?></a>")<?php echo $fg[2]; ?>
            </li>
        <?php }
    } ?>
    <?php if (!empty($error)): ?>
        <li><?= $error ?></li>
    <?php endif; ?>
</ul>
<!--
<button class="btn btn-outline-secondary" id="ajaxAllConsistency">Fix numbering</button>
-->
<p>
    <?php eT("The survey cannot be activated until these problems have been resolved."); ?>
</p>

