<?php
if ($screenname != 'welcome')
{
    echo App()->getController()->renderPartial("/survey/system/actionButton/movePrevious",array('value'=>"moveprev",'class'=>"ls-move-btn ls-move-previous-btn action--ls-button-previous"),true);
}
echo App()->getController()->renderPartial("/survey/system/actionButton/moveNext",array('value'=>"movenext",'class'=>"ls-move-btn ls-move-next-btn action--ls-button-submit"),true);
?>
