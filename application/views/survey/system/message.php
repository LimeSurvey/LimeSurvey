<?php
/**
 * $aMessage string[]
 *
 */
?>
<?php foreach($aMessage as $key=>$message) : ?>
<p class="message-<?php echo $key; ?>"><?php echo $message; ?></p>
<?php endforeach; ?>
