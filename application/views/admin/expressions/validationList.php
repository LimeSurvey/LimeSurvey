<div class="card">
<?php
    foreach($aExpressions as $aExpression)
    {
        if($aExpression['expression']!='')
        {
            echo CHtml::tag('div',array('class'=>"card-header "),false);
            echo CHtml::tag('h4',[],$aExpression['title']);
            echo CHtml::closeTag('div');
            echo CHtml::tag('div',array('class'=>"card-body"),false);
            echo CHtml::tag('div',[],$aExpression['expression']);
            echo CHtml::closeTag('div');

        }
    }
?>
</div>
