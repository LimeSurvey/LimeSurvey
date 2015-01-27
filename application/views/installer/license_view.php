<?php 
$this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); 
?>

<div class="col-md-9">
        <?php
            echo CHtml::tag('h2', array(), $title);
            echo CHtml::tag('p', array(), $descp);
            $license = file_get_contents(Yii::getPathOfAlias('application') . '/../docs/license.txt');
            echo CHtml::tag('div', [
                'style' => 'white-space: pre; max-height: 500px; overflow: auto;'
            ], $license);
        ?>
    
        <?php echo CHtml::form(array("installer/license"), 'post', array('name'=>'formcheck')); ?>


            <div class="row navigator">
            <div class="span3">
                <input class="btn" type="button" value="<?php eT("Previous"); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/install/welcome"); ?>', '_top')" />
            </div>
            <div class="span3"></div>
            <div class="span3">
                <input class="btn"  type="submit" value="<?php eT('I accept'); ?>" />
            </div>
            </div>
        </form>
    </div>
