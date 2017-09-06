<div id='remotecontrol' class="row">
    <div class="col-xs-12">
    <div class="pagetitle h3"><?php echo sprintf(gT('RemoteControl is available using %s for transport and exposes the following functionality:'),$method); ?></div>
    <dl>
    <?php
    foreach ($list as $method => $info) {
        echo \CHtml::tag(
            "dt",
            array('class'=>"h4"),
            \CHtml::link($method,"#definition-{$method}",array(
                "role" => "button",
                "data-toggle" => "collapse",
                "aria-expanded" => "false",
                "aria-controls" => "definition-{$method}",
            ))
        );
        echo \CHtml::tag(
            "dd",
            array(
                "class" => "collapse",
                "id" => "definition-{$method}"
            ),
            \CHtml::tag("pre",array(),\CHtml::encode($info['description']))
        );
    }
    ?>
    </dl>
    </div>
</div>
