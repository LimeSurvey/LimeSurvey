<?php
/**
 * General Survey Topbar
 *
 * @var string $rightSide the optional right side content
 * 
 */

$leftSideFixed = $this->render('includes/surveyTopbarLeft_view', get_defined_vars(), true);

$this->render(
    'baseTopbar_view',
    [
        'topbarId' => $topbarId,
        'leftSideContent' => $leftSideFixed . (!empty($leftSideContent) ? $leftSideContent : ''),
        'rightSideContent' => !empty($rightSideContent) ? $rightSideContent : '',
    ]
);
?>

