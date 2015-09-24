<?php
/**
 * Result of CSV upload
 */
?>
<div class="side-body">
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php if (empty($aTokenListArray) || $iRecordImported == 0 ):?>
                <div class="jumbotron message-box message-box-error">
                    <h2 class="text-danger">
                        <?php 
                                if (empty($aTokenListArray))
                                {
                                    eT("Failed to open the uploaded file!");                                    
                                }
                                else 
                                {
                                    eT("Failed to create token entries");
                                }
                        ?> 
                    </h2>
                    <p>
                        <input class="btn btn-large btn-default" type='button' value='<?php eT("Display tokens"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" /><br />
                    </p>                                                      
                </div>                
            <?php else:?>
                <div class="jumbotron message-box">
                    <h2 class="text-success"><?php eT("Uploaded CSV file successfully"); ?></h2>
                    <p class='lead text-success'><?php eT("Successfully created token entries"); ?></p>

                    <p>
                        <ul class="list-unstyled">
                            <li><?php printf(gT("%s records in CSV"), $iRecordCount); ?></li>
                            <li><?php printf(gT("%s records met minimum requirements"), $iRecordOk); ?></li>
                            <?php if($iInvalidEmailCount) { ?>
                                <li><?php printf(gT("%s records with allowed invalid email"), $iInvalidEmailCount); ?></li>
                            <?php } ?>
                            <li><?php printf(gT("%s records imported"), $iRecordImported); ?></li>
                        </ul>
                    </p>

                    <?php if (!empty($aDuplicateList) || !empty($aInvalidFormatList) || !empty($aInvalidEmailList) || !empty($aModelErrorList)) { ?>
                    <h2 class='text-warning'><?php eT('Warnings'); ?></div>
                    <p>
                        <ul class="list-unstyled">
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
                    </p> 
                    <?php } ?>

                    <p>
                        <input class="btn btn-large btn-default" type='button' value='<?php eT("Display tokens"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" /><br />
                    </p>                                  
                </div>                
            <?php endif;?>
    </div>
</div>    