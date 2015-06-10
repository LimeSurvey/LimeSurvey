<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php eT("Uploaded CSV file successfully"); ?></div>
    <?php if (empty($aTokenListArray)) { ?>
        <div class='warningheader'><?php eT("Failed to open the uploaded file!"); ?></div>
        <?php } ?>
    <?php if ($iRecordImported != 0) { ?>
        <div class='successheader'><?php eT("Successfully created token entries"); ?></div>
        <?php } else { ?>
        <div class='warningheader'><?php eT("Failed to create token entries"); ?></div>
        <?php } ?>

    <ul>
        <li><?php printf(gT("%s records in CSV"), $iRecordCount); ?></li>
        <li><?php printf(gT("%s records met minimum requirements"), $iRecordOk); ?></li>
        <?php if($iInvalidEmailCount) { ?>
            <li><?php printf(gT("%s records with allowed invalid email"), $iInvalidEmailCount); ?></li>
        <?php } ?>
        <li><?php printf(gT("%s records imported"), $iRecordImported); ?></li>
    </ul>

    <?php if (!empty($aDuplicateList) || !empty($aInvalidFormatList) || !empty($aInvalidEmailList) || !empty($aModelErrorList)) { ?>

        <div class='warningheader'><?php eT('Warnings'); ?></div>

        <ul>
            <?php if (!empty($aDuplicateList)) { ?>
                <li>
                    <?php printf(gT("%s duplicate records removed"), count($aDuplicateList)); ?>
                    [<a href='#' onclick='$("#duplicateslist").toggle();'><?php eT("List"); ?></a>]
                    <div class='badtokenlist' id='duplicateslist' style='display: none;'>
                        <ul>
                            <?php foreach ($aDuplicateList as $sDuplicate) { ?>
                                <li><?php echo $sDuplicate; ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </li>
            <?php } ?>

            <?php if (!empty($aInvalidFormatList)) { ?>
                <li>
                    <?php printf(gT("%s lines had a mismatching number of fields."), count($invalidformatlist)); ?>
                    [<a href='#' onclick='$("#invalidformatlist").toggle();'><?php eT("List"); ?></a>]
                    <div class='badtokenlist' id='invalidformatlist' style='display: none;'>
                        <ul>
                            <?php foreach ($aInvalidFormatList as $sInvalidFormatList) { ?>
                                <li><?php echo $sInvalidFormatList; ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </li>
            <?php } ?>

            <?php if (!empty($aInvalidEmailList)) { ?>
                <li>
                    <?php printf(gT("%s records with invalid email address removed"), count($aInvalidEmailList)); ?>
                    [<a href='#' onclick='$("#invalidemaillist").toggle();'><?php eT("List"); ?></a>]
                    <div class='badtokenlist' id='invalidemaillist' style='display: none;'>
                        <ul>
                            <?php foreach ($aInvalidEmailList as $sInvalidEmail) { ?>
                                <li><?php echo $sInvalidEmail; ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </li>
            <?php } ?>

            <?php if (!empty($aModelErrorList)) { ?>
                <li>
                    <?php printf(gT("%s records with other invalid information"), count($aModelErrorList)); ?>
                    [<a href='#' onclick='$("#invalidmodel").toggle();'><?php eT("List"); ?></a>]
                    <div class='badtokenlist' id='invalidmodel' style='display: none;'>
                        <ul>
                            <?php foreach ($aModelErrorList as $sModelError) { ?>
                                <li><?php echo $sModelError; ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </li>
            <?php } ?>

        </ul>
        <?php } ?>
</div>
