<?php
/**
 * General Survey Topbar
 *
 * @var string $rightSide the optional right side content
 * 
 */

$leftSideFixed = App()->getController()->renderPartial('/topbars/includes/surveyTopbarLeft_view', get_defined_vars(), true);

App()->getController()->renderPartial(
    '/topbars/baseTopbar_view',
    [
        'topbarId' => $topbarId,
        'leftSideContent' => $leftSideFixed . (!empty($leftSideContent) ? $leftSideContent : ''),
        'rightSideContent' => !empty($rightSideContent) ? $rightSideContent : '',
    ]
);
?>

