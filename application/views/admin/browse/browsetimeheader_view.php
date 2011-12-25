<div class="header ui-widget-header"><?php echo $clang->eT('Time statistics'); ?></div>
<script type='text/javascript'>
    var strdeleteconfirm='<?php echo $clang->eT('Do you really want to delete this response?', 'js'); ?>';
    var strDeleteAllConfirm='<?php echo $clang->eT('Do you really want to delete all marked responses?', 'js'); ?>';
</script>
<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Data view control"); ?></strong>
    </div>
    <div class='menubar-main'>
        <?php if (!CHttpRequest::getPost('sql'))
        { ?>
            <a href='<?php echo $this->createUrl("/admin/browse/sa/time/surveyid/$iSurveyId/start/0/limit/$limit"); ?>' title='<?php $clang->eT("Show start..."); ?>' >
                <img name='DataBegin' align='left' src='<?php echo $imageurl; ?>/databegin.png' alt='<?php $clang->eT("Show start..."); ?>' />
            </a>
            <a href='<?php echo $this->createUrl("/admin/browse/sa/time/surveyid/$iSurveyId/start/$last/limit/$limit"); ?>' title='<?php $clang->eT("Show previous.."); ?>'>
                <img name='DataBack' align='left'  src='<?php echo $imageurl; ?>/databack.png' alt='<?php $clang->eT("Show previous.."); ?>' />
            </a>
            <img src='<?php echo $imageurl; ?>/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt='' />
            <a href='<?php echo $this->createUrl("/admin/browse/sa/time/surveyid/$iSurveyId/start/$next/limit/$limit"); ?>' title='<?php $clang->eT("Show next..."); ?>'>
                <img name='DataForward' align='left' src='<?php echo $imageurl; ?>/dataforward.png' alt='<?php $clang->eT("Show next.."); ?>' />
            </a>
            <a href='<?php echo $this->createUrl("/admin/browse/sa/time/surveyid/$iSurveyId/start/$end/imit/$limit"); ?>' title='<?php $clang->eT("Show last..."); ?>'>
                <img name='DataEnd' align='left' src='<?php echo $imageurl; ?>/dataend.png' alt='<?php $clang->eT("Show last.."); ?>' />
            </a>
            <img src='<?php echo $imageurl; ?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
        <?php } ?>
        <form action='<?php echo $this->createUrl("/admin/browse/sa/time/surveyid/{$iSurveyId}"); ?>' id='browseresults' method='post'>
            <font size='1' face='verdana'>
            <img src='<?php echo $imageurl; ?>/blank.gif' width='31' height='20' border='0' hspace='0' align='right' alt='' />
            <?php $clang->eT("Records displayed:"); ?> <input type='text' size='4' value='$dtcount2' name='limit' id='<?php echo $limit; ?>' />
            <?php $clang->eT("Starting from:"); ?> <input type='text' size='4' value='<?php echo $start; ?>' name='start' id='start' />
            <input type='submit' value='<?php $clang->eT("Show"); ?>' />
            </font>
            <?php if (CHttpRequest::getPost('sql'))
            { ?>
                <input type='hidden' name='sql' value='<?php echo html_escape(CHttpRequest::getPost('sql')); ?>' />
            <?php } ?>
        </form>
    </div>
</div>

<form action='<?php echo $this->createUrl("/admin/browse/sa/time/surveyid/{$iSurveyId}"); ?>' id='resulttableform' method='post'>
