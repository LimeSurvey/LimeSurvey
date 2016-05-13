<!-- This input will be used to load dynamically via ajax the position widget-->
<input
    type="hidden"
    id="question_position_container"
    data-gid="<?php echo $this->oQuestionGroup->gid; ?>"
    data-url="<?php echo App()->createUrl($this->reloadAction);?>" />
