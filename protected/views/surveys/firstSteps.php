<div class='col-md-4 col-md-offset-4'>
    <h2><?php echo sprintf(gT("Welcome to %s!"), 'LimeSurvey'); ?></h2>
    <?php
        echo TbHtml::tag('p', [], gT("Some piece-of-cake steps to create your very own first survey:")); 
        
        
        $items = [
            sprintf(gT('Create a new survey by clicking on the %s icon in the upper right.'), TbHtml::icon('plus')),
            gT('Create a new question group inside your survey.'),
            gT('Create one or more questions inside the new question group.'),
            sprintf(gT('Done. Test your survey using the %s icon.'), "<img src='" . Yii::app()->getConfig('adminimageurl') . "do_20.png' name='ShowHelp' title='' alt='" . gT("Test survey") . "'/>")
        ];
        echo TbHtml::openTag('ol');
        foreach($items as $item) {
            echo TbHtml::tag('li', [], $item);
        }
        echo TbHtml::closeTag('ol');
        
    ?>
</div>
