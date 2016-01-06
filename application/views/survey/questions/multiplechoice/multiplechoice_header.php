<?php
/**
 * Multiple Choice Html : Header
 *
 * @var $ia
 * @var $anscount
 */
?>

<!-- Multiple choice header -->
<div class="row multiple-choice-container">
    <div class="col-xs-12 subquestions-list questions-list checkbox-list">
        <input type="hidden" name="MULTI<?php echo $ia[1]; ?>" value="<?php echo $anscount; ?>" />
