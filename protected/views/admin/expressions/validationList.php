<?php
    echo CHtml::tag('dl',array('class'=>"ui-widget ui-widget-content"),'',false);
    foreach($aExpressions as $aExpression)
    {
        if($aExpression['expression']!='')
        {
            echo CHtml::tag('dt',array('class'=>"ui-widget-header"),$aExpression['title']);
            echo CHtml::tag('dd',array('class'=>"ui-widget-content"),$aExpression['expression']);
        }
    }
    echo CHtml::closeTag('dl');
