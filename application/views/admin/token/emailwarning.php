<div class='messagebox ui-corner-all'>
    <div class='warningheader'><?php $clang->eT("Warning"); ?></div><br />
    <form method='post' action='<?php echo $this->createUrl("/admin/tokens/sa/{$sSubAction}/surveyid/{$surveyid}"); ?>'>
        <?php $clang->eT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below."); ?><br /><br />
        <?php echo str_replace("{EMAILCOUNT}", "$lefttosend", $clang->gT("There are {EMAILCOUNT} emails still to be sent.")); ?>
        <br /><br />
        <input type='submit' value='<?php $clang->eT("Continue"); ?>' />
        <input type='hidden' name='ok' value="absolutely" />
        <input type='hidden' name='action' value="tokens" />
        <input type='hidden' name='bypassbademails' value="<?php echo CHttpRequest::getPost('bypassbademails'); ?>" />
        <?php
        //Include values for constraints minreminderdelay and maxremindercount if they exist
        if (!$bEmail)
        {
            if (intval(CHttpRequest::getPost('minreminderdelay')) != 0)
            { ?>
                <input type='hidden' name='minreminderdelay' value="<?php echo CHttpRequest::getPost('minreminderdelay'); ?>" />
            <?php }
            if (intval(CHttpRequest::getPost('maxremindercount')) != 0)
            { ?>
                <input type='hidden' name='maxremindercount' value="<?php echo CHttpRequest::getPost('maxremindercount'); ?>" />
            <?php }
        }
        ?>
        <?php if (!empty($tokenids)) { ?>
            <input type='hidden' name='tokenids' value="<?php echo $tids; ?>" />
        <?php } ?>
        <?php
        foreach ($aSurveyLangs as $language)
        {
            $message = html_escape($_POST['message_' . $language]);
            $subject = html_escape($_POST['subject_' . $language]); ?>
            <input type='hidden' name='from_<?php echo $language; ?>' value="<?php echo $_POST['from_' . $language]; ?>" />
            <input type='hidden' name='subject_<?php echo $language; ?>' value="<?php echo $_POST['subject_' . $language]; ?>" />
            <input type='hidden' name='message_<?php echo $language; ?>' value="<?php echo $message; ?>" />
        <?php } ?>
    </form>
</div>
