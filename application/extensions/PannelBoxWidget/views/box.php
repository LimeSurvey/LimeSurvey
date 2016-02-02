<div class="col-lg-2 col-sm-3 rtl-no-left-margin <?php echo $offset; ?>"> <!-- TODO: rtl-no-left-margin is a hack to avoid offset on both sides. Real solution: Include RTL converted bootstrap. -->
    <div class="panel panel-primary panel-clickable" id="pannel-<?php echo $position;?>" data-url="<?php echo $url; ?>" >
    <div class="panel-heading">
        <h3 class="panel-title"><?php eT($title);?></h3>
    </div>
    <div class="panel-body">
        <a  href="<?php echo $url; ?>" >
            <span class="icon-<?php echo $ico;?>" style="font-size: 4em">
            </span>
        </a><br/><br/>
        <a href="<?php echo $url; ?>"><?php eT($description);?></a>
    </div>
    </div>
</div>
