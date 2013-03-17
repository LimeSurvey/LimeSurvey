<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php echo $title; ?></strong>: (<?php echo $thissurvey['surveyls_title']; ?>)
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href='<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>'>
                <img src='<?php echo $sImageURL; ?>home.png' title='' alt='<?php $clang->eT("Return to survey administration"); ?>' /></a>
            <img src='<?php echo $sImageURL; ?>blank.gif' alt='' width='11' />
            <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />

            <?php if (hasSurveyPermission($surveyid, 'responses', 'read'))
                { ?>
                <a href='<?php echo $this->createUrl("admin/responses/sa/index/surveyid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>summary.png' title='' alt='<?php $clang->eT("Show summary information"); ?>' /></a>
                <?php } ?>
        </div>
        <?php if (hasSurveyPermission($surveyid, 'responses', 'read'))
            { ?>
        <ul class='sf-menu'>
            <?php if (count(Survey::model()->findByPk($surveyid)->additionalLanguages) == 0) { ?>
                <li><a href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid"); ?>'>
                        <img src='<?php echo $sImageURL; ?>document.png' title='' alt='<?php $clang->eT("Display Responses"); ?>' /></a></li>
                <?php } else { ?>
                <li><a href='#' accesskey='b'>
                        <img src='<?php echo $sImageURL;?>document.png' alt='<?php $clang->eT("Display Responses"); ?>' />
                    </a><ul>
                        <li><a accesskey='b' target='_blank' href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid"); ?>'>
                            <img src='<?php echo $sImageURL;?>document_30.png' alt=''/> <?php $clang->eT("Display Responses"); ?> </a><ul>
                                <?php foreach ($tmp_survlangs as $tmp_lang) { ?>
                                    <li>
                                        <a href="<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid/start/0/limit/50/order/asc/browselang/$tmp_lang"); ?>" accesskey='b'><img src='<?php echo $sImageURL;?>document_30.png' alt=''/> <?php echo getLanguageNameFromCode($tmp_lang, false); ?></a>
                                    </li>
                                    <?php } ?>
                            </ul></li>
                    </ul></li>
                <?php } ?>
            <?php if (count(Survey::model()->findByPk($surveyid)->additionalLanguages) == 0) { ?>
                <li><a href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid"); ?>'>
                        <img src='<?php echo $sImageURL; ?>viewlast.png' title='' alt='<?php $clang->eT("Display Last 50 Responses"); ?>' /></a></li>
                <?php } else { ?>
                <li><a href='#' accesskey='b'>
                        <img src='<?php echo $sImageURL;?>viewlast.png' alt='<?php $clang->eT("Display Last 50 Responses"); ?>' />
                    </a><ul>
                        <li><a accesskey='b' target='_blank' href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid/start/0/limit/50/order/desc"); ?>'>
                            <img src='<?php echo $sImageURL;?>document_30.png' alt=''/> <?php $clang->eT("Display Last 50 Responses"); ?> </a><ul>
                                <?php foreach ($tmp_survlangs as $tmp_lang) { ?>
                                    <li>
                                        <a href="<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid/start/0/limit/50/order/desc/browselang/$tmp_lang"); ?>" accesskey='b'><img src='<?php echo $sImageURL;?>document_30.png' alt=''/> <?php echo getLanguageNameFromCode($tmp_lang, false); ?></a>
                                    </li>
                                    <?php } ?>
                            </ul></li>
                    </ul></li>
                <?php } ?>                
                </ul>
            <?php } ?>
        <div class='menubar-left'>
            <?php 
                if (hasSurveyPermission($surveyid, 'responses', 'create'))
                { ?>
                <a href='<?php echo $this->createUrl("admin/dataentry/sa/view/surveyid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>dataentry.png' alt='<?php $clang->eT("Dataentry Screen for Survey"); ?>' /></a>
                <?php }
                if (hasSurveyPermission($surveyid, 'statistics', 'read'))
                { ?>
                <a href='<?php echo $this->createUrl("admin/statistics/sa/index/surveyid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>statistics.png' alt='<?php $clang->eT("Get statistics from these responses"); ?>' /></a>
                <?php if ($thissurvey['savetimings'] == "Y")
                    { ?>
                    <a href='<?php echo $this->createUrl("admin/responses/sa/time/surveyid/$surveyid"); ?>'>
                        <img src='<?php echo $sImageURL; ?>statistics_time.png' alt='<?php $clang->eT("Get time statistics from these responses"); ?>' /></a>
                    <?php }
            } ?>
            <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
            <?php if (hasSurveyPermission($surveyid, 'responses', 'export'))
                { ?>
                <a href='<?php echo $this->createUrl("admin/export/sa/exportresults/surveyid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>export.png' alt='<?php $clang->eT("Export results to application"); ?>' /></a>

                <a href='<?php echo $this->createUrl("admin/export/sa/exportspss/sid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>exportspss.png' alt="<?php $clang->eT("Export results to a SPSS/PASW command file"); ?>" /></a>

                <a href='<?php echo $this->createUrl("admin/export/sa/exportr/sid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>exportr.png' alt='<?php $clang->eT("Export results to a R data file"); ?>' /></a>
                <?php
                }
                if (hasSurveyPermission($surveyid, 'responses', 'create'))
                {
                ?>
                <a href='<?php echo $this->createUrl("admin/dataentry/sa/import/surveyid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>importold.png' alt='<?php $clang->eT("Import responses from a deactivated survey table"); ?>' /></a>
                <?php } ?>
            <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />

            <?php if (hasSurveyPermission($surveyid, 'responses', 'read'))
                { ?>
                <a href='<?php echo $this->createUrl("admin/saved/sa/view/surveyid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>saved.png' title='' alt='<?php $clang->eT("View Saved but not submitted Responses"); ?>' /></a>
                <?php }
                if (hasSurveyPermission($surveyid, 'responses', 'import'))
                { ?>
                <a href='<?php echo $this->createUrl("admin/dataentry/sa/vvimport/surveyid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>importvv.png' alt='<?php $clang->eT("Import a VV survey file"); ?>' /></a>
                <?php }
                if (hasSurveyPermission($surveyid, 'responses', 'export'))
                { ?>
                <a href='<?php echo $this->createUrl("admin/export/sa/vvexport/surveyid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>exportvv.png' title='' alt='<?php $clang->eT("Export a VV survey file"); ?>' /></a>
                <?php }
                if (hasSurveyPermission($surveyid, 'responses', 'delete') && $thissurvey['anonymized'] == 'N' && $thissurvey['tokenanswerspersistence'] == 'Y')
                { ?>
                <a href='<?php echo $this->createUrl("admin/dataentry/sa/iteratesurvey/surveyid/$surveyid"); ?>'>
                    <img src='<?php echo $sImageURL; ?>iterate.png' title='' alt='<?php $clang->eT("Iterate survey"); ?>' /></a>
                <?php } ?>
        </div>
    </div>
</div>
