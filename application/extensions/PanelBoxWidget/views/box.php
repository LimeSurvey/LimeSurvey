<div class="col-lg-2 col-sm-3 rtl-no-left-margin <?php echo $offset; ?>">
    <!-- TODO: rtl-no-left-margin is a hack to avoid offset on both sides. Real solution: Include RTL converted bootstrap. -->
    <div class="panel panel-primary panel-clickable" id="panel-<?php echo $position; ?>"
         data-url="<?php echo $url; ?>"<?php if ($external) {
        echo ' data-target="_blank"';
    } ?> >
        <div class="panel-heading">
            <div class="panel-title"><?php eT($title); ?></div>
        </div>
        <div class="panel-body">
            <div class="panel-body-ico">
                <a href="<?php echo $url; ?>"<?php if ($external) {
                    echo ' target="_blank"';
                } ?>>
		<span class="sr-only"><?php eT($title); ?></span>
                <span class="icon-<?php echo $ico; ?>" style="font-size: 4em">
                </span>
                </a>
            </div>
            <div class="panel-body-link">
                <a href="<?php echo $url; ?>"<?php if ($external) {
                    echo ' target="_blank"';
                } ?>><?php eT($description); ?></a>
            </div>
        </div>
    </div>
</div>
