<?php

/**
 * Result of ldap upload
 */

?>

<div class='side-body'>
    <div class="row">
        <div class="col-12 content-right">
            <div class="jumbotron message-box<?php echo $xz == 0 ? ' message-box-error' : ''; ?>">
                <?php if ($xz != 0) : ?>
                    <h2 class="text-success"><?php eT("Success"); ?></h2>
                <?php else : ?>
                    <h2 class="text-danger"><?php eT("Error"); ?></h2>
                <?php endif; ?>
                <p><?php echo $resultnum; ?></p>
                <p>
                    <?php eT("Results from LDAP Query."); ?><br />
                    <?php printf(gT("%s records met minimum requirements"), $xv); ?><br />
                    <?php printf(gT("%s records imported"), $xz); ?>
                </p>
                <p>
                    <?php printf(gT("%s duplicate records skipped"), $xy); ?>
                    <?php if (!empty($duplicatelist)) : ?>
                        [<a href="#duplicateslist" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="duplicateslist"><?php eT("List"); ?></a>]
                    <?php endif; ?>
                </p>
                <?php if (!empty($duplicatelist)) : ?>
                    <div class="collapse" id="duplicateslist">
                        <ul class="list-unstyled">
                            <?php foreach ($duplicatelist as $sDuplicate) : ?>
                                <li><?php echo CHtml::encode($sDuplicate); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if (!empty($invalidemaillist)) : ?>
                    <p>
                        <?php printf(gT("%s records with invalid email address skipped"), $invalidemailcount); ?>
                        [<a href="#invalidemaillist" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="invalidemaillist"><?php eT("List"); ?></a>]
                    </p>
                    <div class="collapse" id="invalidemaillist">
                        <ul class="list-unstyled">
                            <?php foreach ($invalidemaillist as $sInvalidEmail) : ?>
                                <li><?php echo CHtml::encode($sInvalidEmail); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<br />
