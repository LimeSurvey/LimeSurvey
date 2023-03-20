<div class="<?php echo $sizeClass; ?> ls-flex-column ls-panelboxes-panelbox">
    <div class="card card-primary card-clickable ls-panelboxes-panelbox-inner selector__<?php echo CHtml::encode(str_replace(' ', '_', strtolower(strip_tags((string) $title)))) ?>" id="card-<?php echo $position; ?>" data-url="<?php echo CHtml::encode($url); ?>" <?php if ($external) : ?> data-target="_blank" <?php endif; ?>>
        <div class="card-header">
            <div class="card-title"><?php echo viewHelper::filterScript(gT($title)); ?></div>
        </div>
        <div class="card-body d-flex">
            <?php echo viewHelper::filterScript(gT($description)); ?>
        </div>
        <div class="card-footer d-flex">
            <button class="btn btn-outline-secondary" role="button">
                <i class="<?php echo CHtml::encode($ico); ?>"></i>
                <?php echo viewHelper::filterScript(gT($buttontext)); ?>
            </button>
        </div>
    </div>
</div>
