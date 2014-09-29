<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php $clang->eT("Uploaded CSV file successfully"); ?></div>
    <?php if (empty($tokenlistarray)) { ?>
        <div class='warningheader'><?php $clang->eT("Failed to open the uploaded file!"); ?></div>
        <?php } ?>
    <?php if (!in_array('firstname', $firstline) || !in_array('lastname', $firstline) || !in_array('email', $firstline)) { ?>
        <div class='warningheader'><?php printf($clang->gT("Error: Your uploaded file is missing one or more of the mandatory columns (%s)"),"firstname, lastname, email"); ?></div>
        <?php } ?>
    <?php if ($xz != 0) { ?>
        <div class='successheader'><?php $clang->eT("Successfully created token entries"); ?></div>
        <?php } else { ?>
        <div class='warningheader'><?php $clang->eT("Failed to create token entries"); ?></div>
        <?php } ?>

    <ul>
        <li><?php printf($clang->gT("%s records in CSV"), $recordcount); ?></li>
        <li><?php printf($clang->gT("%s records met minimum requirements"), $xv); ?></li>
        <li><?php printf($clang->gT("%s records imported"), $xz); ?></li>
    </ul>

    <?php if (!empty($duplicatelist) || !empty($invalidformatlist) || !empty($invalidemaillist) || !empty($errorlist)) { ?>

        <div class='warningheader'><?php $clang->eT('Warnings'); ?></div>

        <ul>
            <?php if (!empty($duplicatelist)) { ?>
                <li>
                    <?php printf($clang->gT("%s duplicate records removed"), count($duplicatelist)); ?>
                    [<a href='#' onclick='$("#duplicateslist").toggle();'><?php $clang->eT("List"); ?></a>]
                    <div class='badtokenlist' id='duplicateslist' style='display: none;'>
                        <ul>
                            <?php foreach ($duplicatelist as $aData) { ?>
                                <li><?php echo $aData; ?></li>
                                <?php } ?>
                        </ul>
                    </div>
                </li>
                <?php } ?>

            <?php if (!empty($invalidformatlist)) { ?>
                <li>
                    <?php printf($clang->gT("%s lines had a mismatching number of fields."), count($invalidformatlist)); ?>
                    [<a href='#' onclick='$("#invalidformatlist").toggle();'><?php $clang->eT("List"); ?></a>]
                    <div class='badtokenlist' id='invalidformatlist' style='display: none;'>
                        <ul>
                            <?php foreach ($invalidformatlist as $aData) { ?>
                                <li><?php echo $aData; ?></li>
                                <?php } ?>
                        </ul>
                    </div>
                </li>
                <?php } ?>

            <?php if (!empty($invalidemaillist)) { ?>
                <li>
                <?php printf($clang->gT("%s records with invalid email address removed"), count($invalidemaillist)); ?>
                [<a href='#' onclick='$("#invalidemaillist").toggle();'><?php $clang->eT("List"); ?></a>]
                <div class='badtokenlist' id='invalidemaillist' style='display: none;'>
                    <ul>
                        <?php foreach ($invalidemaillist as $aData) { ?>
                            <li><?php echo $aData; ?></li>
                            <?php } ?>
                    </ul>
                </div>
                </li>
            <?php } ?>
            <?php if (!empty($errorlist)) { ?>
                <li>
                <?php printf($clang->gT("%s records with invalid information"), count($errorlist)); ?>
                [<a href='#' onclick='$("#errorlist").toggle();'><?php $clang->eT("List"); ?></a>]
                <div class='badtokenlist' id='errorlist' style='display: none;'>
                    <ul>
                        <?php foreach ($errorlist as $aData) { ?>
                            <li><?php echo $aData; ?></li>
                            <?php } ?>
                    </ul>
                </div>
                </li>
            <?php } ?>
        </ul>
        <?php } ?>
</div>
