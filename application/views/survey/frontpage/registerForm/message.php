<?php
/**
 * message
 * @todo : some cmlass ? Review class ?
 */
?>
<?php if($sStartDate) : ?>
  <p><?php echo sprintf(gT("You may register for this survey but you have to wait for the %s before starting the survey."),$sStartDate); ?></p>
<?php else: ?>
  <p><?php echo gT("You may register for this survey if you wish to take part."); ?></p>
<?php endif; ?>
<p><?php echo gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately."); ?></p>
