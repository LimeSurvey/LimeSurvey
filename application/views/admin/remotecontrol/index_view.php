<div id='remotecontrol' class="row">
    <div class="col-xs-12">
    <h3 class="pagetitle"><?php echo sprintf(gT('RemoteControl is available using %s for transport and exposes the following functionality:'),$method); ?></h3>
    <dl>
    <?php
    foreach ($list as $method => $info) {
        echo \CHtml::tag("dt",array('class'=>"h4"),$method);
        echo \CHtml::tag("dd",array(),\CHtml::tag("pre",array(),\CHtml::encode($info['description'])));
    }
    ?>
    </dl>
    </div>
</div>
