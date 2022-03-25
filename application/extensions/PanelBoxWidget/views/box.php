<div class="<?php echo $sizeClass; ?> ls-flex-column ls-panelboxes-panelbox" >
    <div class="card panel-clickable ls-panelboxes-panelbox-inner selector__<?php echo CHtml::encode(str_replace(' ', '_', strtolower(strip_tags($title)))) ?>"
        id="card-<?php echo $position; ?>"
        data-url="<?php echo CHtml::encode($url); ?>"
        <?php if ($external): ?>
            data-target="_blank"
        <?php endif; ?>
    >
        <div class="card-header bg-primary text-white">
            <div class="card-title"><?php echo viewHelper::filterScript(gT($title)); ?></div>
        </div>
        <div class="card-body">
            <div class="card-body-ico">
                <span class="sr-only"><?php echo viewHelper::filterScript(gT($title)); ?></span>
                <span class="<?php echo CHtml::encode($ico); ?>" style="font-size: 4em">
                </span>
            </div>
        </div>
        <div class="card-footer">
            <div class="card-body-link">
                <?php echo viewHelper::filterScript(gT($description)); ?>
                <?php if ($external): ?>
                    &nbsp;<i class="fa fa-external-link"></i>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
