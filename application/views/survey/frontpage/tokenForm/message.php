<?php
/**
 * message
 *
 */
?>
<p class='text-info'>
<?php if(!isset($token)): ?>
    <?php echo gT("If you have been issued a token, please enter it in the box below and click continue."); ?>
<?php else: ?>
    <?php eT("Please confirm the token by answering the security question below and click continue."); ?>
<?php endif; ?>
</p>
