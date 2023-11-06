<div class="row">
    <div class="col-md-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-9">
        <?php
            echo CHtml::tag('h2', array(), $title);
            echo CHtml::tag('p', array(), $descp);
        ?>
        <iframe src="<?php echo $this->createUrl('installer/viewlicense'); ?>" style="height: 268px; width: 100%; border-width: 0px;"> </iframe>
        <?php echo CHtml::form(array("installer/license"), 'post', array('name'=>'formcheck')); ?>


            <div class="row navigator">
            <div class="col-md-4">
                <input id="ls-previous" class="btn btn-default" type="button" value="<?php eT("Previous"); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/install/welcome"); ?>', '_top')" />
            </div>
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <input id="ls-accept-license" class="btn btn-default" type="submit" value="<?php eT('I accept'); ?>" />
            </div>
            </div>
        </form>
    </div>
</div>
