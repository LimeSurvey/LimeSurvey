<div class="box-widget">
    <div class="box-widget-list">
        <?php $this->render('box', array('items' => $items)); ?>
    </div>
    <div class="btn-container <?php echo !$enableLoadMoreBtn ? 'd-none' : ''?>">
        <a href="#" id="load-more" data-page="1" data-limit="<?php echo $limit?>">Load more</a>
    </div>
</div>
