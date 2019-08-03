<div class="<?php echo $sizeClass; ?> ls-flex-column ls-panelboxes-panelbox text-primary" >
    <div class="panel panel-primary panel-clickable ls-panelboxes-panelbox-inner selector__<?php echo CHtml::encode(str_replace(' ', '_', strtolower(strip_tags($title)))) ?>"
        id="panel-<?php echo $position; ?>"
        data-url="<?php echo CHtml::encode($url); ?>"
        <?php if ($external): ?>
            data-target="_blank"
        <?php endif; ?>
    >
        <div class="panel-heading">
            <div class="panel-title h4"><?php eT($title); ?></div>
        </div>
        <div class="panel-body">
            <div class="panel-body-ico">
                <span class="sr-only"><?php eT($title); ?></span>
                <span class="<?php echo CHtml::encode($ico); ?>" style="font-size: 4em">
                </span>
            </div>
            <div class="panel-body-link">
                <?php eT($description); ?>
                <?php if ($external): ?>
                    &nbsp;<i class="fa fa-external-link"></i>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
