<?php

/** @var $surveyid int */

?>

<div class="jumbotron message-box message-box-error">
        <h2><?php eT("Add answer");?>: <?php eT("Question selection");?></h2>
        <p class="lead"><?php eT("Sorry, there are no supported question types in this survey.");?></p>
        <p>
            <input  class="btn btn-lg btn-primary" type="submit" onclick="window.open('<?php echo $this->createUrl("quotas/index/surveyid/$surveyid");?>', '_top')" value="<?php eT("Continue");?>"/>
        </p>
</div>
