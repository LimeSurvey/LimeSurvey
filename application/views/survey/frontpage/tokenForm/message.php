<?php
/**
 * message
 *
 */
?>
<p class='text-info'>
<?php if(!isset($token)): ?>
    <?php echo gT("If you have been issued an access code, please enter it in the box below and click 'Continue'."); ?>
<?php else: ?>
    <?php eT("Please confirm the access code by answering the security question below and click 'Continue'."); ?>
<?php endif; ?>
</p>
