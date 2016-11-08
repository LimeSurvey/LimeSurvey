<?php
/**
 * This file render the asterisk for mandatory questions
 */
?>
<!-- Add a visual information + just Mandatory string for aria : can be improved -->
<div class="asterisk pull-left">
  <small class="text-danger fa fa-asterisk small" aria-hidden='true'></small>
  <span class="sr-only text-danger"> <?php echo gT("(This question is mandatory)"); ?> </span>
</div>
