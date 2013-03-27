<div class="row">
    <div class="span3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>
    </div>
    <div class="span9">
        <?php
            echo CHtml::tag('h2', array(), $title);
            echo CHtml::tag('p', array(), $descp);
        ?>
        <iframe src="<?php echo $this->createUrl('installer/viewlicense'); ?>" style="height: 268px; width: 100%; border-width: 0px;"> </iframe>
        <?php echo CHtml::form(array("installer/license"), 'post', array('name'=>'formcheck')); ?>


            <div class="row navigator">
            <div class="span3">
                <input class="btn" type="button" value="<?php $clang->eT("Previous"); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/install/welcome"); ?>', '_top')" />
            </div>
            <div class="span3"></div>
            <div class="span3">
                <input class="btn"  type="submit" value="<?php $clang->eT('I accept'); ?>" />
            </div>
            </div>
        </form>
    </div>
</div>