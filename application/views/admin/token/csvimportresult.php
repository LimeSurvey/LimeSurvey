<?php

/**
 * Result of CSV upload
 */

?>
<div class='side-body'>
    <div class="row">
        <div class="col-12 content-right">
            <?php if (empty($aTokenListArray) || $iRecordImported == 0) :?>
                <div class="jumbotron message-box message-box-error">
                    <h2 class="text-danger">
                        <?php
                        if (empty($aTokenListArray)) {
                            eT("Failed to open the uploaded file!");
                        } else {
                            eT("Failed to create participant entries");
                        }
                        ?>
                    </h2>
            <?php else :?>
                <div class="jumbotron message-box">
                    <h2 class="text-success"><?php eT("Uploaded CSV file successfully"); ?></h2>
                    <p class='lead text-success'><?php eT("Successfully created participant entries"); ?></p>
            <?php endif;?>
                    <p>
                        <ul class="list-unstyled">
                            <li><?php printf(gT("%s records in CSV"), $iRecordCount); ?></li>
                            <li><?php printf(gT("%s records met minimum requirements"), $iRecordOk); ?></li>
                            <?php if ($iInvalidEmailCount) { ?>
                                <li><?php printf(gT("%s records with allowed invalid email"), $iInvalidEmailCount); ?></li>
                            <?php } ?>
                            <li><?php printf(gT("%s records imported"), $iRecordImported); ?></li>
                        </ul>
                    </p>

                    <?php if (
                    !empty($aInvalidTokenList) ||
                                !empty($aDuplicateList) ||
                                !empty($aInvalidFormatList) ||
                                !empty($aInvalidEmailList) ||
                                !empty($aModelErrorList) ||
                                !empty($aPluginErrorMessageList) ||
                                !empty($aInvalideAttrFieldName) ||
                                !empty($aMissingAttrFieldName)
) { ?>
                        <h2 class='text-danger'><?php eT('Warnings'); ?></h2>
                    <p>
                        <ul class="list-unstyled">
                                    <?php if (!empty($aInvalidTokenList)) { ?>
                                <li>
                                        <?php printf(gT("%s lines with invalid access codes skipped (access codes may only contain 0-9,a-z,A-Z,_)."), count($aInvalidTokenList)); ?>
                                    [<a href='#' onclick='$("#badtokenlist").toggle();'><?php eT("List"); ?></a>]
                                    <div class='badtokenlist well' id='badtokenlist' style='display: none;'>
                                        <ul class="list-unstyled">
                                            <?php foreach ($aInvalidTokenList as $sInvalidEntry) { ?>
                                                <li><?php echo $sInvalidEntry; ?></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </li>
                                    <?php } ?>
                                    <?php if (!empty($aDuplicateList)) { ?>
                                <li>
                                        <?php printf(gT("%s duplicate records removed"), count($aDuplicateList)); ?>
                                    [<a href='#' onclick='$("#duplicateslist").toggle();'><?php eT("List"); ?></a>]
                                    <div class='badtokenlist well' id='duplicateslist' style='display: none;'>
                                        <ul class="list-unstyled">
                                            <?php foreach ($aDuplicateList as $sDuplicate) { ?>
                                                <li><?php echo $sDuplicate; ?></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </li>
                                    <?php } ?>

                                    <?php if (!empty($aInvalidFormatList)) { ?>
                                <li>
                                        <?php printf(gT("%s lines had a mismatching number of fields."), count($aInvalidFormatList)); ?>
                                    [<a href='#' onclick='$("#invalidformatlist").toggle();'><?php eT("List"); ?></a>]
                                    <div class='badtokenlist well' id='invalidformatlist' style='display: none;'>
                                        <ul class="list-unstyled">
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
                                    <div class='badtokenlist well' id='invalidemaillist' style='display: none;'>
                                        <ul class="list-unstyled">
                                            <?php foreach ($aInvalidEmailList as $sInvalidEmail) { ?>
                                                <li><?php echo $sInvalidEmail; ?></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </li>
                                    <?php } ?>

                                    <?php if (!empty($aPluginErrorMessageList)) {
                                        $iPluginErrorIndex = 0;
                                        foreach ($aPluginErrorMessageList as $sPluginErrorMessage => $aTokenSpecificErrorList) {
                                            $iPluginErrorIndex++; ?>
                                            <li>
                                                <?php printf($sPluginErrorMessage, count($aTokenSpecificErrorList)); ?>
                                                [<a href='#' onclick='$("#pluginerrorlist-<?=$iPluginErrorIndex?>").toggle();'><?php eT("List"); ?></a>]
                                                <div class='badtokenlist well' id='pluginerrorlist-<?=$iPluginErrorIndex?>' style='display: none;'>
                                                    <ul class="list-unstyled">
                                                        <?php foreach ($aTokenSpecificErrorList as $sTokenSpecificErrorMessage) { ?>
                                                            <li><?php echo $sTokenSpecificErrorMessage; ?></li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            </li>
                                        <?php       }
                                    } ?>

                                    <?php if (!empty($aModelErrorList)) { ?>
                                    <li>
                                        <?php printf(gT("%s records with other invalid information"), count($aModelErrorList)); ?>
                                    [<a href='#' onclick='$("#invalidmodel").toggle();'><?php eT("List"); ?></a>]
                                    <div class='container badtokenlist well' id='invalidmodel' style='display: none;'>
                                        <?php
                                        foreach ($aModelErrorList as $aModelError) {
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-1 offset-lg-4"><?php printf(gT("Line %s:"), $aModelError['line']);?></div>
                                                <div class="col-lg-6"><?php echo $aModelError['errors'];?></div>
                                            </div>
                                        <?php } ?>

                                    <?php } ?>

                                    <?php if (!empty($aInvalideAttrFieldName)) { ?>
                                <li>
                                        <?php printf(gT("%s invalid attributes"), count($aInvalideAttrFieldName)); ?>
                                    [<a href='#' onclick='$("#invalidattr").toggle();'><?php eT("Ignored columns"); ?></a>]
                                    <div class='badtokenlist well' id='invalidattr' style='display: none;'>
                                        <ul class="list-unstyled">
                                            <?php foreach ($aInvalideAttrFieldName as $sModelError) { ?>
                                                <li><?php echo $sModelError; ?></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </li>
                                    <?php } ?>

                                    <?php if (!empty($aMissingAttrFieldName)) { ?>
                                <li>
                                        <?php printf(gT("%s missing attributes"), count($aMissingAttrFieldName)); ?>
                                    [<a href='#' onclick='$("#missingattr").toggle();'><?php eT("Missing columns"); ?></a>]
                                    <div class='badtokenlist well' id='missingattr' style='display: none;'>
                                        <ul class="list-unstyled">
                                            <?php foreach ($aMissingAttrFieldName as $sModelError) { ?>
                                                <li><?php echo $sModelError; ?></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </li>
                                    <?php } ?>

                        </ul>
                    <?php } ?>
                    </p>
                    <p>
                        <input class="btn btn-large btn-outline-secondary" type='button' value='<?php eT("Browse participants"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" /><br />
                    </p>
                </div>

        </div>
    </div>
</div>
