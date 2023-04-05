<?php

/**
 * @var $massiveActionTemplate string
 */
?>

<div id="bottom-scroller" class="content-right scrolling-wrapper">
    {items}
</div>
<div class="row mx-auto mt-4" id=''>
    <div class="col-md-4" id="massive-action-container">
        <?= $massiveActionTemplate ?>
    </div>
    <div class="col-md-4 ">{pager}</div>
    <div class="col-md-4 summary-container">{summary}</div>
</div>